<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('recurrents', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('amount');
            $table->enum('interval_unit', ['days','months','years'])->default('months')->after('start_date');
            $table->unsignedSmallInteger('interval_value')->default(1)->after('interval_unit'); // ex.: 7,15,30...
            $table->boolean('include_sat')->default(true)->after('interval_value');
            $table->boolean('include_sun')->default(true)->after('include_sat');
            $table->date('next_run_date')->nullable()->after('include_sun');
            $table->boolean('active')->default(true)->after('next_run_date');

            // índices úteis
            $table->index(['user_id','active']);
            $table->index('next_run_date');
        });

        // backfill simples: start_date = data da transação “modelo”
        DB::statement("
            UPDATE recurrents r
            JOIN transactions t ON t.id = r.transaction_id
            SET r.start_date = t.date
            WHERE r.start_date IS NULL
        ");

        // se quiser: inicializar next_run_date = start_date
        DB::statement("
            UPDATE recurrents
            SET next_run_date = start_date
            WHERE next_run_date IS NULL
        ");
    }

    public function down(): void {
        Schema::table('recurrents', function (Blueprint $table) {
            $table->dropIndex(['user_id','active']);
            $table->dropIndex(['next_run_date']);
            $table->dropColumn(['start_date','interval_unit','interval_value','include_sat','include_sun','next_run_date','active']);
        });
    }
};
