<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PrestataireController extends Controller
{
    // 1. Voir mes évaluations 
    public function mesEvaluations()
    {
        $user = Auth::user();

        $evaluations = DB::table('review')
            ->join('users as clients', 'review.reviewer_id', '=', 'clients.user_id')
            ->where('review.target_id', $user->user_id)
            ->where('review.role_target', 'Prestataire')
            ->orderByDesc('review.review_date')
            ->select(
                'review.rating as note',
                'review.comment as commentaire',
                'review.review_date as date',
                'clients.first_name',
                'clients.last_name'
            )
            ->get();

        return response()->json($evaluations);
    }

    // 2. Voir mes disponibilités 
    public function mesDisponibilites()
    {
        $user = Auth::user();

        $offeredService = DB::table('service')
            ->where('user_id', $user->user_id)
            ->first();

        if (!$offeredService) {
            return response()->json(['message' => 'Aucun service proposé trouvé.'], 404);
        }

        $disponibilites = DB::table('serviceavailability')
            ->where('offered_service_id', $offeredService->offered_service_id)
            ->get();

        return response()->json($disponibilites);
    }

    // 3. Ajouter une disponibilité
    public function ajouterDisponibilite(Request $request)
    {
        $user = Auth::user();

        $offeredService = DB::table('service')
            ->where('user_id', $user->user_id)
            ->first();

        if (!$offeredService) {
            return response()->json(['message' => 'Aucun service proposé trouvé.'], 404);
        }

        DB::table('serviceavailability')->insert([
            'offered_service_id' => $offeredService->offered_service_id,
            'weekday' => $request->weekday,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return response()->json(['message' => 'Disponibilité ajoutée avec succès.']);
    }

    // 4. Supprimer une disponibilité
    public function supprimerDisponibilite($id)
    {
        $user = Auth::user();

        $offeredService = DB::table('service')
            ->where('user_id', $user->user_id)
            ->first();

        if (!$offeredService) {
            return response()->json(['message' => 'Aucun service proposé trouvé.'], 404);
        }

        $deleted = DB::table('serviceavailability')
            ->where('availability_id', $id)
            ->where('offered_service_id', $offeredService->offered_service_id)
            ->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Disponibilité non trouvée.'], 404);
        }

        return response()->json(['message' => 'Disponibilité supprimée avec succès.']);
    }

    // 5. Voir mes interventions (réservations client : ServiceBooking)
    public function mesInterventions()
    {
        $user = Auth::user();

        $offeredService = DB::table('service')
            ->where('user_id', $user->user_id)
            ->first();

        if (!$offeredService) {
            return response()->json(['message' => 'Aucun service proposé trouvé.'], 404);
        }

        $interventions = DB::table('servicebooking')
            ->where('offered_service_id', $offeredService->offered_service_id)
            ->get();

        return response()->json($interventions);
    }

    // 6. Voir mes factures
    public function mesFactures()
    {
        $user = Auth::user();

        $factures = DB::table('invoice')
            ->where('user_id', $user->user_id)
            ->get();

        return response()->json($factures);
    }

    // 7. Télécharger une facture
    public function telechargerFacture($id)
    {
        $user = Auth::user();

        $facture = DB::table('invoice')
            ->where('invoice_id', $id)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$facture) {
            return response()->json(['message' => 'Facture non trouvée.'], 404);
        }

        $path = storage_path('app/' . $facture->pdf_path);

        if (!file_exists($path)) {
            return response()->json(['message' => 'Fichier introuvable.'], 404);
        }

        return response()->download($path);
    }
}