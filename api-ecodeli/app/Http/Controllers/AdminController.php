<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use App\Helpers\NotificationHelper;
use App\Models\Contract;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Justificatif;
use App\Models\Box;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function stats()
    {
        $clients = DB::table('userrole')
            ->join('role', 'userrole.role_id', '=', 'role.role_id')
            ->join('users', 'userrole.user_id', '=', 'users.user_id')
            ->where('role.role_name', 'Customer')
            ->where('users.banned', 0)
            ->count();

        $livreurs = DB::table('userrole')
            ->join('role', 'userrole.role_id', '=', 'role.role_id')
            ->join('users', 'userrole.user_id', '=', 'users.user_id')
            ->where('role.role_name', 'Deliverer')
            ->where('users.banned', 0)
            ->count();

        $prestataires = DB::table('userrole')
            ->join('role', 'userrole.role_id', '=', 'role.role_id')
            ->join('users', 'userrole.user_id', '=', 'users.user_id')
            ->where('role.role_name', 'ServiceProvider')
            ->where('users.banned', 0)
            ->count();

        $commercants = DB::table('userrole')
            ->join('role', 'userrole.role_id', '=', 'role.role_id')
            ->join('users', 'userrole.user_id', '=', 'users.user_id')
            ->where('role.role_name', 'Seller')
            ->where('users.banned', 0)
            ->count();

        $annonces = DB::table('requests')->count();

        $justificatifs = DB::table('justificatifs')
            ->count();
        $justificatifsEnAttente = DB::table('justificatifs')
            ->where('statut', 'En attente')
            ->count();

        $justificatifsValidés = DB::table('justificatifs')
            ->where('statut', 'Validé')
            ->count();

        $contrats = DB::table('contracts')->count();

        $contratsEnAttente = DB::table('contracts')
            ->where('status', 'pending')
            ->count();

        $signalements = DB::table('reports')
            ->where('status', 'Ouvert')
            ->count();

        $boxes = DB::table('storage_boxes')->count();

        $bannis = DB::table('users')
            ->where('banned', 1)
            ->count();

        $avis = DB::table('reviews')->count();
        $finances = DB::table('payments')->sum('amount');

        return response()->json([
            'clients'              => $clients,
            'livreurs'             => $livreurs,
            'prestataires'         => $prestataires,
            'commercants'          => $commercants,
            'annonces'             => $annonces,
            'justificatifs'        => $justificatifs,
            'justificatifs_valides'=> $justificatifsValidés,
            'justificatifs_en_attente' => $justificatifsEnAttente,
            'contrats'             => $contrats,
            'contrats_en_attente'  => $contratsEnAttente,
            'signalements'         => $signalements,
            'boxes'                => $boxes,
            'bannis'               => $bannis,
            'avis'                 => $avis,
            'finances'             => $finances,
        ]);
    }


    public function clients(Request $request)
    {
        $q = trim($request->query('q', ''));

        $query = DB::table('users')
            ->join('userrole', 'users.user_id', '=', 'userrole.user_id')
            ->join('role',     'userrole.role_id', '=', 'role.role_id')
            ->where('role.role_name', 'Customer')
            ->select(
                'users.user_id  as id_utilisateur',
                'users.first_name as prenom',
                'users.last_name  as nom',
                'users.email'
            );

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('users.first_name', 'like', "%{$q}%")
                    ->orWhere('users.last_name', 'like', "%{$q}%")
                    ->orWhere('users.email', 'like', "%{$q}%");
            });
        }

        $clients = $query->orderBy('users.last_name')->get();

        return response()->json([
            'success' => true,
            'clients' => $clients,
        ]);
    }

    public function getClient($id)
    {
        $client = DB::table('users')->where('user_id', $id)->first();

        if (!$client) {
            return response()->json(['success' => false, 'message' => 'Client introuvable'], 404);
        }

        return response()->json([
            'success' => true,
            'client'  => [
                'id_utilisateur' => $client->user_id,
                'nom'            => $client->last_name,
                'prenom'         => $client->first_name,
                'email'          => $client->email,
            ],
        ]);
    }

    public function updateClient(Request $request, $id)
    {
        $validated = $request->validate([
            'nom'    => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email'  => 'required|email|max:100',
        ]);

        DB::table('users')
            ->where('user_id', $id)
            ->update([
                'first_name' => $validated['prenom'],
                'last_name'  => $validated['nom'],
                'email'      => $validated['email'],
            ]);

        return response()->json(['success' => true, 'message' => 'Client mis à jour avec succès.']);
    }


    public function listeLivreurs(Request $request)
    {
        $q = trim($request->query('q', ''));

        $query = DB::table('users')
            ->join('userrole', 'users.user_id', '=', 'userrole.user_id')
            ->join('role', 'userrole.role_id', '=', 'role.role_id')
            ->where('role.role_name', 'Deliverer')
            ->select(
                'users.user_id as id_utilisateur',
                'first_name as prenom',
                'last_name as nom',
                'email',
                'business_address as adresse',
                'phone as cp',
                'users.is_validated as statut_validation'
            );

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('users.first_name', 'like', "%{$q}%")
                    ->orWhere('users.last_name', 'like', "%{$q}%")
                    ->orWhere('users.email', 'like', "%{$q}%")
                    ->orWhere('users.business_address', 'like', "%{$q}%")
                    ->orWhere('users.phone', 'like', "%{$q}%");
            });
        }

        $livreurs = $query->orderBy('users.last_name')->get();

        return response()->json(['success' => true, 'livreurs' => $livreurs]);
    }

    public function validerLivreur(Request $request)
    {
        $validated = $request->validate(['id_utilisateur' => 'required|integer']);

        $affected = DB::table('users')
            ->where('user_id', $validated['id_utilisateur'])
            ->update(['is_validated' => 1]);

        if ($affected) {
            NotificationHelper::envoyer(
                $validated['id_utilisateur'],
                'Compte validé',
                'Ton profil de livreur a été validé. Tu peux désormais effectuer des livraisons.'
            );
            return response()->json(['success' => true, 'message' => 'Livreur validé avec succès.']);
        }

        return response()->json(['success' => false, 'message' => 'Échec de la validation ou utilisateur introuvable.'], 404);
    }

    public function invaliderLivreur(Request $request)
    {
        $validated = $request->validate([
            'id_utilisateur' => 'required|integer|exists:users,user_id',
        ]);

        DB::table('users')
            ->where('user_id', $validated['id_utilisateur'])
            ->update(['is_validated' => 0]);

        return response()->json(['success' => true, 'message' => 'Validation annulée avec succès.']);
    }

    public function supprimerLivreur($id)
    {
        try {
            DB::beginTransaction();
            $assignmentIds = DB::table('deliveryassignment')
                ->where('deliverer_id', $id)
                ->pluck('assignment_id');

            if ($assignmentIds->isNotEmpty()) {
                DB::table('delivery_handoffs')
                    ->whereIn('assignment_id', $assignmentIds)
                    ->delete();

                DB::table('deliveryassignment')
                    ->where('deliverer_id', $id)
                    ->delete();
            }

            DB::table('userrole')
                ->where('user_id', $id)
                ->delete();

            DB::table('users')
                ->where('user_id', $id)
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Livreur supprimé avec succès.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur suppression livreur {$id} : {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ], 500);
        }
    }
    public function getLivreur($id)
    {
        $livreur = DB::table('users')
            ->join('userrole', 'users.user_id', '=', 'userrole.user_id')
            ->join('role',     'userrole.role_id', '=', 'role.role_id')
            ->where('users.user_id', $id)
            ->where('role.role_name', 'Deliverer')
            ->select(
                'users.user_id as id_utilisateur',
                'first_name as prenom',
                'last_name  as nom',
                'email',
                'business_address as adresse'
            )
            ->first();

        if (!$livreur) {
            return response()->json(['success' => false, 'message' => 'Livreur non trouvé'], 404);
        }

        return response()->json([
            'success' => true,
            'livreur' => [
                'id_utilisateur' => $livreur->id_utilisateur,
                'nom'            => $livreur->nom,
                'prenom'         => $livreur->prenom,
                'email'          => $livreur->email,
                'adresse'        => $livreur->adresse ?? '',
            ],
        ]);
    }

    public function updateLivreur($id, Request $request)
    {
        $validated = $request->validate([
            'nom'     => 'required|string|max:100',
            'prenom'  => 'required|string|max:100',
            'email'   => 'required|email|max:100',
            'adresse' => 'nullable|string|max:255',
        ]);

        $livreur = DB::table('users')->where('user_id', $id)->first();

        if (!$livreur) {
            return response()->json(['success' => false, 'message' => 'Livreur introuvable'], 404);
        }

        DB::table('users')
            ->where('user_id', $id)
            ->update([
                'last_name'        => $validated['nom'],
                'first_name'       => $validated['prenom'],
                'email'            => $validated['email'],
                'business_address' => $validated['adresse'] ?? null,
            ]);

        return response()->json(['success' => true, 'message' => 'Livreur mis à jour avec succès.']);
    }


    public function listePrestataires(Request $request)
    {
        $q = trim($request->query('q', ''));

        $query = DB::table('users')
            ->join('userrole', 'users.user_id', '=', 'userrole.user_id')
            ->join('role', 'userrole.role_id', '=', 'role.role_id')
            ->where('role.role_name', 'ServiceProvider')
            ->select(
                'users.user_id as id_utilisateur',
                'users.first_name as prenom',
                'users.last_name as nom',
                'users.email',
                'users.sector as domaine',
                'users.description'
            );

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('users.first_name', 'like', "%{$q}%")
                    ->orWhere('users.last_name', 'like', "%{$q}%")
                    ->orWhere('users.email', 'like', "%{$q}%")
                    ->orWhere('users.sector', 'like', "%{$q}%")
                    ->orWhere('users.description', 'like', "%{$q}%");
            });
        }

        $prestataires = $query->orderBy('users.last_name')->get();

        return response()->json([
            'success'      => true,
            'prestataires' => $prestataires
        ]);
    }

    public function deletePrestataire($id)
    {
        try {
            DB::beginTransaction();

            // Vérifier que l'utilisateur existe et est un prestataire
            $user = DB::table('users')
                ->join('userrole', 'users.user_id', '=', 'userrole.user_id')
                ->join('role',     'userrole.role_id', '=', 'role.role_id')
                ->where('users.user_id', $id)
                ->where('role.role_name', 'ServiceProvider')
                ->first();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Prestataire introuvable ou invalide.'], 404);
            }

            // 1. Supprimer les réservations de services
            $serviceIds = DB::table('service')->where('user_id', $id)->pluck('offered_service_id');
            if ($serviceIds->isNotEmpty()) {
                // Supprimer les créneaux de disponibilité
                DB::table('serviceavailability')->whereIn('offered_service_id', $serviceIds)->delete();
                
                // Supprimer les réservations
                DB::table('reservation')->whereIn('availability_id', function($query) use ($serviceIds) {
                    $query->select('availability_id')
                          ->from('serviceavailability')
                          ->whereIn('offered_service_id', $serviceIds);
                })->delete();
            }

            // 2. Supprimer les services
            DB::table('service')->where('user_id', $id)->delete();

            // 3. Supprimer les propositions de prestations
            DB::table('proposition_de_prestations')->where('user_id', $id)->delete();

            // 4. Supprimer les justificatifs
            DB::table('justificatifs')->where('user_id', $id)->delete();

            // 5. Supprimer les alertes admin
            DB::table('admin_alerts')->where('user_id', $id)->delete();

            // 6. Supprimer les notifications
            DB::table('notifications')->where('user_id', $id)->delete();

            // 7. Supprimer les avis donnés par ce prestataire
            DB::table('review')->where('reviewer_id', $id)->delete();

            // 8. Supprimer les signalements faits par ce prestataire
            DB::table('reports')->where('reporter_id', $id)->delete();

            // 9. Supprimer les messages envoyés par ce prestataire
            DB::table('messages')->where('sender_id', $id)->delete();

            // 10. Supprimer les rôles utilisateur
            DB::table('userrole')->where('user_id', $id)->delete();

            // 11. Supprimer l'utilisateur
            DB::table('users')->where('user_id', $id)->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Prestataire et toutes ses données supprimés avec succès.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur suppression prestataire {$id} : {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ], 500);
        }

        DB::table('requests')->where('user_id', $id)->delete();
        DB::table('service')->where('user_id', $id)->delete();
        DB::table('userrole')->where('user_id', $id)->delete();
        DB::table('users')->where('user_id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Prestataire supprimé avec succès.']);
    }

    public function getPrestataire($id)
    {
        $user = DB::table('users')->where('user_id', $id)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Prestataire introuvable'], 404);
        }

        return response()->json([
            'success'     => true,
            'prestataire' => [
                'id_utilisateur' => $user->user_id,
                'nom'            => $user->last_name,
                'prenom'         => $user->first_name,
                'email'          => $user->email,
                'phone'          => $user->phone ?? '',
                'domaine'        => $user->sector ?? '',
                'description'    => $user->description ?? '',
            ],
        ]);
    }

    public function updatePrestataire($id, Request $request)
    {
        $validated = $request->validate([
            'nom'         => 'required|string|max:100',
            'prenom'      => 'required|string|max:100',
            'email'       => 'required|email|max:100',
            'domaine'     => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $user = DB::table('users')->where('user_id', $id)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Prestataire introuvable'], 404);
        }

        DB::table('users')->where('user_id', $id)->update([
            'last_name'   => $validated['nom'],
            'first_name'  => $validated['prenom'],
            'email'       => $validated['email'],
            'sector'      => $validated['domaine'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Prestataire mis à jour avec succès.']);
    }


    public function listeCommercants(Request $request)
    {
        $q = trim($request->query('q', ''));

        $query = DB::table('users')
            ->join('userrole', 'users.user_id', '=', 'userrole.user_id')
            ->join('role', 'userrole.role_id', '=', 'role.role_id')
            ->where('role.role_name', 'Seller')
            ->select(
                'users.user_id as id_utilisateur',
                'users.first_name as prenom',
                'users.last_name as nom',
                'users.email',
                'users.business_name as raison_sociale'
            );

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('users.first_name', 'like', "%{$q}%")
                    ->orWhere('users.last_name', 'like', "%{$q}%")
                    ->orWhere('users.email', 'like', "%{$q}%")
                    ->orWhere('users.business_name', 'like', "%{$q}%");
            });
        }

        $commercants = $query->orderBy('users.last_name')->get();

        return response()->json([
            'success'     => true,
            'commercants' => $commercants
        ]);
    }

    public function getCommercant($id)
    {
        $user = DB::table('users')
            ->where('user_id', $id)
            ->select(
                'user_id as id_utilisateur',
                'first_name as prenom',
                'last_name  as nom',
                'email',
                'business_name as raison_sociale'
            )
            ->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Commerçant introuvable'], 404);
        }

        return response()->json(['success' => true, 'commercant' => $user]);
    }

    public function updateCommercant($id, Request $request)
    {
        $validated = $request->validate([
            'nom'            => 'required|string|max:100',
            'prenom'         => 'required|string|max:100',
            'email'          => 'required|email|max:100',
            'raison_sociale' => 'required|string|max:255',
        ]);

        DB::table('users')
            ->where('user_id', $id)
            ->update([
                'last_name'     => $validated['nom'],
                'first_name'    => $validated['prenom'],
                'email'         => $validated['email'],
                'business_name' => $validated['raison_sociale'],
            ]);

        return response()->json(['success' => true, 'message' => 'Commerçant mis à jour avec succès.']);
    }

    public function deleteCommercant($id)
    {
        $user = DB::table('users')
            ->join('userrole', 'users.user_id', '=', 'userrole.user_id')
            ->join('role',     'userrole.role_id', '=', 'role.role_id')
            ->where('users.user_id', $id)
            ->where('role.role_name', 'Seller')
            ->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Commerçant introuvable ou invalide.'], 404);
        }

        DB::table('userrole')->where('user_id', $id)->delete();
        DB::table('users')->where('user_id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Commerçant supprimé avec succès.']);
    }


    public function listeAnnonces(Request $request)
    {
        $q = trim($request->query('q', ''));

        $query = DB::table('requests')
            ->leftJoin('users', 'requests.user_id', '=', 'users.user_id')
            ->leftJoin('deliveryassignment', 'requests.request_id', '=', 'deliveryassignment.request_id')
            ->leftJoin('users as livreur', 'deliveryassignment.deliverer_id', '=', 'livreur.user_id')
            ->select(
                'requests.request_id as id_annonce',
                'requests.departure_city as depart',
                'requests.departure_code as depart_code',
                'requests.destination_city as arrivee',
                'requests.destination_code as arrivee_code',
                'deliveryassignment.status as statut_livraison',
                'livreur.first_name as livreur_prenom',
                'livreur.last_name as livreur_nom'
            );

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('requests.departure_city', 'like', "%{$q}%")
                    ->orWhere('requests.destination_city', 'like', "%{$q}%")
                    ->orWhere('requests.departure_code', 'like', "%{$q}%")
                    ->orWhere('requests.destination_code', 'like', "%{$q}%")
                    ->orWhere('livreur.first_name', 'like', "%{$q}%")
                    ->orWhere('livreur.last_name', 'like', "%{$q}%");
            });
        }

        $annonces = $query->orderBy('requests.created_at', 'desc')->get();

        return response()->json([
            'success'  => true,
            'annonces' => $annonces
        ]);
    }

    public function getAnnonce($id)
    {
        $annonce = DB::table('requests')
            ->join('users', 'requests.user_id', '=', 'users.user_id')
            ->where('requests.request_id', $id)
            ->select(
                'requests.*',
                'users.first_name as client_prenom',
                'users.last_name  as client_nom',
                'users.email      as client_email'
            )
            ->first();

        if (!$annonce) {
            return response()->json(['success' => false, 'message' => 'Annonce introuvable'], 404);
        }

        $objets = DB::table('objects')
            ->where('request_id', $id)
            ->select('id', 'request_id', 'nom', 'quantite', 'dimensions', 'poids', 'description')
            ->get();

        foreach ($objets as $objet) {
            try {
                $photos = DB::table('object_photo')
                    ->where('object_id', $objet->id)
                    ->select('id', 'chemin')
                    ->get()
                    ->toArray();
                $objet->photos = $photos;
            } catch (\Exception $e) {
                $objet->photos = [];
            }
        }

        $livraison = DB::table('deliveryassignment')
            ->leftJoin('users', 'deliveryassignment.deliverer_id', '=', 'users.user_id')
            ->where('deliveryassignment.request_id', $id)
            ->select(
                'deliveryassignment.status as statut_livraison',
                'users.first_name as livreur_prenom',
                'users.last_name  as livreur_nom'
            )
            ->first();

        return response()->json([
            'success' => true,
            'annonce' => (object)[
                'id_annonce'       => $annonce->request_id,
                'depart'           => $annonce->departure_city,
                'depart_code'      => $annonce->departure_code,
                'arrivee'          => $annonce->destination_city,
                'arrivee_code'     => $annonce->destination_code,
                'statut'           => $annonce->status ?? 'en_attente',
                'date_creation'    => $annonce->created_at,
                'client_prenom'    => $annonce->client_prenom,
                'client_nom'       => $annonce->client_nom,
                'client_email'     => $annonce->client_email,
                'objets'           => $objets,
                'livreur_prenom'   => $livraison->livreur_prenom ?? null,
                'livreur_nom'      => $livraison->livreur_nom    ?? null,
                'statut_livraison' => $livraison->statut_livraison ?? 'En attente',
            ],
        ]);
    }

    public function deleteAnnonce($id)
    {
        try {
            DB::beginTransaction();

            $assignmentIds = DB::table('deliveryassignment')
                ->where('request_id', $id)
                ->pluck('assignment_id');

            if ($assignmentIds->isNotEmpty()) {
                DB::table('delivery_handoffs')
                    ->whereIn('assignment_id', $assignmentIds)
                    ->delete();
            }

            DB::table('box_assignments')
                ->where('request_id', $id)
                ->delete();

            DB::table('deliveryassignment')
                ->where('request_id', $id)
                ->delete();

            DB::table('objects')
                ->where('request_id', $id)
                ->delete();

            DB::table('requests')
                ->where('request_id', $id)
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Annonce supprimée avec succès.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur suppression annonce {$id} : {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateAnnonce($id, Request $request)
    {
        \Log::info('[AdminController::updateAnnonce] Début de la méthode', [
            'id' => $id,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'source' => $request->input('source', 'inconnu')
        ]);
        
        \Log::info('[AdminController::updateAnnonce] Données reçues', [
            'all_input' => $request->all(),
            'files' => $request->allFiles()
        ]);
        
        $data = $request->validate([
            'depart_code'         => 'sometimes|string|max:10',
            'arrivee_code'        => 'sometimes|string|max:10',
            'statut'              => 'sometimes|in:en_attente,validee,livree',
            
            'adresse_depart'      => 'sometimes|string|max:255',
            'ville'               => 'sometimes|string|max:100',
            'code_postal'         => 'sometimes|string|max:10',
            'lat_depart'          => 'sometimes|numeric|between:-90,90',
            'lon_depart'          => 'sometimes|numeric|between:-180,180',

            'adresse_arrivee'     => 'sometimes|string|max:255',
            'ville_arrivee'       => 'sometimes|string|max:100',
            'code_postal_arrivee' => 'sometimes|string|max:10',
            'lat_arrivee'         => 'sometimes|numeric|between:-90,90',
            'lon_arrivee'         => 'sometimes|numeric|between:-180,180',
            
            'objets'              => 'sometimes|array|min:1',
            'box_option'          => 'nullable|in:yes,no',
            'box_id'              => 'nullable|required_if:box_option,yes|integer|exists:storage_boxes,id',
        ]);

        $annonce = DB::table('requests')->where('request_id', $id)->first();
        if (!$annonce) {
            return response()->json(['success' => false, 'message' => 'Annonce introuvable'], 404);
        }

        $updateData = [];
        
        if (isset($data['depart_code'])) {
            $updateData['departure_code'] = $data['depart_code'];
        }
        if (isset($data['arrivee_code'])) {
            $updateData['destination_code'] = $data['arrivee_code'];
        }
        
        if (isset($data['code_postal'])) {
            $updateData['departure_code'] = $data['code_postal'];
        }
        if (isset($data['code_postal_arrivee'])) {
            $updateData['destination_code'] = $data['code_postal_arrivee'];
        }
        if (isset($data['adresse_depart'])) {
            $updateData['departure_address'] = $data['adresse_depart'];
        }
        if (isset($data['ville'])) {
            $updateData['departure_city'] = $data['ville'];
        }
        if (isset($data['lat_depart'])) {
            $updateData['departure_lat'] = $data['lat_depart'];
        }
        if (isset($data['lon_depart'])) {
            $updateData['departure_lon'] = $data['lon_depart'];
        }
        if (isset($data['adresse_arrivee'])) {
            $updateData['destination_address'] = $data['adresse_arrivee'];
        }
        if (isset($data['ville_arrivee'])) {
            $updateData['destination_city'] = $data['ville_arrivee'];
        }
        if (isset($data['lat_arrivee'])) {
            $updateData['destination_lat'] = $data['lat_arrivee'];
        }
        if (isset($data['lon_arrivee'])) {
            $updateData['destination_lon'] = $data['lon_arrivee'];
        }

        if (!empty($updateData)) {
            DB::table('requests')->where('request_id', $id)->update($updateData);
        }

        if (isset($data['objets'])) {
            \Log::info('[AdminController::updateAnnonce] Structure des objets', [
                'objets' => $data['objets']
            ]);
            
            foreach ($data['objets'] as $index => $obj) {
                $delete = $obj['delete'] ?? '0';
                $objId = $obj['id'] ?? null;

                if ($objId && $delete === '1') {
                    try {
                        $photos = DB::table('object_photo')->where('object_id', $objId)->pluck('chemin');
                        foreach ($photos as $file) {
                            $filePath = storage_path("app/public/uploads/{$file}");
                            if (file_exists($filePath)) {
                                unlink($filePath);
                            }
                        }
                        DB::table('object_photo')->where('object_id', $objId)->delete();
                    } catch (\Exception $e) {
                    }
                    DB::table('objects')->where('id', $objId)->delete();
                    continue;
                }

                $objData = [
                    'request_id'  => $id,
                    'nom'         => $obj['nom'] ?? '',
                    'quantite'    => is_numeric($obj['quantite'] ?? '') ? (int) ($obj['quantite']) : 1,
                    'dimensions'  => $obj['dimensions'] ?? '',
                    'poids'       => is_numeric($obj['poids'] ?? '') ? (float) ($obj['poids']) : null,
                    'description' => $obj['description'] ?? '',
                ];

                if ($objId) {
                    DB::table('objects')->where('id', $objId)->update($objData);
                } else {
                    $objId = DB::table('objects')->insertGetId($objData);
                }

                $photos = $request->file("photos_{$index}") ?? [];
                foreach ($photos as $photo) {
                    try {
                        if (!$photo->isValid()) {
                            continue;
                        }

                        $extension = $photo->getClientOriginalExtension();
                        if (empty($extension)) {
                            $extension = 'jpg';
                        }
                        
                        $filename = uniqid() . '_' . time() . '.' . $extension;
                        $uploadPath = storage_path('app/public/uploads/');
                        
                        if (!is_dir($uploadPath)) {
                            mkdir($uploadPath, 0755, true);
                        }
                        
                        $fullPath = $uploadPath . $filename;
                        if (move_uploaded_file($photo->getRealPath(), $fullPath)) {
                            DB::table('object_photo')->insert([
                                'object_id' => $objId,
                                'chemin'    => $filename,
                            ]);
                        }
                    } catch (\Exception $e) {
                    }
                }
            }
        } else {
            $allInputs = $request->all();
            $objets = [];
            
            foreach ($allInputs as $key => $value) {
                if (preg_match('/^objets\[(\d+)\]\[(\w+)\]$/', $key, $matches)) {
                    $index = $matches[1];
                    $field = $matches[2];
                    $objets[$index][$field] = $value;
                }
            }
            
            \Log::info('[AdminController::updateAnnonce] Objets extraits de FormData', [
                'objets' => $objets
            ]);
            
            foreach ($objets as $index => $obj) {
                $delete = $obj['delete'] ?? '0';
                $objId = $obj['id'] ?? null;

                if ($objId && $delete === '1') {
                    try {
                        $photos = DB::table('object_photo')->where('object_id', $objId)->pluck('chemin');
                        foreach ($photos as $file) {
                            $filePath = storage_path("app/public/uploads/{$file}");
                            if (file_exists($filePath)) {
                                unlink($filePath);
                            }
                        }
                        DB::table('object_photo')->where('object_id', $objId)->delete();
                    } catch (\Exception $e) {
                    }
                    DB::table('objects')->where('id', $objId)->delete();
                    continue;
                }

                $objData = [
                    'request_id'  => $id,
                    'nom'         => $obj['nom'] ?? '',
                    'quantite'    => is_numeric($obj['quantite'] ?? '') ? (int) ($obj['quantite']) : 1,
                    'dimensions'  => $obj['dimensions'] ?? '',
                    'poids'       => is_numeric($obj['poids'] ?? '') ? (float) ($obj['poids']) : null,
                    'description' => $obj['description'] ?? '',
                ];

                if ($objId) {
                    DB::table('objects')->where('id', $objId)->update($objData);
                } else {
                    $objId = DB::table('objects')->insertGetId($objData);
                }

                $photos = $request->file("photos_{$index}") ?? [];
                foreach ($photos as $photo) {
                    try {
                        if (!$photo->isValid()) {
                            continue;
                        }

                        $extension = $photo->getClientOriginalExtension();
                        if (empty($extension)) {
                            $extension = 'jpg';
                        }
                        
                        $filename = uniqid() . '_' . time() . '.' . $extension;
                        $uploadPath = storage_path('app/public/uploads/');
                        
                        if (!is_dir($uploadPath)) {
                            mkdir($uploadPath, 0755, true);
                        }
                        
                        $fullPath = $uploadPath . $filename;
                        if (move_uploaded_file($photo->getRealPath(), $fullPath)) {
                            DB::table('object_photo')->insert([
                                'object_id' => $objId,
                                'chemin'    => $filename,
                            ]);
                        }
                    } catch (\Exception $e) {
                    }
                }
            }
        }

        if (isset($data['box_option'])) {
            DB::table('box_assignments')->where('request_id', $id)->delete();

            if ($data['box_option'] === 'yes' && isset($data['box_id'])) {
                DB::table('box_assignments')->insert([
                    'box_id'     => $data['box_id'],
                    'request_id' => $id,
                    'status'     => 'Réservé',
                    'datetime'   => now(),
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Annonce mise à jour avec succès']);
    }

    public function villes(Request $request)
    {
        $code = $request->query('q');

        $response = Http::withoutVerifying()->get('https://geo.api.gouv.fr/communes', [
            'codePostal' => $code,
            'fields'     => 'nom',
            'format'     => 'json',
        ]);

        $communes = collect($response->json())->map(fn($c) => ['ville' => $c['nom']]);

        return response()->json($communes);
    }

    public function getJustificatifsUtilisateur($id)
    {
        $user = DB::table('users')
            ->where('user_id', $id)
            ->select('user_id', 'first_name as prenom', 'last_name as nom', 'email')
            ->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Utilisateur introuvable'], 404);
        }

        $justificatifs = DB::table('justificatifs')
            ->where('user_id', $id)
            ->select('id', 'type', 'description', 'filename', 'statut', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success'       => true,
            'user'          => $user,
            'justificatifs' => $justificatifs,
        ]);
    }

    public function changerStatutJustificatif(Request $request)
    {
        $data = $request->validate([
            'id_justificatif' => 'required|integer|exists:justificatifs,id',
            'statut'          => 'required|in:En attente,Validé,Refusé',
        ]);

        $justificatif = DB::table('justificatifs')->where('id', $data['id_justificatif'])->first();

        DB::table('justificatifs')->where('id', $data['id_justificatif'])->update(['statut' => $data['statut']]);

        $titre    = $data['statut'] === 'Validé' ? '✅ Justificatif validé' : '❌ Justificatif refusé';
        $contenu  = "Votre justificatif de type « {$justificatif->type} » a été {$data['statut']}.";

        NotificationHelper::envoyer($justificatif->user_id, $titre, $contenu);

        return response()->json(['success' => true, 'message' => 'Statut mis à jour avec succès.']);
    }


    public function pendingContracts()
    {
        $pending = Contract::where('contracts.status', 'pending')
            ->join('users', 'contracts.user_id', '=', 'users.user_id')
            ->select(
                'contracts.*',
                'users.first_name',
                'users.last_name',
                'users.email'
            )
            ->orderBy('contracts.start_date', 'asc')
            ->get()
            ->map(function ($contract) {
                return [
                    'contract_id' => $contract->contract_id,
                    'start_date'  => $contract->start_date,
                    'end_date'    => $contract->end_date,
                    'terms'       => $contract->terms,
                    'pdf_path'    => $contract->pdf_path,
                    'status'      => $contract->status,
                    'user_id'     => $contract->user_id,
                    'user' => [
                        'first_name' => $contract->first_name,
                        'last_name'  => $contract->last_name,
                        'email'      => $contract->email
                    ]
                ];
            });

        return response()->json([
            'success' => true,
            'contracts' => $pending
        ]);
    }
    public function rejectContract($id)
    {
        $contract         = Contract::findOrFail($id);
        $contract->status = 'rejected';
        $contract->save();

        NotificationHelper::envoyer(
            $contract->user_id,
            'Contrat refusé',
            "Votre contrat du {$contract->start_date} au {$contract->end_date} a été refusé."
        );

        return response()->json(['success' => true, 'contract' => $contract]);
    }

    public function approveContract($id)
    {
        $contract         = Contract::findOrFail($id);
        $contract->status = 'active';
        $contract->save();

        NotificationHelper::envoyer(
            $contract->user_id,
            'Contrat approuvé',
            "Votre contrat du {$contract->start_date} au {$contract->end_date} a été approuvé."
        );

        return response()->json(['success' => true, 'contract' => $contract]);
    }

    public function tousLesJustificatifs()
    {
        $justificatifs = DB::table('justificatifs')
            ->join('users', 'justificatifs.user_id', '=', 'users.user_id')
            ->select(
                'justificatifs.id',
                'justificatifs.user_id',
                'justificatifs.type',
                'justificatifs.description',
                'justificatifs.filename',
                'justificatifs.statut',
                'justificatifs.created_at',
                'users.first_name',
                'users.last_name',
                'users.email'
            )
            ->orderBy('justificatifs.created_at', 'desc')
            ->get();

        return response()->json([
            'success'        => true,
            'justificatifs'  => $justificatifs
        ]);
    }

    public function getAllUsers()
    {
        $users = DB::table('users')
            ->leftJoin('userrole', 'users.user_id', '=', 'userrole.user_id')
            ->leftJoin('role',      'userrole.role_id', '=', 'role.role_id')
            ->select(
                'users.user_id',
                'users.first_name',
                'users.last_name',
                'users.email',
                'users.banned',
                DB::raw('GROUP_CONCAT(role.role_name) as roles')
            )
            ->groupBy('users.user_id', 'users.first_name', 'users.last_name', 'users.email', 'users.banned')
            ->orderBy('users.last_name')
            ->get();

        $formatted = $users->map(function ($u) {
            return [
                'user_id' => $u->user_id,
                'name'    => trim("{$u->first_name} {$u->last_name}"),
                'email'   => $u->email,
                'roles'   => $u->roles ? explode(',', $u->roles) : [],
                'banned'  => (bool) $u->banned,
            ];
        });

        return response()->json([
            'success' => true,
            'users'   => $formatted
        ]);
    }


    public function toggleBan(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->banned = !$user->banned;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Utilisateur banni/débanni.']);
    }


    public function listPending()
    {
        $users = \App\Models\User::where('is_validated', 0)
            ->with('roles:role_id,role_name')
            ->get()
            ->map(function ($user) {
                return [
                    'user_id'    => $user->user_id,
                    'first_name' => $user->first_name,
                    'last_name'  => $user->last_name,
                    'email'      => $user->email,
                    'role'       => $user->roles->first()->role_name ?? null
                ];
            });

        return response()->json($users);
    }

    public function validateUser($id)
    {
        $user = \App\Models\User::find($id);
        if (!$user) {
            return response()->json(['error' => 'Utilisateur introuvable'], 404);
        }

        $user->is_validated = 1;
        $user->save();

        return response()->json(['success' => true]);
    }

    public function rejectUser($id)
    {
        $user = \App\Models\User::find($id);
        if (!$user) {
            return response()->json(['error' => 'Utilisateur introuvable'], 404);
        }

        $user->delete();

        return response()->json(['success' => true]);
    }

    public function getPrestataires($id = null)
    {
        if ($id) {
            $p = User::with([
                'services.serviceType',
                'justificatifs'
            ])
            ->whereHas('roles', fn($q) => $q->where('role_name','ServiceProvider'))
            ->find($id);

            if (! $p) {
                return response()->json(['error' => 'Utilisateur introuvable'], 404);
            }
            return response()->json($p);
        }

        $prestataires = User::whereHas('roles', function($q){
            $q->where('role_name','ServiceProvider');
        })
        ->get();

        return response()->json($prestataires);
    }


    public function validerPrestataire($id)
    {
        $user = User::findOrFail($id);
        $user->is_validated = 1;
        $user->save();

        // Ajout automatique de la prestation et de la proposition validée
        // On suppose que le service_type (nom) et description sont stockés sur l'utilisateur
        $nom = $user->sector ?? null; // ou autre champ selon ta structure
        $description = $user->description ?? null;
        if ($nom) {
            // 1. Créer le type de service s'il n'existe pas
            $serviceTypeId = DB::table('servicetype')->where('name', $nom)->value('service_type_id');
            if (!$serviceTypeId) {
                $serviceTypeId = DB::table('servicetype')->insertGetId([
                    'name' => $nom,
                    'description' => $description ?? $nom,
                    'is_price_fixed' => 0,
                    'fixed_price' => null
                ]);
            }
            // 2. Créer la prestation si elle n'existe pas déjà
            $exists = DB::table('service')
                ->where('user_id', $user->user_id)
                ->where('service_type_id', $serviceTypeId)
                ->exists();
            if (!$exists) {
                DB::table('service')->insert([
                    'user_id' => $user->user_id,
                    'service_type_id' => $serviceTypeId,
                    'details' => $description ?? $nom,
                    'address' => $user->business_address ?? '',
                    'price' => 0,
                ]);
            }
            // 3. Créer la proposition validée si elle n'existe pas déjà
            $existsProp = DB::table('proposition_de_prestations')
                ->where('user_id', $user->user_id)
                ->where('nom', $nom)
                ->exists();
            if (!$existsProp) {
                DB::table('proposition_de_prestations')->insert([
                    'user_id' => $user->user_id,
                    'nom' => $nom,
                    'description' => $description ?? $nom,
                    'statut' => 'Validé',
                    'created_at' => now(),
                ]);
            } else {
                // Si elle existe déjà, on la passe à Validé
                DB::table('proposition_de_prestations')
                    ->where('user_id', $user->user_id)
                    ->where('nom', $nom)
                    ->update(['statut' => 'Validé']);
            }
        }

        return response()->json(['success' => true]);
    }

    public function refuserPrestataire($id)
    {
        $user = User::findOrFail($id);
        $user->is_validated = -1;
        $user->save();

        return response()->json(['success' => true]);
    }

    public function supprimerPrestataire($id)
    {
        try {
            DB::beginTransaction();

            // Vérifier que l'utilisateur existe et est un prestataire
            $user = DB::table('users')
                ->join('userrole', 'users.user_id', '=', 'userrole.user_id')
                ->join('role',     'userrole.role_id', '=', 'role.role_id')
                ->where('users.user_id', $id)
                ->where('role.role_name', 'ServiceProvider')
                ->first();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Prestataire introuvable ou invalide.'], 404);
            }

            // 1. Supprimer les réservations de services
            $serviceIds = DB::table('service')->where('user_id', $id)->pluck('offered_service_id');
            if ($serviceIds->isNotEmpty()) {
                // Supprimer les créneaux de disponibilité
                DB::table('serviceavailability')->whereIn('offered_service_id', $serviceIds)->delete();
                
                // Supprimer les réservations
                DB::table('reservation')->whereIn('availability_id', function($query) use ($serviceIds) {
                    $query->select('availability_id')
                          ->from('serviceavailability')
                          ->whereIn('offered_service_id', $serviceIds);
                })->delete();
            }

            // 2. Supprimer les services
            DB::table('service')->where('user_id', $id)->delete();

            // 3. Supprimer les propositions de prestations
            DB::table('proposition_de_prestations')->where('user_id', $id)->delete();

            // 4. Supprimer les justificatifs
            DB::table('justificatifs')->where('user_id', $id)->delete();

            // 5. Supprimer les alertes admin
            DB::table('admin_alerts')->where('user_id', $id)->delete();

            // 6. Supprimer les notifications
            DB::table('notifications')->where('user_id', $id)->delete();

            // 7. Supprimer les avis donnés par ce prestataire
            DB::table('review')->where('reviewer_id', $id)->delete();

            // 8. Supprimer les signalements faits par ce prestataire
            DB::table('reports')->where('reporter_id', $id)->delete();

            // 9. Supprimer les messages envoyés par ce prestataire
            DB::table('messages')->where('sender_id', $id)->delete();

            // 10. Supprimer les rôles utilisateur
            DB::table('userrole')->where('user_id', $id)->delete();

            // 11. Supprimer l'utilisateur
            DB::table('users')->where('user_id', $id)->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Prestataire et toutes ses données supprimés avec succès.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur suppression prestataire {$id} : {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ], 500);
        }
    }

    public function showPrestataire($id)
    {
        // Vérifie que l'utilisateur existe et a le rôle ServiceProvider
        $prestataire = DB::table('users')
            ->join('userrole', 'users.user_id', '=', 'userrole.user_id')
            ->join('role', 'userrole.role_id', '=', 'role.role_id')
            ->where('users.user_id', $id)
            ->where('role.role_name', 'ServiceProvider')
            ->select('users.*')
            ->first();

        if (!$prestataire) {
            return response()->json(['success' => false, 'message' => 'Prestataire introuvable ou sans rôle ServiceProvider'], 404);
        }

        // Justificatifs
        $justificatifs = DB::table('justificatifs')
            ->where('user_id', $id)
            ->get();

        // Prestations/services
        $services = DB::table('service')
            ->join('servicetype', 'service.service_type_id', '=', 'servicetype.service_type_id')
            ->where('service.user_id', $id)
            ->select(
                'service.*',
                'servicetype.name as type_name',
                'servicetype.is_price_fixed',
                'servicetype.fixed_price'
            )
            ->get();

        return response()->json([
            'success' => true,
            'prestataire' => [
                'user_id' => $prestataire->user_id,
                'nom' => $prestataire->last_name,
                'prenom' => $prestataire->first_name,
                'email' => $prestataire->email,
                'domaine' => $prestataire->business_name ?? '',
                'description' => $prestataire->description ?? '',
                'is_validated' => $prestataire->is_validated ?? 0,
                'profile_picture' => $prestataire->profile_picture ?? null,
                'phone' => $prestataire->phone ?? '',
                'justificatifs' => $justificatifs,
                'services' => $services
            ]
        ]);
    }

    // Méthodes pour les propositions de types de prestations
    public function getPropositionsTypes()
    {
        $propositions = DB::table('proposition_de_prestations')
            ->join('users', 'proposition_de_prestations.user_id', '=', 'users.user_id')
            ->leftJoin('justificatifs', 'proposition_de_prestations.justificatif_id', '=', 'justificatifs.id')
            ->select(
                'proposition_de_prestations.id',
                'proposition_de_prestations.user_id',
                'proposition_de_prestations.nom',
                'proposition_de_prestations.description',
                'proposition_de_prestations.justificatif_id',
                'proposition_de_prestations.statut',
                'proposition_de_prestations.created_at',
                'users.first_name',
                'users.last_name',
                'users.email',
                'justificatifs.filename as justificatif_filename'
            )
            ->orderBy('proposition_de_prestations.created_at', 'desc')
            ->get();

        $data = $propositions->map(function($p) {
            $justificatif_url = null;
            if ($p->justificatif_filename) {
                $justificatif_url = url('/storage/justificatifs/' . $p->justificatif_filename);
            }

            return [
                'id' => $p->id,
                'user_id' => $p->user_id,
                'nom' => $p->nom,
                'description' => $p->description,
                'justificatif_id' => $p->justificatif_id,
                'statut' => $p->statut,
                'created_at' => $p->created_at,
                'prestataire_nom' => $p->first_name . ' ' . $p->last_name,
                'prestataire_email' => $p->email,
                'justificatif_url' => $justificatif_url
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function validerPropositionType(Request $request, $id)
    {
        $proposition = DB::table('proposition_de_prestations')
            ->where('id', $id)
            ->first();

        if (!$proposition) {
            return response()->json(['success' => false, 'message' => 'Proposition introuvable'], 404);
        }

        // Mettre à jour le statut de la proposition (même si déjà validée ou refusée)
        DB::table('proposition_de_prestations')
            ->where('id', $id)
            ->update(['statut' => 'Validé']);

        // Créer ou mettre à jour le type de service
        $serviceTypeId = DB::table('servicetype')
            ->where('name', $proposition->nom)
            ->value('service_type_id');

        if (!$serviceTypeId) {
            $serviceTypeId = DB::table('servicetype')->insertGetId([
                'name' => $proposition->nom,
                'description' => $proposition->description ?? $proposition->nom,
                'is_price_fixed' => 0,
                'fixed_price' => null
            ]);
        }

        // Envoyer une notification au prestataire
        $motif = $request->input('motif', 'Votre proposition a été validée avec succès.');
        NotificationHelper::envoyer(
            $proposition->user_id,
            'Proposition de type de prestation validée',
            "Votre proposition '$proposition->nom' a été validée. $motif"
        );

        return response()->json(['success' => true, 'message' => 'Type de prestation validé avec succès']);
    }

    public function refuserPropositionType(Request $request, $id)
    {
        $proposition = DB::table('proposition_de_prestations')
            ->where('id', $id)
            ->first();

        if (!$proposition) {
            return response()->json(['success' => false, 'message' => 'Proposition introuvable'], 404);
        }

        // Mettre à jour le statut de la proposition (même si déjà validée ou refusée)
        DB::table('proposition_de_prestations')
            ->where('id', $id)
            ->update(['statut' => 'Refusé']);

        // Supprimer la prestation correspondante dans la table service
        $serviceTypeId = DB::table('servicetype')
            ->where('name', $proposition->nom)
            ->value('service_type_id');
        if ($serviceTypeId) {
            DB::table('service')
                ->where('user_id', $proposition->user_id)
                ->where('service_type_id', $serviceTypeId)
                ->delete();
        }

        // Envoyer une notification au prestataire
        $motif = $request->input('motif', 'Votre proposition a été refusée.');
        NotificationHelper::envoyer(
            $proposition->user_id,
            'Proposition de type de prestation refusée',
            "Votre proposition '$proposition->nom' a été refusée. Motif : $motif"
        );

        return response()->json(['success' => true, 'message' => 'Type de prestation refusé']);
    }

    /**
     * Ajout manuel d'une prestation à un prestataire par un admin
     */
    public function ajouterPrestationPourPrestataire(Request $request, $id)
    {
        // Vérification admin
        $user = Session::get('user');
        if (!$user || !in_array('Admin', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $validated = $request->validate([
            'service_type_id' => 'required|integer|exists:servicetype,service_type_id',
            'description'     => 'required|string|max:255',
            'price'           => 'required|numeric|min:0',
        ]);

        // Vérifier que le prestataire existe
        $prestataire = DB::table('users')
            ->join('userrole', 'users.user_id', '=', 'userrole.user_id')
            ->join('role', 'userrole.role_id', '=', 'role.role_id')
            ->where('users.user_id', $id)
            ->where('role.role_name', 'ServiceProvider')
            ->first();
        if (!$prestataire) {
            return response()->json(['success' => false, 'message' => 'Prestataire introuvable'], 404);
        }

        // Vérifier si la prestation existe déjà
        $exists = DB::table('service')
            ->where('user_id', $id)
            ->where('service_type_id', $validated['service_type_id'])
            ->exists();
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Cette prestation existe déjà pour ce prestataire.'], 409);
        }

        // Créer la prestation (service)
        DB::table('service')->insert([
            'user_id'         => $id,
            'service_type_id' => $validated['service_type_id'],
            'details'         => $validated['description'],
            'address'         => '',
            'price'           => $validated['price'],
        ]);

        // Récupérer le nom du type de prestation
        $type = DB::table('servicetype')->where('service_type_id', $validated['service_type_id'])->first();
        $nom = $type ? $type->name : '';

        // Créer la proposition validée
        DB::table('proposition_de_prestations')->insert([
            'user_id'    => $id,
            'nom'        => $nom,
            'description'=> $validated['description'],
            'statut'     => 'Validé',
            'created_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Prestation ajoutée avec succès.']);
    }

    public function allContracts()
    {
        $contracts = \App\Models\Contract::join('users', 'contracts.user_id', '=', 'users.user_id')
            ->select(
                'contracts.*',
                'users.first_name',
                'users.last_name',
                'users.email'
            )
            ->orderBy('contracts.start_date', 'asc')
            ->get()
            ->map(function ($contract) {
                return [
                    'contract_id' => $contract->contract_id,
                    'start_date'  => $contract->start_date,
                    'end_date'    => $contract->end_date,
                    'terms'       => $contract->terms,
                    'pdf_path'    => $contract->pdf_path,
                    'status'      => $contract->status,
                    'user_id'     => $contract->user_id,
                    'user' => [
                        'first_name' => $contract->first_name,
                        'last_name'  => $contract->last_name,
                        'email'      => $contract->email
                    ]
                ];
            });

        return response()->json([
            'success' => true,
            'contracts' => $contracts
        ]);
    }

    public function getAdminAlerts()
    {
        try {
            $alerts = DB::table('admin_alerts as a')
                ->leftJoin('users as u', 'a.user_id', '=', 'u.user_id')
                ->select(
                    'a.*',
                    'u.first_name',
                    'u.last_name', 
                    'u.email',
                    'u.phone'
                )
                ->orderBy('a.created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'alerts' => $alerts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des alertes: ' . $e->getMessage()
            ], 500);
        }
    }

}