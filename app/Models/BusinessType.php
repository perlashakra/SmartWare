<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessType extends Model
{
    protected $fillable = ['profile_id', 'name'];

    protected $casts = [
        'product_type' => \App\Enums\ProductType::class,
    ];

    public function profile(){
        return $this->belongsTo(Profile::class);
    }

    public function preferences(){
        return $this->hasMany(Preference::class);
    }
}
