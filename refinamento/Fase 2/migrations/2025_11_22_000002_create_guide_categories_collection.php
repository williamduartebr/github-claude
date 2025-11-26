<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

/**
 * Migration: Create guide_categories collection with indexes (MongoDB)
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
        Schema::connection(env('MONGO_CONNECTION'))->create('guide_categories', function (Blueprint $collection) {
            // Índice único no slug
            $collection->unique('slug');

            // Índices simples
            $collection->index('name');
            $collection->index('order');
            $collection->index('is_active');

            // Índices compostos
            $collection->index(['is_active', 'order']);

            // Índice para ordenação
            $collection->index(['created_at' => -1]);
            $collection->index(['updated_at' => -1]);

            // Índice de texto para busca
            $collection->index([
                'name' => 'text',
                'description' => 'text',
            ], null, [
                'weights' => [
                    'name' => 10,
                    'description' => 5,
                ]
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('MONGO_CONNECTION'))->dropIfExists('guide_categories');
    }
};
