<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $table = 'reservation';

    protected $primaryKey = 'reservation_id';

    protected $fillable = [
    'user_id',
    'availability_id',
    'status',
    'note',
    'commentaire',
    'is_paid',
];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function availability()
    {
        return $this->belongsTo(ServiceAvailability::class, 'availability_id');
    }
}
