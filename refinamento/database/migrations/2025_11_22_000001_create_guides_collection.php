<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

/**
 * Migration: Create guides collection with indexes (MongoDB)
 * 
 * Usando mongodb/laravel-mongodb v5.4
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
