<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contract;
use Carbon\Carbon;

class ActivateContracts extends Command
{
    // Signature pour l’appeler : php artisan contracts:activate
    protected $signature = 'contracts:activate';
    protected $description = 'Active les contrats futur dont la date de début est celle du jour';

    public function handle()
    {
        $today = Carbon::today();

        // On passe en « active » tous les contrats future dont start_date <= aujourd’hui
        $count = Contract::where('status', 'future')
            ->whereDate('start_date', '<=', $today)
            ->update(['status' => 'active']);

        $this->info("✅ $count contrat(s) activé(s).");
    }
}
?>