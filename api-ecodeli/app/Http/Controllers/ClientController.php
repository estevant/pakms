<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\ServiceAvailability;
use App\Models\Reservation;
use App\Models\Review;


class ClientController extends Controller
{
    // Prestations disponibles à la réservation (créneaux non réservés, futurs)
    public function prestationsDisponibles()
    {
        $today = now()->toDateString();

        $dispos = DB::table('serviceavailability')
            ->join('service', 'serviceavailability.offered_service_id', '=', 'service.offered_service_id')
            ->join('servicetype', 'service.service_type_id', '=', 'servicetype.service_type_id')
            ->leftJoin('reservation', 'serviceavailability.availability_id', '=', 'reservation.availability_id')
            ->whereNull('reservation.availability_id')
            ->whereDate('serviceavailability.date', '>=', $today)
            ->select(
                'serviceavailability.availability_id',
                'servicetype.name as service_type_name',
                'servicetype.is_price_fixed',
                'servicetype.fixed_price',
                'service.details',
                'service.address',
                'service.price as provider_price',
                'serviceavailability.date',
                'serviceavailability.start_time',
                'serviceavailability.end_time'
            )
            ->orderBy('serviceavailability.date')
            ->orderBy('serviceavailability.start_time')
            ->get();

        return response()->json($dispos);
    }

    // Réserver un créneau
    public function reserver(Request $request)
    {
        $user = Session::get('user');
        if (!$user) return response()->json(['error' => 'Non connecté'], 401);

        $request->validate([
            'availability_id' => 'required|integer|exists:serviceavailability,availability_id'
        ]);

        $already = Reservation::where('availability_id', $request->availability_id)->exists();
        if ($already) {
            return response()->json(['error' => 'Ce créneau est déjà réservé.'], 409);
        }

        $reservation = Reservation::create([
            'user_id' => $user['user_id'],
            'availability_id' => $request->availability_id,
            'status' => 'validée',
        ]);

        return response()->json([
            'message' => 'Réservation enregistrée avec succès.',
            'reservation_id' => $reservation->reservation_id
        ]);
    }

    // Historique des réservations du client
    public function mesReservations()
    {
        $user = Session::get('user');
        if (!$user) return response()->json(['error' => 'Non connecté'], 401);

        $reservations = DB::table('reservation')
            ->join('serviceavailability', 'reservation.availability_id', '=', 'serviceavailability.availability_id')
            ->join('service', 'serviceavailability.offered_service_id', '=', 'service.offered_service_id')
            ->join('servicetype', 'service.service_type_id', '=', 'servicetype.service_type_id')
            ->where('reservation.user_id', $user['user_id'])
            ->select(
                'reservation.reservation_id',
                'reservation.availability_id',
                'servicetype.name as service_type_name',
                'servicetype.is_price_fixed',
                'servicetype.fixed_price',
                'service.details',
                'service.address',
                'service.price as provider_price',
                'serviceavailability.date',
                'serviceavailability.start_time',
                'serviceavailability.end_time',
                'reservation.status',
                'reservation.is_paid',
                'reservation.note',
                'reservation.commentaire'
            )
            ->orderBy('serviceavailability.date')
            ->get();

        return response()->json($reservations);
    }

    public function annulerReservation($availability_id)
{
    $user = Session::get('user');
    if (!$user) return response()->json(['error' => 'Non connecté'], 401);

    $reservation = Reservation::where('user_id', $user['user_id'])
        ->where('availability_id', $availability_id)
        ->first();

    if (!$reservation) {
        return response()->json(['error' => 'Réservation introuvable.'], 404);
    }

    // Supprimer complètement la réservation pour libérer le créneau
    $reservation->delete();

    return response()->json(['message' => 'Réservation annulée avec succès.']);
}

public function reservationsPassees()
{
    $user = Session::get('user');
    if (!$user) return response()->json(['error' => 'Non connecté'], 401);

    $today = now()->toDateString();

    return DB::table('reservation')
        ->join('serviceavailability', 'reservation.availability_id', '=', 'serviceavailability.availability_id')
        ->join('service', 'serviceavailability.offered_service_id', '=', 'service.offered_service_id')
        ->join('servicetype', 'service.service_type_id', '=', 'servicetype.service_type_id')
        ->leftJoin('review', function ($join) use ($user) {
            $join->on('review.reviewer_id', '=', DB::raw($user['user_id']))
                 ->on('review.request_id', '=', 'reservation.availability_id');
        })
        ->where('reservation.user_id', $user['user_id'])
        ->whereDate('serviceavailability.date', '<', $today)
        ->select(
            'reservation.availability_id',
            'servicetype.name as service_type_name',
            'service.details',
            'service.address',
            'service.price',
            'serviceavailability.date',
            'serviceavailability.start_time',
            'serviceavailability.end_time',
            'reservation.status',
            'review.rating',
            'review.comment'
        )
        ->orderByDesc('serviceavailability.date')
        ->get();
}


public function storeReview(Request $request)
{
    $user = Session::get('user');
    if (!$user) return response()->json(['error' => 'Non connecté'], 401);

    $request->validate([
        'availability_id' => 'required|integer|exists:reservation,availability_id',
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string|max:1000'
    ]);

    // On récupère la réservation du client
    $reservation = \DB::table('reservation')
        ->where('availability_id', $request->availability_id)
        ->where('user_id', $user['user_id'])
        ->first();

    if (!$reservation) {
        return response()->json(['error' => 'Réservation non trouvée ou non autorisée'], 403);
    }

    // On vérifie si déjà noté
    if ($reservation->note !== null) {
        return response()->json(['error' => 'Vous avez déjà noté cette prestation.'], 409);
    }

    // On met à jour la note et le commentaire
    \DB::table('reservation')
        ->where('availability_id', $request->availability_id)
        ->where('user_id', $user['user_id'])
        ->update([
            'note' => $request->rating,
            'commentaire' => $request->comment,
            'updated_at' => now()
        ]);

    return response()->json(['message' => 'Évaluation enregistrée avec succès.']);
}

public function mesFactures()
{
    $user = Session::get('user');
    if (!$user) return response()->json(['error' => 'Non connecté'], 401);

    $invoices = DB::table('invoices')
        ->where('user_id', $user['user_id'])
        ->select(
            'invoice_id',
            'invoice_number',
            'issue_date',
            'total_amount'
        )
        ->orderByDesc('issue_date')
        ->get();

    $invoices = $invoices->map(function($invoice) {
        $invoice->date = date('d/m/Y', strtotime($invoice->issue_date));
        $invoice->total_amount = number_format($invoice->total_amount / 100, 2, ',', ' ') . ' €';
        $invoice->invoice_url = BASE_API . "/invoices/{$invoice->invoice_id}/download";
        return $invoice;
    });

    return response()->json($invoices);
}

public function shouldShowTutorial(Request $request)
{
    $user = $request->user();
    return response()->json(['show' => !$user->tutorial_done]);
}

public function markTutorialDone(Request $request)
{
    $user = $request->user();
    $user->tutorial_done = true;
    $user->save();

    return response()->json(['success' => true]);
}

}