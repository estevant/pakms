<?php

namespace App\Http\Controllers;

use App\Models\Request as RequestModel;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Helpers\NotificationHelper;
use App\Services\RoadService;
use App\Services\PricingService;


class AnnonceController extends Controller
{
    public function villes(HttpRequest $request)
    {
        $code = $request->query('q');

        $response = Http::withoutVerifying()->get('https://geo.api.gouv.fr/communes', [
            'codePostal' => $code,
            'fields'     => 'nom',
            'format'     => 'json',
        ]);

        $communes = collect($response->json())
            ->map(fn($c) => ['ville' => $c['nom']]);

        return response()->json($communes);
    }

    public function submit(HttpRequest $request, PricingService $pricing, RoadService $road)
    {
        /* Auth */
        $session = Session::get('user');
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }
        $user     = \App\Models\User::with('roles')->findOrFail($session['user_id']);
        $isSeller = $user->isSeller();

        /* Validation */
        $validated = $request->validate([
            'objets'               => 'required|json',

            'adresse_depart'       => 'required|string|max:255',
            'ville'                => 'required|string|max:100',
            'code_postal'          => 'required|string|max:10',
            'lat_depart'           => 'required|numeric|between:-90,90',
            'lon_depart'           => 'required|numeric|between:-180,180',

            'adresse_arrivee'      => 'required|string|max:255',
            'ville_arrivee'        => 'required|string|max:100',
            'code_postal_arrivee'  => 'required|string|max:10',
            'lat_arrivee'          => 'required|numeric|between:-90,90',
            'lon_arrivee'          => 'required|numeric|between:-180,180',

            'box_option'           => 'nullable|in:yes,no',
            'box_id' => 'nullable|required_if:box_option,yes|integer|exists:storage_boxes,id',
        ]);

        /* Distance routière */
        $coordsA    = ['lat' => (float) $validated['lat_depart'],  'lon' => (float) $validated['lon_depart']];
        $coordsB    = ['lat' => (float) $validated['lat_arrivee'], 'lon' => (float) $validated['lon_arrivee']];
        $distanceKm = $road->distanceKm($coordsA, $coordsB)
            ?? abort(502, 'Impossible de calculer la distance');

        /* Préparation données annonce */
        $data = [
            'user_id'             => $user->user_id,
            'type'                => $isSeller ? 'merchant' : 'client',

            'departure_city'      => $validated['ville'],
            'departure_code'      => $validated['code_postal'],
            'departure_address'   => $validated['adresse_depart'],
            'departure_lat'       => $coordsA['lat'],
            'departure_lon'       => $coordsA['lon'],

            'destination_city'    => $validated['ville_arrivee'],
            'destination_code'    => $validated['code_postal_arrivee'],
            'destination_address' => $validated['adresse_arrivee'],
            'destination_lat'     => $coordsB['lat'],
            'destination_lon'     => $coordsB['lon'],

            'distance'            => $distanceKm,
        ];

        /* Boucle objets pour totaux */
        $objects      = json_decode($validated['objets'], true);
        $totalWeight  = 0.0;
        $totalVolume  = 0.0;

        foreach ($objects as $o) {
            $l = isset($o['length']) ? (float) $o['length'] : 0;
            $w = isset($o['width'])  ? (float) $o['width']  : 0;
            $h = isset($o['height']) ? (float) $o['height'] : 0;
            $p = isset($o['poids'])  && is_numeric($o['poids']) ? (float) $o['poids'] : 0;

            $totalWeight += $p;
            $totalVolume += $l * $w * $h;
        }

        if ($isSeller) {
            $data['poids']      = $totalWeight;
            $data['volume_m3']  = $totalVolume;
            $data['prix_cents'] = $pricing->calculate($totalWeight, $totalVolume, $distanceKm);
        }

