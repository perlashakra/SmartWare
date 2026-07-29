<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    //Translate Excel rows into your application
    //excel row -> product model
    public function model(array $row)
    {
        logger($row);
        return null;
    //     return new Product([
    //         'sku' => $row['رمز المادة'],
    //         'name' => $row['اسم المادة'],
    //         'price' => $row['السعر'],
    //         'container_type' => $row['الوحدة'],
    //         'product_type' => $row['نوع المادة'],
    //     ]);
     }

    //create or update the product
    //update the inventory quantity

    public function uniqueBy(){
        return 'sku';
    }

    public function upsertColumns(){
        return ['name', 'price', 'container_type', 'product_type'];
    }
}

