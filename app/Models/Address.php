<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'latitude',
        'longitude',
        'address',
    ];
    public function facility()
    {
        return $this->belongeToOne(Facility::class);
    }

}
