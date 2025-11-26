<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

/**
 * Migration: Create guide_seo collection with indexes (MongoDB)
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
        Schema::connection(env('MONGO_CONNECTION'))->create('guide_seo', function (Blueprint $collection) {
            // Índices únicos
            $collection->unique('guide_id');
            $collection->unique('slug');

            // Índices simples
            $collection->index('primary_keyword');
            $collection->index('canonical_url');
            $collection->index('seo_score');

            // Índices para arrays
            $collection->index('secondary_keywords');

            // Índices para ordenação
            $collection->index(['created_at' => -1]);
            $collection->index(['updated_at' => -1]);
            $collection->index(['seo_score' => -1]);

            // Índice de texto para busca
            $collection->index([
                'title' => 'text',
                'h1' => 'text',
                'meta_description' => 'text',
            ], null, [
                'weights' => [
                    'title' => 10,
                    'h1' => 8,
                    'meta_description' => 5,
                ]
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('MONGO_CONNECTION'))->dropIfExists('guide_seo');
    }
};
