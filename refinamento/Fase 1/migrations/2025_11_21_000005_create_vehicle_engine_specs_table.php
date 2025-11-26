<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_engine_specs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('vehicle_versions')->onDelete('cascade');
            
            $table->string('engine_type', 100)->nullable();
            $table->string('engine_code', 50)->nullable();
            $table->integer('displacement_cc')->nullable();
            $table->integer('cylinders')->nullable();
            $table->enum('cylinder_arrangement', ['inline', 'v', 'boxer', 'rotary', 'w'])->nullable();
            $table->integer('valves_per_cylinder')->nullable();
            $table->string('aspiration', 50)->nullable(); // turbo, supercharged, naturally_aspirated
            $table->decimal('compression_ratio', 4, 2)->nullable();
            $table->integer('max_rpm')->nullable();
            
            $table->json('additional_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('version_id');
            $table->index('engine_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_engine_specs');
    }
};
