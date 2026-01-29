<?php

namespace App\Jobs;

use App\Models\Auth\User;
use App\Notifications\EveningReminderPush;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SendEveningReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected function ptCount(int $n, string $sing, string $plur): string
    {
        return $n === 1 ? "1 {$sing}" : "{$n} {$plur}";
    }

    public function handle(): void
    {
        $tz = 'America/Sao_Paulo';
        $today = now($tz)->startOfDay();

        User::query()
            ->whereHas('pushSubscriptions')
            ->chunkById(100, function ($users) use ($today) {
                foreach ($users as $user) {

                    // IDs de transações de HOJE que AINDA NÃO têm pagamento vinculado
                    $pendingIds = $user->transactions()
                        ->select('transactions.id')
                        ->whereDate('transactions.date', $today->toDateString())
                        ->leftJoin('payment_transactions as pt', 'pt.transaction_id', '=', 'transactions.id')
                        ->whereNull('pt.id')             // ainda sem pagamento (qualquer data)
                        ->pluck('transactions.id');

                    if ($pendingIds->isEmpty()) {
                        // Nada pendente → não envia
                        continue;
                    }

                    // Contagens por tipo de categoria
                    $base = $user->transactions()->whereIn('id', $pendingIds);

                    $pIn  = (clone $base)->whereHas('transactionCategory', fn($q)=>$q->where('type','entrada'))->count();
                    $pOut = (clone $base)->whereHas('transactionCategory', fn($q)=>$q->where('type','despesa'))->count();
                    $pInv = (clone $base)->whereHas('transactionCategory', fn($q)=>$q->where('type','investimento'))->count();

                    // (Opcional) Incluir faturas do dia NÃO pagas:
                    // $pendingInvoices = 0;
                    // if (class_exists(\App\Models\Invoice::class)) {
                    //     $pendingInvoices = \App\Models\Invoice::query()
                    //         ->where('user_id', $user->id)
                    //         ->whereDate('due_date', $today->toDateString())   // ajuste se o campo for outro
                    //         ->leftJoin('invoice_payments as ip', 'ip.invoice_id', '=', 'invoices.id')
                    //         ->whereNull('ip.id')
                    //         ->count();
                    // }

                    if (($pIn + $pOut + $pInv) === 0) {
                        continue;
                    }

                    $partes = [];
                    if ($pIn  > 0) $partes[] = $this->ptCount($pIn,  'entrada',      'entradas');
                    if ($pOut > 0) $partes[] = $this->ptCount($pOut, 'saída',        'saídas');
                    if ($pInv > 0) $partes[] = $this->ptCount($pInv, 'investimento', 'investimentos');
                    // if ($pendingInvoices > 0) $partes[] = $this->ptCount($pendingInvoices, 'fatura', 'faturas');

                    $body = 'Ainda há ' . implode(', ', $partes) . ' para hoje.';

                    $user->notify(new EveningReminderPush(
                        title: 'Não se esqueça',
                        body:  $body,
                        url:   url('/lancamentos-do-dia')
                    ));
                }
            });
    }
}
