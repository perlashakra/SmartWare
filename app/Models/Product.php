<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'company_id',
        'serial_number',
        'name',
        'container_type',
    ];

    //belongs to category and company 
    public function clientOrderProduct(){
        return $this->hasMany(ClientOrderProduct::class);
    }

    //has many warehouse order product
}
