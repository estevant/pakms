<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Request as Order;

class Review extends Model
{
    protected $table        = 'reviews';
    protected $primaryKey   = 'review_id';
    public $incrementing    = true;
    protected $keyType      = 'int';
    public $timestamps      = false;

    protected $fillable = [
        'reviewer_id',
        'target_deliverer_id',
        'request_id',
        'rating',
        'comment',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id', 'user_id');
    }

    public function request()
    {
        return $this->belongsTo(Order::class, 'request_id', 'request_id');
    }
    public function deliverer()
{
    return $this->belongsTo(User::class, 'target_deliverer_id', 'user_id');
}
}
