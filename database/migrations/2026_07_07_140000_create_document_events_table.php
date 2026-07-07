<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('user_name');
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->string('terms_version')->default('v1');
            $table->string('operation_result');
            $table->string('ip_address', 45)->nullable();
            $table->string('session_id')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'event_type']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_events');
    }
};
