<?php

namespace App\Models;

use App\Models\Facility;
use App\Models\Inbook;
use app\Models\Inventory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    //

    public function inventory(){
        return $this->hasOne(Inventory::class);
    }

    public function facility(){
        return $this->belongsTo(Facility::class);
    }

    public function inbooks(){
        return $this->hasMany(Inbook::class);
    }

}
