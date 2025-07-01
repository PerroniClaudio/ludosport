<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('clans', function (Blueprint $table) {
            //
            $table->foreignId('weapon_form_id')->nullable()->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('clans', function (Blueprint $table) {
            //
            $table->dropForeign(['weapon_form_id']);
        });
    }
};
