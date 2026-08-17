<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'warehouse_id',
        'company_id',
        'parent_id',
        'name',
        'capacity',
    ];

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function warehouse(){
        return $this->belongsTo(Facility::class);
    }

    public function inventories(){
        return $this->hasMany(Inventory::class);
    }

    public function inBooks(){
        return $this->hasMany(InBook::class);
    }

}
