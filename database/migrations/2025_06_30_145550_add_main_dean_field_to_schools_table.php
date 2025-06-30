<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('schools', function (Blueprint $table) {
            //
            $table->foreignId('main_dean')->nullable()->after('name')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('schools', function (Blueprint $table) {

            //
            $table->dropForeign(['main_dean']);
            $table->dropColumn('main_dean');
        });
    }
};
