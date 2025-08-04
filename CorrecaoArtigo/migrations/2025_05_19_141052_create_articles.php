<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection(env('MONGO_CONNECTION'))
            ->table('articles', function (Blueprint $collection) {
                // Índices básicos
                $collection->index('status');
                $collection->index('slug', null, null, [
                    'sparse' => true,
                    'unique' => true,
                    'background' => true,
                ]);
                $collection->index('category_id');
                $collection->index('category_slug');
                $collection->index('created_at');
                $collection->index('updated_at');
                $collection->index('tags');
                $collection->index('related_topics');
                
                // Índices compostos
                $collection->index(
                    [
                        'status' => 1,
                        'category_slug' => 1,
                    ],
                    'index_status_category_slug'
                );
                
                $collection->index(
                    [
                        'status' => 1,
                        'created_at' => -1,
                    ],
                    'index_status_created_at'
                );
                
                $collection->index(
                    [
                        'status' => 1,
                        'tags' => 1,
                    ],
                    'index_status_tags'
                );
                
                // Índices para buscas específicas de entidades extraídas
                $collection->index('extracted_entities.marca');
                $collection->index('extracted_entities.modelo');
                $collection->index('extracted_entities.motorizacao');
                $collection->index('extracted_entities.tipo_veiculo');
                $collection->index('extracted_entities.categoria');
                $collection->index('extracted_entities.combustivel');
                
                // Índice para busca fulltext
                $collection->index(
                    [
                        'title' => 'text',
                        'content.introducao' => 'text',
                        'content.perguntas_frequentes.pergunta' => 'text',
                        'content.perguntas_frequentes.resposta' => 'text',
                        'seo_data.primary_keyword' => 'text',
                        'seo_data.secondary_keywords' => 'text',
                        'tags' => 'text',
                    ],
                    'index_articles_fulltext',
                    [
                        'weights' => [
                            'title' => 100,
                            'seo_data.primary_keyword' => 80,
                            'tags' => 70,
                            'content.introducao' => 60,
                            'seo_data.secondary_keywords' => 50,
                            'content.perguntas_frequentes.pergunta' => 40,
                            'content.perguntas_frequentes.resposta' => 20,
                        ],
                        'default_language' => 'portuguese',
                    ]
                );
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('MONGO_CONNECTION'))
            ->table('articles', function (Blueprint $collection) {
                $collection->dropIndex('status_1');
                $collection->dropIndex('slug_1');
                $collection->dropIndex('category_id_1');
                $collection->dropIndex('category_slug_1');
                $collection->dropIndex('created_at_1');
                $collection->dropIndex('updated_at_1');
                $collection->dropIndex('tags_1');
                $collection->dropIndex('related_topics_1');
                $collection->dropIndex('index_status_category_slug');
                $collection->dropIndex('index_status_created_at');
                $collection->dropIndex('index_status_tags');
                $collection->dropIndex('extracted_entities.marca_1');
                $collection->dropIndex('extracted_entities.modelo_1');
                $collection->dropIndex('extracted_entities.motorizacao_1');
                $collection->dropIndex('extracted_entities.tipo_veiculo_1');
                $collection->dropIndex('extracted_entities.categoria_1');
                $collection->dropIndex('extracted_entities.combustivel_1');
                $collection->dropFullText('index_articles_fulltext');
            });
    }
};