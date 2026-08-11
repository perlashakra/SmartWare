<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'personal_image',
        'preferences_submitted',
        'completed',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
