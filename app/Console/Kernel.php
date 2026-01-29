<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Os comandos Artisan customizados da sua aplicação.
     *
     * @var array
     */
    protected $commands = [
        // registre aqui os comandos criados:
        \App\Console\Commands\ProcessSavingsAnniversaries::class,
    ];

    /**
     * Define a programação das tarefas.
     */
    protected function schedule(Schedule $schedule)
    {

        /*
        |--------------------------------------------------------------------------
        | PROCESSAR ANIVERSÁRIOS DOS INVESTIMENTOS
        |--------------------------------------------------------------------------
        |
        | Este comando verifica todos os investimentos e calcula:
        | - rendimentos mensais das cotas ainda vivas
        | - rendimentos proporcionais agendados
        | - rolagem da data de aniversário para o próximo mês
        |
        */

        $schedule->command('savings:process-anniversaries')
            ->dailyAt('08:00')                // horário recomendado
            ->withoutOverlapping()            // impede rodar 2 vezes
            ->runInBackground();              // roda em paralelo (opcional)

        $schedule->command('cdi:update')
            ->dailyAt('08:00')       // horário que você quiser
            ->withoutOverlapping()
            ->onOneServer();
        /*
        |--------------------------------------------------------------------------
        | OUTROS EXEMPLOS (caso precise no futuro)
        |--------------------------------------------------------------------------
        |
        | $schedule->command('emails:send')->hourly();
        | $schedule->command('cleanup:temp')->dailyAt('02:00');
        | $schedule->call(fn() => Log::info('Teste'))->everyMinute();
        |
        */


    }

    /**
     * Registra os comandos para a aplicação.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
