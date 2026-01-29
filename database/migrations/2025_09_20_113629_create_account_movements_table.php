<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('account_movements', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('user_id');
            $t->uuid('account_id');
            $t->timestamp('occurred_at');                 // quando entrou/saiu
            $t->decimal('amount', 15, 2);                 // + crédito / - débito
            $t->enum('type', [
                'opening',            // foto inicial (semente)
                'deposit',            // entrada manual
                'withdraw',           // saída manual
                'transfer_in',
                'transfer_out',
                'fee',                // tarifa
                'interest',           // juros/ rendimento
                'correction',         // ajuste
                'saving_in',          // cofrinho → volta p/ conta
                'saving_out',         // conta → cofrinho
                'invoice_payment',    // pagamento de fatura
                'transaction_payment' // pagamento/recebimento de transação
            ]);
            $t->string('description', 255)->nullable();

            // vínculos (previnem duplicidade e ajudam a auditar)
            $t->uuid('transaction_id')->nullable();
            $t->uuid('payment_transaction_id')->nullable();
            $t->uuid('invoice_id')->nullable();
            $t->uuid('saving_id')->nullable();

            // para parear transferências (mesmo id nos dois lados)
            $t->uuid('transfer_group_id')->nullable();

            // opcional, facilita auditoria visual (não é obrigatório manter)
            $t->decimal('balance_after', 15, 2)->nullable();

            $t->timestamps();

            $t->index(['user_id', 'account_id', 'occurred_at']);
            $t->index(['transfer_group_id']);
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $t->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('account_movements'); }
};
