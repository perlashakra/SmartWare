<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;
    protected $fillable = [
        'inventory_id',
        'created_by',
        'percentage',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected function casts(){
        return [
            'percentage' => 'decimal:2',
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
        ];
    } 

    public function inventory(){
        return $this->belongsTo(Inventory::class);
    }

    public function creator(){
        return $this->belongsTo(User::class, 'created_by');
    }
}
