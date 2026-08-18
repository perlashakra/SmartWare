<?php

namespace App\Imports;

use App\Models\Inventory;
use App\Models\Product;
use App\Services\Excel\InventoryColumnMapper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InventoryImport implements ToModel, WithHeadingRow
{
    public function __construct(
        private int $section_id,
        private int $company_id
    ) {}

    public function model(array $row)
    {
        //logger()->info('excel row', $row);
        $mapper = new InventoryColumnMapper();
        if(!$mapper->isProductRow($row)){
            return null;
        }

        $data = $mapper->map($row);

        $name = $data['name'];
        $sku = $data['sku'];
        $unit = $data['unit'];
        $quantity = $data['quantity'];
        $unit_price = $data['unit_price'];

        //ignore empty product rows
        if($name === null){
            return null;
        }

        //find product
        if($sku !== null){
            $product = Product::where('company_id', $this->company_id)->where('sku', $sku)->first();
        } else {
            $query = Product::where('company_id', $this->company_id)->where('name_ar', $name);
            if($unit !== null){
                $query->where('unit', $unit);
            }
            $product = $query->first();
        }

        //create product if doesn't exist
        if(!$product){
            $product = Product::create([
                'company_id' => $this->company_id,
                'sku' => $sku ?? 'pr-'.Str::uuid(),
                'name_ar' => $name,
                'unit' => $unit,
            ]);

            //generate sku if not exists
            if($sku === null){
                $product->update([
                    'sku' => 'WMS'.str_pad($product->id, 6, '0', STR_PAD_LEFT),
                ]);
            }
        }

        //update or create inventory
        DB::table('inventories')->upsert(
            [[
                'section_id' => $this->section_id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unit_price,
                'created_at' => now(),
                'updated_at' => now(),
            ]],
            ['section_id', 'product_id'],
            ['quantity', 'unit_price', 'updated_at']
        );

        return null;
    }
}