        /* Transaction */
        DB::beginTransaction();
        try {
            $req = RequestModel::create($data);

            $photoTableExists = true;
            try {
                DB::table('object_photo')->limit(1)->get();
            } catch (\Exception $e) {
                $photoTableExists = false;
            }

            $photosProvided = false;
            $photoSaveErrors = [];

            foreach ($objects as $index => $o) {
                $objectId = DB::table('objects')->insertGetId([
                    'request_id' => $req->request_id,
                    'nom'        => $o['nom'],
                    'quantite'   => is_numeric($o['quantite']) ? (int) $o['quantite'] : 1,
                    'poids'      => isset($o['poids']) ? (float) $o['poids'] : null,
                    'length'     => $o['length'] ?? null,
                    'width'      => $o['width']  ?? null,
                    'height'     => $o['height'] ?? null,
                    'description' => $o['description'] ?? null,
                ]);

                $photos = $request->file("photos_{$index}") ?? [];
                if (!empty($photos)) {
                    $photosProvided = true;
                    
                    if (!$photoTableExists) {
                        $photoSaveErrors[] = "La table des photos n'existe pas. Impossible de sauvegarder les photos pour l'objet '{$o['nom']}'.";
                        continue;
                    }

                    foreach ($photos as $photo) {
                        try {
                            if (!$photo->isValid()) {
                                throw new \Exception('Fichier invalide');
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
                            if (!move_uploaded_file($photo->getRealPath(), $fullPath)) {
                                throw new \Exception('Impossible de déplacer le fichier uploadé');
                            }

                            DB::table('object_photo')->insert([
                                'object_id' => $objectId,
                                'chemin'    => $filename,
                            ]);
                        } catch (\Exception $e) {
                            $photoSaveErrors[] = "Erreur lors de la sauvegarde d'une photo pour l'objet '{$o['nom']}': " . $e->getMessage();
                        }
                    }
                }
            }

            if ($photosProvided && !empty($photoSaveErrors)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la sauvegarde des photos',
                    'errors'  => $photoSaveErrors,
                    'suggestion' => 'Veuillez créer la table object_photo en exécutant le script SQL fourni.'
                ], 500);
            }

            if (($validated['box_option'] ?? '') === 'yes') {
                DB::table('box_assignments')->insert([
                    'box_id'     => $validated['box_id'],
                    'request_id' => $req->request_id,
                    'status'     => 'Réservé',
                    'datetime'   => now(),
                ]);
            }

            DB::commit();
            $this->notifierLivreursPotentiels($req);

            $message = 'Annonce créée avec ID ' . $req->request_id;
            if ($photosProvided && $photoTableExists) {
                $message .= ' avec photos sauvegardées.';
            } elseif ($photosProvided && !$photoTableExists) {
                $message .= ' mais sans photos (table manquante).';
            }

