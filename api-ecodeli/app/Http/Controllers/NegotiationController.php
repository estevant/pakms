<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Negotiation;
use App\Models\Request as DeliveryRequest;
use Illuminate\Support\Facades\Session;

class NegotiationController extends Controller
{
    // Créer une proposition de prix
  public function propose(Request $request)
{
    $userId = Session::get('user.user_id');

    $validated = $request->validate([
        'request_id'     => 'required|integer|exists:requests,request_id',
        'proposed_price' => 'required|numeric|min:0',
    ]);

    $senderId = $userId;

    // Récupération de l’annonce et de l’assignation
    $annonce = DB::table('requests')->where('request_id', $validated['request_id'])->first();
    $assignment = DB::table('deliveryassignment')->where('request_id', $validated['request_id'])->first();

    if (!$annonce || !$assignment) {
        return response()->json(['success' => false, 'message' => 'Annonce ou assignation introuvable.']);
    }

    $clientId = $annonce->user_id;
    $delivererId = $assignment->deliverer_id;

    // Déduire le destinataire
    $receiverId = $senderId == $clientId ? $delivererId : $clientId;


$existingAccepted = DB::table('negotiations')
    ->where('request_id', $validated['request_id'])
    ->where('status', 'accepted')
    ->first();

if ($existingAccepted) {
    return response()->json(['success' => false, 'message' => 'Une offre a déjà été acceptée.']);
}

    $dejaEnCours = Negotiation::where('request_id', $validated['request_id'])
    ->where('status', 'pending')
    ->exists();

if ($dejaEnCours) {
    return response()->json([
        'success' => false,
        'message' => 'Une négociation est déjà en cours.'
    ], 409); // Code 409 = Conflit
}
    $negotiation = Negotiation::create([
    'request_id'     => $validated['request_id'],
    'sender_id'      => $senderId,
    'receiver_id'    => $receiverId,
    'proposed_price' => $validated['proposed_price'],
    'status'         => 'pending',
]);

// ✅ Ajouter un message système au chat
MessageController::posterSystem(
    $validated['request_id'],
    "💰 Offre proposée : " . number_format($validated['proposed_price'] / 100, 2) . " €",
    [
        'negotiation_id' => $negotiation->negotiation_id,
        'proposed_price' => $negotiation->proposed_price,
        'sender_id'      => $senderId,
        'receiver_id'    => $receiverId, // 👈 ajoute cette ligne
    ],
    $assignment->assignment_id
);
return response()->json([
    'success'     => true,
    'negotiation' => $negotiation,
]);
}
    // Accepter une proposition
    public function accept($id)
    {
        $negotiation = Negotiation::findOrFail($id);
        $negotiation->status = 'accepted';
        $negotiation->save();

        // Mettre à jour le prix dans la table "requests"
        $delivery = DeliveryRequest::find($negotiation->request_id);
        $delivery->prix_negocie_cents = $negotiation->proposed_price;
        $delivery->save();

        return response()->json(['success' => true]);
    }

    // Refuser une proposition
    public function reject($id)
    {
        $negotiation = Negotiation::findOrFail($id);
        $negotiation->status = 'rejected';
        $negotiation->save();

        return response()->json(['success' => true]);
    }
}