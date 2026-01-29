<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('account_saving_movements', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('user_id');
            $t->uuid('saving_id');
            $t->uuid('account_id')->nullable(); // conta origem/destino do fluxo
            $t->timestamp('occurred_at');
            $t->decimal('amount', 15, 2); // + aumenta cofrinho / - reduz cofrinho
            $t->enum('type', ['deposit', 'withdraw', 'interest', 'correction']);
            $t->string('description', 255)->nullable();
            $t->uuid('transfer_group_id')->nullable(); // pareado com account_movements
            $t->timestamps();

            $t->index(['user_id', 'saving_id', 'occurred_at']);
            $t->index(['transfer_group_id']);
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $t->foreign('saving_id')->references('id')->on('savings')->cascadeOnDelete();
            $t->foreign('account_id')->references('id')->on('accounts')->nullOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('saving_movements'); }
};
