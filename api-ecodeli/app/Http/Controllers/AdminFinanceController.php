<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminFinanceController extends Controller
{
    public function caTotal()
    {
        $total = DB::table('payments')
            ->where('status', 'Payé') 
            ->sum('amount'); 

        return response()->json([
            'success' => true,
            'ca_total_euros' => $total / 100,
        ]);
    }

public function listeRetraits()
{
    $retraits = DB::table('withdrawal')
        ->join('users', 'users.user_id', '=', 'withdrawal.user_id')
        ->select(
            'withdrawal.created_at',
            'withdrawal.amount_cent',
            'withdrawal.status',
            'users.first_name',
            'users.last_name'
        )
        ->orderByDesc('withdrawal.created_at')
        ->get()
        ->map(function ($r) {
            $r->montant_euros = $r->amount_cent / 100;
            $r->full_name = $r->first_name . ' ' . $r->last_name;
            return $r;
        });

    return response()->json([
        'success' => true,
        'retraits' => $retraits
    ]);
}

   public function listeEncaissements()
{
    $paiements = DB::table('payments')
        ->join('users', 'users.user_id', '=', 'payments.payee_id')
        ->select(
            'payments.payment_date',
            'payments.amount',
            'payments.payment_type',
            'payments.status',
            'users.first_name',
            'users.last_name'
        )
        ->orderByDesc('payments.payment_date')
        ->get()
        ->map(function ($p) {
            $p->montant_euros = $p->amount / 100;
            $p->full_name = $p->first_name . ' ' . $p->last_name;
            return $p;
        });

    return response()->json([
        'success' => true,
        'encaissements' => $paiements
    ]);
}
}  