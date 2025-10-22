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
    Schema::table('reward_claims', function (Blueprint $table) {
        $table->string('agency_address')->nullable()->after('voucher_path');
        $table->string('pickup_password')->nullable()->after('agency_address');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reward_claims', function (Blueprint $table) {
            //
        });
    }
};
