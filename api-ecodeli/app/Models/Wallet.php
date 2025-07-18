<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = 'wallets';
    protected $primaryKey = 'user_id';
    public $incrementing = false; 
    public $timestamps = false; 

    protected $fillable = [
        'user_id',
        'balance_cent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class, 'user_id', 'user_id');
    }
}
