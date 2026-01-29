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
        Schema::create('yearly_item_recurrents', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('recurrent_id')->references('id')->on('recurrents')->onDelete('cascade');

            $table->string('payment_day');
            $table->string('reference_year');

            $table->decimal('amount', 12, 2);

            $table->boolean('status')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yearly_item_recurrents');
    }
};
