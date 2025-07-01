<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('thumbnail')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_published')->default(false);
            $table->string('location');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('nation_id')->nullable();
            $table->unsignedBigInteger('academy_id')->nullable();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('nation_id')->references('id')->on('nations')->onDelete('cascade');
            $table->foreign('academy_id')->references('id')->on('academies')->onDelete('cascade');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('events');
    }
};
