<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'watermark_fields')) {
                $table->json('watermark_fields')->default(json_encode(['name', 'email', 'user_id', 'downloaded_at', 'network']));
            }

            if (! Schema::hasColumn('documents', 'watermark_side')) {
                $table->string('watermark_side', 8)->default('left');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'watermark_fields')) {
                $table->dropColumn('watermark_fields');
            }

            if (Schema::hasColumn('documents', 'watermark_side')) {
                $table->dropColumn('watermark_side');
            }
        });
    }
};
