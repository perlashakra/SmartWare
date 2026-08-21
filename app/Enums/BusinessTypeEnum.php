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
                //FOOD
                CategoryEnum::CANNED_FOODS,
                CategoryEnum::FRESH_FOODS,
                CategoryEnum::REFRIGERATED_FOODS,
                CategoryEnum::FROZEN_FOODS,
                CategoryEnum::FRUITS_VEGETABLES,
                CategoryEnum::MEAT_POULTRY,
                CategoryEnum::SEAFOOD,
                CategoryEnum::DAIRY_PRODUCTS,
                CategoryEnum::BAKERY_PRODUCTS,
                CategoryEnum::SNACKS,
                CategoryEnum::SPICES_SEASONINGS,
                //BEVERAGES
                CategoryEnum::BEVERAGES,
                CategoryEnum::COFFEE_TEA,
                //CLEANING
                CategoryEnum::CLEANING_SUPPLIES,
            ],

            self::SUPERMARKET => [
                //FOOD
                CategoryEnum::CANNED_FOODS,
                CategoryEnum::FRESH_FOODS,
                CategoryEnum::REFRIGERATED_FOODS,
                CategoryEnum::FROZEN_FOODS,
                CategoryEnum::FRUITS_VEGETABLES,
                CategoryEnum::MEAT_POULTRY,
                CategoryEnum::SEAFOOD,
                CategoryEnum::DAIRY_PRODUCTS,
                CategoryEnum::BAKERY_PRODUCTS,
                CategoryEnum::SNACKS,
                CategoryEnum::SPICES_SEASONINGS,
                //BEVERAGES
                CategoryEnum::BEVERAGES,
                CategoryEnum::COFFEE_TEA,
                //CLEANING
                CategoryEnum::CLEANING_SUPPLIES,
                //PERSONAL CARE
                CategoryEnum::PERSONAL_CARE,
                CategoryEnum::PERSONAL_HYGIENE,
                CategoryEnum::BODY_CARE,
                //PET SUPPLIES
                CategoryEnum::PET_SUPPLIES,
            ],

            self::PHARMACY => [
                //MEDICINE
                CategoryEnum::MEDICINE,
                CategoryEnum::VITAMINS_SUPPLEMENTS,
                CategoryEnum::PRESCRIPTION_MEDICINE,
                CategoryEnum::OVER_THE_COUNTER_MEDICINE,
                //PERSONAL CARE
                CategoryEnum::PERSONAL_CARE,
                CategoryEnum::PERSONAL_HYGIENE,
                CategoryEnum::BODY_CARE,
                //MEDICAL SUPPLIES
                CategoryEnum::MEDICAL_EQUIPMENT,
                CategoryEnum::FIRST_AID_SUPPLIES,
                CategoryEnum::SURGICAL_SUPPLIES,
                //BABY CARE
                CategoryEnum::BABY_CARE,
                CategoryEnum::BABY_PRODUCTS,
                //COSMETICS
                CategoryEnum::COSMETICS,
            ],

            self::ELECTRONICS_STORE => [
                //COMPUTERS
                CategoryEnum::LAPTOPS,
                CategoryEnum::DESKTOP_COMPUTERS,
                CategoryEnum::TABLETS,
                CategoryEnum::COMPUTER_ACCESSORIES,
                //PHONES
                CategoryEnum::SMARTPHONES,
                CategoryEnum::PHONE_ACCESSORIES,
                //AUDIO
                CategoryEnum::AUDIO_DEVICES,
                //SMART HOME
                CategoryEnum::SMART_HOME_DEVICES,
                //NETWORKING
                CategoryEnum::NETWORK_EQUIPMENT,
                //ELECTRONIC PARTS
                CategoryEnum::ELECTRONIC_PARTS,
                CategoryEnum::BATTERIES,
            ],

            self::MAKEUP_STORE => [
                //BEAUTY
                CategoryEnum::COSMETICS,
                CategoryEnum::MAKEUP,
                CategoryEnum::SKINCARE,
                CategoryEnum::HAIR_CARE,
                CategoryEnum::PERFUMES,
                CategoryEnum::NAIL_PRODUCTS,
                CategoryEnum::BEAUTY_TOOLS,
                CategoryEnum::PROFESSIONAL_BEAUTY_PRODUCTS,
                //PERSONAL CARE
                CategoryEnum::PERSONAL_CARE,
                CategoryEnum::PERSONAL_HYGIENE,
                CategoryEnum::BODY_CARE,
            ],
            self::CLOTHING_STORE => [
                // CLOTHING
                CategoryEnum::MENS_CLOTHING,
                CategoryEnum::WOMENS_CLOTHING,
                CategoryEnum::KIDS_CLOTHING,
                CategoryEnum::SPORTSWEAR,
                CategoryEnum::UNDERWEAR,
                CategoryEnum::SEASONAL_FASHION,
                CategoryEnum::FABRIC_MATERIALS,

                // SHOES
                CategoryEnum::SHOES,

                // ACCESSORIES
                CategoryEnum::BAGS,
                CategoryEnum::ACCESSORIES,
                CategoryEnum::JEWELRY,
            ],

            self::FURNITURE_STORE => [
                //FURNITURE
                CategoryEnum::HOME_FURNITURE,
                CategoryEnum::OFFICE_FURNITURE,
                CategoryEnum::BEDROOM_FURNITURE,
                CategoryEnum::LIVING_ROOM_FURNITURE,
                CategoryEnum::KITCHEN_FURNITURE,
                CategoryEnum::OUTDOOR_FURNITURE,
                CategoryEnum::MATTRESSES,
                CategoryEnum::WOOD_MATERIALS,
                CategoryEnum::FURNITURE_ACCESSORIES,
                //HOME DECOR
                CategoryEnum::HOME_DECOR,
                //LIGHTING
                CategoryEnum::LIGHTING,
            ],
        };
    }
}
