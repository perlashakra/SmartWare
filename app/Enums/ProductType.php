<?php

namespace App\Enums;

enum ProductType: string {
    case CANNED_FOODS = 'canned_foods';
    case REFRIGERATED_FOODS = 'refrigerated_foods';
    case FRESH_FOODS = 'fresh_foods';
    case BEVERAGES = 'beverages';
    case MEDICINE = 'medicine'; // Exclusive
    case MEDICAL_SUPPLIES = 'medical_supplies'; // Exclusive
    case COSMETICS = 'cosmetics';
    case CLOTHING = 'clothing';
    case ELECTRONICS = 'electronics';
    case FURNITURE = 'furniture';
}
