<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->uuid('recurrent_id')->nullable()->after('transaction_id');
            $table->foreign('recurrent_id')->references('id')->on('recurrents')->onDelete('cascade');

            $table->boolean('is_projection')->default(false)->after('current_installment');
            $table->index(['recurrent_id','is_projection']);
        });
    }
    public function down(): void {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropIndex(['recurrent_id','is_projection']);
            $table->dropForeign(['recurrent_id']);
            $table->dropColumn(['recurrent_id','is_projection']);
        });
    }
};
