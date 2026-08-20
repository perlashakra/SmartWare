<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    protected $fillable = [
        'status',
        'route_sequence',
        'dispatched_at',
        'completed_at',
    ];

    protected $casts = [
        'route_sequence' => 'array',
        'dispatched_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
