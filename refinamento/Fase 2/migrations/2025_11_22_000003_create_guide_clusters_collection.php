<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

/**
 * Migration: Create guide_clusters collection with indexes (MongoDB)
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
        Schema::connection(env('MONGO_CONNECTION'))->create('guide_clusters', function (Blueprint $collection) {
            // Índices simples
            $collection->index('guide_id');
            $collection->index('make_slug');
            $collection->index('model_slug');
            $collection->index('year_range');
            $collection->index('cluster_type');

            // Índices compostos
            $collection->index(['make_slug', 'model_slug']);
            $collection->index(['make_slug', 'model_slug', 'cluster_type']);
            $collection->index(['guide_id', 'cluster_type']);

            // Índice para ordenação
            $collection->index(['updated_at' => -1]);
            $collection->index(['created_at' => -1]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('MONGO_CONNECTION'))->dropIfExists('guide_clusters');
    }
};
