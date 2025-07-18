<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Objet extends Model
{
    protected $table = 'objets';
    protected $primaryKey = 'objet_id';
    public $timestamps = false;

    protected $fillable = [
        'request_id', 'nom', 'description', 'poids', 'quantite', 'dimensions'
    ];
}
