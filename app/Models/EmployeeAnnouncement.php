<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAnnouncement extends Model
{
    protected $fillable = [
        'warehouse_id',
        'manager_id',
        'first_name',
        'last_name',
    ];
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class);
    }
}
