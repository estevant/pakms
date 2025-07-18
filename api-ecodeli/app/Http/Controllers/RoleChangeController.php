<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\RoleChangeRequest;
use App\Models\User;
use App\Models\Role;
use App\Models\Justificatif;

class RoleChangeController extends Controller
{
    public function requestRole(Request $request)
    {
        $session = Session::get('user');
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $validated = $request->validate([
            'requested_role' => 'required|in:Customer,Deliverer',
            'reason' => 'nullable|string|max:500'
        ]);

        $user = User::with('roles')->findOrFail($session['user_id']);
        $currentRoles = $user->roles->pluck('role_name')->toArray();

        $allowedCurrentRoles = ['Customer', 'Deliverer'];
        $hasAllowedRole = !empty(array_intersect($currentRoles, $allowedCurrentRoles));
        
        if (!$hasAllowedRole) {
            return response()->json([
                'success' => false, 
                'message' => 'Seuls les clients et livreurs peuvent demander un changement de rôle'
            ], 403);
        }

        if (in_array($validated['requested_role'], $currentRoles)) {
            return response()->json([
                'success' => false, 
                'message' => 'Vous avez déjà ce rôle'
            ], 400);
        }

        $existingRequest = RoleChangeRequest::where('user_id', $user->user_id)
            ->where('requested_role', $validated['requested_role'])
            ->where('status', 'En attente')
            ->first();

        if ($existingRequest) {
            return response()->json([
                'success' => false, 
                'message' => 'Vous avez déjà une demande en attente pour ce rôle'
            ], 400);
        }

        $requiresVerification = RoleChangeRequest::requiresVerification(
            $validated['requested_role'], 
            $currentRoles
        );

        DB::beginTransaction();
        try {
            $roleRequest = RoleChangeRequest::create([
                'user_id' => $user->user_id,
                'requested_role' => $validated['requested_role'],
                'current_roles' => json_encode($currentRoles),
                'reason' => $validated['reason'],
                'requires_verification' => $requiresVerification,
                'status' => 'En attente'
            ]);

            if (!$requiresVerification) {
                $this->approveRoleChange($roleRequest);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $requiresVerification ? 
                    'Demande créée. Veuillez uploader vos justificatifs.' : 
                    'Rôle ajouté avec succès!',
                'request_id' => $roleRequest->request_id,
                'requires_verification' => $requiresVerification
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            // Log l'erreur pour le débogage
            \Log::error('Erreur lors de la création de la demande de rôle: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la demande: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadJustificatifs(Request $request)
    {
        $session = Session::get('user');
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $validated = $request->validate([
            'request_id' => 'required|integer|exists:role_change_requests,request_id',
            'justificatifs' => 'required|array',
            'justificatifs.*' => 'file|max:4096',
            'types' => 'required|array',
            'types.*' => 'required|in:CNI,Permis,Carte Grise,Assurance,Autre',
            'descriptions' => 'nullable|array',
            'descriptions.*' => 'nullable|string|max:255'
        ]);

        foreach ($validated['justificatifs'] as $file) {
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format de fichier non autorisé. Seuls les fichiers PDF, JPG, JPEG et PNG sont acceptés.'
                ], 422);
            }
        }

        $roleRequest = RoleChangeRequest::where('request_id', $validated['request_id'])
            ->where('user_id', $session['user_id'])
            ->where('status', 'En attente')
            ->first();

        if (!$roleRequest) {
            return response()->json([
                'success' => false, 
                'message' => 'Demande introuvable ou non modifiable'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $uploadedCount = 0;
            foreach ($validated['justificatifs'] as $index => $file) {
                $filename = uniqid('role_justif_') . '.' . $file->getClientOriginalExtension();
                
                // Créer le dossier s'il n'existe pas
                $uploadPath = storage_path('app/public/justificatifs');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Enregistrer le fichier manuellement pour éviter les problèmes avec fileinfo
                $fullPath = $uploadPath . DIRECTORY_SEPARATOR . $filename;
                $fileContent = file_get_contents($file->getPathname());
                
                if (file_put_contents($fullPath, $fileContent) === false) {
                    throw new \Exception("Impossible d'enregistrer le fichier vers: " . $fullPath);
                }
                
                Justificatif::create([
                    'user_id' => $session['user_id'],
                    'role_request_id' => $roleRequest->request_id,
                    'type' => $validated['types'][$index],
                    'description' => $validated['descriptions'][$index] ?? 'Justificatif pour demande de rôle ' . $roleRequest->requested_role,
                    'filename' => $filename,
                    'statut' => 'En attente'
                ]);

                $uploadedCount++;
            }

            // Marquer que les justificatifs ont été uploadés
            $roleRequest->update(['justificatifs_uploaded' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$uploadedCount} justificatif(s) uploadé(s) avec succès. Votre demande est en cours de traitement."
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            // Log l'erreur complète pour le débogage
            \Log::error('Erreur lors de l\'upload des justificatifs: ' . $e->getMessage());
            \Log::error('Fichier: ' . $e->getFile() . ' Ligne: ' . $e->getLine());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload des justificatifs: ' . $e->getMessage()
            ], 500);
        }
    }

    public function myRequests()
    {
        $session = Session::get('user');
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $requests = RoleChangeRequest::where('user_id', $session['user_id'])
            ->with(['justificatifs', 'processedBy:user_id,first_name,last_name'])
            ->orderBy('requested_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'requests' => $requests
        ]);
    }

    public function cancelRequest(Request $request, $id)
    {
        $session = Session::get('user');
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $roleRequest = RoleChangeRequest::where('request_id', $id)
            ->where('user_id', $session['user_id'])
            ->where('status', 'En attente')
            ->first();

        if (!$roleRequest) {
            return response()->json([
                'success' => false, 
                'message' => 'Demande introuvable ou non annulable'
            ], 404);
        }

        $roleRequest->update([
            'status' => 'Annulé',
            'processed_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Demande annulée'
        ]);
    }

    public function adminIndex()
    {
        $requests = RoleChangeRequest::with(['user:user_id,first_name,last_name,email', 'justificatifs'])
            ->orderBy('requested_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'requests' => $requests
        ]);
    }
    public function adminApprove(Request $request, $id)
    {
        $session = Session::get('user');
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $validated = $request->validate([
            'admin_comment' => 'nullable|string|max:500'
        ]);

        $roleRequest = RoleChangeRequest::where('request_id', $id)
            ->where('status', 'En attente')
            ->first();

        if (!$roleRequest) {
            return response()->json([
                'success' => false, 
                'message' => 'Demande introuvable'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $this->approveRoleChange($roleRequest, $session['user_id'], $validated['admin_comment']);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Demande approuvée et rôle attribué'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'approbation'
            ], 500);
        }
    }

