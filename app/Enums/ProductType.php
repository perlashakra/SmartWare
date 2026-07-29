<?php

namespace App\Enums;

enum ProductType: string {
// Food
    case CANNED_FOODS = 'canned_foods';
    case FRESH_FOODS = 'fresh_foods';
    case REFRIGERATED_FOODS = 'refrigerated_foods';
    case FROZEN_FOODS = 'frozen_foods';
    case FRUITS_VEGETABLES = 'fruits_vegetables';
    case MEAT_POULTRY = 'meat_poultry';
    case SEAFOOD = 'seafood';
    case DAIRY_PRODUCTS = 'dairy_products';
    case BAKERY_PRODUCTS = 'bakery_products';
    case SNACKS = 'snacks';
    case SPICES_SEASONINGS = 'spices_seasonings';

    // Drinks
    case BEVERAGES = 'beverages';
    case COFFEE_TEA = 'coffee_tea';

    // Packaging & Cleaning
    case PACKAGING_SUPPLIES = 'packaging_supplies';
    case CLEANING_SUPPLIES = 'cleaning_supplies';

    // Pharmacy
    case MEDICINE = 'medicine';
    case PRESCRIPTION_MEDICINE = 'prescription_medicine';
    case OVER_THE_COUNTER_MEDICINE = 'over_the_counter_medicine';
    case VITAMINS_SUPPLEMENTS = 'vitamins_supplements';
    case MEDICAL_EQUIPMENT = 'medical_equipment';
    case FIRST_AID_SUPPLIES = 'first_aid_supplies';
    case SURGICAL_SUPPLIES = 'surgical_supplies';

    // Personal Care
    case PERSONAL_CARE = 'personal_care';
    case PERSONAL_HYGIENE = 'personal_hygiene';
    case BODY_CARE = 'body_care';
    case BABY_CARE = 'baby_care';
    case BABY_PRODUCTS = 'baby_products';

    // Beauty
    case COSMETICS = 'cosmetics';
    case MAKEUP = 'makeup';
    case SKINCARE = 'skincare';
    case HAIR_CARE = 'hair_care';
    case PERFUMES = 'perfumes';
    case NAIL_PRODUCTS = 'nail_products';
    case BEAUTY_TOOLS = 'beauty_tools';
    case PROFESSIONAL_BEAUTY_PRODUCTS = 'professional_beauty_products';

    // Clothing
    case MENS_CLOTHING = 'mens_clothing';
    case WOMENS_CLOTHING = 'womens_clothing';
    case KIDS_CLOTHING = 'kids_clothing';
    case SPORTSWEAR = 'sportswear';
    case UNDERWEAR = 'underwear';
    case SEASONAL_FASHION = 'seasonal_fashion';
    case FABRIC_MATERIALS = 'fabric_materials';
    case SHOES = 'shoes';
    case BAGS = 'bags';
    case ACCESSORIES = 'accessories';
    case JEWELRY = 'jewelry';

    // Electronics
    case SMARTPHONES = 'smartphones';
    case LAPTOPS = 'laptops';
    case TABLETS = 'tablets';
    case DESKTOP_COMPUTERS = 'desktop_computers';
    case COMPUTER_ACCESSORIES = 'computer_accessories';
    case PHONE_ACCESSORIES = 'phone_accessories';
    case AUDIO_DEVICES = 'audio_devices';
    case SMART_HOME_DEVICES = 'smart_home_devices';
    case NETWORK_EQUIPMENT = 'network_equipment';
    case ELECTRONIC_PARTS = 'electronic_parts';
    case BATTERIES = 'batteries';

    // Supermarket
    case HOUSEHOLD_ITEMS = 'household_items';
    case PET_SUPPLIES = 'pet_supplies';

