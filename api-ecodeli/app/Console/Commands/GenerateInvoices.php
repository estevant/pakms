<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;

class GenerateInvoices extends Command
{
    protected $signature = 'generate:invoices';
    protected $description = 'Génère les factures PDF pour chaque prestataire à la fin du mois';

    public function handle()
    {
        $this->info('Début de génération des factures...');

        $month = now()->subMonth()->format('Y-m'); // ex: 2024-04

        // Récupérer les prestataires distincts qui ont eu des prestations validées le mois dernier
        $prestataires = DB::table('service')
            ->join('serviceavailability', 'service.offered_service_id', '=', 'serviceavailability.offered_service_id')
            ->join('reservation', 'serviceavailability.availability_id', '=', 'reservation.availability_id')
            ->where('reservation.status', 'validée')
            ->whereBetween('serviceavailability.date', [
                now()->subMonth()->startOfMonth()->toDateString(),
                now()->subMonth()->endOfMonth()->toDateString()
            ])
            ->select('service.user_id')
            ->distinct()
            ->get();

        foreach ($prestataires as $p) {
            $user = DB::table('users')->where('user_id', $p->user_id)->first();

            $prestaDetails = DB::table('serviceavailability')
                ->join('service', 'serviceavailability.offered_service_id', '=', 'service.offered_service_id')
                ->join('servicetype', 'service.service_type_id', '=', 'servicetype.service_type_id')
                ->join('reservation', 'serviceavailability.availability_id', '=', 'reservation.availability_id')
                ->where('service.user_id', $p->user_id)
                ->where('reservation.status', 'validée')
                ->whereBetween('serviceavailability.date', [
                    now()->subMonth()->startOfMonth()->toDateString(),
                    now()->subMonth()->endOfMonth()->toDateString()
                ])
                ->select('servicetype.name as service_type_name', 'service.price', 'service.address', 'serviceavailability.date', 'serviceavailability.start_time', 'serviceavailability.end_time')
                ->get();

            $total = $prestaDetails->sum('price');

            // Générer le HTML
            $html = View::make('pdf.invoice', [
                'user' => $user,
                'month' => now()->subMonth()->format('F Y'),
                'total' => $total,
                'prestaDetails' => $prestaDetails
            ])->render();

            // Générer le PDF via PDFShift
            $response = Http::withBasicAuth('sk_bccdac063e8fdb6f49a2afa8bc2af6b9d947b9de', '')
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post('https://api.pdfshift.io/v3/convert/html', [
                    'source' => $html
                ]);

            if (!$response->successful()) {
                $this->error("Erreur pour l'utilisateur ID {$p->user_id}");
                continue;
            }

            $filename = 'facture_' . $p->user_id . '_' . $month . '.pdf';
            Storage::put('public/invoices/' . $filename, $response->body());

            // Enregistrer la facture
            DB::table('invoice')->insert([
                'user_id' => $p->user_id,
                'invoice_number' => strtoupper('INV-' . $p->user_id . '-' . str_replace('-', '', $month)),
                'month_year' => $month,
                'total_amount' => $total,
                'file_path' => 'storage/invoices/' . $filename,
                'issue_date' => now()
            ]);

            $this->info("Facture générée pour user_id {$p->user_id}");
        }

        $this->info('✅ Toutes les factures ont été générées.');
    }
}
