<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasColumn('invoices', 'fiscal_code')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('fiscal_code')->nullable()->after('sdi');
        });
    }

    public function down(): void {
        if (!Schema::hasColumn('invoices', 'fiscal_code')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('fiscal_code');
        });
    }
};
