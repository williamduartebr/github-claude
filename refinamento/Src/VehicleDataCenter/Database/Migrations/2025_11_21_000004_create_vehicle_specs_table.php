<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_specs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('vehicle_versions')->onDelete('cascade');
            
            // Performance
            $table->decimal('power_hp', 6, 2)->nullable();
            $table->decimal('power_kw', 6, 2)->nullable();
            $table->integer('torque_nm')->nullable();
            $table->integer('top_speed_kmh')->nullable();
            $table->decimal('acceleration_0_100', 4, 2)->nullable();
            
            // Consumption
            $table->decimal('fuel_consumption_city', 5, 2)->nullable();
            $table->decimal('fuel_consumption_highway', 5, 2)->nullable();
            $table->decimal('fuel_consumption_mixed', 5, 2)->nullable();
            $table->integer('fuel_tank_capacity')->nullable();
            
            // Weight and Capacity
            $table->integer('weight_kg')->nullable();
            $table->integer('payload_kg')->nullable();
            $table->integer('trunk_capacity_liters')->nullable();
            $table->integer('seating_capacity')->default(5);
            
            // Outros
            $table->string('body_type', 50)->nullable();
            $table->integer('doors')->nullable();
            $table->enum('drive_type', ['fwd', 'rwd', 'awd', '4wd'])->nullable();
            
            $table->json('additional_specs')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('version_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_specs');
    }
};
