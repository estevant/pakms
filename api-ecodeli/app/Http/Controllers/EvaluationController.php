<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Reservation;

class EvaluationController extends Controller
{
    public function getForPrestataire()
{
    $user = Session::get('user');
    if (!$user) return response()->json(['error' => 'Non connecté'], 401);

    $avis = DB::table('review')
        ->join('users as clients', 'review.reviewer_id', '=', 'clients.user_id')
        ->where('review.target_deliverer_id', $user['user_id'])
        ->orderByDesc('review.created_at')
        ->select(
            'review.rating',
            'review.comment',
            'review.created_at',
            'clients.first_name',
            'clients.last_name'
        )
        ->get();

    return response()->json($avis);
}

}
