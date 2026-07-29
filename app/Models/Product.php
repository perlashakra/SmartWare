<?php

namespace App\Models;

use App\Models\Order;
use App\Models\OrderItem;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'company_id',
        'sku',
        'name',
        'price',
        'container_type',
        'product_type',
        'product_image',
    ];

    protected $casts = [
        'product_type' => ProductType::class,
    ];

    public function categories(){
        return $this->belongsToMany(Category::class);
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function inventories(){
        return $this->hasMany(Inventory::class);
    }

    public function orderItems(){
        return $this -> has(OrderItem::class);
    }
}