    // Furniture
    case HOME_FURNITURE = 'home_furniture';
    case OFFICE_FURNITURE = 'office_furniture';
    case BEDROOM_FURNITURE = 'bedroom_furniture';
    case LIVING_ROOM_FURNITURE = 'living_room_furniture';
    case KITCHEN_FURNITURE = 'kitchen_furniture';
    case OUTDOOR_FURNITURE = 'outdoor_furniture';
    case LIGHTING = 'lighting';
    case HOME_DECOR = 'home_decor';
    case MATTRESSES = 'mattresses';
    case WOOD_MATERIALS = 'wood_materials';
    case FURNITURE_ACCESSORIES = 'furniture_accessories';

    public function category(){
        return match($this){
            self::CANNED_FOODS,
            self::FRESH_FOODS,
            self::FROZEN_FOODS,
            self::REFRIGERATED_FOODS,
            self::FRUITS_VEGETABLES,
            self::MEAT_POULTRY,
            self::SEAFOOD,
            self::DAIRY_PRODUCTS,
            self::BAKERY_PRODUCTS,
            self::SNACKS,
            self::SPICES_SEASONINGS,
            => CategoryEnum::FOOD,

            self::BEVERAGES,
            self::COFFEE_TEA,
            => CategoryEnum::BEVERAGES,

            self::CLEANING_SUPPLIES
            => CategoryEnum::CLEANING,

            self::PRESCRIPTION_MEDICINE,
            self::MEDICINE,
            self::OVER_THE_COUNTER_MEDICINE,
            self::VITAMINS_SUPPLEMENTS
            => CategoryEnum::MEDICINE,

            self::MEDICAL_EQUIPMENT,
            self::FIRST_AID_SUPPLIES,
            self::SURGICAL_SUPPLIES
            => CategoryEnum::MEDICAL_SUPPLIES,

            self::PERSONAL_CARE,
            self::PERSONAL_HYGIENE,
            self::BODY_CARE
            => CategoryEnum::PERSONAL_CARE,

            self::BABY_CARE,
            self::BABY_PRODUCTS
            => CategoryEnum::BABY_CARE,

            self::COSMETICS,
            self::MAKEUP,
            self::SKINCARE,
            self::HAIR_CARE,
            self::PERFUMES,
            self::NAIL_PRODUCTS,
            self::BEAUTY_TOOLS,
            self::PROFESSIONAL_BEAUTY_PRODUCTS
            => CategoryEnum::BEAUTY,

            self::MENS_CLOTHING,
            self::WOMENS_CLOTHING,
            self::KIDS_CLOTHING,
            self::SPORTSWEAR,
            self::UNDERWEAR,
            self::SEASONAL_FASHION,
            self::FABRIC_MATERIALS
            => CategoryEnum::CLOTHING,

            self::SHOES
            => CategoryEnum::SHOES,

            self::BAGS,
            self::ACCESSORIES,
            self::JEWELRY
            => CategoryEnum::ACCESSORIES,

            self::LAPTOPS,
            self::TABLETS,
            self::DESKTOP_COMPUTERS,
            self::COMPUTER_ACCESSORIES
            => CategoryEnum::COMPUTERS,

            self::SMARTPHONES,
            self::PHONE_ACCESSORIES
            => CategoryEnum::PHONES,

            self::AUDIO_DEVICES
            => CategoryEnum::AUDIO,

            self::SMART_HOME_DEVICES
            => CategoryEnum::SMART_HOME,

            self::NETWORK_EQUIPMENT
            => CategoryEnum::NETWORKING,

            self::ELECTRONIC_PARTS,
            self::BATTERIES
            => CategoryEnum::ELECTRONIC_PARTS,

            self::PET_SUPPLIES
            => CategoryEnum::PET_SUPPLIES,

            self::HOME_FURNITURE,
            self::OFFICE_FURNITURE,
            self::BEDROOM_FURNITURE,
            self::LIVING_ROOM_FURNITURE,
            self::KITCHEN_FURNITURE,
            self::OUTDOOR_FURNITURE,
            self::MATTRESSES,
            self::WOOD_MATERIALS,
            self::FURNITURE_ACCESSORIES
            => CategoryEnum::FURNITURE,

            self::HOME_DECOR
            => CategoryEnum::HOME_DECOR,

            self::LIGHTING
            => CategoryEnum::LIGHTING,
        };
    }
}