    public function adminReject(Request $request, $id)
    {
        $session = Session::get('user');
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $validated = $request->validate([
            'admin_comment' => 'required|string|max:500'
        ]);

        $roleRequest = RoleChangeRequest::where('request_id', $id)
            ->where('status', 'En attente')
            ->first();

        if (!$roleRequest) {
            return response()->json([
                'success' => false, 
                'message' => 'Demande introuvable'
            ], 404);
        }

        $roleRequest->update([
            'status' => 'Refusé',
            'admin_comment' => $validated['admin_comment'],
            'processed_at' => now(),
            'processed_by' => $session['user_id']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Demande refusée'
        ]);
    }

    private function approveRoleChange($roleRequest, $adminId = null, $adminComment = null)
    {
        $role = Role::where('role_name', $roleRequest->requested_role)->first();
        if (!$role) {
            throw new \Exception('Rôle introuvable');
        }

        $user = User::findOrFail($roleRequest->user_id);
        
        $hasRole = DB::table('userrole')
            ->where('user_id', $user->user_id)
            ->where('role_id', $role->role_id)
            ->exists();
            
        if (!$hasRole) {
            $user->roles()->attach($role->role_id);
        }

        if ($roleRequest->requested_role === 'Deliverer' && !$user->nfc_code) {
            $user->nfc_code = substr(bin2hex(random_bytes(8)), 0, 16);
            $user->save();
        }

        $roleRequest->update([
            'status' => 'Approuvé',
            'admin_comment' => $adminComment,
            'processed_at' => now(),
            'processed_by' => $adminId
        ]);

        if ($roleRequest->justificatifs()->exists()) {
            $roleRequest->justificatifs()->update(['statut' => 'Validé']);
        }
    }

    public function serveJustificatif($justificatifId)
    {
        $session = Session::get('user');
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $user = User::with('roles')->findOrFail($session['user_id']);
        $isAdmin = $user->roles->contains('role_name', 'Admin');
        
        if (!$isAdmin) {
            return response()->json(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        $justificatif = Justificatif::findOrFail($justificatifId);
        $filePath = storage_path('app/public/justificatifs/' . $justificatif->filename);

        if (!file_exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'Fichier introuvable'], 404);
        }

        $fileExtension = pathinfo($justificatif->filename, PATHINFO_EXTENSION);
        
        $mimeType = match(strtolower($fileExtension)) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'application/octet-stream'
        };
        
        $displayName = $justificatif->type . '_' . $justificatif->user_id . '.' . $fileExtension;

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $displayName . '"'
        ]);
    }
} 