<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('gemini');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('source_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('result_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->text('prompt')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_meta')->nullable();
            $table->string('status')->default('queued');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_logs');
    }
};
