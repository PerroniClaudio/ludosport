<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Aggiungi la colonna senza un valore di default
        Schema::table('weapon_forms_users', function (Blueprint $table) {
            $table->timestamp('awarded_at')->nullable();
        });

        Schema::table('weapon_forms_personnel', function (Blueprint $table) {
            $table->timestamp('awarded_at')->nullable();
        });

        Schema::table('weapon_forms_technicians', function (Blueprint $table) {
            $table->timestamp('awarded_at')->nullable();
        });

        // Aggiorna i record esistenti con l'attuale data e ora
        DB::table('weapon_forms_users')->update(['awarded_at' => DB::raw('CURRENT_TIMESTAMP')]);
        DB::table('weapon_forms_personnel')->update(['awarded_at' => DB::raw('CURRENT_TIMESTAMP')]);
        DB::table('weapon_forms_technicians')->update(['awarded_at' => DB::raw('CURRENT_TIMESTAMP')]);

        // Modifica la colonna per renderla non nullable
        Schema::table('weapon_forms_users', function (Blueprint $table) {
            $table->timestamp('awarded_at')->nullable(false)->useCurrent()->change();
        });

        Schema::table('weapon_forms_personnel', function (Blueprint $table) {
            $table->timestamp('awarded_at')->nullable(false)->useCurrent()->change();
        });

        Schema::table('weapon_forms_technicians', function (Blueprint $table) {
            $table->timestamp('awarded_at')->nullable(false)->useCurrent()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weapon_forms_users', function (Blueprint $table) {
            $table->dropColumn('awarded_at');
        });
        Schema::table('weapon_forms_personnel', function (Blueprint $table) {
            $table->dropColumn('awarded_at');
        });
        Schema::table('weapon_forms_technicians', function (Blueprint $table) {
            $table->dropColumn('awarded_at');
        });
    }
};
