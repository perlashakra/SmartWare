<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'name',
        'country',
        'city',
        'street',
        'postal_code',
        'latitude',
        'longitude', 
        //'parent_id',
    ];
    public function facility()
    {
        return $this->hasOne(Facility::class);
    }

    public function company(){
        return $this->hasOne(Company::class);
    }
}
