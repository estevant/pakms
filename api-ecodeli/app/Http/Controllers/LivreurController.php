<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Helpers\NotificationHelper;
use Illuminate\Validation\Rule;

class LivreurController extends Controller
{
    public function disponibles(Request $request)
    {
        $user = Session::get('user');
        if (!$user || !in_array('Deliverer', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $query = DB::table('requests')
            ->leftJoin('deliveryassignment', 'requests.request_id', '=', 'deliveryassignment.request_id')
            ->leftJoin('box_assignments',    'requests.request_id', '=', 'box_assignments.request_id')
            ->leftJoin('storage_boxes',      'box_assignments.box_id', '=', 'storage_boxes.id')   // clé primaire correcte
            ->whereNull('deliveryassignment.request_id')
            ->where('requests.user_id', '!=', $user['user_id'])
            ->select(
                'requests.request_id',
                'requests.user_id',
                'requests.departure_city',
                'requests.departure_code',
                'requests.departure_address',
                'requests.destination_city',
                'requests.destination_code',
                'requests.destination_address',
                'requests.created_at',
                'requests.is_split            as partial',
                'storage_boxes.label          as box_label',
                'storage_boxes.location_city  as box_city'
            )
            ->orderByDesc('requests.request_id');

        if ($request->filled('depart_code')) {
            $query->where('requests.departure_code', $request->depart_code);
        }
        if ($request->filled('arrivee_code')) {
            $query->where('requests.destination_code', $request->arrivee_code);
        }

        $annonces = $query->get();

        return response()->json([
            'success'  => true,
            'annonces' => $annonces->map(fn($a) => [
                'id_annonce'       => $a->request_id,
                'user_id'          => $a->user_id,
                'depart'           => $a->departure_city,
                'depart_code'      => $a->departure_code,
                'depart_address'   => $a->departure_address,      //  nouvel attribut
                'arrivee'          => $a->destination_city,
                'arrivee_code'     => $a->destination_code,
                'arrivee_address'  => $a->destination_address,    //  nouvel attribut
                'date_creation'    => $a->created_at ? date('Y-m-d', strtotime($a->created_at)) : '—',
                'box_label'        => $a->box_label,
                'box_city'         => $a->box_city,
                'partial'          => (bool) $a->partial,
            ]),
        ]);
    }

    public function attribuer(Request $request)
    {
        $user = Session::get('user');
        if (!$user || !in_array('Deliverer', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $validated = $request->validate(['id_annonce' => 'required|integer']);

        $annonce = DB::table('requests')
            ->select(
                'request_id',
                'user_id',
                'departure_city',
                'departure_code',
                'destination_city',
                'destination_code'
            )
            ->where('request_id', $validated['id_annonce'])
            ->first();

        if (!$annonce) {
            return response()->json(['success' => false, 'message' => 'Annonce introuvable'], 404);
        }

        if ($annonce->user_id == $user['user_id']) {
            return response()->json(['success' => false, 'message' => 'Vous ne pouvez pas vous attribuer votre propre annonce'], 403);
        }

        $existe = DB::table('deliveryassignment')
            ->where('request_id', $validated['id_annonce'])
            ->exists();
        if ($existe) {
            return response()->json(['success' => false, 'message' => 'Annonce déjà attribuée'], 409);
        }

        DB::beginTransaction();
        try {
            DB::table('deliveryassignment')->insert([
                'request_id'           => $validated['id_annonce'],
                'deliverer_id'         => $user['user_id'],
                'pickup_address'       => $annonce->departure_city,
                'pickup_postal_code'   => $annonce->departure_code,
                'delivery_address'     => $annonce->destination_city,
                'delivery_postal_code' => $annonce->destination_code,
                'status'               => 'Acceptée',
                'start_datetime'       => now(),
            ]);

            NotificationHelper::envoyer(
                $annonce->user_id,
                'Livraison acceptée',
                "Un livreur a accepté votre annonce entre {$annonce->departure_city} et {$annonce->destination_city}."
            );

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Annonce attribuée.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur interne'], 500);
        }
    }


    public function livree(Request $request)
    {
        $user = Session::get('user');
        if (! $user || !in_array('Deliverer', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

    if (! $user || !in_array('Deliverer', $user['roles'])) {
        return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
    }

    $validated = $request->validate([
        'id_annonce' => 'required|integer',
    ]);

    DB::beginTransaction();
    try {
        DB::table('deliveryassignment')
            ->where('request_id', $validated['id_annonce'])
            ->where('deliverer_id', $user['user_id'])
            ->update([
                'status' => 'Livrée',
                'end_datetime' => now()
            ]);

        $annonce = DB::table('requests')->where('request_id', $validated['id_annonce'])->first();
        if (!$annonce) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Annonce introuvable'], 404);
        }

        NotificationHelper::envoyer(
            $annonce->user_id,
            'Livraison effectuée',
            "Votre annonce de livraison entre {$annonce->departure_city} et {$annonce->destination_city} a été marquée comme livrée."
        );

        $prix = $annonce->prix_negocie_cents ?? $annonce->prix_cents;

        if ($prix && $prix > 0) {
            DB::table('wallets')->updateOrInsert(
                ['user_id' => $user['user_id']],
                ['balance_cent' => DB::raw("balance_cent + $prix"), 'updated_at' => now()]
            );

            DB::table('payments')->insert([
                'payment_id'   => uniqid('wallet_'),
                'payer_id'     => $annonce->user_id,
                'payee_id'     => $user['user_id'],
                'payment_type' => 'Livraison',
                'amount'       => $prix,
                'status'       => 'Payé',
                'payment_date' => now(),
            ]);
        }

        DB::commit();
        return response()->json(['success' => true]);

    } catch (\Throwable $e) {
        DB::rollBack();
        \Log::error('❌ Erreur lors du marquage Livrée : ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Erreur interne'], 500);
    }
}

    public function mes(Request $request)
    {
        $user = Session::get('user');
        if (!$user || !in_array('Deliverer', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $annonces = DB::table('deliveryassignment')
            ->join('requests',          'deliveryassignment.request_id', '=', 'requests.request_id')
            ->leftJoin('box_assignments', 'requests.request_id',          '=', 'box_assignments.request_id')
            ->leftJoin('storage_boxes', 'box_assignments.box_id',        '=', 'storage_boxes.id')
            ->where('deliveryassignment.deliverer_id', $user['user_id'])
            ->where('deliveryassignment.status', '!=', 'Livrée')
            ->select(
                'requests.request_id',
                'requests.departure_city',
                'requests.departure_code',
                'requests.departure_address',
                'requests.destination_city',
                'requests.destination_code',
                'requests.destination_address',
                'requests.created_at',
                'requests.is_split           as partial',
                'deliveryassignment.status',
                'storage_boxes.label         as box_label',
                'storage_boxes.location_city as box_city'
            )
            ->orderByDesc('requests.request_id')
            ->get();

        return response()->json([
            'success'    => true,
            'livraisons' => $annonces->map(fn($a) => [
                'id_annonce'      => $a->request_id,
                'depart'          => $a->departure_city,
                'depart_code'     => $a->departure_code,
                'depart_address'  => $a->departure_address,
                'arrivee'         => $a->destination_city,
                'arrivee_code'    => $a->destination_code,
                'arrivee_address' => $a->destination_address,
                'date_creation'   => $a->created_at ? date('Y-m-d', strtotime($a->created_at)) : '—',
                'statut'          => $a->status,
                'box_label'       => $a->box_label,
                'box_city'        => $a->box_city,
                'partial'         => (bool) $a->partial,
            ]),
        ]);
    }


    public function desattribuer(Request $request)
    {
        $user = Session::get('user');
        if (! $user || !in_array('Deliverer', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $validated = $request->validate([
            'id_annonce' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            DB::table('deliveryassignment')
                ->where('request_id', $validated['id_annonce'])
                ->where('deliverer_id', $user['user_id'])
                ->delete();

            $annonce = DB::table('requests')->where('request_id', $validated['id_annonce'])->first();
            if ($annonce) {
                NotificationHelper::envoyer(
                    $annonce->user_id,
                    'Livraison annulée',
                    "Le livreur en charge de votre annonce entre {$annonce->departure_city} et {$annonce->destination_city} s'est désisté."
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Désattribution effectuée.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur lors de la désattribution.'], 500);
        }
    }

    public function terminees(Request $request)
    {
        $user = Session::get('user');
        if (!$user || !in_array('Deliverer', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $rows = DB::table('deliveryassignment')
            ->join('requests',          'deliveryassignment.request_id', '=', 'requests.request_id')
            ->leftJoin('box_assignments', 'requests.request_id',          '=', 'box_assignments.request_id')
            /* clé primaire correcte */
            ->leftJoin('storage_boxes', 'box_assignments.box_id',        '=', 'storage_boxes.id')
            ->where('deliveryassignment.deliverer_id', $user['user_id'])
            ->where('deliveryassignment.status', 'Livrée')
            ->select(
                'requests.request_id',
                'requests.departure_city',
                'requests.departure_code',
                'requests.destination_city',
                'requests.destination_code',
                'deliveryassignment.end_datetime',
                'storage_boxes.label         as box_label',
                'storage_boxes.location_city as box_city'
            )
            ->orderByDesc('deliveryassignment.end_datetime')
            ->get();

        return response()->json([
            'success'    => true,
            'livraisons' => $rows->map(fn($l) => [
                'id_annonce'    => $l->request_id,
                'depart'        => $l->departure_city,
                'depart_code'   => $l->departure_code,
                'arrivee'       => $l->destination_city,
                'arrivee_code'  => $l->destination_code,
                'ended_at'      => date('Y-m-d H:i', strtotime($l->end_datetime)),
                'statut'        => 'Livrée',
                'box_label'     => $l->box_label,
                'box_city'      => $l->box_city,
            ]),
        ]);
    }
    public function enCours(Request $request)
    {
        $user = Session::get('user');
        if (! $user || !in_array('Deliverer', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $validated = $request->validate([
            'id_annonce' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            DB::table('deliveryassignment')
                ->where('request_id', $validated['id_annonce'])
                ->where('deliverer_id', $user['user_id'])
                ->update([
                    'status' => 'En cours',
                    'pickup_datetime' => now()
                ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Livraison en cours.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur lors de la mise à jour.'], 500);
        }
    }

    public function annuler(Request $request)
    {
        $user = Session::get('user');
        if (! $user || !in_array('Deliverer', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $validated = $request->validate([
            'id_annonce' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            DB::table('deliveryassignment')
                ->where('request_id', $validated['id_annonce'])
                ->where('deliverer_id', $user['user_id'])
                ->update(['status' => 'Annulée']);

            $annonce = DB::table('requests')->where('request_id', $validated['id_annonce'])->first();
            if ($annonce) {
                NotificationHelper::envoyer(
                    $annonce->user_id,
                    'Livraison annulée',
                    "Le livreur en charge de votre annonce entre {$annonce->departure_city} et {$annonce->destination_city} s'est désisté."
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Livraison annulée.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur lors de l\'annulation.'], 500);
        }
    }

    public function addTracking(Request $request)
    {
        $user = Session::get('user');
        if (!$user || !in_array('Deliverer', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $data = $request->validate([
            'id_annonce'     => 'required|integer|exists:requests,request_id',
            'status'         => ['required', Rule::in([
                'Pris en charge',
                'En cours',
                'Déposé en box',
                'Retiré du box',
                'Livré'
            ])],
            'description'    => 'nullable|string|max:255',
            'location_city'  => 'nullable|string|max:100',
            'location_code'  => 'nullable|string|max:10',
        ]);

        $assign = DB::table('deliveryassignment')
            ->where('request_id', $data['id_annonce'])
            ->where('deliverer_id', $user['user_id'])
            ->first();
        if (!$assign)
            return response()->json(['success' => false, 'message' => 'Vous n\'etes pas affecte a cette annonce'], 403);

        $eventId = DB::table('tracking_events')->insertGetId([
            'request_id'    => $data['id_annonce'],
            'deliverer_id'  => $user['user_id'],
            'status'        => $data['status'],
            'description'   => $data['description'],
            'location_city' => $data['location_city'],
            'location_code' => $data['location_code'],
            'created_at'    => now(),
        ]);

        $annonce = DB::table('requests')->where('request_id', $data['id_annonce'])->first();
        NotificationHelper::envoyer(
            $annonce->user_id,
            "📦  Suivi colis : {$data['status']}",
            $data['description'] ?: "Votre colis est passé à l'étape : {$data['status']}"
        );

        return response()->json(['success' => true, 'message' => 'Évènement ajouté.', 'event_id' => $eventId]);
    }

    public function listTracking(Request $request)
    {
        $user = Session::get('user');
        if (!$user || !in_array('Deliverer', $user['roles']))
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);

        $id = $request->query('id_annonce');
        if (!$id) return response()->json(['success' => false, 'message' => 'id_annonce manquant'], 400);

        $events = DB::table('tracking_events')
            ->where('request_id', $id)
            ->orderBy('created_at')
            ->get();

        return response()->json(['success' => true, 'events' => $events]);
    }

    public function show(Request $request)
    {
        $user = Session::get('user');
        if (!$user || !in_array('Deliverer', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $id = $request->query('id');
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID manquant'], 400);
        }

        // Vérifier que le livreur est bien assigné à cette livraison
        $assignment = DB::table('deliveryassignment')
            ->where('request_id', $id)
            ->where('deliverer_id', $user['user_id'])
            ->first();

        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'Livraison non trouvée ou non autorisée'], 404);
        }

        // Récupérer les détails de la livraison
        $livraison = DB::table('requests')
            ->leftJoin('deliveryassignment', 'requests.request_id', '=', 'deliveryassignment.request_id')
            ->leftJoin('users', 'requests.user_id', '=', 'users.user_id')
            ->leftJoin('objects', 'requests.request_id', '=', 'objects.request_id')
            ->where('requests.request_id', $id)
            ->where('deliveryassignment.deliverer_id', $user['user_id'])
            ->select(
                'requests.*',
                'deliveryassignment.status as delivery_status',
                'deliveryassignment.start_datetime',
                'deliveryassignment.end_datetime',
                'users.first_name',
                'users.last_name',
                'users.phone',
                'objects.nom as object_name',
                'objects.quantite',
                'objects.poids',
                'objects.description as object_description'
            )
            ->first();

        if (!$livraison) {
            return response()->json(['success' => false, 'message' => 'Livraison non trouvée'], 404);
        }

        // Récupérer tous les objets de cette livraison
        $objets = DB::table('objects')
            ->where('request_id', $id)
            ->get();

        return response()->json([
            'success' => true,
            'livraison' => [
                'id_annonce' => $livraison->request_id,
                'depart' => $livraison->departure_city,
                'depart_code' => $livraison->departure_code,
                'depart_address' => $livraison->departure_address,
                'arrivee' => $livraison->destination_city,
                'arrivee_code' => $livraison->destination_code,
                'arrivee_address' => $livraison->destination_address,
                'statut' => $livraison->delivery_status,
                'date_creation' => $livraison->created_at,
                'start_datetime' => $livraison->start_datetime,
                'end_datetime' => $livraison->end_datetime,
                'client' => [
                    'nom' => $livraison->first_name . ' ' . $livraison->last_name,
                    'telephone' => $livraison->phone
                ],
                'objets' => $objets->map(fn($obj) => [
                    'nom' => $obj->nom,
                    'quantite' => $obj->quantite,
                    'poids' => $obj->poids,
                    'description' => $obj->description
                ])
            ]
        ]);
    }
}