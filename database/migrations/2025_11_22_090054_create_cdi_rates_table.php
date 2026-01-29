<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cdi_rates', function (Blueprint $table) {
            $table->date('date')->primary();
            $table->decimal('annual_rate', 8, 6); // ex: 0.108000 (10,80% a.a.)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cdi_rates');
    }

};
