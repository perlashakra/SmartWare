<?php

namespace App\Models;

use App\Enums\FacilityType;
use App\Notifications\QueuedResetPassword;
use App\Notifications\QueuedVerifyEmail;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens, MustVerifyEmailTrait;

    protected $fillable = [
        'manager_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'password',
        'personal_image',
        'role',
        'account_status',
        'identity_status',
        'language_preference',
        'employmentWarehouse_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function document()
    {
        return $this->hasOne(Document::class);
    }

    public function importFiles(){
        return $this->hasMany(ImportFile::class);
    }

    //Employee relationships:
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Facility::class);
    }

    //Manager relationships:

    public function facilities()
    {
        return $this->hasMany(Facility::class);
    }

    public function announcedEmployees()
    {
        return $this->hasMany(EmployeeAnnouncement::class);
    }

    public function workers()
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function managedWarehouses()
    {
        return $this->hasMany(Facility::class, 'user_id')->where('facility_type', FacilityType::Warehouse->value);
    }

    public function canManageSection(Section $section)
    {
        return $section->warehouse !== null &&  $section->warehouse->user_id === $this->id;
    }

    public function owns(){
        return $this->hasMany(Facility::class,'user_id');
    }

    //Client relationship with store
    public function store()
    {
        return $this->hasOne(Facility::class);
    }

    //Profile 1 to 1 relationship
    public function profile()
    {
        return $this->hasOne(Facility::class);
    }

    //Email verification:
    public function sendEmailVerificationNotification()
    {
        $this->notify(new QueuedVerifyEmail);
    }

    //Password Resetting:
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new QueuedResetPassword($token));
    }

    //order_relations
    public function ordersMade(){
        return $this->hasMany(Order::class);
    }

    //in book handle
    public function inBooksHandled(){
        return $this->hasMany(InBook::class);
    }
}
