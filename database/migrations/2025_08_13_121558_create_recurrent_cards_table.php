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
        Schema::create('recurrent_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('recurrent_id')->constrained('recurrents')->onDelete('cascade');
            $table->foreignUuid('card_id')->constrained('cards')->onDelete('cascade');

            $table->unsignedSmallInteger('position')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurrent_cards');
    }
};
