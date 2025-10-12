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
        Schema::table('collectors_master_list', function (Blueprint $table) {
            // ESTAS SON LAS LÍNEAS QUE AÑADIMOS
            $table->string('address')->nullable()->after('district');
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collectors_master_list', function (Blueprint $table) {
            // Y ESTA ES LA LÍNEA PARA REVERTIR
            $table->dropColumn(['address', 'latitude', 'longitude']);
        });
    }
};