<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Request as Order;

class DeliveryAssignment extends Model
{
    protected $table      = 'deliveryassignment';
    protected $primaryKey = 'assignment_id';
    public    $timestamps = false;

    protected $fillable = [
        'request_id',
        'deliverer_id',
        'pickup_address',
        'pickup_postal_code',
        'delivery_address',
        'delivery_postal_code',
        'notes',
        'start_datetime',
        'end_datetime',
        'status',
        'validation_code',
        'step',
        'handoff_status',
        'current_drop_location'
    ];

    public function request()
    {
        return $this->belongsTo(Order::class, 'request_id', 'request_id');
    }
}