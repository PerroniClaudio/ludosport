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

        Schema::table('fees', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('auto_renew')->default(false);
            $table->text('unique_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        //

        Schema::table('fees', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            $table->dropColumn('auto_renew');
            $table->dropColumn('unique_id');
        });
    }
};
