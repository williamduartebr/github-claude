<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_battery_specs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('vehicle_versions')->onDelete('cascade');
            
            $table->string('battery_type', 50)->nullable(); // Lead-acid, AGM, Lithium
            $table->integer('voltage')->default(12);
            $table->integer('capacity_ah')->nullable();
            $table->integer('cca')->nullable(); // Cold Cranking Amps
            $table->string('group_size', 20)->nullable();
            
            // Para veículos elétricos
            $table->decimal('battery_capacity_kwh', 6, 2)->nullable();
            $table->integer('electric_range_km')->nullable();
            $table->string('charging_time', 100)->nullable();
            
            $table->json('additional_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('version_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_battery_specs');
    }
};
