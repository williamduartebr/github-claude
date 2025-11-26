<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create guides collection with indexes
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection(env('MONGO_CONNECTION'))
            ->table('guides', function (Blueprint $collection) {
                // Índice único no slug
                $collection->unique('slug');

                // Índice para categoria
                $collection->index('guide_category_id');

                // Índices compostos para busca por veículo
                $collection->index(
                    ['make_slug' => 1, 'model_slug' => 1],
                    'index_make_model'
                );

                $collection->index(
                    ['make_slug' => 1, 'model_slug' => 1, 'year_start' => 1],
                    'index_make_model_year'
                );

                // Índices para range de anos
                $collection->index('year_start');
                $collection->index('year_end');
                
                $collection->index(
                    ['year_start' => 1, 'year_end' => 1],
                    'index_year_range'
                );

                // Índice para template
                $collection->index('template');

                // Índices para ordenação e busca
                $collection->index(['created_at' => -1], 'index_created_desc');
                $collection->index(['updated_at' => -1], 'index_updated_desc');

                // Índices para SEO
                $collection->index('seo.primary_keyword');
                $collection->index('payload.title');

                // Índice composto para busca completa
                $collection->index(
                    [
                        'make_slug' => 1,
                        'model_slug' => 1,
                        'guide_category_id' => 1,
                        'year_start' => 1
                    ],
                    'index_full_search'
                );

                // Índice de texto para busca full-text
                $collection->index(
                    [
                        'make' => 'text',
                        'model' => 'text',
                        'version' => 'text',
                        'payload.title' => 'text',
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
            ->table('guides', function (Blueprint $collection) {
                $collection->dropIndex('slug_1');
                $collection->dropIndex('guide_category_id_1');
                $collection->dropIndex('index_make_model');
                $collection->dropIndex('index_make_model_year');
                $collection->dropIndex('year_start_1');
                $collection->dropIndex('year_end_1');
                $collection->dropIndex('index_year_range');
                $collection->dropIndex('template_1');
                $collection->dropIndex('index_created_desc');
                $collection->dropIndex('index_updated_desc');
                $collection->dropIndex('seo.primary_keyword_1');
                $collection->dropIndex('payload.title_1');
                $collection->dropIndex('index_full_search');
                $collection->dropIndex('index_text_search');
            });
    }
};
