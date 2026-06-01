<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'birthday',
        'manager_id',
        'warehouse_id',
        'email',
        'phone_number',
        'password',
        'id_image',
        'personal_image',
        'role',
        'language_preference',
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

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }
    //Employee relationships:
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
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
        return $this->hasMany(Warehouse::class, 'admin_id');
    }

    //Client relationship with store
    public function store()
    {
        return $this->hasOne(Store::class);
    }

    //Profile 1 to 1 relationship
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}
