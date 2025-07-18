<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\BannedWordsChecker;

class AuthController extends Controller
{
    use BannedWordsChecker;

    public function register(Request $request)
    {
        if (!$request->expectsJson() && !$request->isMethod('post')) {
            return response()->json(['error' => 'Format de requête non pris en charge.'], 406);
        }

        $rules = [
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'required|string|max:100',
            'email'            => ['required', 'email', 'max:100', Rule::unique('users', 'email')],
            'password'         => 'required|string|min:6',
            'role'             => ['required', Rule::in(['Customer', 'Seller', 'Deliverer', 'ServiceProvider'])],
            'business_name'    => 'nullable|string|max:100',
            'business_address' => 'nullable|string|max:255',
            'profile_picture'  => 'nullable|file|max:2048',
            'phone'            => 'nullable|string|max:20',
        ];

        if ($request->input('role') !== 'Customer') {
            $rules['piece_identite'] = 'required|file|max:4096';
        } else {
            $rules['piece_identite'] = 'nullable|file|max:4096';
        }

        if ($request->input('role') === 'Seller') {
            $rules['start_date'] = 'required|date';
            $rules['end_date']   = 'required|date|after_or_equal:start_date';
            $rules['terms']      = 'nullable|string';
        }

        if ($request->input('role') === 'ServiceProvider') {
            $rules['service_type'] = 'required|integer';
            $rules['description'] = 'required|string';
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format de photo de profil non autorisé. Seuls les formats JPG, JPEG et PNG sont acceptés.'
                ], 422);
            }
        }

        if ($request->hasFile('piece_identite')) {
            $file = $request->file('piece_identite');
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format de pièce d\'identité non autorisé. Seuls les formats PDF, JPG, JPEG et PNG sont acceptés.'
                ], 422);
            }
        }

        // Vérification des mots interdits lors de l'inscription
        $fieldsToCheck = [
            $validated['first_name'] ?? '',
            $validated['last_name'] ?? '',
            $validated['business_name'] ?? '',
            $validated['business_address'] ?? '',
            $validated['description'] ?? '',
            $validated['terms'] ?? ''
        ];

        $checkResult = $this->checkMultipleFields($fieldsToCheck);
        if ($checkResult) {
            return response()->json([
                'success' => false,
                'message' => $checkResult['message']
            ], 403);
        }

        DB::beginTransaction();
        try {
            $path = null;
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                if ($file->isValid()) {
                    $extension = $file->getClientOriginalExtension();
                    if (empty($extension)) {
                        $extension = 'jpg';
                    }
                    
                    $filename = uniqid('profile_') . '_' . time() . '.' . $extension;
                    $uploadPath = storage_path('app/public/profile_pictures/');
                    
                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }
                    
                    $fullPath = $uploadPath . $filename;
                    if (move_uploaded_file($file->getRealPath(), $fullPath)) {
                        $path = 'profile_pictures/' . $filename;
                    }
                }
            }

            $user = User::create([
                'first_name'       => $validated['first_name'],
                'last_name'        => $validated['last_name'],
                'email'            => $validated['email'],
                'password'         => Hash::make($validated['password']),
                'phone'            => $validated['phone'] ?? null,
                'profile_picture'  => $path,
                'business_name'    => $validated['business_name'] ?? null,
                'business_address' => $validated['business_address'] ?? null,
                'is_validated'     => $validated['role'] === 'Deliverer' ? 0 : null,
            ]);

            if ($request->hasFile('piece_identite')) {
                $file = $request->file('piece_identite');
                if ($file->isValid()) {
                    $extension = $file->getClientOriginalExtension();
                    if (empty($extension)) {
                        $extension = 'pdf';
                    }
                    
                    $filename = uniqid('identite_') . '.' . $extension;
                    $uploadPath = storage_path('app/public/justificatifs/');
                    
                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }
                    
                    $fullPath = $uploadPath . $filename;
                    if (move_uploaded_file($file->getRealPath(), $fullPath)) {
                        \App\Models\Justificatif::create([
                            'user_id' => $user->user_id,
                            'type' => $request->input('type', 'CNI'),
                            'description' => 'Pièce d\'identité fournie à l\'inscription',
                            'filename' => $filename,
                            'statut' => 'En attente',
                        ]);
                    }
                }
            }

            if ($validated['role'] === 'Deliverer') {
                $user->nfc_code = substr(bin2hex(random_bytes(8)), 0, 16);
                $user->save();
            }

            $role = Role::where('role_name', $validated['role'])->first();
            if ($role) {
                $user->roles()->attach($role->role_id);
            }

            // Ajout automatique prestation/proposition pour prestataire
            if ($validated['role'] === 'ServiceProvider') {
                // 1. Créer la prestation (service)
                $serviceTypeId = $validated['service_type'];
                $description = $validated['description'];
                // Vérifier si la prestation existe déjà
                $exists = DB::table('service')
                    ->where('user_id', $user->user_id)
                    ->where('service_type_id', $serviceTypeId)
                    ->exists();
                if (!$exists) {
                    DB::table('service')->insert([
                        'user_id' => $user->user_id,
                        'service_type_id' => $serviceTypeId,
                        'details' => $description,
                        'address' => $user->business_address ?? '',
                        'price' => 0,
                    ]);
                }
                // 2. Créer la proposition (proposition_de_prestations)
                $nom = DB::table('servicetype')->where('service_type_id', $serviceTypeId)->value('name');
                $existsProp = DB::table('proposition_de_prestations')
                    ->where('user_id', $user->user_id)
                    ->where('nom', $nom)
                    ->exists();
                if (!$existsProp) {
                    DB::table('proposition_de_prestations')->insert([
                        'user_id' => $user->user_id,
                        'nom' => $nom,
                        'description' => $description,
                        'statut' => 'En attente',
                        'created_at' => now(),
                    ]);
                }
            }

            if ($validated['role'] === 'Seller') {
                Contract::create([
                    'user_id'    => $user->user_id,
                    'start_date' => $validated['start_date'],
                    'end_date'   => $validated['end_date'],
                    'terms'      => $validated['terms'] ?? null,
                    'status'     => 'pending',
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Inscription réussie !'
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Erreur interne, veuillez réessayer.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['error' => 'Identifiants incorrects'], 401);
        }

        // Vérifier si l'utilisateur est banni
        if ($user->banned) {
            return response()->json(['error' => 'Votre compte a été banni. Vous ne pouvez plus vous connecter à EcoDeli.'], 403);
        }

        $roles = $user->roles()->pluck('role_name')->toArray();

        // Empêcher la connexion si le prestataire n'est pas validé
        if (in_array('ServiceProvider', $roles) && $user->is_validated != 1) {
            return response()->json(['error' => 'Votre compte prestataire est en attente de validation.'], 403);
        }

        if (in_array('Deliverer', $roles) && $user->is_validated != 1) {
            return response()->json(['error' => 'Votre compte livreur est en attente de validation.'], 403);
        }

        if (in_array('Seller', $roles)) {
            $contract = Contract::where('user_id', $user->user_id)
                ->latest('contract_id')
                ->first();
            if (!$contract || $contract->status === 'pending') {
                return response()->json(['error' => 'Votre contrat est en attente de validation.'], 403);
            }
            if ($contract->status === 'rejected') {
                return response()->json(['error' => 'Votre demande de contrat a été refusée.'], 403);
            }
        }

        Auth::login($user);
        
        Session::put('user', [
            'user_id'    => $user->user_id,
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'email'      => $user->email,
            'roles'      => $roles,
            'statut'     => $user->statut,
            'banned'     => $user->banned,
        ]);

        return response()->json(['success' => true, 'user' => Session::get('user')]);
    }

    public function logout()
    {
        Auth::logout();
        
        Session::flush(); // vide complètement toutes les données
        Session::invalidate(); // invalide la session
        Session::regenerateToken(); // pour éviter qu'une session soit recréée automatiquement

        return response()->json(['success' => true]);
    }

    public function getSession(Request $request)
    {
        $sessionUser = Session::get('user');
        if (! $sessionUser) {
            return response()->json(['user' => null]);
        }
        $user  = \App\Models\User::find($sessionUser['user_id']);
        
        // Vérifier si l'utilisateur est banni et le déconnecter automatiquement
        if ($user && $user->banned) {
            Auth::logout();
            Session::flush();
            Session::invalidate();
            Session::regenerateToken();
            return response()->json([
                'error' => 'Votre compte a été banni. Vous avez été déconnecté.',
                'user' => null
            ], 403);
        }
        
        if (!$user) {
            return response()->json(['user' => null]);
        }
        
        $roles = $user->roles()->pluck('role_name');
        return response()->json([
            'user' => [
                'user_id'         => $user->user_id,
                'first_name'      => $user->first_name,
                'last_name'       => $user->last_name,
                'email'           => $user->email,
                'roles'           => $roles,
                'is_validated'    => $user->is_validated,
                'profile_picture' => $user->profile_picture,
                'nfc_code'        => $user->nfc_code,
                'banned'          => $user->banned,
            ]
        ]);
    }
}


