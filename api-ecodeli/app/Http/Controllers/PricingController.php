<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PricingController extends Controller
{
    /**
     * Récupère les tarifs imposés d'EcoDeli
     */
    public function getTarifs()
    {
        $tarifs = config('pricing');
        
        return response()->json([
            'success' => true,
            'tarifs' => [
                'base' => [
                    'montant' => $tarifs['base'] / 100, // Conversion en euros
                    'description' => 'Tarif de base pour toute prestation'
                ],
                'per_kg' => [
                    'montant' => $tarifs['per_kg'] / 100, // Conversion en euros
                    'description' => 'Supplément par kilogramme'
                ],
                'per_m3' => [
                    'montant' => $tarifs['per_m3'] / 100, // Conversion en euros
                    'description' => 'Supplément par mètre cube'
                ],
                'per_km' => [
                    'montant' => $tarifs['per_km'] / 100, // Conversion en euros
                    'description' => 'Supplément par kilomètre'
                ]
            ]
        ]);
    }
} 