<?php
namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    // 1) Liste des factures de l’utilisateur connecté
    public function index()
    {
        $userId = Session::get('user.user_id');

        if (! $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non connecté',
            ], 401);
        }

        $invoices = Invoice::where('user_id', $userId)
            ->select('invoice_id', 'invoice_number', 'issue_date', 'total_amount')
            ->orderByDesc('issue_date')
            ->get();

        return response()->json([
            'success'  => true,
            'invoices' => $invoices,
        ]);
    }

    // 2) Télécharger une facture PDF
    public function download($id)
    {
        $userId = Session::get('user.user_id');

        if (! $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non connecté',
            ], 401);
        }

        $invoice = Invoice::where('invoice_id', $id)
                          ->where('user_id', $userId)
                          ->first();

        if (! $invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Facture introuvable',
            ], 404);
        }

        if (! Storage::disk('local')->exists($invoice->pdf_path)) {
            return response()->json([
                'success' => false,
                'message' => 'Fichier introuvable',
            ], 404);
        }

        return response()->download(storage_path("app/{$invoice->pdf_path}"));
    }
}
