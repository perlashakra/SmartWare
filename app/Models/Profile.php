<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        //'product_tyoes',
    ];
    public function User()
    {
        return $this->belongsTo(User::class);
    }

    //Preferences Relationships:

    public function preference()
    {
        // A user has one global onboarding profile (role, business type)
        return $this->hasOne(UserPreference::class);
    }

    public function productTypes()
    {
        // A user can have many saved product type preferences
        return $this->hasMany(UserProductType::class);
    }
}
