<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'company_id',
        'sku',
        'name',
        'price',
        'container_type',
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
}
