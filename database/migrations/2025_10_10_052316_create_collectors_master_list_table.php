<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collectors_master_list', function (Blueprint $table) {
            $table->id(); 
            $table->string('first_name');
            $table->string('middle_name')->nullable(); 
            $table->string('last_name');
            $table->string('second_last_name')->nullable();
            $table->string('dni')->unique(); 
            $table->string('email')->nullable();
            $table->string('department')->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('status')->default('activo'); 
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collectors_master_list');
    }
};
