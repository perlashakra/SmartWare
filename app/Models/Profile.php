<?php

namespace App\Models;

use App\Enums\BusinessTypeEnum;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'personal_image',
        'preferences_submitted',
        'completed',
    ];

    //if business_types migrations is deleted add this
    // protected $casts = [
    //     'business_type' => BusinessType::class,
    // ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type(){
        return $this->hasOne(BusinessTypeEnum::class);
    }

    //this should also be deleted
    public function business_type(){
        return $this->hasOne(BusinessType::class);
    }

    //add this relation instead
    //hasMany(Preferences::class)
}
