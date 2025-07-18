<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleChangeRequest extends Model
{
    protected $table = 'role_change_requests';
    protected $primaryKey = 'request_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'requested_role',
        'current_roles',
        'status',
        'reason',
        'admin_comment',
        'requires_verification',
        'justificatifs_uploaded',
        'processed_at',
        'processed_by'
    ];

    protected $casts = [
        'current_roles' => 'array',
        'requires_verification' => 'boolean',
        'justificatifs_uploaded' => 'boolean',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    protected $dates = [
        'requested_at',
        'processed_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by', 'user_id');
    }

    public function justificatifs()
    {
        return $this->hasMany(Justificatif::class, 'role_request_id', 'request_id');
    }

    public static function requiresVerification($requestedRole, $currentRoles)
    {
        if ($requestedRole === 'Deliverer' && in_array('Customer', $currentRoles)) {
            return true;
        }

        return false;
    }

    public function scopePending($query)
    {
        return $query->where('status', 'En attente');
    }

    public function scopeForRole($query, $role)
    {
        return $query->where('requested_role', $role);
    }
} 