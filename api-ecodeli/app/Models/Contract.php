<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $table      = 'contracts';

    protected $primaryKey = 'contract_id';

    public $timestamps    = false;

    // Colonnes qu’on peut remplir via create() ou update()
    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'status',
        'terms',
        'pdf_path',
    ];

    // Relation : un contrat appartient à un utilisateur
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
