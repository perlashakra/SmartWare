<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientOrderReciept extends Model
{
    protected $fillable = [
        'client_order_id',
        'total_price',
    ];

    public function clientOrder(){
        return $this->belongsTo(ClientOrder::class);
    }

    public function clientRecieptProducts(){
        return $this->hasMany(ClientOrderRecieptProduct::class);
    }
}
