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

    public function uploader(){
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function facility(){
        return $this->belongsTo(Facility::class);
    }
}
