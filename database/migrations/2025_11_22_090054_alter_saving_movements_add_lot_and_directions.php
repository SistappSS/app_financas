<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saving_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('saving_movements', 'lot_id')) {
                $table->uuid('lot_id')->nullable()->after('saving_id');

                $table->foreign('lot_id')
                    ->references('id')->on('saving_lots')
                    ->nullOnDelete();
            }
        });

        // alterar enum
        DB::statement("
            ALTER TABLE saving_movements
            MODIFY COLUMN direction ENUM('deposit','withdraw','earning','fee')
            NOT NULL DEFAULT 'deposit'
        ");
    }

    public function down(): void
    {
        Schema::table('saving_movements', function (Blueprint $table) {
            if (Schema::hasColumn('saving_movements', 'lot_id')) {
                $table->dropForeign(['lot_id']);
                $table->dropColumn('lot_id');
            }
        });

        // voltar enum original
        DB::statement("
            ALTER TABLE saving_movements
            MODIFY COLUMN direction ENUM('deposit','withdraw')
            NOT NULL DEFAULT 'deposit'
        ");
    }

};
