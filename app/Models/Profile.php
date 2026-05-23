<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        //'product_tyoes',
    ];
    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
