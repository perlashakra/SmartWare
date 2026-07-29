<?php

namespace App\Enums;

enum BusinessTypeEnum: string {
    case RESTAURANT = 'restaurant';
    case PHARMACY = 'pharmacy';
    case CLOTHING_STORE = 'clothing_store';
    case ELECTRONICS_STORE = 'electronics_store';
    case SUPERMARKET = 'supermarket';
    case MAKEUP_STORE = 'makeup_store';
    case FURNITURE_STORE = 'furniture_store';

    public function categories(){

        return match($this){
            self::RESTAURANT => [
                CategoryEnum::FOOD,
                CategoryEnum::BEVERAGES,
                CategoryEnum::CLEANING,
            ],

            self::SUPERMARKET => [
                CategoryEnum::FOOD,
                CategoryEnum::BEVERAGES,
                CategoryEnum::CLEANING,
                CategoryEnum::PERSONAL_CARE,
                CategoryEnum::PET_SUPPLIES,
            ],

            self::PHARMACY => [
                CategoryEnum::MEDICINE,
                CategoryEnum::PERSONAL_CARE,
                CategoryEnum::MEDICAL_SUPPLIES,
                CategoryEnum::BABY_CARE,
                CategoryEnum::COSMETICS,
            ],

            self::ELECTRONICS_STORE => [
                CategoryEnum::COMPUTERS,
                CategoryEnum::PHONES,
                CategoryEnum::AUDIO,
                CategoryEnum::SMART_HOME,
                CategoryEnum::NETWORKING,
                CategoryEnum::ELECTRONIC_PARTS,
            ],

            self::MAKEUP_STORE => [
                CategoryEnum::BEAUTY,
                CategoryEnum::PERSONAL_CARE,
            ],

            self::FURNITURE_STORE => [
                CategoryEnum::FURNITURE,
                CategoryEnum::HOME_DECOR,
                CategoryEnum::LIGHTING,
            ],
        };
    }
}
