<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('weapon_forms', function (Blueprint $table) {
            $table->boolean('position_before_specific')->default(false)->after('image');
            $table->boolean('position_long_saber')->default(false)->after('position_before_specific');
            $table->boolean('position_dual_saber')->default(false)->after('position_long_saber');
            $table->boolean('position_saberstaff')->default(false)->after('position_dual_saber');
            $table->boolean('position_after_specific')->default(false)->after('position_saberstaff');
        });
    }

    public function down(): void
    {
        Schema::table('weapon_forms', function (Blueprint $table) {
            $table->dropColumn([
                'position_before_specific',
                'position_long_saber',
                'position_dual_saber',
                'position_saberstaff',
                'position_after_specific',
            ]);
        });
    }
};
