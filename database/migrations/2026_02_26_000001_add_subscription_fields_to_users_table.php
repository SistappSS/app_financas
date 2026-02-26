<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('trial_ends_at')->nullable()->after('email_verified_at');
            $table->timestamp('subscription_expires_at')->nullable()->after('trial_ends_at');
            $table->string('asaas_customer_id')->nullable()->after('subscription_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['trial_ends_at', 'subscription_expires_at', 'asaas_customer_id']);
        });
    }
};
