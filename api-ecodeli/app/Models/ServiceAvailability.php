<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceAvailability extends Model
{
    protected $table = 'serviceavailability'; // ← nom EXACT de ta table

    protected $primaryKey = 'availability_id';

    public $timestamps = false; // ← désactivé si ta table n'a pas `created_at` et `updated_at`

    protected $fillable = [
        'offered_service_id',
        'date',
        'start_time',
        'end_time',
    ];
}
