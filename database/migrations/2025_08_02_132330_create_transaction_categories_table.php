<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('transaction_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name'); // Ex: Salário, Aluguel, Mercado, etc.
            $table->enum('type', ['entrada', 'despesa', 'investimento']);
            $table->decimal('monthly_limit', 12, 2)->nullable();
            $table->string('color')->default('#18dec7');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('transaction_categories');
    }
};
