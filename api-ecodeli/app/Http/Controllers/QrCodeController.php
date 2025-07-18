<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Request as RequestModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Helpers\NotificationHelper;

class QrCodeController extends Controller
{
    /**
     * Valide un livreur via QR code pour une livraison
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateLivreur(Request $request)
    {
        $qrCode = $request->input('qr_code') ?? $request->query('qr_code');
        $requestId = $request->input('request_id') ?? $request->query('request_id');
        
        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'Code QR manquant'
            ], 400);
        }

        $livreur = User::where('qr_code', $qrCode)
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

        // Si un request_id est fourni, vérifier que le livreur est bien assigné
        if ($requestId) {
            $assignment = DB::table('deliveryassignment')
                           ->where('request_id', $requestId)
                           ->where('deliverer_id', $livreur->user_id)
                           ->first();
            
            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce livreur n\'est pas assigné à cette livraison'
                ], 403);
            }
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
        $user = Session::get('user');
        
        if (!$user || !in_array('Deliverer', $user['roles'])) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $userModel = User::find($user['user_id']);

        // Génère un nouveau code si nécessaire
        if (!$userModel->qr_code) {
            $userModel->qr_code = 'ECODELI_' . substr(bin2hex(random_bytes(8)), 0, 16);
            $userModel->save();
        }

        return response()->json([
            'success' => true,
            'qr_code' => $userModel->qr_code,
            'qr_url' => url("/api/qr/validate?code=" . $userModel->qr_code)
        ]);
    }

    /**
     * Confirme une livraison après validation QR
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmDelivery(Request $request)
    {
        $requestId = $request->input('request_id');
        $qrCode = $request->input('qr_code');
        
        if (!$requestId || !$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'Request ID et QR code requis'
            ], 400);
        }

        $user = Session::get('user');
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 401);
        }
        
        // Vérifier que l'utilisateur est le client de cette livraison
        $requestModel = RequestModel::where('request_id', $requestId)
                                   ->where('user_id', $user['user_id'])
                                   ->first();

        if (!$requestModel) {
            return response()->json([
                'success' => false,
                'message' => 'Livraison non trouvée ou accès non autorisé'
            ], 404);
        }

        // Vérifier le livreur
        $livreur = User::where('qr_code', $qrCode)->first();
        if (!$livreur) {
            return response()->json([
                'success' => false,
                'message' => 'Code QR invalide'
            ], 403);
        }

        // Vérifier que le livreur est bien assigné à cette livraison
        $assignment = DB::table('deliveryassignment')
                       ->where('request_id', $requestId)
                       ->where('deliverer_id', $livreur->user_id)
                       ->first();

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Ce livreur n\'est pas assigné à cette livraison'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // Marquer la livraison comme terminée
            DB::table('deliveryassignment')
                ->where('request_id', $requestId)
                ->where('deliverer_id', $livreur->user_id)
                ->update([
                    'status' => 'Livrée',
                    'end_datetime' => now()
                ]);

            // Notifier le livreur
            NotificationHelper::envoyer(
                $livreur->user_id,
                'Livraison confirmée',
                "Votre livraison #{$requestId} a été confirmée par le client."
            );

            // Paiement automatique si configuré
            $prix = $requestModel->prix_negocie_cents ?? $requestModel->prix_cents;
            if ($prix && $prix > 0) {
                DB::table('wallets')->updateOrInsert(
                    ['user_id' => $livreur->user_id],
                    ['balance_cent' => DB::raw("balance_cent + $prix"), 'updated_at' => now()]
                );

                DB::table('payments')->insert([
                    'payment_id'   => uniqid('wallet_'),
                    'payer_id'     => $user['user_id'],
                    'payee_id'     => $livreur->user_id,
                    'payment_type' => 'Livraison',
                    'amount'       => $prix,
                    'status'       => 'Payé',
                    'payment_date' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Livraison confirmée avec succès',
                'request_id' => $requestId,
                'status' => 'Livrée'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la confirmation',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Génère un QR code pour une annonce spécifique
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateAnnonceQrCode(Request $request)
    {
        $requestId = $request->input('request_id');
        
        if (!$requestId) {
            return response()->json([
                'success' => false,
                'message' => 'Request ID requis'
            ], 400);
        }

        $user = Session::get('user');
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 401);
        }

        // Vérifier que l'utilisateur est le propriétaire de l'annonce
        $requestModel = RequestModel::where('request_id', $requestId)
                                   ->where('user_id', $user['user_id'])
                                   ->first();

        if (!$requestModel) {
            return response()->json([
                'success' => false,
                'message' => 'Annonce non trouvée ou accès non autorisé'
            ], 404);
        }

        // Générer un QR code unique pour cette annonce
        $qrCode = 'ANNONCE_' . $requestId . '_' . substr(bin2hex(random_bytes(4)), 0, 8);

        return response()->json([
            'success' => true,
            'qr_code' => $qrCode,
            'request_id' => $requestId,
            'qr_url' => url("/api/qr/validate?code=" . $qrCode . "&request_id=" . $requestId)
        ]);
    }

    /**
     * Ancienne méthode pour compatibilité
     */
    public function handle(Request $request)
    {
        return $this->validateLivreur($request);
    }
} 