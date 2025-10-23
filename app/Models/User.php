<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin'
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
            'is_admin' => 'boolean',
        ];
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'user_id', 'id');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'user_id', 'id');
    }

    public function cashSessions()
    {
        return $this->hasMany(CashSession::class, 'user_id', 'id');
    }

    public function customers()
    {
        return $this->hasOne(Customer::class, 'user_id', 'id');
    }

    public function suppliers()
    {
        return $this->hasOne(Supply::class, 'user_id', 'id');
    }

    public function generateCustomId()
    {

        $role = $this->roles->first();

        $prefix = 'DEFAULT';
        if ($role) {
            $prefix = strtoupper(substr($role->name, 0, 5));
        }

        $sequenceNumber = mt_rand(001, 999);
        $sequenceNumber = sprintf('%03d', mt_rand(001, 999));
        $custom_id = $prefix . $sequenceNumber;
        while (self::where('id', $custom_id)->exists()) {
            $sequenceNumber = mt_rand(001, 999);
            $custom_id = $prefix . $sequenceNumber;
        }
        return $custom_id;
    }
}
