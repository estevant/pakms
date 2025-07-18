<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class PaiementController extends Controller
{
    /**
     * Télécharge la facture au format PDF pour un mois donné.
     */
    public function downloadInvoice(int $year, int $month)
    {
        $user = Session::get('user');

        if (!$user) {
            return response()->json(['error' => 'Non connecté'], 401);
        }

        // Prestations validées du mois ciblé
        $prestations = DB::table('serviceavailability')
            ->join('service', 'serviceavailability.offered_service_id', '=', 'service.offered_service_id')
            ->join('reservation', 'serviceavailability.availability_id', '=', 'reservation.availability_id')
            ->where('service.user_id', $user['user_id'])
            ->where('reservation.status', 'validée')
            ->whereYear('serviceavailability.date', $year)
            ->whereMonth('serviceavailability.date', $month)
            ->select([
                'serviceavailability.date',
                'service.details',
                'service.price',
            ])
            ->orderBy('serviceavailability.date')
            ->get();

        $total = $prestations->sum('price');

        // Vue Blade générant le HTML de la facture
        $html = view('factures.pdfshift', [
            'prestataire' => $user,
            'mois'        => $month,
            'annee'       => $year,
            'prestations' => $prestations,
            'total'       => $total,
        ])->render();

        // Appel à PDFShift
        $response = Http::withHeaders([
                // Stockez la clé dans .env : PDFSHIFT_SECRET=sk_xxx
                'Authorization' => 'Bearer '.config('services.pdfshift.secret'),
                'Content-Type'  => 'application/json',
            ])
            ->post('https://api.pdfshift.io/v3/convert/html', [
                'source' => $html,
            ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Erreur lors de la génération du PDF'], 500);
        }

        return response($response->body(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename=facture_'.$month.'_'.$year.'.pdf',
        ]);
    }
}