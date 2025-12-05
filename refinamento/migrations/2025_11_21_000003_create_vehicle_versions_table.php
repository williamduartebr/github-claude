<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->constrained('vehicle_models')->onDelete('cascade');
            $table->string('name', 200);
            $table->string('slug', 200);
            $table->integer('year');
            $table->string('engine_code', 50)->nullable();
            $table->enum('fuel_type', ['gasoline', 'diesel', 'ethanol', 'flex', 'electric', 'hybrid', 'plugin_hybrid', 'cng'])->nullable();
            $table->enum('transmission', ['manual', 'automatic', 'cvt', 'dct', 'amt'])->nullable();
            $table->decimal('price_msrp', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['model_id', 'slug', 'year']);
            $table->index(['model_id', 'year', 'is_active']);
            $table->index(['fuel_type', 'is_active']);
            $table->index(['year', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_versions');
    }
};
