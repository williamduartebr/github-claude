<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_tire_specs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('vehicle_versions')->onDelete('cascade');
            
            $table->string('front_tire_size', 50)->nullable();
            $table->string('rear_tire_size', 50)->nullable();
            $table->string('front_rim_size', 20)->nullable();
            $table->string('rear_rim_size', 20)->nullable();
            $table->decimal('front_pressure_psi', 4, 1)->nullable();
            $table->decimal('rear_pressure_psi', 4, 1)->nullable();
            $table->string('spare_tire_type', 50)->nullable();
            
            $table->json('additional_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('version_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_tire_specs');
    }
};
