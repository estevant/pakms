<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use Illuminate\Http\Request;
use App\Models\Proposition_de_prestations;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Models\Justificatif;
use Illuminate\Support\Facades\DB;
use App\Traits\BannedWordsChecker;

class ServiceTypeController extends Controller
{
    use BannedWordsChecker;

    public function index()
    {
        return response()->json([
            'data' => ServiceType::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_price_fixed' => 'required|boolean',
        ]);
        $type = new ServiceType();
        $type->name = $validated['name'];
        $type->description = $validated['description'] ?? null;
        $type->is_price_fixed = $validated['is_price_fixed'];
        $type->fixed_price = $validated['price'];
        $type->save();
        return response()->json(['success'=>true,'message'=>'Type ajouté','type'=>$type]);
    }

    public function proposerNouveauType(Request $request)
    {
        $user = Session::get('user');
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Non connecté'], 401);
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        // Vérification des mots interdits dans la proposition
        $checkResult = $this->checkMultipleFields([
            $validated['nom'] ?? '',
            $validated['description'] ?? ''
        ], $user['user_id']);
        
        if ($checkResult) {
            return response()->json([
                'success' => false,
                'message' => $checkResult['message']
            ], 403);
        }

        // Vérifier si la proposition existe déjà
        $exists = Proposition_de_prestations::where('user_id', $user['user_id'])
            ->where('nom', $validated['nom'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà proposé ce type de prestation'
            ], 409);
        }

        Proposition_de_prestations::create([
            'user_id' => $user['user_id'],
            'nom' => $validated['nom'],
            'description' => $validated['description'],
            'statut' => 'En attente',
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Proposition envoyée avec succès'
        ]);
    }

    public function mesDemandesTypes()
    {
        $user = Session::get('user');
        if (!$user) return response()->json(['error' => 'Non connecté'], 401);

        $demandes = DB::table('proposition_de_prestations')
            ->leftJoin('justificatifs', 'proposition_de_prestations.justificatif_id', '=', 'justificatifs.id')
            ->where('proposition_de_prestations.user_id', $user['user_id'])
            ->orderBy('proposition_de_prestations.created_at', 'desc')
            ->select(
                'proposition_de_prestations.nom',
                'proposition_de_prestations.description',
                'proposition_de_prestations.created_at',
                'proposition_de_prestations.statut',
                'justificatifs.filename as justificatif_filename'
            )
            ->get();

        // Générer l'URL du justificatif si présent
        $data = $demandes->map(function($d) {
            $url = null;
            if ($d->justificatif_filename) {
                $url = url('/storage/justificatifs/' . $d->justificatif_filename);
            }
            return [
                'nom' => $d->nom,
                'description' => $d->description,
                'created_at' => $d->created_at,
                'statut' => $d->statut,
                'justificatif_url' => $url
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function mesJustificatifs()
    {
        $user = Session::get('user');
        if (!$user) return response()->json(['error' => 'Non connecté'], 401);

        $justifs = \DB::table('justificatifs')
            ->where('user_id', $user['user_id'])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $justifs->map(function($j) use ($user) {
            $url = null;
            if ($j->filename) {
                $filePath = storage_path("app/public/{$j->filename}");
                if (file_exists($filePath)) {
                    $pathParts = explode('/', $j->filename);
                    if (count($pathParts) >= 3 && $pathParts[0] === 'justificatifs') {
                        $userId = $pathParts[1];
                        $filename = $pathParts[2];
                        $url = url("/storage/justificatifs/{$userId}/{$filename}");
                    }
                }
            }
            
            return [
                'type' => $j->type,
                'description' => $j->description,
                'created_at' => $j->created_at,
                'statut' => $j->statut ?? 'En attente',
                'url' => $url
            ];
        });

        return response()->json(['data' => $data]);
    }

    // --- CRUD ADMIN ---
    public function adminIndex() {
        return response()->json(ServiceType::all());
    }
    public function adminStore(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_price_fixed' => 'required|boolean',
        ]);
        $type = new ServiceType();
        $type->name = $validated['name'];
        $type->description = $validated['description'] ?? null;
        $type->is_price_fixed = $validated['is_price_fixed'];
        $type->fixed_price = $validated['price'];
        $type->save();
        return response()->json(['success'=>true,'message'=>'Type ajouté','type'=>$type]);
    }
    public function adminUpdate(Request $request, $id) {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_price_fixed' => 'required|boolean',
        ]);
        $type = ServiceType::findOrFail($id);
        $type->name = $validated['name'];
        $type->description = $validated['description'] ?? null;
        $type->is_price_fixed = $validated['is_price_fixed'];
        $type->fixed_price = $validated['price'];
        $type->save();
        return response()->json(['success'=>true,'message'=>'Type modifié','type'=>$type]);
    }
    public function adminDestroy($id) {
        $type = ServiceType::findOrFail($id);
        $type->delete();
        return response()->json(['success'=>true,'message'=>'Type supprimé']);
    }
    public function adminTogglePrixImpose(Request $request, $id) {
        $type = ServiceType::findOrFail($id);
        $isFixed = $request->input('is_price_fixed') ? 1 : 0;
        $type->is_price_fixed = $isFixed;
        if ($isFixed) {
            $type->fixed_price = $type->price ?? 0;
            $type->price = null;
        } else {
            $type->price = $type->fixed_price ?? 0;
            $type->fixed_price = null;
        }
        $type->save();
        return response()->json(['success'=>true,'message'=>'Statut prix imposé modifié','type'=>$type]);
    }

    // Méthode RESTful pour update
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_price_fixed' => 'required|boolean',
        ]);
        $type = ServiceType::findOrFail($id);
        $type->name = $validated['name'];
        $type->description = $validated['description'] ?? null;
        $type->is_price_fixed = $validated['is_price_fixed'];
        $type->fixed_price = $validated['price'];
        $type->save();
        return response()->json(['success'=>true,'message'=>'Type modifié','type'=>$type]);
    }

    // Méthode RESTful pour destroy
    public function destroy($id)
    {
        $type = ServiceType::findOrFail($id);
        $type->delete();
        return response()->json(['success'=>true,'message'=>'Type supprimé']);
    }
} 