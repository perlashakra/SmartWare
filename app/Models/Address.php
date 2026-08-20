<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude', 
    ];
    public function facility()
    {
        return $this->hasOne(Facility::class);
    }

}
