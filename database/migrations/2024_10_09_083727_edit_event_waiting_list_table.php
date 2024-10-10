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
        Schema::table('event_waiting_list', function (Blueprint $table) {
            $table->dateTime('payment_deadline')->nullable();
            $table->boolean('is_waiting_payment')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_waiting_list', function (Blueprint $table) {
            $table->dropColumn('payment_deadline');
            $table->dropColumn('is_waiting_payment');
        });
    }
};
