<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_dimensions_specs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('vehicle_versions')->onDelete('cascade');
            
            $table->integer('length_mm')->nullable();
            $table->integer('width_mm')->nullable();
            $table->integer('height_mm')->nullable();
            $table->integer('wheelbase_mm')->nullable();
            $table->integer('front_track_mm')->nullable();
            $table->integer('rear_track_mm')->nullable();
            $table->integer('ground_clearance_mm')->nullable();
            
            $table->json('additional_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('version_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_dimensions_specs');
    }
};
