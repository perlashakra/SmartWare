<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessType extends Model
{
    protected $fillable = ['profile_id', 'business_type'];

    public function profile(){
        return $this->belongsTo(Profile::class);
    }

    public function preferences(){
        return $this->hasMany(Preference::class);
    }

    // public function categories(){
    //     return $this->hasMany(Category::class);
    // }
}
