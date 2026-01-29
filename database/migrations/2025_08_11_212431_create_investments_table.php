<?php

// database/migrations/2025_08_11_000000_create_investments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');

            $table->string('name', 255);
            // valor de compra em R$
            $table->decimal('purchase_value', 15, 2);

            // taxa de juros em % (ex.: 1.10 = 1,10%)
            $table->decimal('interest_rate', 7, 4)->default(0); // percentuais com 4 casas
            // período da taxa: monthly | yearly
            $table->enum('rate_period', ['monthly', 'yearly'])->default('monthly');

            // opcional: quando começou a render (para projeções futuras, se quiser usar)
            $table->date('start_date')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
