<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
    ];

    public function products(){
        return $this->belongsToMany(Product::class);
    }

    //delete
    // public function business_type(){
    //     return $this->belongsTo(BusinessType::class);
    // }
}
