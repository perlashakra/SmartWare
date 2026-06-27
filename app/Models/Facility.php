<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $fillable = [
        'facility_name',
        'facility_type',
        'facility_status',
        'user_id',
        'address_id',
    ];

    //there is also a relation with the FacilityUsers

    //this relation could be either for a business owner or a warehouse manager
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
