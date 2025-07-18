<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Negotiation extends Model
{
    protected $table = 'negotiations';
    protected $primaryKey = 'negotiation_id';
    public $timestamps = false;

    protected $fillable = [
        'request_id',
        'sender_id',
        'receiver_id',
        'proposed_price',
        'status',
    ];
}