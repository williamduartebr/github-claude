<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration para criação de índices da collection vehicle_data no MongoDB
 * 
 * Armazena dados centralizados dos veículos extraídos dos artigos
 * de pressão de pneus com índices otimizados para consultas rápidas
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection(env('MONGO_CONNECTION', 'mongodb'))
            ->table('vehicle_data', function (Blueprint $collection) {

                // Índices básicos de busca
                $collection->index('make');
                $collection->index('model');
                $collection->index('year');
                $collection->index('main_category');
                $collection->index('vehicle_segment');
                $collection->index('vehicle_type');

                // Índice único para veículo
                try {
                    $collection->dropIndex('vehicle_unique');
                } catch (\Exception $e) {
                    // Ignora se não existe
                }
                $collection->index(['make', 'model', 'year'], 'vehicle_unique');

                // Índices para características booleanas
                $collection->index('is_premium');
                $collection->index('has_tpms');
                $collection->index('is_motorcycle');
                $collection->index('is_electric');
                $collection->index('is_hybrid');
                $collection->index('is_verified');

                // Índices de qualidade e validação
                $collection->index('validation_status');
                $collection->index('data_quality_score');

                // Índices de tempo
                $collection->index('created_at');
                $collection->index('updated_at');
                $collection->index('extracted_at');
                $collection->index('last_validated_at');

                // Índices compostos para otimização de queries complexas
                $collection->index(['main_category', 'vehicle_segment'], 'category_segment_index');
                $collection->index(['is_electric', 'is_hybrid'], 'electric_hybrid_index');
                $collection->index(['validation_status', 'data_quality_score'], 'quality_status_index');
                $collection->index(['make', 'main_category'], 'make_category_index');
                $collection->index(['year', 'main_category'], 'year_category_index');
                $collection->index(['is_premium', 'has_tpms'], 'premium_tpms_index');

                // Índices para estatísticas e relatórios
                $collection->index(['created_at', 'main_category'], 'daily_stats_index');
                $collection->index(['data_quality_score', 'validation_status'], 'quality_validation_index');
                $collection->index(['year', 'is_electric'], 'year_electric_index');
                $collection->index(['year', 'is_hybrid'], 'year_hybrid_index');

                // Índices para busca avançada
                $collection->index(['make', 'model', 'main_category'], 'vehicle_category_index');
                $collection->index(['vehicle_segment', 'year'], 'segment_year_index');
                $collection->index(['is_premium', 'data_quality_score'], 'premium_quality_index');

                // Índices para performance de consultas específicas
                $collection->index(['validation_status', 'created_at'], 'validation_created_index');
                $collection->index(['main_category', 'is_verified'], 'category_verified_index');
                $collection->index(['data_quality_score', 'last_validated_at'], 'score_validated_index');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('MONGO_CONNECTION', 'mongodb'))
            ->table('vehicle_data', function (Blueprint $collection) {

                // Drop índices básicos
                $collection->dropIndex(['make']);
                $collection->dropIndex(['model']);
                $collection->dropIndex(['year']);
                $collection->dropIndex(['main_category']);
                $collection->dropIndex(['vehicle_segment']);
                $collection->dropIndex(['vehicle_type']);

                // Drop índice único
                $collection->dropIndex('vehicle_unique');

                // Drop índices booleanos
                $collection->dropIndex(['is_premium']);
                $collection->dropIndex(['has_tpms']);
                $collection->dropIndex(['is_motorcycle']);
                $collection->dropIndex(['is_electric']);
                $collection->dropIndex(['is_hybrid']);
                $collection->dropIndex(['is_verified']);

                // Drop índices de qualidade
                $collection->dropIndex(['validation_status']);
                $collection->dropIndex(['data_quality_score']);

                // Drop índices de tempo
                $collection->dropIndex(['created_at']);
                $collection->dropIndex(['updated_at']);
                $collection->dropIndex(['extracted_at']);
                $collection->dropIndex(['last_validated_at']);

                // Drop índices compostos
                $collection->dropIndex('category_segment_index');
                $collection->dropIndex('electric_hybrid_index');
                $collection->dropIndex('quality_status_index');
                $collection->dropIndex('make_category_index');
                $collection->dropIndex('year_category_index');
                $collection->dropIndex('premium_tpms_index');

                // Drop índices de estatísticas
                $collection->dropIndex('daily_stats_index');
                $collection->dropIndex('quality_validation_index');
                $collection->dropIndex('year_electric_index');
                $collection->dropIndex('year_hybrid_index');

                // Drop índices de busca avançada
                $collection->dropIndex('vehicle_category_index');
                $collection->dropIndex('segment_year_index');
                $collection->dropIndex('premium_quality_index');

                // Drop índices de performance
                $collection->dropIndex('validation_created_index');
                $collection->dropIndex('category_verified_index');
                $collection->dropIndex('score_validated_index');
            });
    }
};
