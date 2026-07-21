<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terms_of_access_contents', function (Blueprint $table) {
            $table->id();
            $table->longText('content');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        $terms = DB::table('document_terms')->latest('version')->first();
        if ($terms?->content) {
            DB::table('terms_of_access_contents')->insert([
                'content' => $terms->content,
                'updated_by' => $terms->uploaded_by,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('terms_of_access_contents');
    }
};
