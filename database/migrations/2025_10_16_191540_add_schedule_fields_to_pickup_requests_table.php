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
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->date('proposed_date')->nullable()->after('status');
            $table->time('proposed_time_start')->nullable()->after('proposed_date');
            $table->time('proposed_time_end')->nullable()->after('proposed_time_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->dropColumn(['proposed_date', 'proposed_time_start', 'proposed_time_end']);
        });
    }
};
