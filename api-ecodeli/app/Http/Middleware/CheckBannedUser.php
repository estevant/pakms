<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CheckBannedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $sessionUser = Session::get('user');
        
        if ($sessionUser) {
            $user = User::find($sessionUser['user_id']);
            
            if ($user && $user->banned) {
                // Déconnecter l'utilisateur banni
                Auth::logout();
                Session::flush();
                Session::invalidate();
                Session::regenerateToken();
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Votre compte a été banni. Vous avez été déconnecté.',
                        'user' => null
                    ], 403);
                } else {
                    return redirect('/login')->with('error', 'Votre compte a été banni. Vous avez été déconnecté.');
                }
            }
        }
        
        return $next($request);
    }
} 