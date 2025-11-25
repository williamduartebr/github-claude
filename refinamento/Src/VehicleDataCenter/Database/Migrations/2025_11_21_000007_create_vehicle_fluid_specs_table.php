<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_fluid_specs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('vehicle_versions')->onDelete('cascade');
            
            // Engine Oil
            $table->string('engine_oil_type', 50)->nullable(); // ex: 5W-30
            $table->decimal('engine_oil_capacity', 5, 2)->nullable();
            $table->string('engine_oil_standard', 100)->nullable(); // API SN, ACEA A5/B5
            
            // Coolant
            $table->string('coolant_type', 50)->nullable();
            $table->decimal('coolant_capacity', 5, 2)->nullable();
            
            // Transmission
            $table->string('transmission_fluid_type', 50)->nullable();
            $table->decimal('transmission_fluid_capacity', 5, 2)->nullable();
            
            // Brake
            $table->string('brake_fluid_type', 50)->nullable();
            
            // Power Steering
            $table->string('power_steering_fluid_type', 50)->nullable();
            
            $table->json('additional_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('version_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_fluid_specs');
    }
};
