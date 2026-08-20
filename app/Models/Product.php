<?php

namespace App\Models;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'sku',
        'name_en',
        'name_ar',
        'unit',
        'description_en',
        'description_ar',
        'company_name_en',
        'company_name_ar',
        'product_image',
    ];

    public function categories(){
        return $this->belongsToMany(Category::class);
    }

    public function inventories(){
        return $this->hasMany(Inventory::class);
    }

    public function orderItems(){
        return $this->hasMany(OrderItem::class);
    }
}
