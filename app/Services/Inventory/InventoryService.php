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
    private function checkAndNotifyStockRisk(
        Inventory $inventory
    ): void {
        $inventory->loadMissing([
            'product',
            'section.warehouse',
        ]);

        $facility = $inventory->section?->warehouse;

        if (!$facility) {
            return;
        }

        $facility->stock_out_risk_count =
            $this->stock_out_risk($facility);

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

        if ($totalProductQuantity > 10) {
            return;
        }

        $manager = User::whereHas(
            'owner',
            function ($query) use ($facility) {
                $query->whereKey($facility->id);
            }
        )->first();

        if (!$manager) {
            return;
        }

        // TEMPORARILY STOP HERE.
        // We will put your existing AppNotification here
        // after checking its constructor.
    }
}