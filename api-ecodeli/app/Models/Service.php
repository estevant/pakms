<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'service'; 

    protected $primaryKey = 'offered_service_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'service_type_id',
        'price',
        'details',
        'address', // ← nouveau champ
    ];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }
}