            return response()->json([
                'success'      => true,
                'message'      => $message,
                'request_id'   => $req->request_id,
                'photos_saved' => $photosProvided && $photoTableExists,
                'photos_provided' => $photosProvided,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur interne',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    public function mes()
    {
        $user = Session::get('user');
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $rows = DB::table('requests')
            ->leftJoin('deliveryassignment', 'requests.request_id', '=', 'deliveryassignment.request_id')
            ->where('requests.user_id', $user['user_id'])
            ->select([
                'requests.request_id',
                'requests.departure_city',
                'requests.departure_code',
                'requests.destination_city',
                'requests.destination_code',
                'requests.created_at',
                'requests.is_split',
                'deliveryassignment.status as statut',
                'deliveryassignment.deliverer_id'
            ])
            ->orderByDesc('requests.request_id')
            ->get();

        $annonces = $rows->map(function($a) {
            $objets = DB::table('objects')->where('request_id', $a->request_id)->get();
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

            return [
                'id_annonce'    => $a->request_id,
                'depart'        => $a->departure_city,
                'depart_code'   => $a->departure_code,
                'arrivee'       => $a->destination_city,
                'arrivee_code'  => $a->destination_code,
                'date_creation' => $a->created_at ? date('Y-m-d', strtotime($a->created_at)) : '—',
                'statut'        => $a->statut ?? 'En attente',
                'livreur_id'    => $a->deliverer_id,
                'is_split'      => (int) $a->is_split,
                'objets'        => $objets,
            ];
        });

        return response()->json(['success' => true, 'annonces' => $annonces]);
    }

    public function delete(HttpRequest $request)
    {
        $id = $request->input('id_annonce');

        DB::transaction(function () use ($id) {
            DB::table('delivery_handoffs')->whereIn(
                'assignment_id',
                DB::table('deliveryassignment')->where('request_id', $id)->pluck('assignment_id')
            )->delete();

            DB::table('deliveryassignment')->where('request_id', $id)->delete();

            DB::table('box_assignments')->where('request_id', $id)->delete();

            DB::table('requests')->where('request_id', $id)->delete();
        });

        return response()->json(['success' => true, 'message' => 'Annonce supprimée']);
    }

    public function show(HttpRequest $request)
    {
        $id = $request->query('id');
        if (!$id || !is_numeric($id)) {
            return response()->json(['success' => false, 'message' => 'ID invalide'], 400);
        }

        $user = Session::get('user');
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $annonce = RequestModel::with('user')->find($id);
        if (!$annonce) {
            return response()->json(['success' => false, 'message' => 'Annonce introuvable'], 404);
        }

        $isAdmin = in_array('Admin', $user['roles']);
        $isOwner = ($annonce->user_id === $user['user_id']);
        if (!$isAdmin && !$isOwner) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $deliveryAssignment = DB::table('deliveryassignment')
            ->where('request_id', $id)
            ->select('status')
            ->first();
        
        $statut = $deliveryAssignment ? $deliveryAssignment->status : 'en_attente';

        $objets = DB::table('objects')->where('request_id', $id)->get();

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

        $box = DB::table('box_assignments')
            ->join('storage_boxes', 'box_assignments.box_id', '=', 'storage_boxes.id')
            ->where('box_assignments.request_id', $id)
            ->select(
                'storage_boxes.id   as box_id',
                'storage_boxes.label',
                'storage_boxes.location_city',
                'box_assignments.status'
            )
            ->first();

        $data = [
            // adresses et coordonnées complètes
            'depart_address'  => $annonce->departure_address,
            'depart_lat'      => $annonce->departure_lat,
            'depart_lon'      => $annonce->departure_lon,
            'arrivee_address' => $annonce->destination_address,
            'arrivee_lat'     => $annonce->destination_lat,
            'arrivee_lon'     => $annonce->destination_lon,

            // anciennes infos
            'depart'        => $annonce->departure_city,
            'depart_code'   => $annonce->departure_code,
            'arrivee'       => $annonce->destination_city,
            'arrivee_code'  => $annonce->destination_code,
            'date_creation' => $this->formatDate($annonce->created_at),
            'statut'        => $statut,

            // client
            'client_prenom' => $annonce->user->first_name,
            'client_nom'    => $annonce->user->last_name,
            'client_email'  => $annonce->user->email,

            // objets avec photos
            'objets' => $objets,

            // box
            'box_id'     => $box->box_id ?? null,
            'box_label'  => $box->label ?? null,
            'box_city'   => $box->location_city ?? null,
            'box_status' => $box->status ?? null,
        ];

        return response()->json(['success' => true, 'annonce' => $data]);
    }


    public function update(HttpRequest $request)
    {
        \Log::info('[AnnonceController::update] Début de la méthode', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'source' => $request->input('source', 'inconnu')
        ]);
        
        \Log::info('[AnnonceController::update] Données reçues', [
            'all_input' => $request->all(),
            'files' => $request->allFiles()
        ]);
        
        $user = Session::get('user');
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        /* ---------- validation incluant adresses et coordonnées ---------- */
        $validated = $request->validate([
            'id_annonce'          => 'required|integer',

            'adresse_depart'      => 'required|string|max:255',
            'ville'               => 'required|string|max:100',
            'code_postal'         => 'required|string|max:10',
            'lat_depart'          => 'required|numeric|between:-90,90',
            'lon_depart'          => 'required|numeric|between:-180,180',

            'adresse_arrivee'     => 'required|string|max:255',
            'ville_arrivee'       => 'required|string|max:100',
            'code_postal_arrivee' => 'required|string|max:10',
            'lat_arrivee'         => 'required|numeric|between:-90,90',
            'lon_arrivee'         => 'required|numeric|between:-180,180',

            'objets'              => 'required|array|min:1',
            'box_option'          => 'nullable|in:yes,no',
            'box_id' => 'nullable|required_if:box_option,yes|integer|exists:storage_boxes,id',
        ]);

        DB::beginTransaction();
        try {
            $annonce = RequestModel::where('request_id', $validated['id_annonce'])->first();

            if (!$annonce) {
                return response()->json(['success' => false, 'message' => 'Annonce non trouvée'], 404);
            }

            $isOwner = $annonce->user_id == $user['user_id'];
            $isAdmin = in_array('Admin', $user['roles'] ?? []);
            
            if (!$isOwner && !$isAdmin) {
                return response()->json(['success' => false, 'message' => 'Accès refusé - vous n\'êtes pas autorisé à modifier cette annonce'], 403);
            }

            /* ---------- mise à jour des champs adresse/coordonnées ---------- */
            $annonce->departure_address   = $validated['adresse_depart'];
            $annonce->departure_city      = $validated['ville'];
            $annonce->departure_code      = $validated['code_postal'];
            $annonce->departure_lat       = $validated['lat_depart'];
            $annonce->departure_lon       = $validated['lon_depart'];

            $annonce->destination_address = $validated['adresse_arrivee'];
            $annonce->destination_city    = $validated['ville_arrivee'];
            $annonce->destination_code    = $validated['code_postal_arrivee'];
            $annonce->destination_lat     = $validated['lat_arrivee'];
            $annonce->destination_lon     = $validated['lon_arrivee'];

            $annonce->save();

            /* ---------- gestion objets (inchangée) ---------- */
            foreach ($validated['objets'] as $index => $obj) {
                $delete = $obj['delete'] ?? '0';
                $id     = $obj['id'] ?? null;

                if ($id && $delete === '1') {
                    try {
                        $photos = DB::table('object_photo')->where('object_id', $id)->pluck('chemin');
                        foreach ($photos as $file) {
                            $filePath = storage_path("app/public/uploads/{$file}");
                            if (file_exists($filePath)) {
                                unlink($filePath);
                            }
                        }
                        DB::table('object_photo')->where('object_id', $id)->delete();
                    } catch (\Exception $e) {
                    }
                    DB::table('objects')->where('id', $id)->delete();
                    continue;
                }

                $data = [
                    'nom'         => $obj['nom'] ?? '',
                    'quantite'    => is_numeric($obj['quantite']) ? (int) $obj['quantite'] : 1,
                    'dimensions'  => $obj['dimensions'] ?? '',
                    'poids'       => is_numeric($obj['poids']) ? (float) $obj['poids'] : null,
                    'description' => $obj['description'] ?? '',
                    'request_id'  => $annonce->request_id,
                ];

                if ($id) {
                    DB::table('objects')->where('id', $id)->update($data);
                } else {
                    $id = DB::table('objects')->insertGetId($data);
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
                                'object_id' => $id,
                                'chemin'    => $filename,
                            ]);
                        }
                    } catch (\Exception $e) {
                    }
                }
            }

            /* ---------- gestion box ---------- */
            DB::table('box_assignments')->where('request_id', $annonce->request_id)->delete();

            if (($validated['box_option'] ?? '') === 'yes' && isset($validated['box_id'])) {
                DB::table('box_assignments')->insert([
                    'box_id'     => $validated['box_id'],
                    'request_id' => $annonce->request_id,
                    'status'     => 'Réservé',
                    'datetime'   => now(),
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Annonce mise à jour avec succès.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour.',
                'details' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], 500);
        }
    }

    private function notifierLivreursPotentiels($annonce)
    {
        $trajets = DB::table('deliveryroutes')
            ->leftJoin('deliveryroutewaypoints as wp', 'deliveryroutes.route_id', '=', 'wp.route_id')
            ->select(
                'deliveryroutes.deliverer_id',
                'deliveryroutes.departure_city',
                'deliveryroutes.destination_city',
                'wp.city as waypoint_city'
            )
            ->get();

        $potentiels = [];

        foreach ($trajets as $t) {
            $matchDepart   = strcasecmp($t->departure_city,   $annonce->departure_city)   === 0;
            $matchArrivee  = strcasecmp($t->destination_city, $annonce->destination_city) === 0;
            $matchWaypoint = $t->waypoint_city &&
                (strcasecmp($t->waypoint_city, $annonce->departure_city)   === 0 ||
                    strcasecmp($t->waypoint_city, $annonce->destination_city) === 0);

            if ($matchDepart || $matchArrivee || $matchWaypoint) {
                $potentiels[] = $t->deliverer_id;
            }
        }

        foreach (array_unique($potentiels) as $livreurId) {
            NotificationHelper::envoyer(
                $livreurId,
                'Nouveau trajet disponible',
                "Une nouvelle annonce correspond à ton trajet : de {$annonce->departure_city} à {$annonce->destination_city}."
            );
        }
    }

    private function formatDate($value)
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        if (is_string($value) && ($ts = strtotime($value))) {
            return date('Y-m-d', $ts);
        }
        return '—';
    }
    public function getTracking(HttpRequest $request)
    {
        $id = $request->query('id');
        if (!$id || !is_numeric($id))
            return response()->json(['success' => false, 'message' => 'ID invalide'], 400);

        $user = Session::get('user');
        if (!$user) return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);

        $annonce = RequestModel::find($id);
        if (!$annonce) return response()->json(['success' => false, 'message' => 'Annonce introuvable'], 404);

        $isOwner = $annonce->user_id == $user['user_id'];
        if (!$isOwner && !in_array('Admin', $user['roles']))
            return response()->json(['success' => false, 'message' => 'Accès refusé'], 403);

        $events = DB::table('tracking_events')
            ->where('request_id', $id)
            ->orderBy('created_at')
            ->get();

        return response()->json(['success' => true, 'events' => $events]);
    }

    public function terminees()
    {
        $user = Session::get('user');
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $rows = DB::table('deliveryassignment')
            ->join('requests', 'deliveryassignment.request_id', '=', 'requests.request_id')
            ->where('requests.user_id', $user['user_id'])
            ->where('deliveryassignment.status', 'Livrée')
            ->select(
                'requests.request_id',
                'requests.departure_city',
                'requests.departure_code',
                'requests.destination_city',
                'requests.destination_code',
                'deliveryassignment.end_datetime'
            )
            ->orderByDesc('deliveryassignment.end_datetime')
            ->get();

        $annonces = $rows->map(fn($r) => [
            'id_annonce'   => $r->request_id,
            'depart'       => $r->departure_city,
            'depart_code'  => $r->departure_code,
            'arrivee'      => $r->destination_city,
            'arrivee_code' => $r->destination_code,
            'ended_at'     => date('Y-m-d H:i', strtotime($r->end_datetime)),
        ]);

        return response()->json([
            'success'  => true,
            'annonces' => $annonces,
        ]);
    }
    public function estimate(HttpRequest $request, PricingService $pricing, RoadService $road)
{
    // 1) Validation
    $validated = $request->validate([
        'objets'              => 'required|json',
        'code_postal'         => 'required|string|max:10',
        'code_postal_arrivee' => 'required|string|max:10',
    ]);

    // 2) Géocodage & distance
    $geocode = fn(string $cp): ?array => optional(
        Http::withoutVerifying()->get('https://geo.api.gouv.fr/communes', [
            'codePostal' => $cp, 'fields'=>'centre', 'format'=>'json'
        ])->json()[0] ?? null,
        fn($c) => ['lat'=>$c['centre']['coordinates'][1],'lon'=>$c['centre']['coordinates'][0]]
    );
    $coordsA = $geocode($validated['code_postal']);
    $coordsB = $geocode($validated['code_postal_arrivee']);
    if (! $coordsA || ! $coordsB) {
        return response()->json(['success'=>false,'message'=>'Code postal invalide'], 422);
    }
    $distanceKm = $road->distanceKm($coordsA, $coordsB);
    if (is_null($distanceKm)) {
        return response()->json(['success'=>false,'message'=>'Impossible de calculer la distance'], 502);
    }

    // 3) Calcul poids + volume
    $objects     = json_decode($validated['objets'], true);
    $totalWeight = 0.0;
    $totalVolume = 0.0;
    foreach ($objects as $o) {
        $w  = isset($o['poids'])   && is_numeric($o['poids']) ? (float)$o['poids']   : 0;
        $l  = isset($o['length'])  ? (float)$o['length']  : 0;
        $wd = isset($o['width'])   ? (float)$o['width']   : 0;
        $h  = isset($o['height'])  ? (float)$o['height']  : 0;
        $totalWeight += $w;
        $totalVolume += ($l * $wd * $h);
    }

    // 4) Calcul du prix
    $prixCents = $pricing->calculate(
        $totalWeight,
        $totalVolume,
        $distanceKm
    );

    // 5) Retourne le JSON
    return response()->json([
        'success'    => true,
        'prix_cents' => $prixCents,
        'prix_euros' => number_format($prixCents/100, 2, ',', ' ')
    ]);
}

