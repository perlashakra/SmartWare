<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Section;
use App\Services\Excel\InventoryColumnMapper;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InventoryImport implements ToModel, WithHeadingRow
{
    public function __construct(
        private Section $section,
        private InventoryService $inventoryService,
    ) {}

    public function model(array $row)
    {
        $mapper = new InventoryColumnMapper();

        if (!$mapper->isProductRow($row)) {
            return null;
        }

        $data = $mapper->map($row);

        $name = $data['name'];
        $sku = $data['sku'];
        $unit = $data['unit'];
        $quantity = $data['quantity'];
        $unitPrice = $data['unit_price'];

        // Ignore empty product rows.
        if ($name === null) {
            return null;
        }

        $section = $this->section;
        $companyId = $section->company_id;

        /*
         * If an SKU exists, it is the product identity.
         */
        if ($sku !== null) {

            $product = Product::where('sku', $sku)->first();

            /*
             * Same SKU exists but belongs to another company.
             * This is an import conflict and must not create
             * another product with the same SKU.
             */
            if ($product && $product->company_id !== $companyId) {
                throw new \InvalidArgumentException(
                    "SKU '{$sku}' already belongs to another company."
                );
            }

            /*
             * Same SKU and same company.
             */
            if (!$product) {
                $product = Product::create([
                    'company_id' => $companyId,
                    'sku' => $sku,
                    'name_ar' => $name,
                    'unit' => $unit,
                ]);
            }

        } else {

            /*
             * No SKU was provided.
             *
             * Fall back to company + name + unit.
             */
            $query = Product::where('company_id', $companyId)
                ->where('name_ar', $name);

            if ($unit !== null) {
                $query->where('unit', $unit);
            }

            $product = $query->first();

            /*
             * Product doesn't exist, so create one and
             * generate an internal SKU.
             */
            if (!$product) {
                $product = Product::create([
                    'company_id' => $companyId,
                    'sku' => 'pr-' . Str::uuid(),
                    'name_ar' => $name,
                    'unit' => $unit,
                ]);

                $product->update([
                    'sku' => 'WMS' . str_pad(
                        $product->id,
                        6,
                        '0',
                        STR_PAD_LEFT
                    ),
                ]);
            }
        }

        /*
         * Final business-rule safety check.
         */
        if ($section->company_id !== $product->company_id) {
            throw new \InvalidArgumentException(
                "Product '{$product->name_ar}' does not belong to the section's company."
            );
        }

        /*
         * Excel represents a stock snapshot.
         *
         * Existing inventory → replace quantity/price.
         * Missing inventory → create inventory.
         */
        $this->inventoryService->setImportedStock(
            section: $section,
            product: $product,
            quantity: $quantity,
            unitPrice: $unitPrice
        );

        return null;
    }
}