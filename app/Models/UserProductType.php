<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProductType extends Model
{
    protected $fillable = ['user_id', 'product_type'];

    protected $casts = [
        'product_type' => \App\Enums\ProductType::class,
    ];
}
