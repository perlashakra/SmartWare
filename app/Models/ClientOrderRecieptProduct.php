<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientOrderRecieptProduct extends Model
{
    protected $fillable = [
        'client_order_reciept_id',
        'inbook_product_id',
        'quantity',
    ];

    public function clientOrderReciept(){
        return $this->belongsTo(ClientOrderReciept::class);
    }

    //relationship with the inbook_products belongs to 
}
