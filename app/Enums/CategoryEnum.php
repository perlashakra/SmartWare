<?php

namespace App\Enums;

enum CategoryEnum : string
{
    //FOOD
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

    //DRINKS
    case BEVERAGES = 'beverages';
    case COFFEE_TEA = 'coffee_tea';

    //CLEANING SUPPLIES
    case CLEANING_SUPPLIES = 'cleaning_supplies';

    //PACKAGING SUPPLIES
    case PACKAGING_SUPPLIES = 'packaging_supplies';

    //PET SUPPLIES
    case PET_SUPPLIES = 'pet_supplies';

    //MEDICINE
    case MEDICINE = 'medicine';
    case PRESCRIPTION_MEDICINE = 'prescription_medicine';
    case OVER_THE_COUNTER_MEDICINE = 'over_the_counter_medicine';
    case VITAMINS_SUPPLEMENTS = 'vitamins_supplements';

    //MEDICAL SUPPLIES
    case MEDICAL_EQUIPMENT = 'medical_equipment';
    case FIRST_AID_SUPPLIES = 'first_aid_supplies';
    case SURGICAL_SUPPLIES = 'surgical_supplies';

    //PERSONAL CARE
    case PERSONAL_CARE = 'personal_care';
    case PERSONAL_HYGIENE = 'personal_hygiene';
    case BODY_CARE = 'body_care';

    //BABY CARE
    case BABY_CARE = 'baby_care';
    case BABY_PRODUCTS = 'baby_products';

    //COSMETICS
    case COSMETICS = 'cosmetics';

    //BEAUTY
    case MAKEUP = 'makeup';
    case SKINCARE = 'skincare';
    case HAIR_CARE = 'hair_care';
    case PERFUMES = 'perfumes';
    case NAIL_PRODUCTS = 'nail_products';
    case BEAUTY_TOOLS = 'beauty_tools';
    case PROFESSIONAL_BEAUTY_PRODUCTS = 'professional_beauty_products';

    //CLOTHING
    case MENS_CLOTHING = 'mens_clothing';
    case WOMENS_CLOTHING = 'womens_clothing';
    case KIDS_CLOTHING = 'kids_clothing';
    case SPORTSWEAR = 'sportswear';
    case UNDERWEAR = 'underwear';
    case SEASONAL_FASHION = 'seasonal_fashion';
    case FABRIC_MATERIALS = 'fabric_materials';

    //SHOES
    case SHOES = 'shoes';

    //ACCESSORIES
    case BAGS = 'bags';
    case ACCESSORIES = 'accessories';
    case JEWELRY = 'jewelry';

    //COMPUTERS
    case LAPTOPS = 'laptops';
    case TABLETS = 'tablets';
    case DESKTOP_COMPUTERS = 'desktop_computers';
    case COMPUTER_ACCESSORIES = 'computer_accessories';

    //PHONES
    case SMARTPHONES = 'smartphones';
    case PHONE_ACCESSORIES = 'phone_accessories';

    //AUDIO
    case AUDIO_DEVICES = 'audio_devices';

    //SMART HOME
    case SMART_HOME_DEVICES = 'smart_home_devices';

    //NETWORKING
    case NETWORK_EQUIPMENT = 'network_equipment';

    //ELECTRONIC PARTS
    case ELECTRONIC_PARTS = 'electronic_parts';
    case BATTERIES = 'batteries';

    //FURNITURE
    case HOME_FURNITURE = 'home_furniture';
    case OFFICE_FURNITURE = 'office_furniture';
    case BEDROOM_FURNITURE = 'bedroom_furniture';
    case LIVING_ROOM_FURNITURE = 'living_room_furniture';
    case KITCHEN_FURNITURE = 'kitchen_furniture';
    case OUTDOOR_FURNITURE = 'outdoor_furniture';
    case MATTRESSES = 'mattresses';
    case WOOD_MATERIALS = 'wood_materials';
    case FURNITURE_ACCESSORIES = 'furniture_accessories';

    //HOME DECOR
    case HOME_DECOR = 'home_decor';

    //LIGHTING
    case LIGHTING = 'lighting';
    
}
