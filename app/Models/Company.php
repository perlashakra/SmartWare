<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'address_id',
        'name', 
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
}
