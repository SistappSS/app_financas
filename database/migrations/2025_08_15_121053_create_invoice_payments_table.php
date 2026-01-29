<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->decimal('amount', 12, 2);
            $table->dateTime('paid_at');

            $table->string('method', 20)->nullable();   // pix, ted, boleto, etc.
            $table->string('reference', 100)->nullable(); // id externo / obs

            $table->timestamps();

            // só 1 pagamento por fatura (pagamento total)
            $table->unique('invoice_id');
        });

        // garante 1 fatura por cartão/mês
        Schema::table('invoices', function (Blueprint $table) {
            $table->unique(['card_id','current_month']);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['card_id','current_month']);
        });
        Schema::dropIfExists('invoice_payments');
    }
};
