<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Service;
use App\Models\ServiceType;
use App\Traits\BannedWordsChecker;

class PrestationController extends Controller
{
    use BannedWordsChecker;

    public function historique(Request $request)
    {
        $user = Session::get('user');
        if (!$user) return response()->json(['error' => 'Non connecté'], 401);

        $serviceTypeId = $request->get('service_type_id');
        $withEvaluations = $request->get('with_evaluations', false);

        // Récupérer les prestations terminées
        $query = DB::table('serviceavailability')
            ->join('service', 'serviceavailability.offered_service_id', '=', 'service.offered_service_id')
            ->join('servicetype', 'service.service_type_id', '=', 'servicetype.service_type_id')
            ->join('reservation', 'serviceavailability.availability_id', '=', 'reservation.availability_id')
            ->join('users', 'reservation.user_id', '=', 'users.user_id')
            ->where('service.user_id', $user['user_id'])
            ->whereDate('serviceavailability.date', '<', now()->toDateString())
            ->select(
                'servicetype.name as prestation',
                'serviceavailability.date',
                'serviceavailability.start_time',
                'serviceavailability.end_time',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as client"),
                'reservation.reservation_id'
            )
            ->orderBy('serviceavailability.date', 'desc')
            ->orderBy('serviceavailability.start_time', 'desc');

        if ($serviceTypeId) {
            $query->where('service.service_type_id', $serviceTypeId);
        }

        $prestations = $query->get();

        // Si on veut les évaluations, prendre directement dans reservation
        if ($withEvaluations) {
            $prestations = DB::table('serviceavailability')
                ->join('service', 'serviceavailability.offered_service_id', '=', 'service.offered_service_id')
                ->join('servicetype', 'service.service_type_id', '=', 'servicetype.service_type_id')
                ->join('reservation', 'serviceavailability.availability_id', '=', 'reservation.availability_id')
                ->join('users', 'reservation.user_id', '=', 'users.user_id')
                ->where('service.user_id', $user['user_id'])
                ->whereDate('serviceavailability.date', '<', now()->toDateString())
                ->select(
                    'servicetype.name as prestation',
                    'serviceavailability.date',
                    'serviceavailability.start_time',
                    'serviceavailability.end_time',
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as client"),
                    'reservation.note as note',
                    'reservation.commentaire as commentaire',
                    'reservation.reservation_id'
                )
                ->orderBy('serviceavailability.date', 'desc')
                ->orderBy('serviceavailability.start_time', 'desc');

            if ($serviceTypeId) {
                $prestations->where('service.service_type_id', $serviceTypeId);
            }

            $prestations = $prestations->get();

            // Calculer la moyenne des notes
            $notes = $prestations->whereNotNull('note')->pluck('note');
            $moyenne = $notes->count() > 0 ? round($notes->avg(), 2) : null;

            // Formater les données pour le frontend avec évaluations
            $formattedPrestations = $prestations->map(function ($prestation) {
                return [
                    'prestation' => $prestation->prestation,
                    'date' => $prestation->date,
                    'heure' => $prestation->start_time . ' - ' . $prestation->end_time,
                    'client' => $prestation->client,
                    'note' => $prestation->note,
                    'commentaire' => $prestation->commentaire
                ];
            });

            return response()->json([
                'prestations' => $formattedPrestations,
                'moyenne' => $moyenne,
                'count' => $notes->count()
            ]);
        }

        // Formater les données pour le frontend sans évaluations
        $formattedPrestations = $prestations->map(function ($prestation) {
            return [
                'prestation' => $prestation->prestation,
                'date' => $prestation->date,
                'heure' => $prestation->start_time . ' - ' . $prestation->end_time,
                'client' => $prestation->client
            ];
        });

        return response()->json($formattedPrestations);
    }

