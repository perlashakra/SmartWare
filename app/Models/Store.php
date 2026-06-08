<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'name',
        'client_id',
        'address_id',
    ];
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
