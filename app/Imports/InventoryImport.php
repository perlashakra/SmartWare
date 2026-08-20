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
         * If an SKU exists, it is the product identity.
         */
        if ($sku !== null) {

            $product = Product::where('sku', $sku)->first();

            if ($product) {
                if($product->name_ar !== null && $name !== null && $product->name_ar !== $name){
                    throw new \InvalidArgumentException("SKU '{$sku}' already exists but the product name does not match.");
                }    

                if($product->unit !== null && $unit !== null && $product->unit !== $unit){
                    throw new \InvalidArgumentException("SKU '{$sku}' already exists but the product unit does not match.");
                }
                
            } else {
                $product = null;
            }

            if (!$product) {
                $product = Product::create([
                    'sku' => $sku ?? 'WMS'.strtoupper(Str::random(10)),
                    'name_ar' => $name,
                    'unit' => $unit,
                ]);
            }

        }

        //Existing inventory → update quantity/price.
        //Missing inventory → create inventory.
        
        $this->inventoryService->setImportedStock(
            $this->section,
            product: $product,
            quantity: $quantity,
            unitPrice: $unitPrice
        );

        return null;
    }
}