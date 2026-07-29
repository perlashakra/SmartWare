<?php

namespace App\Models;

use App\Models\InbookProduct;
use App\Models\OrderItem;
use App\Models\Receipt;
use Illuminate\Database\Eloquent\Model;

class ReceiptItem extends Model
{
    //
    public function receipt(){
        return $this->belongsTo(Receipt::class);
    }

    public function item(){
        return $this->belongsTo(OrderItem::class);
    }

    public function inbook(){
        return $this->hasMany(InbookProduct::class);
    }
}
