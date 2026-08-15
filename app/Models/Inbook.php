<?php

namespace App\Models;

use App\Models\InBookProduct;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class InBook extends Model
{
    //
    public function userWhoHandleTheInBook(){
        return $this->belongsTo(User::class);
    }

    public function section(){
        return $this->belongsTo(Section::class);
    }

    public function products(){
        return $this->hasMany(InBookProduct::class);
    }
}
