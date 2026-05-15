<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientOrder extends Model
{
    protected $fillable = [
        'client_id',
        //'date_of_order',
        'expected_price',
        'completion',
    ];

    public function client(){
        return $this->belongsTo(Client::class);
    }

    public function clientOrderProducts(){
        return $this->hasMany(ClientOrderProduct::class);
    } 
    public function clientOrderReciepts(){
        return $this->hasMany(ClientOrderReciept::class);
    } 

    
}
