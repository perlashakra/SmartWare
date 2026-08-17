<?php

namespace App\Services\Excel;

use RuntimeException;

class InventoryColumnMapper {
    private array $aliases = [
        'name' => [
            'اسم المادة',
            'اسم المنتج',
            'اسم الصنف',
            'المادة',
            'الصنف',
            'الوصف',
            'asm_almad',
            'product name',
            'item name',
            'product',
            'item',
            'name',
        ],

        'sku' => [
            'رمز المادة',
            'رمز المنتج',
            'رمز الصنف',
            'كود المادة',
            'كود المنتج',
            'كود الصنف',
            'sku',
            'product code',
            'item code',
            'code',
        ],

        'unit' => [
            'الوحدة',
            'وحدة القياس',
            'وحدة المادة',
            'alohd',
            'unit',
            'measurement unit',
            'unit of measure',
            'uom',
        ],

        'quantity' => [
            'الكمية',
            'كمية المادة',
            'كمية المنتج',
            'الكمية المتوفرة',
            'الكمية الحالية',
            'alkmy',
            'quantity',
            'qty',
            'stock',
            'available quantity',
            'current quantity',
        ],

        'unit_price' => [
            'السعر',
            'سعر الوحدة',
            'سعر وحده',
            'سعر المادة',
            'سعر المنتج',
            'alsaar',
            'price',
            'unit price',
            'price per unit',
            'item price',
            'product price',
        ],

        'container_type' => [
            'نوع العبوة',
            'نوع الحاوية',
            'العبوة',
            'الحاوية',
            'container type',
            'packaging',
            'package type',
        ],
    ];

    public function map(array $row){
        $columns = array_keys($row);

        $mapping = [];

        foreach($this->aliases as $field => $possible_names){
            foreach($columns as $column){
                if($this->matches($column, $possible_names)){
                    $mapping[$field] = $column;
                    break;
                }
            }
        }

        if(!isset($mapping['name'])){
            throw new RuntimeException('Unsupported inventory format: product name column could not be identified.');
        }

        if(!isset($mapping['quantity'])){
            throw new RuntimeException('Unsupported inventory format: quantity column could not be identified.');
        }

        return[
            'name' => $this->value($row, $mapping, 'name'),
            'sku' => $this->value($row, $mapping, 'sku'),
            'unit' => $this->value($row, $mapping, 'unit'),
            'quantity' => $this->value($row, $mapping, 'quantity'),
            'unit_price' => $this->value($row, $mapping, 'unit_price'),
            'container_type' => $this->value($row, $mapping, 'container_type'),
        ];
    }

    private function value(array $row, array $mapping, string $field): mixed{
        if(!isset($mapping[$field])){
            return null;
        }

        $value = $row[$mapping[$field]] ?? null;

        if(is_string($value)){
            $value = trim($value);
        }

        return $value === '' ? null : $value;
    }

    private function matches(string $column, array $possible_names): bool{
        $column = $this->normalize($column);

        foreach($possible_names as $possible_name){
            if($column === $this->normalize($possible_name)){
                return true;
            }
        }

        return false;
    }

    private function normalize(string $value): string{
        $value = trim($value);

        $value = mb_strtolower($value);

        $value = preg_replace('/[\s_\-]+/u', '', $value);

        return $value;
    }

    public function isProductRow($row){
        return isset($row['asm_almad']);
    }
}