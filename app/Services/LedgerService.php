<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LedgerService
{
    public function recordAccount(
        string $userId, string $accountId, string $type,
        float $amount, \DateTimeInterface $when, ?string $desc = null,
        array $links = [], ?string $transferGroupId = null, ?float $balanceAfter = null
    ): string {
        $id = (string) Str::uuid();
        DB::table('account_movements')->insert(array_filter([
            'id' => $id,
            'user_id' => $userId,
            'account_id' => $accountId,
            'occurred_at' => $when,
            'amount' => round($amount, 2), // + crédito / - débito
            'type' => $type,
            'description' => $desc,
            'transaction_id' => $links['transaction_id'] ?? null,
            'payment_transaction_id' => $links['payment_transaction_id'] ?? null,
            'invoice_id' => $links['invoice_id'] ?? null,
            'saving_id' => $links['saving_id'] ?? null,
            'transfer_group_id' => $transferGroupId,
            'balance_after' => $balanceAfter,
            'created_at' => now(), 'updated_at' => now(),
        ]));
        return $id;
    }

    public function depositToSaving(
        string $userId, string $savingId, string $fromAccountId, float $amount,
        \DateTimeInterface $when, ?string $desc = null
    ): void {
        $g = (string) Str::uuid(); // parear
        // 1) debita a conta
        $this->recordAccount($userId, $fromAccountId, 'saving_out', -abs($amount), $when, $desc, ['saving_id'=>$savingId], $g);
        // 2) credita o cofrinho
        DB::table('account_saving_movements')->insert([
            'id'=>(string)Str::uuid(),'user_id'=>$userId,'saving_id'=>$savingId,'account_id'=>$fromAccountId,
            'occurred_at'=>$when,'amount'=>abs($amount),'type'=>'deposit','description'=>$desc,
            'transfer_group_id'=>$g,'created_at'=>now(),'updated_at'=>now(),
        ]);
        // 3) opcional: atualiza campos “espelho”
        DB::table('savings')->where('id',$savingId)->increment('current_amount', abs($amount));
        DB::table('accounts')->where('id',$fromAccountId)->decrement('current_balance', abs($amount));
    }

    public function withdrawFromSaving(
        string $userId, string $savingId, string $toAccountId, float $amount,
        \DateTimeInterface $when, ?string $desc = null
    ): void {
        $g = (string) Str::uuid();
        // 1) debita o cofrinho
        DB::table('account_saving_movements')->insert([
            'id'=>(string)Str::uuid(),'user_id'=>$userId,'saving_id'=>$savingId,'account_id'=>$toAccountId,
            'occurred_at'=>$when,'amount'=>-abs($amount),'type'=>'withdraw','description'=>$desc,
            'transfer_group_id'=>$g,'created_at'=>now(),'updated_at'=>now(),
        ]);
        // 2) credita a conta
        $this->recordAccount($userId, $toAccountId, 'saving_in', abs($amount), $when, $desc, ['saving_id'=>$savingId], $g);
        // 3) atualiza “espelhos”
        DB::table('savings')->where('id',$savingId)->decrement('current_amount', abs($amount));
        DB::table('accounts')->where('id',$toAccountId)->increment('current_balance', abs($amount));
    }

    public function transferBetweenAccounts(
        string $userId,
        string $fromAccountId,
        string $toAccountId,
        float $amount,
        \DateTimeInterface $when,
        ?string $desc = null
    ): void {
        $g = (string) \Illuminate\Support\Str::uuid();
        $val = abs($amount);

        // saída na origem
        $this->recordAccount($userId, $fromAccountId, 'transfer_out', -$val, $when, $desc, [], $g);
        // entrada no destino
        $this->recordAccount($userId, $toAccountId, 'transfer_in',  $val, $when, $desc, [], $g);

        // espelho opcional nos saldos atuais (se você mantém)
        DB::table('accounts')->where('id',$fromAccountId)->decrement('current_balance', $val);
        DB::table('accounts')->where('id',$toAccountId)->increment('current_balance', $val);
    }
}
