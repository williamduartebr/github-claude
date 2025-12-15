<?php

use Illuminate\Database\Migrations\Migration;
use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration para criar collection vehicle_enrichment_groups
 * 
 * Armazena agrupamentos de veículos para enrichment otimizado via Claude API
 * Reduz 963 chamadas para ~200 através de agrupamento inteligente
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection(env('MONGO_CONNECTION'))
            ->create('vehicle_enrichment_groups', function (Blueprint $collection) {
                
                // Identificação única do grupo
                $collection->string('generation_key')->index(); // ex: Honda_Civic_hatch_2020-2024
                $collection->string('make')->index();
                $collection->string('model')->index();
                $collection->string('main_category')->index();
                $collection->string('year_span'); // ex: "2020-2024"
                
                // Dados do agrupamento
                $collection->json('group_vehicles'); // Metadados do grupo (count, years, etc)
                $collection->integer('sibling_count')->default(0);
                
                // Representante (veículo escolhido para API)
                $collection->json('representative_data'); // Dados completos do representante
                $collection->string('representative_vehicle_id')->index();
                
                // Veículos irmãos (receberão propagação)
                $collection->json('sibling_vehicles'); // Array com dados dos siblings
                
                // Status de processamento
                $collection->enum('processing_status', [
                    'pending',      // Aguardando enrichment
                    'enriching',    // Processando via Claude API
                    'enriched',     // Enrichment concluído
                    'propagating',  // Propagando para siblings
                    'completed',    // Tudo concluído
                    'failed'        // Falhou em alguma etapa
                ])->default('pending')->index();
                
                $collection->enum('priority', ['high', 'medium', 'low'])->default('medium')->index();
                
                // Controle de tentativas
                $collection->integer('enrichment_attempts')->default(0);
                $collection->integer('propagation_attempts')->default(0);
                $collection->timestamp('last_enrichment_attempt_at')->nullable();
                $collection->timestamp('last_propagation_attempt_at')->nullable();
                
                // Flags de status
                $collection->boolean('is_enriched')->default(false)->index();
                $collection->boolean('is_propagated')->default(false)->index();
                
                // Dados enriquecidos
                $collection->json('enriched_data')->nullable(); // Dados retornados pela API Claude
                $collection->timestamp('enriched_at')->nullable()->index();
                
                // Resultados da propagação
                $collection->json('propagation_results')->nullable(); // IDs atualizados, erros, etc
                $collection->timestamp('propagated_at')->nullable()->index();
                
                // Controle de erros
                $collection->text('enrichment_error')->nullable();
                $collection->text('propagation_error')->nullable();
                
                // Timestamps padrão
                $collection->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('MONGO_CONNECTION', 'mongodb'))
            ->dropIfExists('vehicle_enrichment_groups');
    }
};