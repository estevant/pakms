<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use App\Helpers\NotificationHelper;

class SuiviController extends Controller
{
    public function index(Request $request)
    {
        $user = Session::get('user');
        if (!$user || !in_array('Deliverer', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $id = $request->query('id_annonce');
        if (!is_numeric($id)) {
            return response()->json(['success' => false, 'message' => 'ID manquant'], 400);
        }

        $events = DB::table('tracking_events')
            ->where('request_id', $id)
            ->orderBy('created_at')
            ->get();

        $annonce = DB::table('requests')
            ->select(
                'departure_address',
                'departure_city',
                'departure_code',
                'destination_address',
                'destination_city',
                'destination_code'
            )
            ->where('request_id', $id)
            ->first();

        if (!$annonce) {
            return response()->json(['success' => false, 'message' => 'Annonce introuvable'], 404);
        }

        return response()->json([
            'success'           => true,
            'depart_address'    => $annonce->departure_address,
            'depart_city'       => $annonce->departure_city,
            'depart_code'       => $annonce->departure_code,
            'arrivee_address'   => $annonce->destination_address,
            'arrivee_city'      => $annonce->destination_city,
            'arrivee_code'      => $annonce->destination_code,
            'events'            => $events
        ]);
    }

    public function store(Request $request)
    {
        $user = Session::get('user');
        if (!$user || !in_array('Deliverer', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $data = $request->validate([
            'id_annonce'    => 'required|integer|exists:requests,request_id',
            'status'        => ['required', Rule::in([
                'Pris en charge',
                'En cours',
                'Déposé en box',
                'Retiré du box',
                'Livré'
            ])],
            'description'   => 'nullable|string|max:255',
            'location_city' => 'nullable|string|max:100',
            'location_code' => 'nullable|string|max:10',
        ]);

        $assign = DB::table('deliveryassignment')
            ->where('request_id',  $data['id_annonce'])
            ->where('deliverer_id', $user['user_id'])
            ->first();

        if (!$assign) {
            return response()->json(['success' => false, 'message' => 'Vous n’êtes pas affecté à cette annonce'], 403);
        }

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
            $data['description'] ?: "Votre colis est passé à l’étape : {$data['status']}"
        );

        return response()->json([
            'success'   => true,
            'message'   => 'Événement ajouté.',
            'event_id'  => $eventId
        ]);
    }
}