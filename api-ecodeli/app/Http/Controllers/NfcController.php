<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    /**
     * Gère la validation d'un livreur via QR code
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateLivreur(Request $request)
    {
        $qrCode = $request->input('qr_code');
        
        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'Code QR manquant'
            ], 400);
        }

        $livreur = User::where('nfc_code', $qrCode)
                      ->whereHas('roles', function($query) {
                          $query->where('role_name', 'Deliverer');
                      })
                      ->first();

        if (!$livreur) {
            return response()->json([
                'success' => false,
                'message' => 'Livreur non trouvé ou code invalide'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'livreur' => [
                'user_id' => $livreur->user_id,
                'first_name' => $livreur->first_name,
                'last_name' => $livreur->last_name,
                'profile_picture' => $livreur->profile_picture,
                'phone' => $livreur->phone,
                'is_validated' => $livreur->is_admin
            ]
        ]);
    }

    /**
     * Génère un QR code pour un livreur
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateQrCode(Request $request)
    {
        $user = $request->user();
        
        if (!$user->hasRole('Deliverer')) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        // Génère un nouveau code si nécessaire
        if (!$user->nfc_code) {
            $user->nfc_code = substr(bin2hex(random_bytes(8)), 0, 16);
            $user->save();
        }

        return response()->json([
            'success' => true,
            'qr_code' => $user->nfc_code,
            'qr_url' => url("/api/qr/validate?code=" . $user->nfc_code)
        ]);
    }

    /**
     * Ancienne méthode pour compatibilité (redirige vers la nouvelle)
     */
    public function handle(Request $request)
    {
        return $this->validateLivreur($request);
    }
}