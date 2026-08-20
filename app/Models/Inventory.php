<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;
    protected $fillable = [
        'section_id',
        'product_id',
        'quantity',
        'unit_price',
    ];

    public function section(){
        return $this->belongsTo(Section::class);
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function discounts(){
        return $this->hasMany(Discount::class);
    }
}
