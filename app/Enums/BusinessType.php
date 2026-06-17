<?php

namespace App\Enums;

enum BusinessType: string {
    case RESTAURANT = 'restaurant';
    case PHARMACY = 'pharmacy';
    case CLOTHING_STORE = 'clothing_store';
    case ELECTRONICS_STORE = 'electronics_store';
    case SUPERMARKET = 'supermarket';
    case MAKEUP_STORE = 'makeup_store';
    case FURNITURE_STORE = 'furniture_store';
}
