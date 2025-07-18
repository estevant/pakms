<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Traits\BannedWordsChecker;

class ProfileController extends Controller
{
    use BannedWordsChecker;

    /**
     * Nettoie une valeur en supprimant les guillemets en trop
     */
    private function cleanValue($value)
    {
        if (is_string($value)) {
            // Supprime les guillemets doubles en trop
            $value = trim($value, '"');
            // Supprime les guillemets simples en trop
            $value = trim($value, "'");
            // Supprime les espaces en trop
            $value = trim($value);
        }
        return $value;
    }

    public function show()
    {
        $session = Session::get('user');
        if (!$session) {
            return response()->json(['success'=>false,'message'=>'Non autorisé'], 401);
        }

        $user = User::with('roles')
                    ->findOrFail($session['user_id']);

        return response()->json([
            'success' => true,
            'profile' => $user->only([
                'user_id','first_name','last_name','email','phone',
                'preferred_city','profile_picture','business_name',
                'business_address','description','sector'
            ]),
            'roles'   => $user->roles->pluck('role_name')
        ]);
    }

    public function update(Request $request)
    {
        $session = Session::get('user');
        if (!$session) {
            return response()->json(['success'=>false,'message'=>'Non autorisé'], 401);
        }

        $rules = [
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'required|string|max:100',
            'phone'            => 'nullable|string|max:20',
            'preferred_city'   => 'nullable|string|max:100',
            'profile_picture'  => 'nullable|file|max:2048',
            'password'         => 'nullable|confirmed|min:8',
            'business_name'    => 'nullable|string|max:100',
            'business_address' => 'nullable|string|max:255',
            'description'      => 'nullable|string|max:255',
            'sector'           => 'nullable|string|max:255',
        ];
        $data = $request->validate($rules);

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            if (!$file->isValid()) {
                return response()->json(['success'=>false,'message'=>'Fichier invalide'], 422);
            }
            
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                return response()->json(['success'=>false,'message'=>'Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WebP.'], 422);
            }
        }

        $fieldsToCheck = [
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            $data['business_name'] ?? '',
            $data['business_address'] ?? '',
            $data['description'] ?? '',
            $data['sector'] ?? ''
        ];

        $checkResult = $this->checkMultipleFields($fieldsToCheck, $session['user_id']);
        if ($checkResult) {
            return response()->json([
                'success' => false,
                'message' => $checkResult['message']
            ], 403);
        }

        $data = array_map([$this, 'cleanValue'], $data);

        $user = User::findOrFail($session['user_id']);

        $userData = $data;
        unset($userData['password']);
        $user->fill($userData);

        if ($request->filled('password') && !empty(trim($request->password))) {
            $user->password = Hash::make($data['password']);
        }

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            
            $extension = $file->getClientOriginalExtension();
            if (empty($extension)) {
                $extension = 'jpg';
            }
            
            $filename = uniqid('profile_') . '_' . time() . '.' . $extension;
            $uploadPath = storage_path('app/public/profile_pics/');
            
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            $fullPath = $uploadPath . $filename;
            if (move_uploaded_file($file->getRealPath(), $fullPath)) {
                $user->profile_picture = 'profile_pics/' . $filename;
            } else {
                return response()->json(['success'=>false,'message'=>'Erreur lors de l\'upload'], 500);
            }
        }

        $user->save();

        Session::put('user', array_merge($session, [
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'email'      => $user->email,
            'roles'      => $user->roles()->pluck('role_name')->toArray()
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour'
        ]);
    }
}
