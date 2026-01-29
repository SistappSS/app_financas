<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saving_lot_pending_yields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lot_id');
            $table->uuid('saving_id');
            $table->decimal('base_amount', 12, 2);   // principal considerado
            $table->integer('days_invested');        // quantos dias ficou aplicado
            $table->decimal('yield_amount', 12, 2);  // rendimento calculado
            $table->date('credit_date');             // quando deve ser creditado (aniversário)
            $table->timestamp('credited_at')->nullable();

            $table->foreign('lot_id')
                ->references('id')->on('saving_lots')
                ->onDelete('cascade');

            $table->foreign('saving_id')
                ->references('id')->on('savings')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saving_lot_pending_yields');
    }

};
