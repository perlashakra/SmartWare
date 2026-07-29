<?php

namespace App\Models;

use App\Models\Order;
use App\Models\ReceiptItem;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    //
    public function order(){
        return $this->belongsTo(Order::class);
    }
    public function items(){
        return $this->belongsToMany(ReceiptItem::class);
    }
}
