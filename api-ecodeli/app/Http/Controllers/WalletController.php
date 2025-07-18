<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function show()
    {
        $user = Session::get('user');
        if (! $user || !array_intersect(['Deliverer', 'ServiceProvider'], $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $wallet = DB::table('wallets')->where('user_id', $user['user_id'])->first();

        return response()->json([
            'success' => true,
            'balance_cent' => $wallet->balance_cent ?? 0,
        ]);
    }

    public function withdraw(Request $request)
    {
        $user = Session::get('user');
        if (! $user || !array_intersect(['Deliverer', 'ServiceProvider'], $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $validated = $request->validate([
            'amount_cent' => 'required|integer|min:100', // au moins 1 €
        ]);

        $wallet = DB::table('wallets')->where('user_id', $user['user_id'])->first();

        if (!$wallet || $wallet->balance_cent < $validated['amount_cent']) {
            return response()->json(['success' => false, 'message' => 'Solde insuffisant'], 400);
        }

        // Déduire le montant du wallet
        DB::table('wallets')->where('user_id', $user['user_id'])->update([
            'balance_cent' => $wallet->balance_cent - $validated['amount_cent'],
            'updated_at' => now(),
        ]);

        // Enregistrer la demande de retrait
        DB::table('withdrawal')->insert([
            'user_id' => $user['user_id'],
            'amount_cent' => $validated['amount_cent'],
            'status' => 'pending',
            'created_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Demande de retrait enregistrée']);
    }
public function received()
{
    $user = Session::get('user');
    if (! $user || !array_intersect(['Deliverer', 'ServiceProvider'], $user['roles'])) {
        return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
    }

    $payments = DB::table('payments')
        ->where('payee_id', $user['user_id'])
        ->orderByDesc('payment_date')
        ->get(['payment_date', 'amount', 'payment_type', 'status']);

    return response()->json([
        'success' => true,
        'payments' => $payments
    ]);
}
public function listWithdrawals()
{
    $user = Session::get('user');
    if (! $user || !array_intersect(['Deliverer', 'ServiceProvider'], $user['roles'])) {
        return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
    }

    $withdrawal = DB::table('withdrawal')
        ->where('user_id', $user['user_id'])
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json(['success' => true, 'withdrawal' => $withdrawal]);
}

}