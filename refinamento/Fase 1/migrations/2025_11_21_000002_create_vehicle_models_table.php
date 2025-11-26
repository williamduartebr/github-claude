<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('make_id')->constrained('vehicle_makes')->onDelete('cascade');
            $table->string('name', 150);
            $table->string('slug', 150);
            $table->integer('year_start')->nullable();
            $table->integer('year_end')->nullable();
            $table->enum('category', ['sedan', 'hatch', 'suv', 'pickup', 'van', 'coupe', 'convertible', 'wagon', 'sport', 'motorcycle', 'truck', 'bus', 'other'])->default('sedan');
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['make_id', 'slug']);
            $table->index(['make_id', 'is_active']);
            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_models');
    }
};
