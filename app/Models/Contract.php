<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'type',
        'start_date',
        'expiration_date',
        'contract_image',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function warehouses()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
