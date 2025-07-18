<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Request as DeliveryRequest;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StripeController extends Controller
{
public function createSession(Request $request)
{   
    Log::info('🔐 Clé Stripe utilisée : ' . env('STRIPE_SECRET_KEY'));

    \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

    $requestId = $request->input('request_id');
    $deliveryRequest = DeliveryRequest::findOrFail($requestId);

    $prix = $deliveryRequest->prix_negocie_cents ?? $deliveryRequest->prix_cents;

if (is_null($prix)) {
    return response()->json(['error' => 'Aucun prix défini pour cette annonce'], 400);
}

    try {
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'annonce'. $deliveryRequest->request_id,
                    ],
                    'unit_amount' => $prix,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => 'http://localhost/PA2/SIte/front/pages/confirmation.php',
            'cancel_url' => 'http://localhost/PA2/SIte/front/pages/annulation.php',
           'metadata' => [
    'request_id' => $deliveryRequest->request_id,
    'user_id' => $deliveryRequest->user_id,
],
        ]);

        return response()->json(['url' => $session->url]);
    } catch (\Exception $e) {
        Log::error('❌ Erreur Stripe create-session : ' . $e->getMessage());
        return response()->json(['error' => 'Erreur Stripe : ' . $e->getMessage()], 500);
    }
}

public function createSessionService(Request $request)
{   
    Log::info('🔐 Création session Stripe pour service');

    \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

    $reservationId = $request->input('reservation_id');
    
    $reservation = DB::table('reservation')
        ->join('serviceavailability', 'reservation.availability_id', '=', 'serviceavailability.availability_id')
        ->join('service', 'serviceavailability.offered_service_id', '=', 'service.offered_service_id')
        ->join('servicetype', 'service.service_type_id', '=', 'servicetype.service_type_id')
        ->where('reservation.reservation_id', $reservationId)
        ->select(
            'reservation.reservation_id',
            'reservation.user_id',
            'servicetype.is_price_fixed',
            'servicetype.fixed_price',
            'service.price as provider_price',
            'servicetype.name as service_type_name',
            'service.details',
            'serviceavailability.date',
            'serviceavailability.start_time',
            'serviceavailability.end_time'
        )
        ->first();

    if (!$reservation) {
        return response()->json(['error' => 'Réservation introuvable'], 404);
    }

    $startTime = strtotime($reservation->date . ' ' . $reservation->start_time);
    $endTime = strtotime($reservation->date . ' ' . $reservation->end_time);
    $durationMinutes = max(0, ($endTime - $startTime) / 60);

    $basePrice = 0;
    if ($reservation->is_price_fixed) {
        $basePrice = floatval($reservation->fixed_price) ?: 0;
    } else {
        $basePrice = floatval($reservation->provider_price) ?: 0;
    }

    $pricePerMinute = $basePrice / 60;
    $totalPrice = $pricePerMinute * $durationMinutes;

    if ($totalPrice <= 0) {
        return response()->json(['error' => 'Prix invalide pour ce service'], 400);
    }

    $prixCents = (int)($totalPrice * 100);

    try {
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $reservation->service_type_name,
                        'description' => $reservation->details . ' - ' . $reservation->date . ' ' . $reservation->start_time,
                    ],
                    'unit_amount' => $prixCents,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => 'http://localhost/PA2/SIte/front/pages/confirmation.php',
            'cancel_url' => 'http://localhost/PA2/SIte/front/pages/annulation.php',
            'metadata' => [
                'reservation_id' => $reservation->reservation_id,
                'user_id' => $reservation->user_id,
                'type' => 'service'
            ],
        ]);

        return response()->json(['url' => $session->url]);
    } catch (\Exception $e) {
        Log::error('❌ Erreur Stripe create-session-service : ' . $e->getMessage());
        return response()->json(['error' => 'Erreur Stripe : ' . $e->getMessage()], 500);
    }
}
}
?>