<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class NotificationHelper
{
    public static function envoyer(int $user_id, string $titre, string $contenu): void
    {
        DB::table('notifications')->insert([
            'user_id'    => $user_id,
            'titre'      => $titre,
            'contenu'    => $contenu,
            'is_read'    => false,
            'created_at' => now(),
        ]);
    }

    public static function envoyerAuxLivreursProches(string $ville, string $titre, string $contenu): void
    {
        $ids = DB::table('users')
            ->join('userrole', 'users.user_id', '=', 'userrole.user_id')
            ->where('role_id', 4)
            ->where('preferred_city', $ville)
            ->pluck('users.user_id');

        foreach ($ids as $id) {
            self::envoyer($id, $titre, $contenu);
        }
    }
}
