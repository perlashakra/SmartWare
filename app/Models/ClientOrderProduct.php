<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientOrderProduct extends Model
{
    protected $fillable = [
        'client_order_id',
        'product_id',
        'quantity',
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function clientOrder(){
        return $this->belongsTo(ClientOrder::class);
    }
}
