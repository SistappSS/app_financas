<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LedgerBackfillPayments extends Command
{
    protected $signature = 'ledger:backfill-payments {--user=} {--from=} {--to=}';
    protected $description = 'Gera account_movements a partir de payment_transactions inexistentes no livro-razão';

    public function handle()
    {
        $user = $this->option('user'); $from = $this->option('from'); $to = $this->option('to');

        $q = DB::table('payment_transactions as pt')
            ->join('transactions as t','t.id','=','pt.transaction_id')
            ->leftJoin('transaction_categories as tc','tc.id','=','t.transaction_category_id')
            ->when($user, fn($qq)=>$qq->where('t.user_id',$user))
            ->when($from, fn($qq)=>$qq->where('pt.payment_date','>=',$from))
            ->when($to,   fn($qq)=>$qq->where('pt.payment_date','<=',$to))
            ->selectRaw('pt.id as pid, pt.payment_date, pt.amount as pamount, t.id as tid, t.user_id, tc.type as cat_type, t.account_id');

        $rows = $q->get();

        foreach ($rows as $r) {
            $exists = DB::table('account_movements')->where('payment_transaction_id',$r->pid)->exists();
            if ($exists) continue;

            // escolhe conta: t.account_id ou a primeira conta do usuário
            $accId = $r->account_id ?: DB::table('accounts')
                ->where('user_id',$r->user_id)
                ->orderBy('created_at')->value('id');

            if (!$accId) continue;

            $amount = (float)$r->pamount;
            $amount = ($r->cat_type === 'entrada') ? abs($amount) : -abs($amount);

            DB::table('account_movements')->insert([
                'id' => (string) Str::uuid(),
                'user_id' => $r->user_id,
                'account_id' => $accId,
                'occurred_at' => $r->payment_date,
                'amount' => round($amount,2),
                'type' => 'transaction_payment',
                'description' => null,
                'transaction_id' => $r->tid,
                'payment_transaction_id' => $r->pid,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $this->info('Backfill concluído.');
    }
}
