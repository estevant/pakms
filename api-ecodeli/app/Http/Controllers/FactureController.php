<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class FactureController extends Controller
{
    public static function generateForMonth($user, $month, $total, $prestaDetails)
    {
        // Générer le HTML depuis la vue Blade
        $html = View::make('pdf.invoice', compact('user', 'month', 'total', 'prestaDetails'))->render();

        // Appel API PDFShift
        $response = Http::withBasicAuth('sk_bccdac063e8fdb6f49a2afa8bc2af6b9d947b9de', '')
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post('https://api.pdfshift.io/v3/convert/html', [
                'source' => $html,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Erreur PDF: ' . $response->body());
        }

        $filename = 'facture_' . $user->user_id . '_' . now()->format('Ym') . '.pdf';
        Storage::put('public/invoices/' . $filename, $response->body());

        return 'storage/invoices/' . $filename;
    }
}
