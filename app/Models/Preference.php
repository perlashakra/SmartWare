<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Preference extends Model
{
    protected $fillable = [
        'business_type_id',
        'name',
    ];

    public function business_type(){
        return $this->belongsTo(BusinessType::class);
    }
}
