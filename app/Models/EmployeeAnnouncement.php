<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAnnouncement extends Model
{
    protected $fillable = [
        'facility_id',
        'manager_id',
        'first_name',
        'last_name',
        'national_id',
        'claimed',
    ];
    public function warehouse()
    {
        return $this->belongsTo(Facility::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class);
    }
}
