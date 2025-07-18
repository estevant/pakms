<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Helpers\NotificationHelper;
use Carbon\Carbon;
use GuzzleHttp\Client;

class LivreurRouteController extends Controller
{
    public function index(Request $request)
    {
        $user = Session::get('user');
        if (! $user || !in_array('Deliverer', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $routes = DB::table('deliveryroutes')
            ->where('deliverer_id', $user['user_id'])
            ->orderByDesc('departure_date')
            ->get();

        $result = [];
        foreach ($routes as $route) {
            $waypoints = DB::table('deliveryroutewaypoints')
                ->where('route_id', $route->route_id)
                ->get();
            $result[] = [
                'id_trajet'      => $route->route_id,
                'ville_depart'   => $route->departure_city,
                'code_depart'    => $route->departure_postal_code,
                'ville_arrivee'  => $route->destination_city,
                'code_arrivee'   => $route->destination_postal_code,
                'date_depart'    => $route->departure_date,
                'notes'          => $route->notes,
                'heure_depart'   => $route->heure_depart,
                'date_creation'  => $route->created_at ? date('Y-m-d', strtotime($route->created_at)) : '—',
                'waypoints'      => $waypoints->map(fn($w) => ['ville' => $w->city, 'code' => $w->postal_code]),
            ];
        }
        return response()->json(['success' => true, 'routes' => $result]);
    }

    public function store(Request $request)
    {
        $user = Session::get('user');
        if (! $user || !in_array('Deliverer', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $validated = $request->validate([
            'ville_depart'     => 'required|string|max:100',
            'code_depart'      => 'nullable|string|max:10',
            'ville_arrivee'    => 'required|string|max:100',
            'code_arrivee'     => 'nullable|string|max:10',
            'date_depart'      => 'required|date',
            'heure_depart'     => 'required|string',
            'notes'            => 'nullable|string',
            'waypoints'        => 'nullable|array',
            'waypoints.*.city' => 'required_with:waypoints|string|max:100',
            'waypoints.*.postal_code' => 'nullable|string|max:10',
        ]);

        DB::beginTransaction();
        try {
            $routeId = DB::table('deliveryroutes')->insertGetId([
                'deliverer_id'            => $user['user_id'],
                'departure_city'          => $validated['ville_depart'],
                'departure_postal_code'   => $validated['code_depart'],
                'destination_city'        => $validated['ville_arrivee'],
                'destination_postal_code' => $validated['code_arrivee'],
                'departure_date'          => $validated['date_depart'],
                'heure_depart'            => $validated['heure_depart'],
                'notes'                   => $validated['notes'] ?? null,
                'created_at'              => now(),
            ]);

            if (!empty($validated['waypoints'])) {
                foreach ($validated['waypoints'] as $wp) {
                    DB::table('deliveryroutewaypoints')->insert([
                        'route_id'    => $routeId,
                        'city'        => $wp['city'],
                        'postal_code' => $wp['postal_code'] ?? null,
                        'created_at'  => now(),
                    ]);
                }
            }

            DB::commit();
            $this->notifierAnnoncesPotentielles($routeId);

            return response()->json(['success' => true, 'message' => 'Trajet ajouté avec succès.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur : '.$e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = Session::get('user');
        if (! $user || !in_array('Deliverer', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $validated = $request->validate([
            'ville_depart'             => 'required|string|max:100',
            'code_depart'              => 'nullable|string|max:10',
            'ville_arrivee'            => 'required|string|max:100',
            'code_arrivee'             => 'nullable|string|max:10',
            'date_depart'              => 'nullable|date',
            'notes'                    => 'nullable|string',
            'waypoints'                => 'nullable|array',
            'waypoints.*.city'         => 'required_with:waypoints|string|max:100',
            'waypoints.*.postal_code'  => 'nullable|string|max:10',
        ]);

        $trajet = DB::table('deliveryroutes')
            ->where('route_id', $id)
            ->where('deliverer_id', $user['user_id'])
            ->first();

        if (! $trajet) {
            return response()->json(['success' => false, 'message' => 'Trajet introuvable.'], 404);
        }

        DB::beginTransaction();
        try {
            DB::table('deliveryroutes')
                ->where('route_id', $id)
                ->update([
                    'departure_city'          => $validated['ville_depart'],
                    'departure_postal_code'   => $validated['code_depart'],
                    'destination_city'        => $validated['ville_arrivee'],
                    'destination_postal_code' => $validated['code_arrivee'],
                    'departure_date'          => $validated['date_depart'],
                    'notes'                   => $validated['notes'] ?? null,
                ]);

            DB::table('deliveryroutewaypoints')->where('route_id', $id)->delete();
            if (!empty($validated['waypoints'])) {
                foreach ($validated['waypoints'] as $wp) {
                    DB::table('deliveryroutewaypoints')->insert([
                        'route_id'    => $id,
                        'city'        => $wp['city'],
                        'postal_code' => $wp['postal_code'] ?? null,
                        'created_at'  => now(),
                    ]);
                }
            }

            DB::commit();

            $this->notifierAnnoncesPotentielles($id);

            return response()->json(['success' => true, 'message' => 'Trajet mis à jour avec succès.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur : '.$e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $user = Session::get('user');
        if (! $user || !in_array('Deliverer', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $trajet = DB::table('deliveryroutes')
            ->where('route_id', $id)
            ->where('deliverer_id', $user['user_id'])
            ->first();
        if (! $trajet) {
            return response()->json(['success' => false, 'message' => 'Trajet introuvable.'], 404);
        }

        DB::table('deliveryroutes')->where('route_id', $id)->delete();
        DB::table('deliveryroutewaypoints')->where('route_id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Trajet supprimé avec succès.']);
    }

    private function notifierAnnoncesPotentielles(int $routeId): void
    {
        $route      = DB::table('deliveryroutes')->where('route_id', $routeId)->first();
        $waypoints  = DB::table('deliveryroutewaypoints')->where('route_id', $routeId)->get();

        $annonces = DB::table('requests')
            ->leftJoin('deliveryassignment', 'requests.request_id', '=', 'deliveryassignment.request_id')
            ->whereNull('deliveryassignment.request_id')
            ->select('requests.*')
            ->get();

        $match = collect();
        foreach ($annonces as $annonce) {
            $matchDepart  = strcasecmp($annonce->departure_city, $route->departure_city) == 0;
            $matchArrivee = strcasecmp($annonce->destination_city, $route->destination_city) == 0;
            $matchWP      = $waypoints->first(function($wp) use ($annonce) {
                return strcasecmp($wp->city, $annonce->departure_city) == 0
                    || strcasecmp($wp->city, $annonce->destination_city) == 0;
            });
            if ($matchDepart || $matchArrivee || $matchWP) {
                $match->push($annonce);
            }
        }

        if ($match->isNotEmpty()) {
            $nb = $match->count();
            $first = $match->first();
            NotificationHelper::envoyer(
                $route->deliverer_id,
                'Annonces correspondant à ton trajet',
                $nb === 1
                    ? "Une annonce correspond à ton trajet : de {$first->departure_city} à {$first->destination_city}."
                    : "$nb annonces correspondent à ton trajet entre {$route->departure_city} et {$route->destination_city}."
            );
        }
    }
    public function planning(Request $request)
    {
        $user = Session::get('user');
        if (!$user || !in_array('Deliverer', $user['roles'])) {
            return response()->json([]);
        }

        $trajets = DB::table('deliveryroutes')
            ->where('deliverer_id', $user['user_id'])
            ->get();

        $events = [];
        foreach ($trajets as $t) {
            $start = $t->departure_date . 'T' . ($t->heure_depart ?? '08:00:00');
            $duree = $this->calculerDureeTrajet($t->departure_city, $t->destination_city);
            $end = Carbon::parse($start)->addMinutes($duree)->format('Y-m-d\TH:i:s');

            $events[] = [
                'id'    => "trajet_" . $t->route_id,
                'title' => "{$t->departure_city} ➝ {$t->destination_city}",
                'start' => $start,
                'end'   => $end,
                'color' => "#1976D2",
                'allDay' => false,
            ];
        }

        return response()->json($events);
    }
    private function calculerDureeTrajet(string $ville1, string $ville2): int
{
    $key    = '5b3ce3597851110001cf624808a3f868e3d543b28c12b83a5bce22ac';
    $client = new Client([
        'headers' => [
            'Authorization'   => $key,
            'Content-Type'    => 'application/json',
        ],
        'timeout' => 10,
    ]);

    try {
        // 1) Géocodage de la ville de départ
        $res1 = $client->get('https://api.openrouteservice.org/geocode/search', [
            'query' => [
                'text'    => "{$ville1}, France",
                'size'    => 1,
                'api_key' => $key,
            ]
        ]);
        $j1 = json_decode($res1->getBody(), true);
        $c1 = $j1['features'][0]['geometry']['coordinates'] ?? null;

        // 2) Géocodage de la ville d’arrivée
        $res2 = $client->get('https://api.openrouteservice.org/geocode/search', [
            'query' => [
                'text'    => "{$ville2}, France",
                'size'    => 1,
                'api_key' => $key,
            ]
        ]);
        $j2 = json_decode($res2->getBody(), true);
        $c2 = $j2['features'][0]['geometry']['coordinates'] ?? null;

        if (! $c1 || ! $c2) {
            Log::warning("ORS géocodage impossible pour “{$ville1}” ou “{$ville2}”");
            throw new \Exception("Géocodage échoué");
        }

        // 3) Demande de l’itinéraire
        $res3 = $client->post('https://api.openrouteservice.org/v2/directions/driving-car', [
            'json' => ['coordinates' => [$c1, $c2]],
        ]);
        $j3 = json_decode($res3->getBody(), true);

        $durationSec = $j3['features'][0]['properties']['segments'][0]['duration'] ?? null;
        if (! $durationSec) {
            Log::warning("ORS directions sans durée pour “{$ville1}” → “{$ville2}”");
            throw new \Exception("Durée introuvable");
        }

        // Retour en minutes, arrondi à l’entier supérieur
        return (int) ceil($durationSec / 60);

    } catch (\Throwable $e) {
        Log::error("calculerDureeTrajet() fallback pour “{$ville1}”→“{$ville2}” : {$e->getMessage()}");

        // fallback : distance à vol d’oiseau / 80 km/h
        try {
            // extraire lat/lon
            [$lon1, $lat1] = $c1 ?? [0,0];
            [$lon2, $lat2] = $c2 ?? [0,0];

            $rad = pi() / 180;
            $dLat = ($lat2 - $lat1) * $rad;
            $dLon = ($lon2 - $lon1) * $rad;
            $a = sin($dLat/2)**2 + cos($lat1*$rad)*cos($lat2*$rad)*sin($dLon/2)**2;
            $km = 6371 * 2 * atan2(sqrt($a), sqrt(1-$a));

            return (int) ceil(($km / 80) * 60);
        } catch (\Throwable $e2) {
            // si tout échoue, on retourne 480 minutes
            return 480;
        }
    }
}
}