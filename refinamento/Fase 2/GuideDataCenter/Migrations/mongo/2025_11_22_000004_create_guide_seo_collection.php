<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create guide_seo collection with indexes
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection(env('MONGO_CONNECTION'))
            ->table('guide_seo', function (Blueprint $collection) {
                // Índice único por guia (cada guia tem apenas um SEO)
                $collection->unique('guide_id');

                // Índice único por slug
                $collection->unique('slug');

                // Índice para palavra-chave primária
                $collection->index('primary_keyword');

                // Índice para palavras-chave secundárias
                $collection->index('secondary_keywords');

                // Índice para URL canônica
                $collection->index('canonical_url');

                // Índices de ordenação
                $collection->index(['created_at' => -1], 'index_created_desc');
                $collection->index(['updated_at' => -1], 'index_updated_desc');

                // Índice para contagem de palavras
                $collection->index('word_count');

                // Índice para score de legibilidade
                $collection->index('readability_score');

                // Índice de texto para busca
                $collection->index(
                    [
                        'title' => 'text',
                        'h1' => 'text',
                        'meta_description' => 'text',
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
            ->table('guide_seo', function (Blueprint $collection) {
                $collection->dropIndex('guide_id_1');
                $collection->dropIndex('slug_1');
                $collection->dropIndex('primary_keyword_1');
                $collection->dropIndex('secondary_keywords_1');
                $collection->dropIndex('canonical_url_1');
                $collection->dropIndex('index_created_desc');
                $collection->dropIndex('index_updated_desc');
                $collection->dropIndex('word_count_1');
                $collection->dropIndex('readability_score_1');
                $collection->dropIndex('index_text_search');
            });
    }
};