    public function index()
    {
        $user = Session::get('user');
        if (!$user) return response()->json(['error' => 'Non connecté'], 401);

        // Récupérer toutes les prestations du prestataire avec le nom du type
        $prestations = DB::table('service')
            ->join('servicetype', 'service.service_type_id', '=', 'servicetype.service_type_id')
            ->where('service.user_id', $user['user_id'])
            ->select(
                'service.offered_service_id',
                'service.details',
                'service.address',
                'service.price',
                'servicetype.name as service_type_name'
            )
            ->get();

        // Récupérer tous les créneaux futurs pour ces prestations
        $creneaux = DB::table('serviceavailability')
            ->join('service', 'serviceavailability.offered_service_id', '=', 'service.offered_service_id')
            ->join('servicetype', 'service.service_type_id', '=', 'servicetype.service_type_id')
            ->leftJoin('reservation', 'serviceavailability.availability_id', '=', 'reservation.availability_id')
            ->where('service.user_id', $user['user_id'])
            ->whereDate('serviceavailability.date', '>=', now()->toDateString())
            ->select(
                'service.offered_service_id',
                'servicetype.name as service_type_name',
                'service.details',
                'service.address',
                'service.price',
                'serviceavailability.date',
                'serviceavailability.start_time',
                'serviceavailability.end_time',
                'serviceavailability.availability_id',
                DB::raw('CASE WHEN reservation.availability_id IS NOT NULL THEN 1 ELSE 0 END as is_reserved')
            )
            ->orderBy('serviceavailability.date')
            ->orderBy('serviceavailability.start_time')
            ->get();

        // Combiner les prestations et les créneaux
        $result = [];
        
        // Ajouter d'abord toutes les prestations (sans créneaux)
        foreach ($prestations as $prestation) {
            $result[] = $prestation;
        }
        
        // Ajouter ensuite tous les créneaux
        foreach ($creneaux as $creneau) {
            $result[] = $creneau;
        }

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $user = Session::get('user');
        if (!$user) return response()->json(['error' => 'Non connecté'], 401);

        // Vérification des mots interdits
        $checkResult = $this->checkMultipleFields([
            $request->description ?? '',
            $request->details ?? ''
        ], $user['user_id']);
        
        if ($checkResult) {
            return response()->json([
                'success' => false,
                'message' => $checkResult['message']
            ], 403);
        }

        $request->validate([
            'type' => 'required|string|max:100',
            'description' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'price' => 'required|numeric|min:0'
        ]);

        $type = ServiceType::firstOrCreate(
            ['name' => $request->type],
            ['description' => $request->type]
        );

        $service = new Service([
            'user_id' => $user['user_id'],
            'service_type_id' => $type->service_type_id,
            'details' => $request->description,
            'address' => $request->address,
            'price' => $request->price,
        ]);

        $service->save();

        return response()->json(['message' => 'Prestation ajoutée avec succès.']);
    }

    public function update(Request $request, $id)
    {
        $user = Session::get('user');
        if (!$user) return response()->json(['error' => 'Non connecté'], 401);

        // Vérification des mots interdits
        $checkResult = $this->checkMultipleFields([
            $request->description ?? '',
            $request->details ?? ''
        ], $user['user_id']);
        
        if ($checkResult) {
            return response()->json([
                'success' => false,
                'message' => $checkResult['message']
            ], 403);
        }

        $request->validate([
            'type' => 'required|string|max:100',
            'description' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'price' => 'required|numeric|min:0'
        ]);

        $service = Service::where('offered_service_id', $id)
            ->where('user_id', $user['user_id'])
            ->first();

        if (!$service) {
            return response()->json(['error' => 'Prestation introuvable.'], 404);
        }

        $type = ServiceType::firstOrCreate(
            ['name' => $request->type],
            ['description' => $request->type]
        );

        $service->update([
            'service_type_id' => $type->service_type_id,
            'details' => $request->description,
            'price' => $request->price,
            'address' => $request->address,
        ]);

        return response()->json(['message' => 'Prestation mise à jour avec succès.']);
    }

    public function destroy($id)
    {
        $user = Session::get('user');
        if (!$user) return response()->json(['error' => 'Non connecté'], 401);

        $service = Service::where('offered_service_id', $id)
            ->where('user_id', $user['user_id'])
            ->first();

        if (!$service) {
            return response()->json(['error' => 'Prestation introuvable.'], 404);
        }

        // Supprimer d'abord les créneaux associés
        \DB::table('serviceavailability')->where('offered_service_id', $id)->delete();

        // Puis supprimer la prestation
        $service->delete();

        return response()->json(['message' => 'Prestation supprimée avec succès.']);
    }

