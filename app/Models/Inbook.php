<?php

namespace App\Models;

use App\Models\InbookProduct;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Inbook extends Model
{
    //
    public function userWhoHandleTheInbook(){
        return $this->belongsTo(User::class);
    }

    public function section(){
        return $this->belongsTo(Section::class);
    }

    public function products(){
        return $this->hasMany(InbookProduct::class);
    }
}
