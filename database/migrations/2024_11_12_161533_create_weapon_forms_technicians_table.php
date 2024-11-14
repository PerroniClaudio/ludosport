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
        Schema::create('weapon_forms_technicians', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('weapon_form_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('admin_id')->nullable()->default(null); // admin who added the user to the form
            $table->timestamps();
            
            $table->foreign('weapon_form_id')->references('id')->on('weapon_forms')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weapon_forms_technicians');
    }
};
