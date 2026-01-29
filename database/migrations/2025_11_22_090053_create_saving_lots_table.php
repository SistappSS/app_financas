<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saving_lots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('saving_id');
            $table->decimal('original_amount', 12, 2);
            $table->decimal('invested_amount', 12, 2);
            $table->date('created_at');             // data do aporte
            $table->date('last_principal_event');   // último evento de principal (depósito/saque/aniversário)
            $table->date('next_yield_date');        // próxima data de aniversário
            $table->date('closed_at')->nullable();  // quando a cota zerou

            $table->foreign('saving_id')
                ->references('id')->on('savings')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saving_lots');
    }

};
