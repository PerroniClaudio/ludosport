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
        Schema::table('exports', function (Blueprint $table) {
            $table->foreignId('user_role_id')
                ->nullable()
                ->comment('Ruolo dell\'utente che ha richiesto l\'export, per limitare i dati esportati in base al ruolo')
                ->constrained('roles')
                ->nullOnDelete();
            $table->foreignId('user_academy_id')
                ->nullable()
                ->comment('Accademia dell\'utente che ha richiesto l\'export, per limitare i dati esportati in base all\'accademia')
                ->constrained('academies')
                ->nullOnDelete();
            $table->foreignId('user_school_id')
                ->nullable()
                ->comment('Scuola dell\'utente che ha richiesto l\'export, per limitare i dati esportati in base alla scuola')
                ->constrained('schools')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exports', function (Blueprint $table) {
            $table->dropForeign(['user_role_id']);
            $table->dropForeign(['user_academy_id']);
            $table->dropForeign(['user_school_id']);
            $table->dropColumn(['user_role_id', 'user_academy_id', 'user_school_id']);
        });
    }
};
