<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('support_request_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('support_request_id')
                ->constrained('support_requests')
                ->cascadeOnDelete();

            $table->string('path');          // caminho no storage
            $table->string('original_name'); // nome original do arquivo
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('size')->nullable(); // em KB, se quiser

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_request_attachments');
    }
};
