<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
    ];
    public function warehouse()
    {
        return $this->hasOne(Warehouse::class);
    }
}
