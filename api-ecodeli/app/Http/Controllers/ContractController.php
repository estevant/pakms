<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ContractController extends Controller
{
    /**
     * 1) Liste de tous les contrats du commerçant
     */
    public function index()
    {
        $userId = Session::get('user.user_id');
        $contracts = Contract::where('user_id', $userId)->get();

        return response()->json([
            'success'   => true,
            'contracts' => $contracts,
        ]);
    }

    /**
     * 2) Détail d’un contrat
     */
    public function show($id)
    {
        $userId = Session::get('user.user_id');
        $c = Contract::where('contract_id', $id)
                     ->where('user_id', $userId)
                     ->first();

        if (! $c) {
            return response()->json([
                'success' => false,
                'message' => 'Contrat introuvable',
            ], 404);
        }

        return response()->json([
            'success'  => true,
            'contract' => $c,
        ]);
    }

    /**
     * 3) Soumission d’une nouvelle demande
     */
    public function store(Request $req)
    {
        $userId = Session::get('user.user_id');

        // 1) On vérifie qu'il n'existe pas déjà
        $hasOpen = Contract::where('user_id', $userId)
            ->whereIn('status', ['pending','future'])
            ->exists();

        if ($hasOpen) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà une demande de contrat en cours ou un contrat actif.',
            ], 422);
        }

        // 2) Validation
        $data = $req->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'terms'      => 'nullable|string',
            'pdf'        => 'nullable|file|mimes:pdf|max:5120',
        ]);

        // 3) Stockage du PDF si fourni
        if ($req->hasFile('pdf')) {
            $file = $req->file('pdf');
            if ($file->isValid()) {
                $extension = $file->getClientOriginalExtension();
                if (empty($extension)) {
                    $extension = 'pdf';
                }
                
                $filename = uniqid('contract_') . '_' . time() . '.' . $extension;
                $uploadPath = storage_path('app/public/contracts/');
                
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                $fullPath = $uploadPath . $filename;
                if (move_uploaded_file($file->getRealPath(), $fullPath)) {
                    $data['pdf_path'] = 'contracts/' . $filename;
                }
            }
        }

        // 4) Complétion des champs
        $data['user_id'] = $userId;
        $data['status']  = 'pending';

        // 5) Création
        $contract = Contract::create($data);

        return response()->json([
            'success'  => true,
            'contract' => $contract,
        ], 201);
    }

    /**
     * 4) Mise à jour d’une demande (avant validation admin)
     */
    public function update(Request $req, $id)
    {
        $userId = Session::get('user.user_id');
        $c = Contract::where('contract_id', $id)
                     ->where('user_id', $userId)
                     ->first();

        if (! $c) {
            return response()->json([
                'success' => false,
                'message' => 'Contrat introuvable',
            ], 404);
        }

        $data = $req->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'terms'      => 'nullable|string',
            'pdf'        => 'nullable|file|mimes:pdf|max:5120',
        ]);

        if ($req->hasFile('pdf')) {
            if ($c->pdf_path) {
                $filePath = storage_path("app/public/{$c->pdf_path}");
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            $file = $req->file('pdf');
            if ($file->isValid()) {
                $extension = $file->getClientOriginalExtension();
                if (empty($extension)) {
                    $extension = 'pdf';
                }
                
                $filename = uniqid('contract_') . '_' . time() . '.' . $extension;
                $uploadPath = storage_path('app/public/contracts/');
                
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                $fullPath = $uploadPath . $filename;
                if (move_uploaded_file($file->getRealPath(), $fullPath)) {
                    $c->pdf_path = 'contracts/' . $filename;
                }
            }
        }

        $c->start_date = $data['start_date'];
        $c->end_date   = $data['end_date'];
        $c->terms      = $data['terms'] ?? null;
        $c->save();

        return response()->json([
            'success'  => true,
            'contract' => $c,
        ]);
    }
}
