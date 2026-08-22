<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Business rules enforced here:
     *
     * 1. warehouse_admins may only own facilities of type "warehouse"
     *    (mirrors FacilityController::store's role check).
     * 2. A user's very first facility - the one created through onboarding -
     *    is never inserted already "approved". It always starts life
     *    pending/submitted admin review.
     * 3. A user may only be given an additional facility once every facility
     *    they already own has been approved. So for any owner who ends up
     *    with N facilities here, the first N-1 (in creation order) are
     *    "approved" and only the LAST one is allowed to sit in
     *    pending/submitted/rejected.
     * 4. account_status/onboarding_complete are kept in sync with the
     *    outcome of the FIRST facility, mirroring AdminController::reviewRequest
     *    (approving/rejecting a user's registration approves/rejects their
     *    facilities together). A later, separately-added facility can still
     *    be independently pending without touching account_status again
     *    (that's handled by AdminController::reviewFacility instead).
     */
    public function run(): void
    {
        $admins = User::where('role', 'warehouse_admin')->orderBy('id')->get();
        $addressIds = Address::orderBy('id')->pluck('id')->toArray();

        if ($admins->isEmpty() || empty($addressIds)) {
            $this->command->warn('No warehouse_admin users or addresses found. Skipping WarehouseSeeder.');
            return;
        }

        $addrCursor = 0;
        $nextAddress = function () use ($addressIds, &$addrCursor) {
            $id = $addressIds[$addrCursor % count($addressIds)];
            $addrCursor++;
            return $id;
        };

        $namePool = [
            'Central Logistics Facility', 'Northern Cold Storage', 'East Distribution Hub',
            'Southern Fulfillment Center', 'Riverside Storage Depot', 'Metro Freight Warehouse',
            'Highland Bulk Storage', 'Coastal Import Warehouse', 'Industrial Zone Depot',
            'Downtown Cross-Dock Facility', 'Skyline Storage Complex', 'Valley Distribution Point',
        ];

        foreach ($admins as $index => $admin) {
            if ($index === 0) {
                // Explicit scenario user (Nicole): owns two already-approved
                // warehouses plus a third that is still awaiting review.
                // This is the canonical "multi-facility owner" example.
                $statuses = ['approved', 'approved', 'pending'];
            } elseif ($index === 1) {
                // Explicit scenario user: first facility got rejected on review,
                // so the account never advanced (account_status => deleted)
                // and they were never allowed to submit a second warehouse.
                $statuses = ['rejected'];
            } elseif ($index === 2) {
                // Explicit scenario user: first facility approved, second one
                // freshly submitted and still pending - the "in progress"
                // multi-facility case.
                $statuses = ['approved', 'submitted'];
            } else {
                // Remaining admins: mostly a single warehouse, occasionally two.
                $count = random_int(1, 10) <= 3 ? 2 : 1;
                $statuses = [];
                for ($i = 0; $i < $count; $i++) {
                    $statuses[] = $i < $count - 1
                        ? 'approved'
                        : collect(['approved', 'approved', 'pending', 'submitted'])->random();
                }
            }

            foreach ($statuses as $n => $status) {
                Facility::create([
                    'address_id' => $nextAddress(),
                    'user_id' => $admin->id,
                    'facility_type' => 'warehouse',
                    'facility_status' => $status,
                    'business_type' => 'warehouse',
                    'facility_name_en' => $namePool[($index + $n) % count($namePool)] . ($n > 0 ? ' ' . ($n + 1) : ''),
                ]);
            }

            $accountStatus = match ($statuses[0]) {
                'approved' => 'approved',
                'rejected' => 'deleted',
                default => 'pending', // pending or submitted
            };

            // Bulk update via the query builder so 'onboarding_complete' -
            // which is intentionally NOT in User::$fillable - actually gets set.
            User::where('id', $admin->id)->update([
                'account_status' => $accountStatus,
                'onboarding_complete' => true,
            ]);
        }
    }
}
