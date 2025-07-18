<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Justificatif extends Model
{
    use HasFactory;

    /**
     * Le nom de la table associée au modèle.
     *
     * @var string
     */
    protected $table = 'justificatifs';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Les attributs qui peuvent être assignés en masse.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'type',
        'description',
        'filename',
        'statut',
        'role_request_id',
    ];

    /**
     * Obtenir l'utilisateur qui a uploadé le justificatif.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function roleChangeRequest()
    {
        return $this->belongsTo(RoleChangeRequest::class, 'role_request_id', 'request_id');
    }
}
