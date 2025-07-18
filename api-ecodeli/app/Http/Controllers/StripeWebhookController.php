<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use App\Models\Request as DeliveryRequest;
use App\Models\Payment;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('📡 Webhook Stripe reçu');
        Log::info('📋 Headers reçus : ' . json_encode($request->headers->all()));

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        Log::info('📄 Payload reçu : ' . substr($payload, 0, 500) . '...');

        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        if (empty($endpointSecret)) {
            Log::error('❌ Aucune clé webhook Stripe trouvée dans le fichier .env !');
            return response('Missing webhook secret', 500);
        }

        Log::info("🔐 Clé webhook utilisée : $endpointSecret");

        try {
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            Log::error('❌ Mauvais JSON Stripe');
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('❌ Signature Stripe invalide');
            return response('Invalid signature', 400);
        }

        Log::info('✅ Signature Stripe validée');
        Log::info('🎯 Type d\'événement reçu : ' . $event->type);

        if ($event->type === 'checkout.session.completed') {
            Log::info('✅ Paiement confirmé par Stripe');

            $session = $event->data->object;
            $requestId = $session->metadata->request_id ?? null;
            $reservationId = $session->metadata->reservation_id ?? null;
            $userId = $session->metadata->user_id ?? null;
            $type = $session->metadata->type ?? 'delivery';

            Log::info("📋 Métadonnées Stripe - type: $type, request_id: $requestId, reservation_id: $reservationId, user_id: $userId");

            if ($type === 'service' && $reservationId) {
                $reservation = DB::table('reservation')->where('reservation_id', $reservationId)->first();
                if (!$reservation) {
                    Log::error("❌ Réservation introuvable : $reservationId");
                    return response('Reservation not found', 404);
                }

                Log::info("📦 Réservation trouvée - ID: $reservationId, is_paid actuel: " . ($reservation->is_paid ? 'true' : 'false'));

                DB::table('reservation')
                    ->where('reservation_id', $reservationId)
                    ->update(['is_paid' => 1]);

                $serviceAvailability = DB::table('serviceavailability')
                    ->join('service', 'serviceavailability.offered_service_id', '=', 'service.offered_service_id')
                    ->where('serviceavailability.availability_id', $reservation->availability_id)
                    ->select('service.user_id as prestataire_id')
                    ->first();

                $payeeId = $serviceAvailability ? $serviceAvailability->prestataire_id : null;

                Payment::create([
                    'payment_id'   => $session->payment_intent,
                    'payer_id'     => $userId ?? $reservation->user_id,
                    'payee_id'     => $payeeId,
                    'payment_type' => 'Service',
                    'amount'       => $session->amount_total,
                    'status'       => 'Payé',
                    'payment_date' => now(),
                ]);

                if ($payeeId) {
                    DB::table('wallets')->updateOrInsert(
                        ['user_id' => $payeeId],
                        [
                            'balance_cent' => DB::raw("balance_cent + " . intval($session->amount_total)),
                            'updated_at' => now()
                        ]
                    );
                }

                $invoiceNumber = 'INV-SRV-' . now()->format('Ymd') . '-' . str_pad($reservationId, 4, '0', STR_PAD_LEFT);
                $path = 'invoices/' . $invoiceNumber . '.pdf';

                $serviceDetails = DB::table('reservation')
                    ->join('serviceavailability', 'reservation.availability_id', '=', 'serviceavailability.availability_id')
                    ->join('service', 'serviceavailability.offered_service_id', '=', 'service.offered_service_id')
                    ->join('servicetype', 'service.service_type_id', '=', 'servicetype.service_type_id')
                    ->where('reservation.reservation_id', $reservationId)
                    ->select(
                        'servicetype.name as service_type_name',
                        'service.details',
                        'service.address',
                        'serviceavailability.date',
                        'serviceavailability.start_time',
                        'serviceavailability.end_time'
                    )
                    ->first();

                // Facture pour le client
                $invoice = Invoice::create([
                    'user_id' => $userId ?? $reservation->user_id,
                    'invoice_number' => $invoiceNumber,
                    'issue_date' => now(),
                    'total_amount' => $session->amount_total,
                    'payment_id' => $session->payment_intent,
                    'pdf_path' => $path,
                ]);

                $pdf = Pdf::loadView('pdf.invoice', [
                    'invoice' => $invoice,
                    'serviceDetails' => $serviceDetails
                ]);
                Storage::disk('local')->put($path, $pdf->output());

                // Facture pour le prestataire
                if ($payeeId) {
                    $invoiceNumberPrestataire = 'INV-SRV-PROV-' . now()->format('Ymd') . '-' . str_pad($reservationId, 4, '0', STR_PAD_LEFT);
                    $pathPrestataire = 'invoices/' . $invoiceNumberPrestataire . '.pdf';

                    $invoicePrestataire = Invoice::create([
                        'user_id' => $payeeId,
                        'invoice_number' => $invoiceNumberPrestataire,
                        'issue_date' => now(),
                        'total_amount' => $session->amount_total,
                        'payment_id' => $session->payment_intent,
                        'pdf_path' => $pathPrestataire,
                    ]);

                    $pdfPrestataire = Pdf::loadView('pdf.invoice', [
                        'invoice' => $invoicePrestataire,
                        'serviceDetails' => $serviceDetails
                    ]);
                    Storage::disk('local')->put($pathPrestataire, $pdfPrestataire->output());

                    Log::info("✅ Facture prestataire générée pour la réservation $reservationId - prestataire: $payeeId");
                }

                Log::info("✅ Paiement et factures PDF générés pour la réservation $reservationId");

            } else if ($type === 'delivery' && $requestId) {
                $deliveryRequest = DeliveryRequest::find($requestId);
                if (!$deliveryRequest) {
                    Log::error("❌ Demande introuvable : $requestId");
                    return response('Request not found', 404);
                }

                Log::info("📦 Demande trouvée - ID: {$deliveryRequest->request_id}, is_paid actuel: " . ($deliveryRequest->is_paid ? 'true' : 'false'));

                $deliveryRequest->is_paid = true;
                $result = $deliveryRequest->save();
                Log::info("💾 Sauvegarde Eloquent - résultat: " . ($result ? 'succès' : 'échec'));

                $updateResult = DB::table('requests')
                    ->where('request_id', $requestId)
                    ->update(['is_paid' => 1]);
                Log::info("🔧 Mise à jour SQL directe - lignes affectées: $updateResult");

                $updatedRequest = DeliveryRequest::find($requestId);
                Log::info("✅ Vérification après mise à jour - is_paid: " . ($updatedRequest->is_paid ? 'true' : 'false'));

                $deliveryAssignment = DB::table('deliveryassignment')
                    ->where('request_id', $requestId)
                    ->first();

                $payeeId = $deliveryAssignment ? $deliveryAssignment->deliverer_id : null;

                Payment::create([
                    'payment_id'   => $session->payment_intent,
                    'payer_id'     => $userId ?? $deliveryRequest->user_id,
                    'payee_id'     => $payeeId,
                    'payment_type' => 'Livraison',
                    'amount'       => $session->amount_total,
                    'status'       => 'Payé',
                    'payment_date' => now(),
                ]);

                // Créditer le wallet du livreur
                if ($payeeId) {
                    DB::table('wallets')->updateOrInsert(
                        ['user_id' => $payeeId],
                        [
                            'balance_cent' => DB::raw("balance_cent + " . intval($session->amount_total)),
                            'updated_at' => now()
                        ]
                    );
                }

                $invoiceNumber = 'INV-' . now()->format('Ymd') . '-' . str_pad($requestId, 4, '0', STR_PAD_LEFT);
                $path = 'invoices/' . $invoiceNumber . '.pdf';

                $deliveryDetails = DB::table('requests')
                    ->where('request_id', $requestId)
                    ->select(
                        'request_id',
                        'departure_address',
                        'destination_address'
                    )
                    ->first();

                $invoice = Invoice::create([
                    'user_id' => $userId ?? $deliveryRequest->user_id,
                    'invoice_number' => $invoiceNumber,
                    'issue_date' => now(),
                    'total_amount' => $session->amount_total,
                    'payment_id' => $session->payment_intent,
                    'pdf_path' => $path,
                ]);

                $pdf = Pdf::loadView('pdf.invoice', [
                    'invoice' => $invoice,
                    'deliveryDetails' => $deliveryDetails
                ]);
                Storage::disk('local')->put($path, $pdf->output());

                Log::info("✅ Paiement et facture PDF générés pour la demande $requestId");
            } else {
                Log::error("❌ Type de paiement non reconnu ou métadonnées manquantes");
                return response('Invalid payment type', 400);
            }
        }

        return response('Webhook traité', 200);
    }
}
?>