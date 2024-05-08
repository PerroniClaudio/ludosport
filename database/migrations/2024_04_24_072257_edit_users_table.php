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
        Schema::table('users', function (Blueprint $table) {

            $table->string('surname')->nullable();
            $table->string('weapon')->nullable();
            $table->boolean('has_paid_fee')->default(false);
            $table->timestamp('fee_payment_date')->nullable();
            $table->timestamp('fee_expires_at')->nullable();
            $table->integer('subscription_year')->default('1970');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        //

        Schema::table('users', function (Blueprint $table) {
        });
    }
};
