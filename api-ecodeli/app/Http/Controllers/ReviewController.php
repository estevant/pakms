<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\NotificationHelper;
use App\Models\Review;
use App\Models\Request            as Order;
use App\Models\DeliveryAssignment as Assignment;
use App\Traits\BannedWordsChecker;

class ReviewController extends Controller
{
    use BannedWordsChecker;

    public function store(Request $request)
    {

        $user = Session::get('user');
        if (!$user || (!in_array('Customer', $user['roles'] ?? []) && !in_array('Seller', $user['roles'] ?? []))) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $v = $request->validate([
            'deliverer_id' => 'required|integer|exists:users,user_id',
            'request_id'   => 'required|integer|exists:requests,request_id',
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'nullable|string|max:1000',
        ]);

        // Vérification des mots interdits dans le commentaire
        $checkResult = $this->checkBannedWords($v['comment'] ?? '', $user['user_id']);
        if ($checkResult) {
            return response()->json([
                'success' => false,
                'message' => $checkResult['message']
            ], 403);
        }

        $order = Order::findOrFail($v['request_id']);
        if ($order->user_id !== $user['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Annonce introuvable ou non autorisée'
            ], 403);
        }

        $assignment = Assignment::where('request_id',  $order->request_id)
                                ->where('deliverer_id', $v['deliverer_id'])
                                ->latest('assignment_id')
                                ->first();

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Livreur invalide pour cette annonce'
            ], 400);
        }

        if ($assignment->status !== 'Livrée') {
            return response()->json([
                'success' => false,
                'message' => 'La course doit être terminée pour laisser un avis'
            ], 400);
        }

        if (Review::where('reviewer_id', $user['user_id'])
                  ->where('request_id', $v['request_id'])
                  ->where('target_deliverer_id', $v['deliverer_id'])
                  ->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà laissé un avis pour cette course'
            ], 409);
        }

        $review = Review::create([
            'reviewer_id'        => $user['user_id'],
            'target_deliverer_id' => $v['deliverer_id'],
            'request_id'         => $v['request_id'],
            'rating'             => $v['rating'],
            'comment'            => $v['comment'] ?? null,
        ]);

        NotificationHelper::envoyer(
            $v['deliverer_id'],
            '⭐ Nouvel avis reçu',
            "Vous avez reçu un avis {$v['rating']}/5 pour votre livraison."
        );

        return response()->json([
            'success' => true,
            'message' => 'Avis enregistré avec succès'
        ], 201);
    }

    public function indexForDeliverer(int $delivererId)
    {
        $reviews = Review::with('reviewer')
                         ->where('target_deliverer_id', $delivererId)
                         ->orderByDesc('created_at')
                         ->get();

        $data = $reviews->map(fn ($r) => [
            'review_id'  => $r->review_id,
            'reviewer'   => trim("{$r->reviewer->first_name} {$r->reviewer->last_name}"),
            'rating'     => $r->rating,
            'comment'    => $r->comment,
            'created_at' => $r->created_at->format('Y-m-d H:i'),
        ]);

        return response()->json([
            'success' => true,
            'average' => round($reviews->avg('rating') ?: 0, 2),
            'reviews' => $data,
        ]);
    }
    public function index()
{
    $reviews = Review::with(['reviewer', 'deliverer', 'request'])
        ->orderByDesc('created_at')
        ->get();

    $data = $reviews->map(fn($r) => [
        'review_id' => $r->review_id,
        'rating'    => $r->rating,
        'comment'   => $r->comment,
        'created_at'=> $r->created_at->format('Y-m-d H:i'),
        'reviewer'  => trim("{$r->reviewer->first_name} {$r->reviewer->last_name}"),
        'deliverer' => trim("{$r->deliverer->first_name} {$r->deliverer->last_name}"),
        'request_id'=> $r->request_id,
    ]);

    return response()->json(['success' => true, 'reviews' => $data]);
}
public function destroy($id)
{
    $review = Review::findOrFail($id);
    $review->delete();

    return response()->json(['success' => true, 'message' => 'Avis supprimé']);
}

}