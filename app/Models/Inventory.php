<?php

namespace App\Models;

use App\Models\Product;
use app\Models\Section;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    //products -> company
    //section -> warehouse
    public function section(){
        return $this->belongsTo(Section::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
