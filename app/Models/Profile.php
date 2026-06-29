<?php

namespace App\Models;

use App\Enums\BusinessTypeEnum;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'personal_image',
        'preferences_submitted',
        'completed',
    ];
    public function User()
    {
        return $this->belongsTo(User::class);
    }

    public function type(){
        return $this->hasOne(BusinessTypeEnum::class);
    }
}
