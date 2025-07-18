<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Helpers\NotificationHelper;
use App\Services\DeliverySplitService;

class BoxController extends Controller
{
    private function baseSelect(): array
    {
        return [
            DB::raw('id AS box_id'),
            'label',
            'address_street',
            'address_zip',
            'address_city',
            'location_city',
            'location_code',
            'lat',
            'lon',
            DB::raw("CONCAT_WS(' ', address_street, address_zip, address_city) AS full_address"),
        ];
    }

    public function listAll()
    {
        $boxes = DB::table('storage_boxes')
            ->select($this->baseSelect())
            ->orderBy('label')
            ->get();

        return response()->json(['success' => true, 'boxes' => $boxes]);
    }

    public function near(Request $request)
    {
        $code = $request->query('code');
        if (!preg_match('/^\d{5}$/', $code)) {
            return response()->json(['success' => false, 'message' => 'Code postal invalide'], 400);
        }

        $dept = substr($code, 0, 2);

        $boxes = DB::table('storage_boxes')
            ->select($this->baseSelect())
            ->where('location_code', 'like', $dept . '%')
            ->orderBy('label')
            ->get();

        return response()->json(['success' => true, 'boxes' => $boxes]);
    }

    public function index()
    {
        $user     = Session::get('user');
        $allowed  = ['Deliverer', 'Customer', 'Seller'];

        if (!$user || !array_intersect($user['roles'], $allowed)) {
            return response()->json(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        $boxes = DB::table('storage_boxes')
            ->select($this->baseSelect())
            ->orderBy('label')
            ->get();

        return response()->json(['success' => true, 'boxes' => $boxes]);
    }

    public function deposit(Request $request)
    {
        $user = $this->authLivreur();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $data = $request->validate([
            'id_annonce' => 'required|integer|exists:requests,request_id',
            'box_id'     => 'required|integer|exists:storage_boxes,id',   // colonne réelle
        ]);

        $assign = DB::table('deliveryassignment')
            ->where('request_id', $data['id_annonce'])
            ->where('deliverer_id', $user['user_id'])
            ->first();
        if (!$assign) {
            return response()->json(['success' => false, 'message' => 'Vous n\'êtes pas affecté à cette livraison'], 403);
        }

        DB::beginTransaction();
        try {
            DB::table('box_assignments')->insert([
                'box_id'     => $data['box_id'],
                'request_id' => $data['id_annonce'],
                'status'     => 'Déposé',
                'datetime'   => now(),
            ]);

            DB::table('deliveryassignment')
                ->where('assignment_id', $assign->assignment_id)
                ->update(['step' => 'Box dépôt']);

            $handoffId = DB::table('delivery_handoffs')
                ->where('assignment_id', $assign->assignment_id)
                ->where('status', 'Accepté')
                ->value('handoff_id');
            if ($handoffId) {
                DeliverySplitService::split($handoffId);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur ' . $e->getMessage()], 500);
        }

        $this->notifyClient(
            $data['id_annonce'],
            $data['box_id'],
            'Colis déposé en box',
            function ($box) {
                $adresse = $box->address_street
                    ? "{$box->address_street}, {$box->address_zip} {$box->address_city}"
                    : "{$box->location_city} ({$box->location_code})";
                return "Votre colis est déposé dans la box « {$box->label} » à {$adresse}.";
            }
        );

        return response()->json(['success' => true, 'message' => 'Colis déposé en box.']);
    }

    public function retrieve(Request $request)
    {
        $user = $this->authLivreur();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $data = $request->validate([
            'id_annonce' => 'required|integer|exists:requests,request_id',
            'box_id'     => 'required|integer|exists:storage_boxes,id',
        ]);

        $boxLog = DB::table('box_assignments')
            ->where([
                ['box_id', '=', $data['box_id']],
                ['request_id', '=', $data['id_annonce']],
                ['status', '=', 'Déposé'],
            ])->first();
        if (!$boxLog) {
            return response()->json(['success' => false, 'message' => 'Aucun dépôt trouvé pour cette box'], 404);
        }

        $assign = DB::table('deliveryassignment')
            ->where('request_id', $data['id_annonce'])
            ->where('deliverer_id', $user['user_id'])
            ->first();
        if (!$assign) {
            return response()->json(['success' => false, 'message' => 'Vous n\'êtes pas affecté à cette livraison'], 403);
        }

        DB::beginTransaction();
        try {
            DB::table('box_assignments')
                ->where('assignment_id', $boxLog->assignment_id)
                ->update(['status' => 'Retiré', 'datetime' => now()]);

            DB::table('deliveryassignment')
                ->where('assignment_id', $assign->assignment_id)
                ->update(['step' => 'Box retrait']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur ' . $e->getMessage()], 500);
        }

        $this->notifyClient(
            $data['id_annonce'],
            $data['box_id'],
            'Colis retiré de la box',
            fn() => 'Votre colis vient d\'être retiré de la box. Livraison en cours.'
        );

        return response()->json(['success' => true, 'message' => 'Colis récupéré, livraison poursuivie.']);
    }

    public function adminList()
    {
        $user = Session::get('user');
        if (!$user || !in_array('Admin', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $boxes = DB::table('storage_boxes')
            ->select($this->baseSelect())
            ->orderBy('label')
            ->get();

        return response()->json(['success' => true, 'boxes' => $boxes]);
    }

    public function store(Request $request)
    {
        $user = Session::get('user');
        if (!$user || !in_array('Admin', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $data = $request->validate([
            'label'          => 'required|string|max:100',
            'address_street' => 'required|string|max:150',
            'address_zip'    => 'required|string|max:10',
            'address_city'   => 'required|string|max:100',
            'location_city'  => 'required|string|max:100',
            'location_code'  => 'required|string|max:10',
            'lat'            => 'required|numeric|between:-90,90',
            'lon'            => 'required|numeric|between:-180,180',
        ]);

        DB::table('storage_boxes')->insert([
            'label'          => $data['label'],
            'address_street' => $data['address_street'],
            'address_zip'    => $data['address_zip'],
            'address_city'   => $data['address_city'],
            'location_city'  => $data['location_city'],
            'location_code'  => $data['location_code'],
            'lat'            => $data['lat'],
            'lon'            => $data['lon'],
            'created_at'     => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Boîte ajoutée']);
    }

    public function update(Request $request, $id)
    {
        $user = Session::get('user');
        if (!$user || !in_array('Admin', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $box = DB::table('storage_boxes')->where('id', $id)->first();
        if (!$box) {
            return response()->json(['success' => false, 'message' => 'Boîte introuvable'], 404);
        }

        $data = $request->validate([
            'label'          => 'required|string|max:100',
            'address_street' => 'required|string|max:150',
            'address_zip'    => 'required|string|max:10',
            'address_city'   => 'required|string|max:100',
            'location_city'  => 'required|string|max:100',
            'location_code'  => 'required|string|max:10',
            'lat'            => 'required|numeric|between:-90,90',
            'lon'            => 'required|numeric|between:-180,180',
        ]);

        DB::table('storage_boxes')->where('id', $id)->update([
            'label'          => $data['label'],
            'address_street' => $data['address_street'],
            'address_zip'    => $data['address_zip'],
            'address_city'   => $data['address_city'],
            'location_city'  => $data['location_city'],
            'location_code'  => $data['location_code'],
            'lat'            => $data['lat'],
            'lon'            => $data['lon'],
        ]);

        return response()->json(['success' => true, 'message' => 'Boîte mise à jour']);
    }

    public function destroy($id)
    {
        $user = Session::get('user');
        if (!$user || !in_array('Admin', $user['roles'])) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $box = DB::table('storage_boxes')->where('id', $id)->first();
        if (!$box) {
            return response()->json(['success' => false, 'message' => 'Boîte introuvable'], 404);
        }

        DB::table('storage_boxes')->where('id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Boîte supprimée']);
    }

    private function authLivreur(): ?object
    {
        $user = Session::get('user');
        return ($user && in_array('Deliverer', $user['roles'])) ? (object) $user : null;
    }

    private function notifyClient(int $idAnnonce, int $boxId, string $titre, callable $msgBuilder): void
    {
        $annonce = DB::table('requests')->where('request_id', $idAnnonce)->first();
        $box     = DB::table('storage_boxes')->where('id', $boxId)->first();

        if ($annonce && $box) {
            NotificationHelper::envoyer(
                $annonce->user_id,
                $titre,
                $msgBuilder($box)
            );
        }
    }
}