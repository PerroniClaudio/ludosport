<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_user_minor')->default(false);
            $table->boolean('has_user_uploaded_documents')->default(false);
            $table->boolean('has_admin_approved_minor')->default(false);
            $table->text('uploaded_documents_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_user_minor');
            $table->dropColumn('has_user_uploaded_documents');
            $table->dropColumn('has_admin_approved_minor');
            $table->dropColumn('uploaded_documents_path');
        });
    }
};
