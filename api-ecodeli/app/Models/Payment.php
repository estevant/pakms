<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments'; // au pluriel si c'est bien le nom exact

    protected $primaryKey = 'payment_id';
    public $incrementing = false; // Stripe fournit un ID alphanumérique
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'payment_id',
        'payer_id',
        'payee_id',
        'payment_type',
        'amount',
        'status',
        'payment_date',
    ];

    public function payer()
{
    return $this->belongsTo(User::class, 'payer_id', 'user_id');
}
}
