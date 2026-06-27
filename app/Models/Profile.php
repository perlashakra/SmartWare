<?php

namespace App\Models;

use App\Enums\BusinessType;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'personal_image',
        'preferences_submitted',
        'completed',
        //'product_types',
    ];
    public function User()
    {
        return $this->belongsTo(User::class);
    }

    public function type(){
        return $this->hasOne(BusinessType::class);
    }

    //Preferences Relationships:   THESE SHOULD BE DELETED 
    // public function preference()
    // {
    //     // A user has one global onboarding profile (role, business type)
    //     return $this->hasOne(Preference::class);
    // }

    // public function productTypes()
    // {
    //     // A user can have many saved product type preferences
    //     return $this->hasMany(UserProductType::class);
    // }
}
