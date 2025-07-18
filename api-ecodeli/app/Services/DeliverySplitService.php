<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\MessageController;
use App\Helpers\NotificationHelper;

class DeliverySplitService
{
    public static function split(int $handoffId): void
    {
        DB::transaction(function () use ($handoffId) {

            $h = DB::table('delivery_handoffs')
                ->where('handoff_id', $handoffId)
                ->lockForUpdate()
                ->first();

            if (!$h || $h->status !== 'Accepté') {
                Log::warning("Split : handoff #{$handoffId} absent ou non accepté");
                return;
            }

            $assign = DB::table('deliveryassignment')
                ->where('assignment_id', $h->assignment_id)
                ->lockForUpdate()
                ->first();

            if (!$assign) {
                Log::error("Split : assignment #{$h->assignment_id} introuvable");
                return;
            }

            $req = DB::table('requests')
                ->where('request_id', $assign->request_id)
                ->lockForUpdate()
                ->first();

            if ($req->is_split) {
                Log::info("Split : request #{$req->request_id} déjà scindée");
                return;
            }

            $box = DB::table('storage_boxes')->where('id', $h->box_id)->first();
            if (!$box) {
                Log::error("Split : box #{$h->box_id} introuvable");
                return;
            }

            $newRequest = (array) $req;
            unset($newRequest['request_id']);
            unset($newRequest['created_at']);

            $newRequest['departure_city']    = $box->location_city ?? '';
            $newRequest['departure_address'] = $box->label ?? '';
            $newRequest['departure_code']    = $box->location_code ?? '';
            $newRequest['departure_lat']     = $box->lat ?? null;
            $newRequest['departure_lon']     = $box->lon ?? null;

            $newRequest['parent_request_id'] = $req->request_id;
            $newRequest['is_split']          = 0;
            $newRequest['created_at']        = now();


            $newReqId = DB::table('requests')->insertGetId($newRequest);

            DB::table('requests')
                ->where('request_id', $req->request_id)
                ->update([
                    'destination_city'    => $box->location_city ?? '',
                    'destination_address' => $box->label ?? '',
                    'destination_code'    => $box->location_code ?? '',
                    'destination_lat'     => $box->lat ?? null,
                    'destination_lon'     => $box->lon ?? null,
                    'is_split'            => 1,
                ]);

            MessageController::posterSystem(
                $req->request_id,
                " Livraison divisée en 2 tronçons :\n• Tronçon 1 : {$req->departure_city} → " . ($box->location_city ?? 'Box') . " (actuel)\n• Tronçon 2 : " . ($box->location_city ?? 'Box') . " → {$req->destination_city} (nouvelle annonce #{$newReqId})",
                [
                    'handoff_id' => $handoffId,
                    'from'       => $req->departure_city,
                    'to'         => $box->location_city ?? 'Box',
                    'new_request_id' => $newReqId,
                ],
                $h->assignment_id
            );

            NotificationHelper::envoyerAuxLivreursProches(
                $box->location_city ?? '',
                'Nouvelle annonce deuxième tronçon',
                "Annonce #{$newReqId} : livraison de " . ($box->location_city ?? 'Box') . " à {$req->destination_city}."
            );
        });
    }
}