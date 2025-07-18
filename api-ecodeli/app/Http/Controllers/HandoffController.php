<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\MessageController;
use App\Services\DeliverySplitService;

class HandoffController extends Controller
{
    public function store(Request $request)
    {
        $user = Session::get('user');
        if (!$user || !in_array('Deliverer', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $initialValidation = $request->validate([
            'assignment_id' => 'required|integer',  // C'est en fait un request_id
            'type'          => 'required|in:BOX',
            'box_id'        => 'required|integer',
        ]);

        $box = DB::table('storage_boxes')->where('id', $initialValidation['box_id'])->first();
        if (!$box) {
            return response()->json(['success' => false, 'message' => 'Box non trouvée'], 422);
        }

        $da = DB::table('deliveryassignment')
            ->where('request_id', $initialValidation['assignment_id'])
            ->where('deliverer_id', $user['user_id'])
            ->first();
        if (!$da) {
            return response()->json(['success' => false, 'message' => 'Assignment non trouvé ou vous n\'êtes pas le livreur assigné'], 422);
        }

        if (DB::table('delivery_handoffs')
            ->where('assignment_id', $da->assignment_id)
            ->where('status', 'En attente')
            ->exists()) {
            return response()->json(['success' => false, 'message' => 'Une proposition est déjà en attente'], 409);
        }

        $v = [
            'assignment_id' => $da->assignment_id,
            'type' => $initialValidation['type'],
            'box_id' => $initialValidation['box_id']
        ];

        $from = $da->current_drop_location ?: ($da->pickup_address ?? '—');
        $to   = "{$box->label} ({$box->location_city})";

        $handoffId = DB::table('delivery_handoffs')->insertGetId([
            'assignment_id' => $v['assignment_id'],
            'proposer_id'   => $user['user_id'],
            'type'          => 'BOX',
            'box_id'        => $v['box_id'],
            'new_address'   => null,
        ]);

        DB::table('deliveryassignment')
            ->where('assignment_id', $v['assignment_id'])
            ->update(['handoff_status' => 'En attente']);

        MessageController::posterSystem(
            $da->request_id,
            'Le livreur propose un dépôt intermédiaire',
            ['handoff_id' => $handoffId, 'from' => $from, 'to' => $to, 'proposer_id' => $user['user_id']],
            $da->assignment_id
        );

        return response()->json(['success' => true]);
    }

    public function accept(int $id)
    {
        $user = Session::get('user');
        if (!$user || !in_array('Customer', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $h = DB::table('delivery_handoffs')->where('handoff_id', $id)->first();
        if (!$h || $h->status !== 'En attente') {
            return response()->json(['success' => false, 'message' => 'Handoff invalide'], 422);
        }

        $assignment = DB::table('deliveryassignment')
            ->join('requests', 'deliveryassignment.request_id', '=', 'requests.request_id')
            ->where('deliveryassignment.assignment_id', $h->assignment_id)
            ->select(
                'deliveryassignment.*',
                'requests.departure_address',
                'requests.departure_city',
                'requests.destination_address',
                'requests.destination_city'
            )
            ->first();

        $box = DB::table('storage_boxes')->where('id', $h->box_id)->first();
        
        $from = $assignment->current_drop_location ?: 
                ($assignment->departure_address ? "{$assignment->departure_address} ({$assignment->departure_city})" : $assignment->departure_city);
        $to   = "{$box->label} ({$box->location_city})";

        DB::transaction(function () use ($h, $user) {
            DB::table('delivery_handoffs')
                ->where('handoff_id', $h->handoff_id)
                ->update([
                    'status'      => 'Accepté',
                    'acceptor_id' => $user['user_id'],
                    'accepted_at' => now(),
                ]);

            DB::table('deliveryassignment')
                ->where('assignment_id', $h->assignment_id)
                ->update([
                    'handoff_status'        => 'Accepté',
                    'current_drop_location' => 'BOX #' . $h->box_id,
                ]);
        });

        DeliverySplitService::split($h->handoff_id);

        $requestId = DB::table('deliveryassignment')
            ->where('assignment_id', $h->assignment_id)
            ->value('request_id');

        MessageController::posterSystem(
            $requestId,
            ' Dépôt intermédiaire accepté - Division de la livraison en cours...',
            ['handoff_id' => $h->handoff_id, 'from' => $from, 'to' => $to],
            $h->assignment_id
        );

        return response()->json(['success' => true]);
    }

    public function refuse(int $id)
    {
        $h = DB::table('delivery_handoffs')->where('handoff_id', $id)->first();
        if (!$h || $h->status !== 'En attente') {
            return response()->json(['success' => false], 422);
        }

        $assignment = DB::table('deliveryassignment')
            ->join('requests', 'deliveryassignment.request_id', '=', 'requests.request_id')
            ->where('deliveryassignment.assignment_id', $h->assignment_id)
            ->select(
                'deliveryassignment.*',
                'requests.departure_address',
                'requests.departure_city'
            )
            ->first();

        $box = DB::table('storage_boxes')->where('id', $h->box_id)->first();
        $from = $assignment->current_drop_location ?: 
                ($assignment->departure_address ? "{$assignment->departure_address} ({$assignment->departure_city})" : $assignment->departure_city);
        $to   = "{$box->label} ({$box->location_city})";

        DB::table('delivery_handoffs')->where('handoff_id', $id)
            ->update(['status' => 'Refusé']);

        DB::table('deliveryassignment')->where('assignment_id', $h->assignment_id)
            ->update(['handoff_status' => 'Refusé']);

        $requestId = DB::table('deliveryassignment')
            ->where('assignment_id', $h->assignment_id)
            ->value('request_id');

        MessageController::posterSystem(
            $requestId,
            'Le client a refusé le dépôt intermédiaire',
            ['handoff_id' => $h->handoff_id, 'from' => $from, 'to' => $to],
            $h->assignment_id
        );

        return response()->json(['success' => true]);
    }

    public function cancel(int $id)
    {
        $user = Session::get('user');
        if (!$user || !in_array('Deliverer', $user['roles'])) {
            return response()->json(['success' => false], 401);
        }

        $h = DB::table('delivery_handoffs')
            ->where('handoff_id', $id)
            ->where('proposer_id', $user['user_id'])
            ->where('status', 'En attente')
            ->first();
        if (!$h) {
            return response()->json(['success' => false], 422);
        }

        $assignment = DB::table('deliveryassignment')
            ->join('requests', 'deliveryassignment.request_id', '=', 'requests.request_id')
            ->where('deliveryassignment.assignment_id', $h->assignment_id)
            ->select(
                'deliveryassignment.*',
                'requests.departure_address',
                'requests.departure_city'
            )
            ->first();

        $box = DB::table('storage_boxes')->where('id', $h->box_id)->first();
        $from = $assignment->current_drop_location ?: 
                ($assignment->departure_address ? "{$assignment->departure_address} ({$assignment->departure_city})" : $assignment->departure_city);
        $to   = "{$box->label} ({$box->location_city})";

        DB::table('delivery_handoffs')->where('handoff_id', $id)
            ->update(['status' => 'Refusé']);

        DB::table('deliveryassignment')->where('assignment_id', $h->assignment_id)
            ->update(['handoff_status' => 'Aucun']);

        $requestId = DB::table('deliveryassignment')
            ->where('assignment_id', $h->assignment_id)
            ->value('request_id');

        MessageController::posterSystem(
            $requestId,
            'Le livreur a annulé le dépôt intermédiaire',
            ['handoff_id' => $h->handoff_id, 'from' => $from, 'to' => $to],
            $h->assignment_id
        );

        return response()->json(['success' => true]);
    }
}