<?php

namespace Database\Seeders;

use App\Enums\CategoryEnum;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach(CategoryEnum::cases() as $category){
            Category::updateOrCreate([
                'name' => $category->value,
            ]);
        }
    }
}

/** 
Whenever you add a category
1.Add enum
2.Seed database
Don't insert them manually every time.
Now the database stays synchronized with the enum*/