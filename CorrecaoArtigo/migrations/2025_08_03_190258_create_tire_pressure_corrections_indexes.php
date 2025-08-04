<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration para criação de índices da collection tire_pressure_corrections no MongoDB
 * 
 * Armazena registros de correções de pressão de pneus com índices otimizados
 * para consultas rápidas de status, artigos e estatísticas
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection(env('MONGO_CONNECTION', 'mongodb'))
            ->table('tire_pressure_corrections', function (Blueprint $collection) {

                // Primeiro, tentar remover índices existentes que podem conflitar
                $conflictingIndexes = [
                    'article_correction_unique',
                    'status_created_index',
                    'status_processed_index', 
                    'type_status_index',
                    'processed_status_index',
                    'created_status_index',
                    'cleanup_index',
                    'cleanup_processed_index'
                ];

                foreach ($conflictingIndexes as $indexName) {
                    try {
                        $collection->dropIndex($indexName);
                    } catch (\Exception $e) {
                        // Ignora se não existe
                    }
                }

                // Índices básicos para consultas principais (apenas se não existirem)
                try { $collection->index('article_id', 'tpc_article_id_idx'); } catch (\Exception $e) {}
                try { $collection->index('article_slug', 'tpc_article_slug_idx'); } catch (\Exception $e) {}
                try { $collection->index('status', 'tpc_status_idx'); } catch (\Exception $e) {}
                try { $collection->index('correction_type', 'tpc_correction_type_idx'); } catch (\Exception $e) {}
                try { $collection->index('vehicle_name', 'tpc_vehicle_name_idx'); } catch (\Exception $e) {}

                // Índices de tempo para controle de processamento
                try { $collection->index('created_at', 'tpc_created_at_idx'); } catch (\Exception $e) {}
                try { $collection->index('updated_at', 'tpc_updated_at_idx'); } catch (\Exception $e) {}
                try { $collection->index('processed_at', 'tpc_processed_at_idx'); } catch (\Exception $e) {}

                // Índices compostos essenciais
                try { 
                    $collection->index(['article_id', 'created_at'], 'tpc_article_created_idx'); 
                } catch (\Exception $e) {}
                
                try { 
                    $collection->index(['status', 'created_at'], 'tpc_status_created_idx'); 
                } catch (\Exception $e) {}
                
                try { 
                    $collection->index(['status', 'processed_at'], 'tpc_status_processed_idx'); 
                } catch (\Exception $e) {}
                
                try { 
                    $collection->index(['correction_type', 'status'], 'tpc_type_status_idx'); 
                } catch (\Exception $e) {}

                // Índices para consultas de artigos processados recentemente
                try { 
                    $collection->index(['processed_at', 'status'], 'tpc_proc_status_idx'); 
                } catch (\Exception $e) {}

                // Índices para estatísticas e relatórios
                try { 
                    $collection->index(['status', 'correction_type'], 'tpc_stats_idx'); 
                } catch (\Exception $e) {}
                
                try { 
                    $collection->index(['created_at', 'correction_type'], 'tpc_daily_stats_idx'); 
                } catch (\Exception $e) {}

                // Índices para performance de scopes mais importantes
                try { 
                    $collection->index(['status', 'article_id'], 'tpc_scope_pending_idx'); 
                } catch (\Exception $e) {}
                
                try { 
                    $collection->index(['article_id', 'status'], 'tpc_article_status_idx'); 
                } catch (\Exception $e) {}
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('MONGO_CONNECTION', 'mongodb'))
            ->table('tire_pressure_corrections', function (Blueprint $collection) {

                // Lista de todos os índices criados
                $indexesToDrop = [
                    'tpc_article_id_idx',
                    'tpc_article_slug_idx', 
                    'tpc_status_idx',
                    'tpc_correction_type_idx',
                    'tpc_vehicle_name_idx',
                    'tpc_created_at_idx',
                    'tpc_updated_at_idx',
                    'tpc_processed_at_idx',
                    'tpc_article_created_idx',
                    'tpc_status_created_idx',
                    'tpc_status_processed_idx',
                    'tpc_type_status_idx',
                    'tpc_proc_status_idx',
                    'tpc_stats_idx',
                    'tpc_daily_stats_idx',
                    'tpc_scope_pending_idx',
                    'tpc_article_status_idx'
                ];

                foreach ($indexesToDrop as $indexName) {
                    try {
                        $collection->dropIndex($indexName);
                    } catch (\Exception $e) {
                        // Ignora se não existe
                    }
                }
            });
    }
};