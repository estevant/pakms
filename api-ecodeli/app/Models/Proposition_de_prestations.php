<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposition_de_prestations extends Model
{
    use HasFactory;

    /**
     * Le nom de la table associée au modèle.
     *
     * @var string
     */
    protected $table = 'proposition_de_prestations';

    /**
     * Les attributs qui peuvent être assignés en masse.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'nom',
        'description',
        'statut',
        'justificatif_id',
    ];

    /**
     * Obtenir l'utilisateur qui a proposé la prestation.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
} 