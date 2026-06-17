<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $fillable = ['user_id', 'role', 'business_type'];
    protected $casts = [
        'product_type' => \App\Enums\ProductType::class,
    ];
}
