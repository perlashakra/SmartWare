<?php

namespace App\Models;

<<<<<<< HEAD
use App\Models\Order;
use App\Models\OrderItem;
=======
use App\Enums\ProductType;
>>>>>>> 98cf0ab66069abaa95650b91068c6d7a59eabbcb
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
