<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::table('event_instructor_results', function (Blueprint $table) {
            $table->string('internship_duration', 100)->nullable();
            $table->string('internship_notes', 100)->nullable();
            $table->enum('retake', ['exam', 'course'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
