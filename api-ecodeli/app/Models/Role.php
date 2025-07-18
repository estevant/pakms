<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'role';
    protected $primaryKey = 'role_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = ['role_name'];

    /**
     * Utilisateurs associés à ce rôle
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'userrole',
            'role_id',
            'user_id'
        );
    }
}
