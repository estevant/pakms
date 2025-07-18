<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contract;
use Carbon\Carbon;

class ExpireContracts extends Command
{
    protected $signature   = 'contracts:expire';
    protected $description = 'Passe en statut expired tous les contrats dépassant leur end_date';

    public function handle()
    {
        $today = Carbon::today();
        $count = Contract::where('status', 'active')
            ->whereDate('end_date', '<', $today)
            ->update(['status' => 'expired']);

        $this->info("✅ $count contrat(s) expiré(s).");
    }
}
?>