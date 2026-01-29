<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
            Schema::create('transactions', function (Blueprint $table) {
                $table->uuid('id')->primary();

                $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
                $table->foreignUuid('card_id')->nullable()->constrained('cards')->onDelete('cascade');
                $table->foreignUuid('transaction_category_id')->constrained('transaction_categories')->onDelete('cascade');

                $table->string('title')->nullable();
                $table->decimal('amount', 12, 2);
                $table->date('date');

                $table->enum('type', ['pix', 'card', 'money'])->default('pix');
                $table->enum('type_card', ['credit', 'debit'])->nullable();

                $table->enum('recurrence_type', ['unique', 'monthly', 'yearly', 'custom'])->nullable();

                $table->integer('custom_occurrences')->nullable();

                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
