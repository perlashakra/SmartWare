<?php

namespace App\Models;

use App\Models\Order;
use App\Models\Address;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    protected $fillable = [
        'address_id',
        'name_en',
        'name_ar',
        'phone',
        'email',
        'website',
    ];

    public function products(){
        return $this->hasMany(Product::class);
    }

    public function address(){
        return $this->belongsTo(Address::class);
    }

    public function orders(){
        return $this->hasMany(Order::class);
    }
}
