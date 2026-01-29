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
        Schema::table('recurrents', function (Blueprint $table) {
            $table->boolean('alternate_cards')->default(false)->after('active');
            $table->uuid('last_used_card_id')->nullable()->after('alternate_cards');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
