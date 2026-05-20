<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'birthday',
        'manager_id',
        'warehouse_id',
        'email',
        'password',
        'id_image',
        'personal_image',
        'role',
        'status',
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
    public function workers()
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function managedWarehouses()
    {
        return $this->hasMany(Warehouse::class, 'admin_id');
    }
}
