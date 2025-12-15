<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

/**
 * Migration: Create guides collection with indexes (MongoDB)
 * 
 * Usando mongodb/laravel-mongodb v5.4
 * ✅ ATUALIZADO: Adicionados índices para FKs e make_logo_url
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection(env('MONGO_CONNECTION'))->create('guides', function (Blueprint $collection) {
            // Índice único no slug
            $collection->unique('slug');

            // ✅ Índices para FKs MySQL
            $collection->index('vehicle_make_id');
            $collection->index('vehicle_model_id');
            $collection->index('vehicle_version_id');
            
            // ✅ NOVO - Índice para logo da marca
            $collection->index('make_logo_url');

            // Índices simples
            $collection->index('guide_category_id');
            $collection->index('make_slug');
            $collection->index('model_slug');
            $collection->index('year_start');
            $collection->index('year_end');
            $collection->index('template');
            $collection->index('is_active');
            $collection->index('full_title');
            $collection->index('short_title');

            // Índices compostos com FKs
            $collection->index(['vehicle_make_id', 'vehicle_model_id']);
            $collection->index(['vehicle_make_id', 'vehicle_model_id', 'vehicle_version_id']);
            $collection->index(['vehicle_make_id', 'guide_category_id']);
            $collection->index(['vehicle_model_id', 'guide_category_id']);

            // Índices para content_blocks
            $collection->index('content_blocks.type');
            $collection->index('content_blocks.order');

            // Índices compostos
            $collection->index(['make_slug', 'model_slug']);
            $collection->index(['make_slug', 'model_slug', 'year_start']);
            $collection->index(['year_start', 'year_end']);
            $collection->index(['make_slug', 'model_slug', 'guide_category_id', 'year_start']);

            // Índices para ordenação
            $collection->index(['created_at' => -1]);
            $collection->index(['updated_at' => -1]);

            // Índice de texto para full-text search
            $collection->index([
                'make' => 'text',
                'model' => 'text',
                'version' => 'text',
                'full_title' => 'text',
                'short_title' => 'text',
            ], null, [
                'weights' => [
                    'full_title' => 10,
                    'short_title' => 8,
                    'make' => 6,
                    'model' => 6,
                    'version' => 4,
                ]
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('MONGO_CONNECTION'))->dropIfExists('guides');
    }
};