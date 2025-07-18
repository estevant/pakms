<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    /**
     * Vérifie que l’utilisateur a l’un des rôles passés en paramètre.
     * Ex : ->middleware('role:Seller,Customer')
     */
    public function handle(Request $request, Closure $next, ...$rolesExpected)
    {
        // Récupère la session user
        $user = Session::get('user');
        Log::info('[RoleMiddleware] Session user :', ['user' => $user]);

        if (! $user) {
            Log::warning('[RoleMiddleware] Aucune session utilisateur.');
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé – pas connecté.'
            ], 401);
        }

        // $rolesExpected est maintenant un tableau ['Seller','Customer',…]
        $allowed   = $rolesExpected;
        $userRoles = $user['roles'] ?? [];

        Log::info('[RoleMiddleware] Rôles attendus :', $allowed);
        Log::info('[RoleMiddleware] Rôles utilisateur :', $userRoles);

        $intersect = collect($userRoles)->intersect($allowed);
        Log::info('[RoleMiddleware] Rôles communs :', $intersect->all());

        if ($intersect->isEmpty()) {
            Log::warning('[RoleMiddleware] Accès refusé. Aucun rôle correspondant.');
            return response()->json([
                'success' => false,
                'message' => 'Accès réservé aux : ' . implode(' ou ', $allowed)
            ], 403);
        }

        Log::info('[RoleMiddleware] Accès autorisé.');
        return $next($request);
    }
}
