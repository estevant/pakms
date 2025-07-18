<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    protected $table = 'servicetype';
    protected $primaryKey = 'service_type_id';
    public $timestamps = false;

    protected $fillable = ['name', 'description', 'is_price_fixed', 'fixed_price'];
}
