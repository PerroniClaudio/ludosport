<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('academies', function (Blueprint $table) {
            $table->foreignId('main_rector')->nullable()->after('name')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('academies', function (Blueprint $table) {
            $table->dropForeign(['main_rector']);
            $table->dropColumn('main_rector');
        });
    }
};
