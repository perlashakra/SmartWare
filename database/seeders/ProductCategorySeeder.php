<?php

namespace Database\Seeders;

use App\Enums\CategoryEnum;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Attaches products to categories via the category_product pivot.
     *
     * IMPORTANT: CategoryEnum is scoped to grocery / pharmacy / electronics /
     * clothing / beauty / furniture verticals (see BusinessTypeEnum::categories()).
     * ProductSeeder's 150-item catalog also contains generic tools,
     * kitchenware, office supplies, car accessories, travel gear, and
     * sporting goods that have NO matching CategoryEnum case. Rather than
     * force those SKUs into a category that doesn't really describe them,
     * this seeder only tags the ones that genuinely fit today's taxonomy.
     * If you want full catalog coverage, CategoryEnum needs new cases first
     * (e.g. tools, kitchenware, office_supplies, automotive, travel, sports_equipment).
     */
    public function run(): void
    {
        $map = [
            '001' => [CategoryEnum::AUDIO_DEVICES],
            '002' => [CategoryEnum::AUDIO_DEVICES],
            '003' => [CategoryEnum::SMARTPHONES],
            '004' => [CategoryEnum::PHONE_ACCESSORIES],
            '005' => [CategoryEnum::PHONE_ACCESSORIES],
            '006' => [CategoryEnum::COMPUTER_ACCESSORIES],
            '007' => [CategoryEnum::COMPUTER_ACCESSORIES],
            '008' => [CategoryEnum::COMPUTER_ACCESSORIES],
            '009' => [CategoryEnum::COMPUTER_ACCESSORIES],
            '010' => [CategoryEnum::PHONE_ACCESSORIES, CategoryEnum::BATTERIES],
            '011' => [CategoryEnum::NETWORK_EQUIPMENT],
            '012' => [CategoryEnum::SMART_HOME_DEVICES],
            '014' => [CategoryEnum::SMART_HOME_DEVICES],
            '016' => [CategoryEnum::BAGS],
            '017' => [CategoryEnum::BAGS],
            '018' => [CategoryEnum::BAGS],
            '019' => [CategoryEnum::BAGS],
            '020' => [CategoryEnum::ACCESSORIES],
            '021' => [CategoryEnum::ACCESSORIES],
            '022' => [CategoryEnum::ACCESSORIES],
            '023' => [CategoryEnum::MENS_CLOTHING, CategoryEnum::WOMENS_CLOTHING],
            '024' => [CategoryEnum::MENS_CLOTHING],
            '025' => [CategoryEnum::MENS_CLOTHING, CategoryEnum::WOMENS_CLOTHING],
            '026' => [CategoryEnum::MENS_CLOTHING, CategoryEnum::WOMENS_CLOTHING],
            '027' => [CategoryEnum::SEASONAL_FASHION],
            '028' => [CategoryEnum::SHOES, CategoryEnum::SPORTSWEAR],
            '029' => [CategoryEnum::SHOES],
            '030' => [CategoryEnum::SPORTSWEAR],
            '060' => [CategoryEnum::LIGHTING],
            '061' => [CategoryEnum::OFFICE_FURNITURE],
            '085' => [CategoryEnum::HOME_DECOR],
            '087' => [CategoryEnum::LIGHTING],
            '088' => [CategoryEnum::HOME_DECOR],
            '091' => [CategoryEnum::CLEANING_SUPPLIES],
            '097' => [CategoryEnum::HOME_DECOR],
            '098' => [CategoryEnum::HOME_DECOR],
            '099' => [CategoryEnum::HOME_DECOR],
            '100' => [CategoryEnum::HOME_DECOR],
            '101' => [CategoryEnum::PERSONAL_HYGIENE],
            '102' => [CategoryEnum::SKINCARE],
            '103' => [CategoryEnum::PERSONAL_HYGIENE],
            '104' => [CategoryEnum::PERSONAL_HYGIENE],
            '105' => [CategoryEnum::PERSONAL_HYGIENE],
            '106' => [CategoryEnum::HAIR_CARE],
            '107' => [CategoryEnum::HAIR_CARE],
            '108' => [CategoryEnum::BODY_CARE],
            '109' => [CategoryEnum::BODY_CARE],
            '110' => [CategoryEnum::NAIL_PRODUCTS],
            '121' => [CategoryEnum::COFFEE_TEA],
            '122' => [CategoryEnum::COFFEE_TEA],
            '123' => [CategoryEnum::CANNED_FOODS],
            '124' => [CategoryEnum::CANNED_FOODS],
            '125' => [CategoryEnum::CANNED_FOODS],
            '126' => [CategoryEnum::CANNED_FOODS],
            '127' => [CategoryEnum::CANNED_FOODS],
            '128' => [CategoryEnum::CANNED_FOODS],
            '129' => [CategoryEnum::SNACKS],
            '130' => [CategoryEnum::SNACKS],
            '131' => [CategoryEnum::PET_SUPPLIES],
            '132' => [CategoryEnum::PET_SUPPLIES],
            '133' => [CategoryEnum::PET_SUPPLIES],
            '134' => [CategoryEnum::PET_SUPPLIES],
            '135' => [CategoryEnum::PET_SUPPLIES],
            '141' => [CategoryEnum::COMPUTER_ACCESSORIES],
            '142' => [CategoryEnum::COMPUTER_ACCESSORIES],
            '143' => [CategoryEnum::COMPUTER_ACCESSORIES],
            '144' => [CategoryEnum::COMPUTER_ACCESSORIES],
        ];

        $categoryIdsByName = Category::pluck('id', 'name');

        foreach ($map as $sku => $categories) {
            $product = Product::where('sku', $sku)->first();

            if (!$product) {
                continue;
            }

            $ids = collect($categories)
                ->map(fn (CategoryEnum $category) => $categoryIdsByName[$category->value] ?? null)
                ->filter()
                ->values();

            if ($ids->isNotEmpty()) {
                $product->categories()->syncWithoutDetaching($ids);
            }
        }
    }
}
