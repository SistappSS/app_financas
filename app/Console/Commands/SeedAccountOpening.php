<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Account;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SeedAccountOpening extends Command {
    protected $signature = 'ledger:seed-opening {userId?}';
    protected $description = 'Cria movimento opening por conta, se não existir';

    public function handle() {
        $q = Account::query();
        if ($uid = $this->argument('userId')) $q->where('user_id', $uid);

        $q->get(['id','user_id','current_balance','created_at'])->each(function($acc){
            $has = DB::table('account_movements')
                ->where('account_id', $acc->id)->where('type','opening')->exists();
            if ($has) return;

            DB::table('account_movements')->insert([
                'id' => (string) Str::uuid(),
                'user_id' => $acc->user_id,
                'account_id' => $acc->id,
                'occurred_at' => $acc->created_at ?? now(),
                'amount' => (float) $acc->current_balance,   // “foto” inicial
                'type' => 'opening',
                'description' => 'Saldo inicial cadastrado',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->info("Opening → {$acc->id}");
        });
    }
}
