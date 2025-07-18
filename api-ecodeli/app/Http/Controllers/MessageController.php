<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Helpers\NotificationHelper;
use App\Traits\BannedWordsChecker;

class MessageController extends Controller
{
    use BannedWordsChecker;

    public function index(Request $request)
    {
        $id_annonce = $request->input('id_annonce');
        if (!is_numeric($id_annonce)) {
            return response()->json(['success' => false], 400);
        }

        $user = Session::get('user');
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $isPaid = DB::table('requests')->where('request_id', $id_annonce)->value('is_paid');

        $messages = DB::table('messages')
            ->leftJoin('users', 'messages.sender_id', '=', 'users.user_id')
            ->where('messages.request_id', $id_annonce)
            ->orderBy('messages.sent_at')
            ->select(
                'messages.*',
                'users.first_name',
                'users.last_name',
                DB::raw('messages.sent_at as date_envoi')
            )
            ->get();

        $formatted = $messages->map(function ($m) use ($user, $isPaid) {
            $meta = json_decode($m->metadata, true) ?? [];
            $handoffId = $meta['handoff_id'] ?? null;
            $negotiationStatus = null;
            if (!empty($meta['negotiation_id'])) {
                $negotiationStatus = DB::table('negotiations')
                    ->where('negotiation_id', $meta['negotiation_id'])
                    ->value('status');
            }
            $meta['paid'] = (bool)$isPaid;
            return [
                'system'             => is_null($m->sender_id),
                'handoff_id'         => $handoffId,
                'handoff_status'     => $handoffId
                    ? DB::table('delivery_handoffs')->where('handoff_id', $handoffId)->value('status')
                    : null,
                'negotiation_status' => $negotiationStatus,
                'meta'               => $meta,
                'metadata'           => json_encode($meta),
                'nom'                => is_null($m->sender_id)
                    ? 'Système'
                    : trim(($m->first_name ?? '') . ' ' . ($m->last_name ?? '')),
                'contenu'            => $m->content,
                'date_envoi'         => date('Y-m-d H:i', strtotime($m->date_envoi)),
                'est_moi'            => $m->sender_id == $user['user_id'],
            ];
        });

        return response()->json(['success' => true, 'messages' => $formatted]);
    }

    public function store(Request $request)
    {
        $user = Session::get('user');
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $validated = $request->validate([
            'id_annonce' => 'required|integer',
            'contenu'    => 'required|string|max:1000',
        ]);

        // Vérification des mots interdits dans le message
        $checkResult = $this->checkBannedWords($validated['contenu'], $user['user_id']);
        if ($checkResult) {
            return response()->json([
                'success' => false,
                'message' => $checkResult['message']
            ], 403);
        }

        $annonce = DB::table('requests')->where('request_id', $validated['id_annonce'])->first();
        if (!$annonce) {
            return response()->json(['success' => false], 404);
        }

        $assignment = DB::table('deliveryassignment')
            ->where('request_id', $validated['id_annonce'])
            ->first();
        if (!$assignment) {
            return response()->json(['success' => false], 404);
        }

        $client_id   = $annonce->user_id;
        $livreur_id  = $assignment->deliverer_id;
        $receiver_id = $user['user_id'] == $client_id ? $livreur_id : $client_id;

        DB::table('messages')->insert([
            'sender_id'   => $user['user_id'],
            'receiver_id' => $receiver_id,
            'request_id'  => $validated['id_annonce'],
            'content'     => $validated['contenu'],
            'sent_at'     => now()
        ]);

        NotificationHelper::envoyer(
            $receiver_id,
            '💬 Nouveau message',
            "Vous avez reçu un message concernant votre annonce n°{$validated['id_annonce']}."
        );

        return response()->json(['success' => true]);
    }

    public function participants(Request $request)
    {
        $id_annonce = $request->query('id_annonce');
        if (!is_numeric($id_annonce)) {
            return response()->json(['success' => false], 400);
        }

        $annonce = DB::table('requests')
            ->join('users as clients', 'requests.user_id', '=', 'clients.user_id')
            ->where('requests.request_id', $id_annonce)
            ->select('clients.first_name as client_prenom', 'clients.last_name as client_nom')
            ->first();

        $assignment = DB::table('deliveryassignment')
            ->join('users as livreurs', 'deliveryassignment.deliverer_id', '=', 'livreurs.user_id')
            ->where('deliveryassignment.request_id', $id_annonce)
            ->select('livreurs.first_name as livreur_prenom', 'livreurs.last_name as livreur_nom', 'deliveryassignment.deliverer_id')
            ->first();

        if (!$annonce || !$assignment) {
            return response()->json(['success' => false]);
        }

        $user = Session::get('user');
        $isAssignedDeliverer = $user && $assignment->deliverer_id == $user['user_id'];

        return response()->json([
            'success' => true,
            'client'  => $annonce->client_prenom . ' ' . $annonce->client_nom,
            'livreur' => $assignment->livreur_prenom . ' ' . $assignment->livreur_nom,
            'is_assigned_deliverer' => $isAssignedDeliverer,
        ]);
    }
    public static function posterSystem(
        int $idAnnonce,
        string $texte,
        array $meta = [],
        ?int $assignmentId = null
    ): void {
        DB::table('messages')->insert([
            'request_id'    => $idAnnonce,
            'assignment_id' => $assignmentId,
            'sender_id'     => null,
            'receiver_id'   => null,
            'content'       => $texte,
            'metadata'      => json_encode($meta),
            'sent_at'       => now(),
        ]);
    }
}