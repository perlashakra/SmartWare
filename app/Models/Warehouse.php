<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = [
            'name',
            'manager_id',
            'address_id',
        ];
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function workers()
    {
        return $this->hasMany(User::class);
    }
}
