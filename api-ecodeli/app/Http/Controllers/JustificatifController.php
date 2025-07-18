<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Helpers\NotificationHelper;
use App\Traits\BannedWordsChecker;

class JustificatifController extends Controller
{
    use BannedWordsChecker;

    protected $typesAutorises = [
        'CNI',
        'Passeport',
        'Permis',
        'Carte Grise',
        'Assurance',
        'Autre'
    ];

    public function liste(Request $request)
    {
        $user = Session::get('user');
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Non authentifié.'], 401);
        }

        $justificatifs = DB::table('justificatifs')
            ->where('user_id', $user['user_id'])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $justificatifs->map(function($j) use ($user) {
            $url = null;
            if ($j->filename) {
                $filePath = storage_path("app/public/{$j->filename}");
                if (file_exists($filePath)) {
                    $pathParts = explode('/', $j->filename);
                    if (count($pathParts) >= 3 && $pathParts[0] === 'justificatifs') {
                        $userId = $pathParts[1];
                        $filename = $pathParts[2];
                        $url = url("/storage/justificatifs/{$userId}/{$filename}");
                    }
                }
            }
            
            return [
                'id' => $j->id,
                'type' => $j->type,
                'description' => $j->description,
                'created_at' => $j->created_at,
                'statut' => $j->statut ?? 'En attente',
                'url' => $url,
                'filename' => $j->filename
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'types_autorises' => $this->typesAutorises,
        ]);
    }

    public function upload(Request $request)
    {
        try {
            $user = Session::get('user');
            if (! $user) {
                return response()->json(['success' => false, 'message' => 'Non authentifié.'], 401);
            }

            $request->validate([
                'type' => 'required|string|in:' . implode(',', $this->typesAutorises),
                'document' => 'required|file|max:4096',
                'description' => 'nullable|string|max:255'
            ]);

            $type = $request->input('type');
            $description = $request->input('description');

            // Vérification des mots interdits dans la description
            $checkResult = $this->checkBannedWords($description, $user['user_id']);
            if ($checkResult) {
                return response()->json([
                    'success' => false,
                    'message' => $checkResult['message']
                ], 403);
            }

            if ($type === 'Autre' && !$description) {
                return response()->json(['success' => false, 'message' => 'Une description est obligatoire pour un type "Autre".'], 400);
            }

            $fichier = $request->file('document');
            
            if (!$fichier->isValid()) {
                return response()->json(['success' => false, 'message' => 'Fichier invalide.'], 422);
            }
            
            $extension = $fichier->getClientOriginalExtension();
            if (empty($extension)) {
                $extension = 'pdf';
            }
            
            $filename = uniqid('justif_') . '_' . time() . '.' . $extension;
            $userDir = "justificatifs/{$user['user_id']}";
            $uploadPath = storage_path("app/public/{$userDir}");
            
            if (!is_dir($uploadPath)) {
                if (!mkdir($uploadPath, 0755, true)) {
                    return response()->json(['success' => false, 'message' => 'Impossible de créer le répertoire de destination.'], 500);
                }
            }
            
            if (!is_writable($uploadPath)) {
                return response()->json(['success' => false, 'message' => 'Le répertoire de destination n\'est pas accessible en écriture.'], 500);
            }
            
            $fullPath = $uploadPath . '/' . $filename;
            
            $fileContent = file_get_contents($fichier->getPathname());
            if ($fileContent === false) {
                return response()->json(['success' => false, 'message' => 'Impossible de lire le fichier uploadé.'], 500);
            }
            
            if (file_put_contents($fullPath, $fileContent) === false) {
                return response()->json(['success' => false, 'message' => 'Erreur lors de la sauvegarde du fichier.'], 500);
            }
            
            if (!file_exists($fullPath)) {
                return response()->json(['success' => false, 'message' => 'Le fichier n\'a pas été créé.'], 500);
            }
            
            $nomFichier = $userDir . '/' . $filename;

            DB::table('justificatifs')->insert([
                'user_id'    => $user['user_id'],
                'type'       => $type,
                'description'=> $description,
                'filename'   => $nomFichier,
                'created_at' => now()
            ]);

            $admins = DB::table('users')
                ->join('userrole', 'users.user_id', '=', 'userrole.user_id')
                ->join('role', 'userrole.role_id', '=', 'role.role_id')
                ->where('role.role_name', 'Admin')
                ->select('users.user_id')
                ->get();

            foreach ($admins as $admin) {
                NotificationHelper::envoyer(
                    $admin->user_id,
                    'Nouveau justificatif à valider',
                    "Un nouveau justificatif de type '{$type}' a été soumis par {$user['first_name']} {$user['last_name']}."
                );
            }

            return response()->json(['success' => true, 'message' => 'Justificatif envoyé avec succès.']);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'upload de justificatif: ' . $e->getMessage());
            \Log::error('Fichier: ' . $e->getFile() . ' Ligne: ' . $e->getLine());
            
            return response()->json(['success' => false, 'message' => 'Erreur lors de l\'envoi du justificatif: ' . $e->getMessage()], 500);
        }
    }

    public function telecharger($id)
    {
        $user = Session::get('user');
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Non authentifié.'], 401);
        }

        if (in_array('Admin', $user['roles'] ?? [])) {
            $justificatif = DB::table('justificatifs')
                ->where('id', $id)
                ->first();
        } else {
            $justificatif = DB::table('justificatifs')
                ->where('id', $id)
                ->where('user_id', $user['user_id'])
                ->first();
        }

        if (!$justificatif) {
            return response()->json(['success' => false, 'message' => 'Justificatif introuvable.'], 404);
        }

        $filePath = storage_path("app/public/{$justificatif->filename}");
        if (!file_exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'Fichier introuvable.'], 404);
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

        $displayName = $justificatif->type . '_' . $id . '.' . $fileExtension;

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $displayName . '"'
        ]);
    }

    public function supprimer($id)
    {
        $user = Session::get('user');
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Non authentifié.'], 401);
        }

        $justificatif = DB::table('justificatifs')
            ->where('id', $id)
            ->where('user_id', $user['user_id'])
            ->first();

        if (! $justificatif) {
            return response()->json(['success' => false, 'message' => 'Justificatif introuvable.'], 404);
        }

        $filePath = storage_path("app/public/{$justificatif->filename}");
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        DB::table('justificatifs')
            ->where('id', $id)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Justificatif supprimé avec succès.']);
    }
}
