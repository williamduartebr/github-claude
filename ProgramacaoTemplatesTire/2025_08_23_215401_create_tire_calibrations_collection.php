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
        Schema::connection(env('MONGO_CONNECTION', 'mongodb'))
            ->table('tire_calibrations', function (Blueprint $collection) {
                // ========================================
                // CAMPOS ORIGINAIS (já existentes)
                // ========================================
                $collection->string('wordpress_url')->nullable();
                $collection->timestamp('blog_modified_time')->nullable();
                $collection->timestamp('blog_published_time')->nullable();
                $collection->timestamps();
                
                // ========================================
                // DADOS MAPEADOS DO VEHICLEDATA
                // ========================================
                $collection->string('vehicle_make')->nullable();           // Honda, Toyota, etc.
                $collection->string('vehicle_model')->nullable();          // Civic, Corolla, etc.
                $collection->integer('vehicle_year')->nullable();          // 2023, 2024, etc.
                $collection->string('vehicle_data_id')->nullable();        // ObjectId referência VehicleData
                
                // Dados estruturados mapeados do VehicleData
                $collection->json('vehicle_basic_data')->nullable();       // Dados básicos do veículo
                $collection->json('pressure_specifications')->nullable();   // Especificações de pressão
                $collection->json('vehicle_features')->nullable();         // Características do veículo
                
                // Categorização
                $collection->string('main_category')->nullable();          // hatch, suv, motorcycle, etc.
                
                // ========================================
                // CONTROLE DE FASES SIMPLIFICADO (2 FASES)
                // ========================================
                $collection->enum('enrichment_phase', [
                    'pending',              // Inicial - sem processamento
                    'article_generated',    // Artigo JSON estruturado gerado (FASE 1+2 completa)
                    'claude_processing',    // Claude refinando linguagem/SEO
                    'claude_completed',     // Claude finalizou refinamento (FASE 3 completa)
                    'completed',           // Processo 100% concluído
                    'failed'              // Falhou em alguma etapa
                ])->default('pending');
                
                // ========================================
                // TIMESTAMPS SIMPLIFICADOS
                // ========================================
                $collection->timestamp('article_generated_at')->nullable();        // Quando artigo foi estruturado
                $collection->timestamp('claude_processing_started_at')->nullable(); // Quando Claude iniciou
                $collection->timestamp('claude_completed_at')->nullable();          // Quando Claude finalizou
                
                // ========================================
                // ARTIGOS GERADOS (JSON COMPLETOS)
                // ========================================
                $collection->json('generated_article')->nullable();        // JSON artigo estruturado (FASE 1+2)
                $collection->json('article_refined')->nullable();          // JSON artigo refinado (FASE 3 - Claude)
                
                // ========================================
                // CONTROLE DE QUALIDADE
                // ========================================
                $collection->float('data_completeness_score')->nullable();  // Score 0-10 completude dados VehicleData
                $collection->float('content_quality_score')->nullable();    // Score 0-10 qualidade artigo estruturado
                $collection->float('seo_score')->nullable();               // Score 0-10 qualidade SEO (pós Claude)
                
                $collection->integer('processing_attempts')->default(0);    // Número de tentativas
                $collection->text('last_error')->nullable();               // Último erro ocorrido
                
                // ========================================
                // CAMPOS DE PRIORIZAÇÃO (OPCIONAIS)
                // ========================================
                $collection->string('processing_priority')->default('medium'); // high, medium, low
                
                // ========================================
                // ÍNDICES PARA PERFORMANCE
                // ========================================
                
                // Índice original
                $collection->index('wordpress_url');
                
                // Índices para consultas de processamento
                $collection->index('enrichment_phase');                     // Consultas por fase
                $collection->index(['enrichment_phase', 'processing_priority']); // Consultas priorizadas
                
                // Índices para consultas de veículos
                $collection->index(['vehicle_make', 'vehicle_model', 'vehicle_year']); // Busca de veículo específico
                $collection->index('vehicle_data_id');                      // Join com VehicleData
                $collection->index('main_category');                        // Consultas por categoria
                
                // Índices para estatísticas e relatórios
                $collection->index('article_generated_at');                 // Relatórios de produtividade
                $collection->index('claude_completed_at');                  // Performance Claude
                
                // Índice composto para consultas de processamento eficiente
                $collection->index([
                    'enrichment_phase', 
                    'processing_attempts', 
                    'processing_priority'
                ]);
                
                // Índice para limpeza de registros antigos falhos
                $collection->index(['enrichment_phase', 'updated_at']);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('MONGO_CONNECTION', 'mongodb'))
            ->dropIfExists('tire_calibrations');
    }
};