<?php

namespace App\Models;

use App\Models\InbookProduct;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Inbook extends Model
{
    //
    protected $table = 'inbooks';
    protected $fillable = [
        'user_id',
        'section_id',
        'storage_date',
    ];

    public function userWhoHandleTheInBook()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function products()
    {
        return $this->hasMany(InbookProduct::class, 'inbook_id');
    }
}
