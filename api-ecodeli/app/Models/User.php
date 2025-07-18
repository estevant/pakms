<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Cashier\Billable;

use App\Models\Justificatif;
use App\Models\Service;
use App\Models\Role;

class User extends Authenticatable
{
    use Billable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'profile_picture',
        'business_name',
        'business_address',
        'is_validated',
        'preferred_city',
        'description',
        'sector',     
        'nfc_code',
        'qr_code',
        'banned',
        'tutorial_done',
    ];

    protected $dates = [
        'registration_date',
    ];

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'userrole',
            'user_id',
            'role_id'
        );
    }

    public function annonces()
    {
        return $this->hasMany(\App\Models\Request::class,
            'user_id', 'user_id');
    }

    public function justificatifs()
    {
        return $this->hasMany(Justificatif::class, 'user_id', 'user_id');
    }

   public function services()
    {
        return $this->hasMany(Service::class, 'user_id', 'user_id')
                    ->with('serviceType');
    }

   
    public function hasRole(string $roleName): bool
    {
        return $this->roles()
                    ->where('role_name', $roleName)
                    ->exists();
    }

    public function isSeller(): bool
    {
        return $this->hasRole('Seller');
    }
}
