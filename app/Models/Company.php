<?php

namespace App\Models;

<<<<<<< HEAD
use App\Models\Order;
=======
use Illuminate\Database\Eloquent\Factories\HasFactory;
>>>>>>> 98cf0ab66069abaa95650b91068c6d7a59eabbcb
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    protected $fillable = [
        'address_id',
        'name', 
        'phone',
        'email',
        'website',
    ];

    public function products(){
        return $this->hasMany(Product::class);
    }

    public function address(){
        return $this->belongsTo(Address::class);
    }

    public function orders(){
        return $this->hasMany(Order::class);
    }
}
