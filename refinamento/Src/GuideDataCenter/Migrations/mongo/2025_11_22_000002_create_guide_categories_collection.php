<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create guide_categories collection with indexes
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection(env('MONGO_CONNECTION'))
            ->table('guide_categories', function (Blueprint $collection) {
                // Índice único no slug
                $collection->unique('slug');

                // Índice para nome
                $collection->index('name');

                // Índice para ordenação
                $collection->index('order');

                // Índice composto para categorias ativas ordenadas
                $collection->index(
                    ['active' => 1, 'order' => 1],
                    'index_active_order'
                );

                // Índice de texto para busca
                $collection->index(
                    [
                        'name' => 'text',
                        'description' => 'text',
                    ],
                    'index_text_search'
                );
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('MONGO_CONNECTION'))
            ->table('guide_categories', function (Blueprint $collection) {
                $collection->dropIndex('slug_1');
                $collection->dropIndex('name_1');
                $collection->dropIndex('order_1');
                $collection->dropIndex('index_active_order');
                $collection->dropIndex('index_text_search');
            });
    }
};
