<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Saving;
use App\Models\SavingLot;
use App\Models\SavingLotPendingYield;
use App\Models\SavingMovement;
use App\Models\AccountMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvestmentService
{
    public function __construct(
        protected CdiService $cdiService
    ) {}

    private function nextAnniversary(Carbon $createdAt): string
    {
        return $createdAt->copy()->addMonthNoOverflow()->toDateString();
    }

    /**
     * DEPÓSITO NO COFRINHO
     * - Debita a conta vinculada
     * - Cria movimento em account_movements (saving_out)
     * - Cria lote e movement em savings
     */
    public function deposit(Saving $saving, float $amount, Carbon $date, ?Account $account = null, ?string $notes = null): SavingLot
    {
        return DB::transaction(function () use ($saving, $amount, $date, $account, $notes) {

            // Sempre resolve a conta vinculada ao cofrinho
            if (!$account) {
                if (!$saving->account_id) {
                    throw ValidationException::withMessages([
                        'account_id' => 'Este cofrinho não possui uma conta vinculada.',
                    ]);
                }

                $account = Account::lockForUpdate()->find($saving->account_id);
            }

            if (!$account) {
                throw ValidationException::withMessages([
                    'account_id' => 'Conta vinculada não encontrada.',
                ]);
            }

            if ($account->user_id !== $saving->user_id) {
                throw ValidationException::withMessages([
                    'account_id' => 'Conta inválida para este cofrinho.',
                ]);
            }

            if ($account->current_balance < $amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Saldo insuficiente na conta vinculada.',
                ]);
            }

            // Debita a conta
            $account->current_balance -= $amount;
            $account->save();

            // Movimento de conta: dinheiro saiu da conta pro cofrinho
            AccountMovement::create([
                'user_id'          => $account->user_id,
                'account_id'       => $account->id,
                'occurred_at'      => $date,
                'amount'           => $amount,
                'type'             => 'saving_out', // saiu da conta
                'description'      => $notes ?? "Transferência para cofrinho {$saving->name}",
                'transaction_id'   => null,
                'payment_transaction_id' => null,
                'invoice_id'       => null,
                'saving_id'        => $saving->id,
                'transfer_group_id'=> null,
                'balance_after'    => $account->current_balance,
            ]);

            // Cria lote
            $lot = SavingLot::create([
                'saving_id'            => $saving->id,
                'original_amount'      => $amount,
                'invested_amount'      => $amount,
                'created_at'           => $date->toDateString(),
                'last_principal_event' => $date->toDateString(),
                'next_yield_date'      => $this->nextAnniversary($date),
            ]);

            // Movimento do saving
            SavingMovement::create([
                'user_id'    => $saving->user_id,
                'saving_id'  => $saving->id,
                'lot_id'     => $lot->id,
                'account_id' => $account->id,
                'direction'  => 'deposit',
                'amount'     => $amount,
                'date'       => $date->toDateString(),
                'notes'      => $notes ?? 'Aporte',
            ]);

            // Atualiza saldo do cofrinho
            $saving->current_amount += $amount;
            $saving->save();

            return $lot;
        });
    }

    /**
     * RESGATE DO COFRINHO
     * - Debita o cofrinho
     * - Credita a conta vinculada
     * - Cria movimento em account_movements (saving_in)
     * - Atualiza lotes e movements
     */
    public function withdraw(Saving $saving, float $amount, Carbon $date, ?Account $account = null, ?string $notes = null): void
    {
        DB::transaction(function () use ($saving, $amount, $date, $account, $notes) {

            if ($saving->current_amount < $amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Saldo insuficiente no cofrinho.',
                ]);
            }

            if (!$account) {
                if (!$saving->account_id) {
                    throw ValidationException::withMessages([
                        'account_id' => 'Este cofrinho não possui uma conta vinculada.',
                    ]);
                }

                $account = Account::lockForUpdate()->find($saving->account_id);
            }

            if (!$account) {
                throw ValidationException::withMessages([
                    'account_id' => 'Conta vinculada não encontrada.',
                ]);
            }

            if ($account->user_id !== $saving->user_id) {
                throw ValidationException::withMessages([
                    'account_id' => 'Conta inválida para este cofrinho.',
                ]);
            }

            // Credita a conta
            $account->current_balance += $amount;
            $account->save();

            // Movimento de conta: dinheiro entrou vindo do cofrinho
            AccountMovement::create([
                'user_id'          => $account->user_id,
                'account_id'       => $account->id,
                'occurred_at'      => $date,
                'amount'           => $amount,
                'type'             => 'saving_in', // entrou na conta
                'description'      => $notes ?? "Resgate do cofrinho {$saving->name}",
                'transaction_id'   => null,
                'payment_transaction_id' => null,
                'invoice_id'       => null,
                'saving_id'        => $saving->id,
                'transfer_group_id'=> null,
                'balance_after'    => $account->current_balance,
            ]);

            $remaining = $amount;

            $lots = SavingLot::open()
                ->where('saving_id', $saving->id)
                ->orderBy('created_at')
                ->lockForUpdate()
                ->get();

            foreach ($lots as $lot) {
                if ($remaining <= 0) break;

                $consumed = min($lot->invested_amount, $remaining);

                $days = Carbon::parse($lot->last_principal_event)->diffInDays($date);

                if ($days > 0 && $consumed > 0) {
                    $dailyRate = $this->cdiService->dailyRate($date);
                    $effective = $dailyRate * ($saving->cdi_percent ?? 1.0);

                    $yieldAmount = round($consumed * $effective * $days, 2);

                    if ($yieldAmount > 0) {
                        SavingLotPendingYield::create([
                            'lot_id'        => $lot->id,
                            'saving_id'     => $saving->id,
                            'base_amount'   => $consumed,
                            'days_invested' => $days,
                            'yield_amount'  => $yieldAmount,
                            'credit_date'   => $lot->next_yield_date,
                        ]);
                    }
                }

                $lot->invested_amount -= $consumed;
                $lot->last_principal_event = $date->toDateString();

                if ($lot->invested_amount <= 0) {
                    $lot->invested_amount = 0;
                    $lot->closed_at = $date->toDateString();
                }

                $lot->save();

                SavingMovement::create([
                    'user_id'    => $saving->user_id,
                    'saving_id'  => $saving->id,
                    'lot_id'     => $lot->id,
                    'account_id' => $account->id,
                    'direction'  => 'withdraw',
                    'amount'     => $consumed,
                    'date'       => $date->toDateString(),
                    'notes'      => $notes ?? 'Saque',
                ]);

                $remaining -= $consumed;
            }

            $saving->current_amount -= $amount;
            $saving->save();
        });
    }

    public function processAnniversaries(Carbon $date): void
    {
        DB::transaction(function () use ($date) {
            $lots = SavingLot::whereDate('next_yield_date', $date->toDateString())
                ->lockForUpdate()
                ->get();

            foreach ($lots as $lot) {
                $saving = $lot->saving;

                $daysPrincipal = Carbon::parse($lot->last_principal_event)->diffInDays($date);
                $yieldPrincipal = 0.0;

                if ($daysPrincipal > 0 && $lot->invested_amount > 0) {
                    $dailyRate = $this->cdiService->dailyRate($date);
                    $effective = $dailyRate * ($saving->cdi_percent ?? 1.0);
                    $yieldPrincipal = round($lot->invested_amount * $effective * $daysPrincipal, 2);
                }

                $pending = SavingLotPendingYield::where('lot_id', $lot->id)
                    ->whereDate('credit_date', $date->toDateString())
                    ->whereNull('credited_at')
                    ->lockForUpdate()
                    ->get();

                $yieldPending = $pending->sum('yield_amount');

                $totalYield = $yieldPrincipal + $yieldPending;

                if ($totalYield > 0) {
                    SavingMovement::create([
                        'user_id'   => $saving->user_id,
                        'saving_id' => $saving->id,
                        'lot_id'    => $lot->id,
                        'direction' => 'earning',
                        'amount'    => $totalYield,
                        'date'      => $date->toDateString(),
                        'notes'     => 'Rendimento de aniversário',
                    ]);

                    $saving->current_amount += $totalYield;
                    $saving->save();
                }

                foreach ($pending as $p) {
                    $p->credited_at = now();
                    $p->save();
                }

                $lot->last_principal_event = $date->toDateString();
                $lot->next_yield_date = Carbon::parse($lot->next_yield_date)
                    ->addMonthNoOverflow()
                    ->toDateString();
                $lot->save();
            }
        });
    }
}
