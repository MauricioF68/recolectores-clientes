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
            // Añadimos las 3 nuevas columnas después de 'shipping_longitude'
            $table->string('shipping_department')->nullable()->after('shipping_longitude');
            $table->string('shipping_province')->nullable()->after('shipping_department');
            $table->string('shipping_district')->nullable()->after('shipping_province');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reward_claims', function (Blueprint $table) {
            // Esto permite revertir los cambios si es necesario
            $table->dropColumn(['shipping_department', 'shipping_province', 'shipping_district']);
        });
    }
};
