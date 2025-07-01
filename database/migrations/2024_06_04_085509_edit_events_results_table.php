<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        //

        Schema::table('event_results', function (Blueprint $table) {
            $table->integer('bonus_war_points')->default(0);
            $table->integer('bonus_style_points')->default(0);
            $table->integer('total_war_points')->default(0);
            $table->integer('total_style_points')->default(0);
            $table->dropColumn('total_points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        //
    }
};
