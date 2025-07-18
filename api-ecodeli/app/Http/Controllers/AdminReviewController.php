<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use Carbon\Carbon;

class AdminReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with('reviewer')->orderByDesc('created_at')->get();
    
        $data = $reviews->map(function ($r) {
            $moyenneLivreur = Review::where('target_deliverer_id', $r->target_deliverer_id)->avg('rating') ?? 0;
            $moyenneClient  = Review::where('reviewer_id', $r->reviewer_id)->avg('rating') ?? 0;
    
            return [
                'review_id'        => $r->review_id,
                'reviewer'         => trim("{$r->reviewer->first_name} {$r->reviewer->last_name}"),
                'reviewer_id'      => $r->reviewer_id,
                'deliverer_id'     => $r->target_deliverer_id,
                'request_id'       => $r->request_id,
                'rating'           => $r->rating,
                'comment'          => $r->comment,
                'created_at'       => \Carbon\Carbon::parse($r->created_at)->format('Y-m-d H:i'),
                'avg_deliverer'    => round($moyenneLivreur, 2),
                'avg_reviewer'     => round($moyenneClient, 2),
            ];
        });
    
        return response()->json([
            'success' => true,
            'total'   => $reviews->count(),
            'reviews' => $data,
        ]);
    }      

    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return response()->json(['success' => true]);
    }
    public function avisPrestataires()
{
    try {
        $avis = \DB::table('reservation as r')
            ->leftJoin('users as u', 'r.user_id', '=', 'u.user_id')
            ->leftJoin('serviceavailability as sa', 'r.availability_id', '=', 'sa.availability_id')
            ->leftJoin('service as s', 'sa.offered_service_id', '=', 's.offered_service_id')
            ->leftJoin('users as p', 's.user_id', '=', 'p.user_id')
            ->select(
                'r.created_at',
                'u.first_name as client_prenom',
                'u.last_name as client_nom',
                'p.first_name as prestataire_prenom',
                'p.last_name as prestataire_nom',
                'r.note',
                'r.commentaire'
            )
            ->where(function($q) {
                $q->whereNotNull('r.note')
                  ->orWhereNotNull('r.commentaire')
                  ->orWhere('r.note', '!=', '')
                  ->orWhere('r.commentaire', '!=', '');
            })
            ->orderByDesc('r.created_at')
            ->get();

        return response()->json([
            'success' => true, 
            'avis' => $avis,
            'debug' => [
                'total_reservations' => \DB::table('reservation')->count(),
                'reservations_with_data' => $avis->count(),
                'services_count' => \DB::table('service')->count(),
                'availability_count' => \DB::table('serviceavailability')->count()
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}
}