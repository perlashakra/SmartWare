<?php

namespace App\Services\Inventory;

use App\Models\Facility;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Section;
use App\Models\User;
use App\Notifications\StockOutRiskNotification;
use App\Notifications\AppNotification;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    /**
     * Set the inventory quantity based on an imported stock snapshot.
     *
     * Excel represents the current quantity, so this method SETS
     * the quantity instead of adding to the existing quantity.
     */
    public function setImportedStock(
        Section $section,
        Product $product,
        int|float $quantity,
        float|int $unitPrice = 0
    ): Inventory {
        if ($quantity < 0) {
            throw new InvalidArgumentException(
                'Inventory quantity cannot be negative.'
            );
        }

        return DB::transaction(function () use ($section, $product, $quantity, $unitPrice) {
            $inventory = Inventory::updateOrCreate(
                [
                    'section_id' => $section->id,
                    'product_id' => $product->id,
                ],
                [
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ]
            );

            $this->checkAndNotifyStockRisk($inventory);

            return $inventory->fresh();
        });
    }


    /**
     * Increase inventory quantity.
     */
    public function increaseStock(
        Section $section,
        Product $product,
        int|float $quantity,
        float|int|null $unitPrice = null
    ): Inventory {
        $this->validateProductBelongsToSectionCompany(
            $section,
            $product
        );

        if ($quantity <= 0) {
            throw new InvalidArgumentException(
                'The quantity to add must be greater than zero.'
            );
        }

        return DB::transaction(function () use ($section, $product, $quantity, $unitPrice) {
            $inventory = Inventory::firstOrCreate(
                [
                    'section_id' => $section->id,
                    'product_id' => $product->id,
                ],
                [
                    'quantity' => 0,
                    'unit_price' => $unitPrice ?? 0,
                ]
            );

            $inventory->quantity += $quantity;

            if ($unitPrice !== null) {
                $inventory->unit_price = $unitPrice;
            }

            $inventory->save();

            $this->checkAndNotifyStockRisk($inventory);

            return $inventory->fresh();
        });
    }


    /**
     * Decrease inventory quantity.
     */
    public function decreaseStock(
        Section $section,
        Product $product,
        int|float $quantity
    ): Inventory {
        $this->validateProductBelongsToSectionCompany(
            $section,
            $product
        );

        if ($quantity <= 0) {
            throw new InvalidArgumentException(
                'The quantity to remove must be greater than zero.'
            );
        }

        return DB::transaction(function () use ($section, $product, $quantity) {
            $inventory = Inventory::where('section_id', $section->id)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                throw new InvalidArgumentException(
                    'The product does not exist in this section.'
                );
            }

            if ($inventory->quantity < $quantity) {
                throw new InvalidArgumentException(
                    'Insufficient inventory. Available quantity: '
                    . $inventory->quantity
                );
            }

            $inventory->quantity -= $quantity;

            $inventory->save();

            $this->checkAndNotifyStockRisk($inventory);

            return $inventory->fresh();
        });
    }


    /**
     * Explicitly correct inventory.
     *
     * This should be used for physical stock-count corrections,
     * not normal receiving/shipping.
     */
    public function adjustStock(
        Inventory $inventory,
        int|float $newQuantity
    ): Inventory {
        if ($newQuantity < 0) {
            throw new InvalidArgumentException(
                'Inventory quantity cannot be negative.'
            );
        }

        return DB::transaction(function () use ($inventory, $newQuantity) {
            $inventory->quantity = $newQuantity;

            $inventory->save();

            $this->checkAndNotifyStockRisk($inventory);

            return $inventory->fresh();
        });
    }


    /**
     * Update inventory quantity and unit price.
     */
    public function updateInventoryDetails(
        Inventory $inventory,
        int|float $quantity,
        int|float $unitPrice
    ): Inventory {
        if ($quantity < 0) {
            throw new InvalidArgumentException(
                'Inventory quantity cannot be negative.'
            );
        }

        if ($unitPrice < 0) {
            throw new InvalidArgumentException(
                'Inventory unit price cannot be negative.'
            );
        }

        return DB::transaction(function () use ($inventory, $quantity, $unitPrice) {
            $inventory->quantity = $quantity;
            $inventory->unit_price = $unitPrice;

            $inventory->save();

            $this->checkAndNotifyStockRisk($inventory);

            return $inventory->fresh();
        });
    }


    /**
     * Ensure that a section only contains products belonging
     * to the section's company.
     */
    private function validateProductBelongsToSectionCompany(
        Section $section,
        Product $product
    ): void {
        if ($section->company_id !== $product->company_id) {
            throw new InvalidArgumentException(
                'You can only store products belonging to the same company as the section.'
            );
        }
    }


    /**
     * Calculate the number of products at stock-out risk
     * for a facility.
     *
     * A product is considered at risk when the total quantity
     * across all sections of the facility is <= 10.
     */
    public function stock_out_risk(Facility $facility): int
    {
        $products = $facility->sections
            ->flatMap(fn($section) => $section->inventories)
            ->groupBy('product_id');

        return $products
            ->filter(
                fn($inventories) =>
                $inventories->sum('quantity') <= 10
            )
            ->count();
    }


    /**
     * Check whether the changed product is at stock-out risk
     * and notify the facility manager if necessary.
     */
    private function checkAndNotifyStockRisk(Inventory $inventory): void
{
    $inventory->loadMissing([
        'product',
        'section.warehouse',
    ]);

    $facility = $inventory->section?->warehouse;

    if (!$facility) {
        return;
    }

    /*
     * Calculate the number of products currently at
     * stock-out risk in this facility.
     */
    $facility->stock_out_risk_count =
        $this->stock_out_risk($facility);


    /*
     * Calculate the total quantity of THIS product
     * across all sections of this facility.
     */
    $totalProductQuantity = Inventory::whereHas(
        'section',
        function ($query) use ($facility) {
            $query->where(
                'warehouse_id',
                $facility->id
            );
        }
    )
        ->where(
            'product_id',
            $inventory->product_id
        )
        ->sum('quantity');


    /*
     * Product is not at stock-out risk.
     */
    if ($totalProductQuantity > 10) {
        return;
    }


    /*
     * The facility owner/manager is stored in
     * facilities.user_id.
     */
    $manager = User::find($facility->user_id);

    if (!$manager) {
        return;
    }


    /*
     * Prevent duplicate unread stock-risk notifications
     * for the same product and facility.
     */
    $alreadyNotified = $manager->notifications()
        ->where(
            'type',
            AppNotification::class
        )
        ->whereNull('read_at')
        ->where(
            'data->type',
            'stock_out_risk'
        )
        ->where(
            'data->data->facility_id',
            $facility->id
        )
        ->where(
            'data->data->product_id',
            $inventory->product_id
        )
        ->exists();


    if ($alreadyNotified) {
        return;
    }


    /*
     * Queue the notification.
     *
     * AppNotification implements ShouldQueue,
     * so Laravel will process this through the queue.
     */
    $manager->notify(
        new AppNotification(
            title: 'Stock Out Risk',

            message:
                "{$inventory->product->name_en} is at risk of stock-out in "
                . "{$facility->facility_name_en}. "
                . "Current quantity: {$totalProductQuantity}.",

            type: 'stock_out_risk',

            data: [
                'facility_id' => $facility->id,

                'facility_name' =>
                    $facility->facility_name_en,

                'product_id' =>
                    $inventory->product_id,

                'product_name' =>
                    $inventory->product->name_en,

                'quantity' =>
                    $totalProductQuantity,

                'stock_out_risk_count' =>
                    $facility->stock_out_risk_count,
            ],
        )
    );
}
}