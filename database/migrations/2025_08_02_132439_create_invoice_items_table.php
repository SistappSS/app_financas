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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('transaction_category_id')->references('id')->on('transaction_categories')->onDelete('cascade');

            $table->string('title')->nullable();

            $table->decimal('amount', 12, 2);

            $table->date('date');

            $table->unsignedTinyInteger('installments')->default(1);
            $table->unsignedTinyInteger('current_installment')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
