<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use app\Models\Section;

class Inventory extends Model
{
    //products -> company
    //section -> warehouse
    public function section(){
        return $this->belongsTo(Section::class);
    }
}
