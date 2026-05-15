<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'address_id',
        'first_name',
        'last_name',
        'username',
        'phone_number',
        'email',
        'password',
    ];

    public function clientOrders(){
        return $this->hasMany(ClientOrder::class);
    }

    //address relationship
}
