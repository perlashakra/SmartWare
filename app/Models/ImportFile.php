<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportFile extends Model
{
    protected $fillable = [
        'uploaded_by',
        'facility_id',
        'file_name',
        'file_path',
        'status',
        'uploaded_at',
    ];

    public function uploaded_by(){
        return $this->belongsTo(User::class);
    }

    public function facility(){
        return $this->belongsTo(Facility::class);
    }
}
