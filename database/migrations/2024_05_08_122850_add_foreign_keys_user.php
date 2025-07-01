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

        Schema::table('users', function (Blueprint $table) {

            $table->unsignedBigInteger('nation_id')->nullable();
            $table->unsignedBigInteger('academy_id')->nullable();
            $table->unsignedBigInteger('school_id')->nullable();

            $table->foreign('nation_id')->references('id')->on('nations')->onDelete('cascade');
            $table->foreign('academy_id')->references('id')->on('academies')->onDelete('cascade');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
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
