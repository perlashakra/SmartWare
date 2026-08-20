<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'source_id',
        'destination_id',
        'transaction_type',
        'transaction_date',
        'status',
    ];

    public function sourceFacility() {
        return $this->belongsTo(Facility::class, 'source_id');
    }
    
    public function destinationFacility()
    {
        return $this->belongsTo(Facility::class,'destination_id');
    }

    //transaction item not created
    // public function items(){
    //     return $this->hasMany(TransactionItem::class);
    // }
}
