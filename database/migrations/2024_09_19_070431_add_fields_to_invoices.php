<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('invoices', function (Blueprint $table) {
            //
            $table->boolean('is_business')->default(false);
            $table->boolean('want_invoice')->default(false);
            $table->string('business_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('invoices', function (Blueprint $table) {
            //
            $table->dropColumn('is_business');
            $table->dropColumn('want_invoice');
            $table->dropColumn('business_name');
        });
    }
};