    public function cleanupNonReserved()
    {
        DB::table('serviceavailability')
            ->leftJoin('reservation', 'serviceavailability.availability_id', '=', 'reservation.availability_id')
            ->whereNull('reservation.availability_id')
            ->whereDate('serviceavailability.date', '<', now()->toDateString())
            ->delete();

        return response()->json(['message' => 'Prestations non réservées supprimées.']);
    }

    public function getPrestataireServiceTypes()
    {
        $user = Session::get('user');
        if (!$user) {
            return response()->json(['error' => 'Non connecté'], 401);
        }

        // Récupérer tous les types de prestations validés pour cet utilisateur
        $types = \DB::table('proposition_de_prestations')
            ->join('servicetype', 'proposition_de_prestations.nom', '=', 'servicetype.name')
            ->where('proposition_de_prestations.user_id', $user['user_id'])
            ->where('proposition_de_prestations.statut', 'Validé')
            ->select('servicetype.service_type_id', 'servicetype.name', 'servicetype.is_price_fixed', 'servicetype.fixed_price')
            ->distinct()
            ->get();

        // Si aucun type validé, retourner tous les types disponibles
        if ($types->isEmpty()) {
            $types = \DB::table('servicetype')
                ->select('service_type_id', 'name', 'is_price_fixed', 'fixed_price')
                ->get();
        }

        return response()->json(['data' => $types]);
    }

    /**
     * Permet à un prestataire d'envoyer une demande d'ajout de prestation (insère dans proposition_de_prestations)
     */
    public function demandePrestation(Request $request)
    {
        $user = Session::get('user');
        if (!$user) return response()->json(['success' => false, 'message' => 'Non connecté'], 401);

        $validated = $request->validate([
            'service_type_id' => 'required|integer|exists:servicetype,service_type_id',
            'price' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        // Récupérer le nom du type de prestation
        $serviceType = \App\Models\ServiceType::find($validated['service_type_id']);
        if (!$serviceType) {
            return response()->json(['success' => false, 'message' => 'Type de prestation introuvable.'], 404);
        }

        DB::table('proposition_de_prestations')->insert([
            'user_id' => $user['user_id'],
            'nom' => $serviceType->name,
            'description' => $validated['description'],
            'statut' => 'En attente',
            'created_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Demande envoyée à l\'administration.']);
    }

    /**
     * Retourne pour chaque prestation le prix total cumulé de tous ses créneaux (prix * durée, somme)
     */
    public function prestationsTotaux()
    {
        $user = Session::get('user');
        if (!$user) return response()->json(['error' => 'Non connecté'], 401);

        // Récupérer toutes les prestations du prestataire avec le nom du type
        $prestations = \DB::table('service')
            ->join('servicetype', 'service.service_type_id', '=', 'servicetype.service_type_id')
            ->where('service.user_id', $user['user_id'])
            ->select('service.*', 'servicetype.name as service_type_name')
            ->get();

        $result = [];
        foreach ($prestations as $presta) {
            // Récupérer tous les créneaux de cette prestation
            $dispos = \DB::table('serviceavailability')
                ->where('offered_service_id', $presta->offered_service_id)
                ->get();
            $total = 0;
            foreach ($dispos as $d) {
                $start = strtotime($d->date . ' ' . $d->start_time);
                $end = strtotime($d->date . ' ' . $d->end_time);
                $hours = ($end - $start) / 3600;
                if ($hours > 0) {
                    $total += $presta->price * $hours;
                }
            }
            $result[] = [
                'offered_service_id' => $presta->offered_service_id,
                'service_type_id' => $presta->service_type_id,
                'service_type_name' => $presta->service_type_name,
                'details' => $presta->details,
                'address' => $presta->address,
                'price' => $presta->price,
                'total' => round($total, 2),
            ];
        }
        return response()->json($result);
    }
}
