<?php

namespace App\Jobs;

use App\Models\Auth\User;
use App\Notifications\DailyDigestPush;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDailyDigestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $tz = 'America/Sao_Paulo';
        $today = now($tz)->startOfDay();
        $tomorrow = (clone $today)->addDay();

        // manda apenas para quem tem push subscription
        User::query()
            ->whereHas('pushSubscriptions')
            ->chunkById(100, function ($users) use ($today, $tomorrow) {
                foreach ($users as $user) {
                    // === base de consultas ===
                    $txBase = $user->transactions()->with('transactionCategory');

                    // HOJE
                    $todayTx = (clone $txBase)->whereDate('date', $today->toDateString());
                    $todayIn  = (clone $todayTx)->whereHas('transactionCategory', fn($q)=>$q->where('type','entrada'))->count();
                    $todayOut = (clone $todayTx)->whereHas('transactionCategory', fn($q)=>$q->where('type','despesa'))->count();

                    // investimentos HOJE (duas opções)
                    $todayInv = (clone $todayTx)
                        ->whereHas('transactionCategory', fn($q)=>$q->where('type','investimento')) // ← op. via categoria
                        ->count();
                    // Se tiver model próprio de Investment com coluna 'date', descomente e comente a linha acima:
                    // $todayInv = class_exists(\App\Models\Investment::class)
                    //     ? $user->investments()->whereDate('date', $today->toDateString())->count()
                    //     : $todayInv;

                    // AMANHÃ
                    $tomTx = (clone $txBase)->whereDate('date', $tomorrow->toDateString());
                    $tomIn  = (clone $tomTx)->whereHas('transactionCategory', fn($q)=>$q->where('type','entrada'))->count();
                    $tomOut = (clone $tomTx)->whereHas('transactionCategory', fn($q)=>$q->where('type','despesa'))->count();
                    $tomInv = (clone $tomTx)->whereHas('transactionCategory', fn($q)=>$q->where('type','investimento'))->count();
                    // // Versão Investment model:
                    // $tomInv = class_exists(\App\Models\Investment::class)
                    //     ? $user->investments()->whereDate('date', $tomorrow->toDateString())->count()
                    //     : $tomInv;

                    $body = sprintf(
                        "%d entradas, %d saídas, %d investimentos",
                        $todayIn, $todayOut, $todayInv
                    );

                    $user->notify(new DailyDigestPush(
                        'Lançamentos do dia',
                        $body,
                        url('/lancamentos-do-dia')
                    ));
                }
            });
    }
}
