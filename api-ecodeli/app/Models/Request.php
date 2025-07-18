<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Request extends Model
{
    use HasFactory;

    protected $table      = 'requests';
    protected $primaryKey = 'request_id';
    protected $keyType    = 'int';
    public    $incrementing = true;
    public    $timestamps   = false;

    protected $fillable = [
        'user_id',
        'type',

        'departure_city',
        'departure_code',
        'departure_address',
        'departure_lat',
        'departure_lon',

        'destination_city',
        'destination_code',
        'destination_address',
        'destination_lat',
        'destination_lon',

        'is_split',
        'parent_request_id',

        'poids',
        'longueur',
        'largeur',
        'hauteur',
        'distance',
        'prix_cents',
        'prix_negocie_cents',
        'is_paid',
    ];

    /* ---------- Relations ---------- */

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function objets()
    {
        return $this->hasMany(Objet::class, 'request_id', 'request_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_request_id', 'request_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_request_id', 'request_id');
    }
}