public function adresses(HttpRequest $request)
{
    $term = $request->query('q');
    if (strlen($term) < 3) {
        return response()->json([]);
    }

    $response = Http::withoutVerifying()->get(
        'https://api-adresse.data.gouv.fr/search/',
        ['q' => $term, 'limit' => 7]
    );

    $features = collect($response->json()['features'] ?? [])
        ->map(fn($f) => [
            'label'    => $f['properties']['label'],
            'postcode' => $f['properties']['postcode'],
            'city'     => $f['properties']['city'],
            'lat'      => $f['geometry']['coordinates'][1],
            'lon'      => $f['geometry']['coordinates'][0],
        ]);

    return response()->json($features);
}

public function deletePhoto($photoId)
{
    $user = Session::get('user');
    if (!$user) {
        return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
    }

    $photo = DB::table('object_photo')
        ->join('objects', 'object_photo.object_id', '=', 'objects.id')
        ->join('requests', 'objects.request_id', '=', 'requests.request_id')
        ->where('object_photo.id', $photoId)
        ->where('requests.user_id', $user['user_id'])
        ->select('object_photo.*')
        ->first();

    if (!$photo) {
        return response()->json(['success' => false, 'message' => 'Photo non trouvée ou accès refusé'], 404);
    }

    try {
        $filePath = storage_path("app/public/uploads/{$photo->chemin}");
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        DB::table('object_photo')->where('id', $photoId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Photo supprimée avec succès'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la suppression de la photo'
        ], 500);
    }
}


}