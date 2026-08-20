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

        /*
         * SKU exists:
         * SKU is the product identity.
         */
        if ($sku !== null) {

            $product = Product::where('sku', $sku)->first();

            if ($product) {

                // Existing SKU but different product name.
                if (
                    $product->name_ar !== null &&
                    $name !== null &&
                    $product->name_ar !== $name
                ) {
                    throw new \InvalidArgumentException(
                        "SKU '{$sku}' already exists but the product name does not match."
                    );
                }

                // Existing SKU but different unit.
                if (
                    $product->unit !== null &&
                    $unit !== null &&
                    $product->unit !== $unit
                ) {
                    throw new \InvalidArgumentException(
                        "SKU '{$sku}' already exists but the product unit does not match."
                    );
                }

            } else {

                // SKU does not exist → create product.
                $product = Product::create([
                    'sku' => $sku,
                    'name_ar' => $name,
                    'unit' => $unit,
                ]);
            }

        } else {

            /*
             * No SKU in Excel.
             *
             * Try to identify an existing product using
             * the available product information.
             */
            $query = Product::where('name_ar', $name);

            if ($unit !== null) {
                $query->where('unit', $unit);
            }

            $product = $query->first();

            /*
             * No matching product → create one and generate
             * a WMS SKU for it.
             */
            if (!$product) {
                $product = Product::create([
                    'sku' => 'WMS' . strtoupper(Str::random(10)),
                    'name_ar' => $name,
                    'unit' => $unit,
                ]);
            }
        }

        /*
         * Existing inventory → update quantity/price.
         * Missing inventory → create inventory.
         *
         * Excel represents the current stock snapshot,
         * so quantity is SET, not added.
         */
        $this->inventoryService->setImportedStock(
            $this->section,
            product: $product,
            quantity: $quantity,
            unitPrice: $unitPrice
        );

        return null;
    }
}