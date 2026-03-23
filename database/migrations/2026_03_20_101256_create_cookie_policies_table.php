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
        Schema::create('cookie_policies', function (Blueprint $table) {
            $table->id();
            $table->longText('content')->nullable();
            $table->unsignedBigInteger('last_modified_by')->nullable();
            $table->timestamps();
            $table->foreign('last_modified_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cookie_policies');
    }
};
