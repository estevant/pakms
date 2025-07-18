<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\Report;
use App\Models\Request            as Order;
use App\Models\DeliveryAssignment as Assignment;
use App\Models\User;
use App\Traits\BannedWordsChecker;

class ReportController extends Controller
{
    use BannedWordsChecker;

    public function store(Request $request)
    {
        $user = Session::get('user');
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $data = $request->validate([
            'target_type' => ['required', Rule::in(['annonce', 'user'])],
            'target_id'   => 'required|integer',
            'reason'      => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // Vérification des mots interdits dans le signalement
        $checkResult = $this->checkMultipleFields([
            $data['reason'] ?? '',
            $data['description'] ?? ''
        ], $user['user_id']);
        
        if ($checkResult) {
            return response()->json([
                'success' => false,
                'message' => $checkResult['message']
            ], 403);
        }

        if ($data['target_type'] === 'annonce') {
            $order = Order::find($data['target_id']);
            if (! $order || $order->user_id !== $user['user_id']) {
                return response()->json(['success' => false, 'message' => 'Annonce introuvable ou non autorisée'], 403);
            }
        } else {
            $worked = Assignment::where('deliverer_id', $data['target_id'])
                ->whereHas('request', fn($q) => $q->where('user_id', $user['user_id']))
                ->exists();
            if (! $worked) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez signaler que des livreurs ayant travaillé pour vous'
                ], 400);
            }
        }

        if (Report::where('reporter_id', $user['user_id'])
                  ->where('target_type', $data['target_type'])
                  ->where('target_id',   $data['target_id'])
                  ->exists()) {
            return response()->json(['success' => false, 'message' => 'Vous avez déjà signalé cette cible'], 409);
        }

        Report::create([
            'reporter_id' => $user['user_id'],
            'target_type' => $data['target_type'],
            'target_id'   => $data['target_id'],
            'reason'      => $data['reason'],
            'description' => $data['description'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Signalement enregistré'], 201);
    }

    public function index(Request $request)
    {
        $user = Session::get('user');
        if (! $user || ! in_array('Admin', $user['roles'] ?? [])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $query = Report::with('reporter')->orderByDesc('created_at');

        if ($request->filled('target_type')) {
            $query->where('target_type', $request->target_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($sub) =>
                $sub->where('reason', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
            );
        }
        if ($request->filled('after')) {
            $query->where('created_at', '>=', $request->after);
        }
        if ($request->filled('before')) {
            $query->where('created_at', '<=', $request->before);
        }

        $reports = $query->get();

        $userIds    = $reports->where('target_type','user')->pluck('target_id')->unique()->toArray();
        $users      = User::whereIn('user_id', $userIds)
                          ->select('user_id','first_name','last_name')
                          ->get()
                          ->keyBy('user_id');

        $data = $reports->map(function($r) use($users) {
            if ($r->target_type === 'annonce') {
                $label = "Annonce #{$r->target_id}";
            } else {
                $u = $users->get($r->target_id);
                $label = $u
                    ? trim("{$u->first_name} {$u->last_name}")
                    : "Utilisateur #{$r->target_id}";
            }

            $count = Report::where('target_type', $r->target_type)
                           ->where('target_id',   $r->target_id)
                           ->count();

            return [
                'report_id'     => $r->report_id,
                'created_at'    => Carbon::parse($r->created_at)->format('Y-m-d H:i'),
                'reporter'      => trim("{$r->reporter->first_name} {$r->reporter->last_name}"),
                'target_type'   => $r->target_type,
                'target_id'     => $r->target_id,
                'target_label'  => $label,
                'reports_count' => $count,
                'reason'        => $r->reason,
                'description'   => $r->description,
                'status'        => $r->status,
            ];
        });

        return response()->json(['success' => true, 'reports' => $data]);
    }

    public function updateStatus($id, Request $request)
    {
        $user = Session::get('user');
        if (! $user || ! in_array('Admin', $user['roles'] ?? [])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $v = $request->validate([
            'status' => ['required', Rule::in(['Ouvert','Résolu','Rejeté'])]
        ]);

        $r = Report::find($id);
        if (! $r) {
            return response()->json(['success' => false, 'message' => 'Signalement introuvable'], 404);
        }

        $r->status = $v['status'];
        $r->save();

        return response()->json(['success' => true, 'message' => 'Statut mis à jour']);
    }

    public function destroy($id)
    {
        $user = Session::get('user');
        if (! $user || ! in_array('Admin', $user['roles'] ?? [])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $r = Report::find($id);
        if (! $r) {
            return response()->json(['success' => false, 'message' => 'Signalement introuvable'], 404);
        }

        $r->delete();
        return response()->json(['success' => true, 'message' => 'Signalement supprimé']);
    }
}