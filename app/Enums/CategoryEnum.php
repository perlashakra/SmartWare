<?php

namespace App\Enums;

enum CategoryEnum : string
{
    case FOOD = 'food';
    case BEVERAGES = 'beverages';
    case CLEANING = 'cleaning'; 
    case PET_SUPPLIES = 'pet_supplies'; 
    case MEDICINE = 'medicine';
    case MEDICAL_SUPPLIES = 'medical_supplies';
    case PERSONAL_CARE = 'personal_care';
    case BABY_CARE = 'baby_care';
    case COSMETICS = 'cosmetics';
    case BEAUTY = 'beauty';
    case CLOTHING = 'clothing';
    case SHOES = 'shoes';
    case ACCESSORIES = 'accessories';
    case COMPUTERS = 'computers';
    case PHONES = 'phones';
    case AUDIO = 'audio';
    case SMART_HOME = 'smart_home';
    case NETWORKING = 'networking';
    case ELECTRONIC_PARTS = 'electronic_parts';
    case FURNITURE = 'furniture';
    case HOME_DECOR = 'home_decor';
    case LIGHTING = 'lighting';
    
    //$productType->category()->businessType();
    public function productTypes(){
        return match($this){
            self::FOOD => [
                ProductType::CANNED_FOODS,
                ProductType::FRESH_FOODS,
                ProductType::REFRIGERATED_FOODS,
                ProductType::FROZEN_FOODS,
                ProductType::FRUITS_VEGETABLES,
                ProductType::MEAT_POULTRY,
                ProductType::SEAFOOD,
                ProductType::DAIRY_PRODUCTS,
                ProductType::BAKERY_PRODUCTS,
                ProductType::SNACKS,
                ProductType::SPICES_SEASONINGS,
            ],

            self::BEVERAGES =>[
                ProductType::BEVERAGES,
                ProductType::COFFEE_TEA,
            ],
            
            self::CLEANING =>[
                ProductType::CLEANING_SUPPLIES,
            ],
            
            self::MEDICINE =>[
                ProductType::MEDICINE,
                ProductType::VITAMINS_SUPPLEMENTS,
                ProductType::PRESCRIPTION_MEDICINE,
                ProductType::OVER_THE_COUNTER_MEDICINE,
            ],
            
            self::MEDICAL_SUPPLIES =>[
                ProductType::MEDICAL_EQUIPMENT,
                ProductType::FIRST_AID_SUPPLIES,
                ProductType::SURGICAL_SUPPLIES,
            ],
            
            self::PERSONAL_CARE =>[
                ProductType::PERSONAL_CARE,
                ProductType::PERSONAL_HYGIENE,
                ProductType::BODY_CARE,
            ],

            self::BABY_CARE =>[
                ProductType::BABY_CARE,
                ProductType::BABY_PRODUCTS,
            ],
            
            self::COSMETICS,

            self::BEAUTY => [
                ProductType::COSMETICS,
                ProductType::MAKEUP,
                ProductType::SKINCARE,
                ProductType::HAIR_CARE,
                ProductType::PERFUMES,
                ProductType::NAIL_PRODUCTS,
                ProductType::BEAUTY_TOOLS,
                ProductType::PROFESSIONAL_BEAUTY_PRODUCTS,
            ],

            self::CLOTHING => [
                ProductType::MENS_CLOTHING,
                ProductType::WOMENS_CLOTHING,
                ProductType::KIDS_CLOTHING,
                ProductType::SPORTSWEAR,
                ProductType::UNDERWEAR,
                ProductType::SEASONAL_FASHION,
                ProductType::FABRIC_MATERIALS,
            ],

            self::SHOES => [
                ProductType::SHOES,
            ],

            self::ACCESSORIES => [
                ProductType::BAGS,
                ProductType::ACCESSORIES,
                ProductType::JEWELRY,
            ],
            
            self::COMPUTERS => [
                ProductType::LAPTOPS,
                ProductType::DESKTOP_COMPUTERS,
                ProductType::TABLETS,
                ProductType::COMPUTER_ACCESSORIES,
            ],

            self::PHONES => [
                ProductType::SMARTPHONES,
                ProductType::PHONE_ACCESSORIES,
            ],
            
            self::AUDIO => [ 
                ProductType::AUDIO_DEVICES,
            ],
            
            self::SMART_HOME => [
                ProductType::SMART_HOME_DEVICES,
            ],
            
            self::NETWORKING => [
                ProductType::NETWORK_EQUIPMENT,
            ],
            
            self::ELECTRONIC_PARTS => [
                ProductType::ELECTRONIC_PARTS,
                ProductType::BATTERIES,
            ],
            
            self::PET_SUPPLIES => [
                ProductType::PET_SUPPLIES,
            ],
            
            self::FURNITURE => [
                ProductType::HOME_FURNITURE,
                ProductType::OFFICE_FURNITURE,
                ProductType::BEDROOM_FURNITURE,
                ProductType::LIVING_ROOM_FURNITURE,
                ProductType::KITCHEN_FURNITURE,
                ProductType::OUTDOOR_FURNITURE,
                ProductType::MATTRESSES,
                ProductType::WOOD_MATERIALS,
                ProductType::FURNITURE_ACCESSORIES,
            ],

            self::HOME_DECOR => [
                ProductType::HOME_DECOR,
            ],

            self::LIGHTING => [
                ProductType::LIGHTING,
            ],
        };
    }
}
