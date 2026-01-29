<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // FKs
            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignUuid('account_id')
                ->constrained('accounts')
                ->cascadeOnDelete(); // mantém seu desejo de deletar savings ao apagar a conta

            // Dados
            $table->string('name', 255);
            $table->decimal('current_amount', 15, 2)->default(0);   // default evita inserts sem valor
            $table->decimal('interest_rate', 7, 4)->default(0);     // ex.: 1.1000 = 1,10%
            $table->enum('rate_period', ['monthly', 'yearly'])->default('monthly');
            $table->date('start_date')->nullable();
            $table->text('notes')->nullable();

            // Meta
            $table->timestamps();

            // Índices úteis
            $table->index(['user_id', 'account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings');
    }
};
