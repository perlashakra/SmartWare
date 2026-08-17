<?php

namespace App\Models;

use App\Enums\FacilityType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;
    protected $fillable = [
        'facility_name_en',
        'facility_name_ar',
        'facility_type',
        'business_type',
        'facility_status',
        'user_id',
        'address_id',
    ];

    protected function casts() : array{
        return [
            'facility_type' => FacilityType::class,
        ];
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'user_id')->where('role', 'client');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'user_id')->where('role', 'warehouse_admin');
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function sections(){
        return $this->hasMany(Section::class,'warehouse_id');
    }

    public function facilityUsers(){
        return $this->hasMany(FacilityUser::class);
    }

    public function employeeAnnouncements(){
        return $this->hasMany(EmployeeAnnouncement::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'facility_category');
    }

    public function importFiles(){
        return $this->hasMany(ImportFile::class);
    }

    public function transactions(){
        return $this->hasMany(Transaction::class);
    }

    public function isWarehouse(){
        return $this->facility_type === FacilityType::Warehouse;
    }

    public function isBusiness(){
        return $this->facility_type === FacilityType::Business;
    }

    public function scopeWarehouses(Builder $query)
    {
        return $query->where('facility_type', FacilityType::Warehouse->value);
    }

    public function scopeBusinesses(Builder $query)
    {
        return $query->where('facility_type', FacilityType::Business->value);
    }

    public function ordersToThisWarehouse(){
        return $this->hasMany(Order::class,'dest_facility_id');
    }
    public function ordersFromThisWarehouse(){
        return $this->hasMany(Order::class,'src_facility_id');
    }

}
