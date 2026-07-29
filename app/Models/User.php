<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use App\Notifications\QueuedVerifyEmail;
use App\Notifications\QueuedResetPassword;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, MustVerifyEmailTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'manager_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'password',
        'role',
        'account_status',
        'identity_status',
        'language_preference',
        'employmentWarehouse_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
        return $this->hasMany(Facility::class, 'admin_id');
    }

    //Client relationship with store
    public function store()
    {
        return $this->hasOne(Facility::class);
    }

    //Profile 1 to 1 relationship
    public function profile()
    {
        return $this->hasOne(Profile::class);
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
}
