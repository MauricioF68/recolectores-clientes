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
        Schema::create('reward_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('reward_id')->constrained('rewards');
            $table->unsignedInteger('points_spent'); // Puntos que costó
            $table->string('status')->default('solicitado'); // solicitado, enviado, entregado

            // Datos de envío confirmados por el cliente
            $table->text('shipping_address');
            $table->decimal('shipping_latitude', 10, 7);
            $table->decimal('shipping_longitude', 10, 7);

            // Datos de envío proporcionados por el admin
            $table->string('tracking_number')->nullable();
            $table->string('tracking_code')->nullable();
            $table->string('voucher_path')->nullable(); // Foto del boucher

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_claims');
    }
};
