<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Session::get('user');
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $limit = (int)($request->query('limit', 50));
        $limit = $limit > 0 ? $limit : 50;

        $notifications = DB::table('notifications')
            ->where('user_id', $user['user_id'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        $unread = DB::table('notifications')
            ->where('user_id', $user['user_id'])
            ->where('is_read', 0)
            ->count();

        return response()->json([
            'success'        => true,
            'unread_count'   => $unread,
            'notifications'  => $notifications->map(fn($n) => [
                'id'      => $n->id,
                'titre'   => $n->titre,
                'contenu' => $n->contenu,
                'is_read' => (bool) $n->is_read,
                'date'    => $n->created_at ? date('Y-m-d H:i', strtotime($n->created_at)) : '—',
            ])
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $user = Session::get('user');
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $affected = DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', $user['user_id'])
            ->update(['is_read' => 1]);

        if (!$affected) {
            return response()->json(['success' => false, 'message' => 'Notification introuvable'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Notification marquée comme lue.']);
    }

    public function markAllAsRead()
    {
        $user = Session::get('user');
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        DB::table('notifications')
            ->where('user_id', $user['user_id'])
            ->update(['is_read' => 1]);

        return response()->json(['success' => true, 'message' => 'Toutes les notifications sont maintenant lues.']);
    }
}