<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Tabela de marcas
        Schema::create('makes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo_url')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('article_count')->default(0);
            $table->timestamps();
        });
        
        // Tabela de modelos
        Schema::create('models', function (Blueprint $table) {
            $table->id();
            $table->string('make_slug');
            $table->string('name');
            $table->string('slug');
            $table->string('image_url')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('article_count')->default(0);
            $table->timestamps();
            
            $table->unique(['make_slug', 'slug']);
            $table->index('make_slug');
        });
        
        // Tabela de veículos (mapeia artigos para veículos)
        Schema::create('sync_vehicle_models', function (Blueprint $table) {
            $table->id();
            $table->string('article_id');
            $table->string('make')->nullable();
            $table->string('make_slug')->nullable();
            $table->string('model')->nullable();
            $table->string('model_slug')->nullable();
            $table->string('year_start')->nullable();
            $table->string('year_end')->nullable();
            $table->boolean('year_range')->default(false);
            $table->string('engine')->nullable();
            $table->string('version')->nullable();
            $table->string('fuel')->nullable();
            $table->string('category')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('article_title');
            $table->string('article_slug');
            $table->timestamps();
            
            $table->unique('article_id');
            $table->index('make_slug');
            $table->index('model_slug');
            $table->index(['make_slug', 'model_slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sync_vehicle_models');
        Schema::dropIfExists('models');
        Schema::dropIfExists('makes');
    }
}