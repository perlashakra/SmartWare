<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable =   ['dest_facility_id'  
                            ,'src_facility_id'   
                            ,'user_id'           
                            ,'order_type'        
                            ,'expected_price'    
                            ,'status'            
                            ,'order_date'        
                            ,'notes'
                            ];
    public function userWhoMadeTheOrder(){
        return $this->belongsTo(User::class);
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }
    public function receipts(){
        return $this->hasMany(Receipt::class);
    }

    public function products(){
        return $this -> hasMany(OrderItem::class);
    }
    public function warehouseOfTheOrder(){
        return $this -> belongsTo(Facility::class);
    }
}
