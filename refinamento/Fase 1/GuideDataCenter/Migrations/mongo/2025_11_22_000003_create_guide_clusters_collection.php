<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create guide_clusters collection with indexes
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection(env('MONGO_CONNECTION'))
            ->table('guide_clusters', function (Blueprint $collection) {
                // Índice por guia
                $collection->index('guide_id');

                // Índices por veículo
                $collection->index('make_slug');
                $collection->index('model_slug');
                
                $collection->index(
                    ['make_slug' => 1, 'model_slug' => 1],
                    'index_make_model'
                );

                // Índice por range de anos
                $collection->index('year_range');

                // Índice por tipo de cluster
                $collection->index('cluster_type');

                // Índice composto para super clusters
                $collection->index(
                    [
                        'make_slug' => 1,
                        'model_slug' => 1,
                        'cluster_type' => 1,
                    ],
                    'index_super_cluster'
                );

                // Índice por data de atualização
                $collection->index(['updated_at' => -1], 'index_updated_desc');

                // Índice composto otimizado
                $collection->index(
                    [
                        'guide_id' => 1,
                        'cluster_type' => 1,
                    ],
                    'index_guide_cluster_type'
                );
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('MONGO_CONNECTION'))
            ->table('guide_clusters', function (Blueprint $collection) {
                $collection->dropIndex('guide_id_1');
                $collection->dropIndex('make_slug_1');
                $collection->dropIndex('model_slug_1');
                $collection->dropIndex('index_make_model');
                $collection->dropIndex('year_range_1');
                $collection->dropIndex('cluster_type_1');
                $collection->dropIndex('index_super_cluster');
                $collection->dropIndex('index_updated_desc');
                $collection->dropIndex('index_guide_cluster_type');
            });
    }
};
