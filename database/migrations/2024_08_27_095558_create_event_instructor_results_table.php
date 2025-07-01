<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('event_instructor_results', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('result', ['passed', 'review', 'failed'])->nullable()->default(null);
            $table->text('notes', 100)->nullable();
            $table->enum('stage', ['registered', 'pending', 'confirmed'])->default('registered');
            
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('weapon_form_id')->nullable()->references('id')->on('weapon_forms')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('event_instructor_results');
    }
};
