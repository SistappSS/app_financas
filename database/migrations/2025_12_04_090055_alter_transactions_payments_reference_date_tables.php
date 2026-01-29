<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // migration
Schema::table('payment_transactions', function (\Illuminate\Database\Schema\Blueprint $table) {
    $table->date('reference_date')->nullable()->after('payment_date');
});
        
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            
        });
    }

};
