<?php

namespace Src\ContentGeneration\TireSchedule\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Src\ContentGeneration\TireSchedule\Infrastructure\Eloquent\TireArticleCorrection as ArticleCorrection;

class ForceCompleteCorrectionsCommand extends Command
{
    /**
     * Nome e assinatura do comando.
     */
    protected $signature = 'force-complete-corrections 
                           {--batch-size=50 : Tamanho do lote para processamento}
                           {--dry-run : Apenas mostrar o que seria feito}
                           {--force : Força execução sem confirmação}
                           {--skip-existing : Pular artigos que já têm alguma correção}';

    /**
     * Descrição do comando.
     */
    protected $description = 'Força criação completa de correções para garantir 969×2=1938 correções totais';

    /**
     * Execute o comando.
     */
    public function handle()
    {
        $this->info('🚀 Forçando Criação Completa de Correções');
        $this->info('Meta: 969 artigos × 2 tipos = 1.938 correções totais');
        $this->line('');

        // Análise inicial
        $analysis = $this->performInitialAnalysis();
        $this->displayAnalysis($analysis);

        if ($this->option('dry-run')) {
            $this->info('🔍 [DRY RUN] Execução simulada concluída.');
            return Command::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm('Confirma execução da correção forçada?')) {
            $this->info('❌ Operação cancelada pelo usuário.');
            return Command::SUCCESS;
        }

        // Executar correção forçada
        $results = $this->executeForceComplete($analysis);
        $this->displayResults($results);

        return Command::SUCCESS;
    }

    /**
     * 📊 Análise inicial detalhada
     */
    private function performInitialAnalysis(): array
    {
        $this->info('📊 Realizando análise inicial...');

        // 1. Buscar todos os artigos de pneus - CORRIGIDO
        // Primeiro vamos verificar qual campo usar: domain ou template
        $articlesByDomain = TempArticle::where('domain', 'when_to_change_tires')
            ->where('status', 'draft')
            ->count();
            
        $articlesByTemplate = TempArticle::where('template', 'when_to_change_tires')
            ->where('status', 'draft')
            ->count();
            
        $this->info("🔍 Debug: Artigos por domain: {$articlesByDomain}");
        $this->info("🔍 Debug: Artigos por template: {$articlesByTemplate}");
        
        // Usar o campo que retorna mais resultados
        if ($articlesByTemplate > $articlesByDomain) {
            $allTireArticles = TempArticle::where('template', 'when_to_change_tires')
                ->where('status', 'draft')
                ->get(['slug', 'title', 'domain', 'template', 'vehicle_data', 'seo_data', 'content', 'created_at'])
                ->keyBy('slug');
            $this->info("✅ Usando campo 'template' - encontrados: " . $allTireArticles->count());
        } else {
            $allTireArticles = TempArticle::where('domain', 'when_to_change_tires')
                ->where('status', 'draft')
                ->get(['slug', 'title', 'domain', 'template', 'vehicle_data', 'seo_data', 'content', 'created_at'])
                ->keyBy('slug');
            $this->info("✅ Usando campo 'domain' - encontrados: " . $allTireArticles->count());
        }

        // 2. Buscar todas as correções existentes
        $existingCorrections = ArticleCorrection::whereIn('correction_type', [
            ArticleCorrection::TYPE_TIRE_PRESSURE_FIX,
            ArticleCorrection::TYPE_TITLE_YEAR_FIX
        ])->get(['article_slug', 'correction_type', 'status', 'created_at']);

        // 3. Mapear correções por slug
        $correctionMap = [];
        foreach ($existingCorrections as $correction) {
            $slug = $correction->article_slug;
            if (!isset($correctionMap[$slug])) {
                $correctionMap[$slug] = [
                    'pressure' => false,
                    'title' => false,
                    'total' => 0
                ];
            }
            
            if ($correction->correction_type === ArticleCorrection::TYPE_TIRE_PRESSURE_FIX) {
                $correctionMap[$slug]['pressure'] = true;
            } elseif ($correction->correction_type === ArticleCorrection::TYPE_TITLE_YEAR_FIX) {
                $correctionMap[$slug]['title'] = true;
            }
            $correctionMap[$slug]['total']++;
        }

        // 4. Categorizar artigos
        $categories = [
            'complete' => [],           // Tem ambas as correções
            'partial_pressure_only' => [], // Só tem correção de pressão
            'partial_title_only' => [],    // Só tem correção de título
            'missing_both' => [],          // Não tem nenhuma correção
            'duplicates' => []             // Tem mais de 2 correções
        ];

        foreach ($allTireArticles as $slug => $article) {
            $corrections = $correctionMap[$slug] ?? ['pressure' => false, 'title' => false, 'total' => 0];
            
            if ($corrections['total'] > 2) {
                $categories['duplicates'][] = $slug;
            } elseif ($corrections['pressure'] && $corrections['title']) {
                $categories['complete'][] = $slug;
            } elseif ($corrections['pressure'] && !$corrections['title']) {
                $categories['partial_pressure_only'][] = $slug;
            } elseif (!$corrections['pressure'] && $corrections['title']) {
                $categories['partial_title_only'][] = $slug;
            } else {
                $categories['missing_both'][] = $slug;
            }
        }

        return [
            'all_tire_articles' => $allTireArticles,
            'existing_corrections' => $existingCorrections,
            'correction_map' => $correctionMap,
            'categories' => $categories,
            'statistics' => [
                'total_articles' => $allTireArticles->count(),
                'expected_corrections' => $allTireArticles->count() * 2,
                'actual_corrections' => $existingCorrections->count(),
                'gap' => ($allTireArticles->count() * 2) - $existingCorrections->count()
            ]
        ];
    }

    /**
     * 📋 Exibir análise
     */
    private function displayAnalysis(array $analysis): void
    {
        $stats = $analysis['statistics'];
        $categories = $analysis['categories'];

        $this->table(['Métrica', 'Valor', 'Status'], [
            ['Total de artigos', $stats['total_articles'], '📊'],
            ['Correções esperadas', $stats['expected_corrections'], '🎯'],
            ['Correções encontradas', $stats['actual_corrections'], 
                $stats['actual_corrections'] == $stats['expected_corrections'] ? '✅' : '❌'],
            ['Gap total', $stats['gap'], $stats['gap'] == 0 ? '✅' : '🚨']
        ]);

        $this->line('');
        $this->info('📊 Categorização dos artigos:');
        $this->table(['Categoria', 'Quantidade', 'Ação Necessária'], [
            ['✅ Completos (2 correções)', count($categories['complete']), 'Nenhuma'],
            ['🔧 Só pressão', count($categories['partial_pressure_only']), 'Criar correção de título'],
            ['📝 Só título', count($categories['partial_title_only']), 'Criar correção de pressão'],
            ['❌ Sem correções', count($categories['missing_both']), 'Criar ambas as correções'],
            ['⚠️ Com duplicatas', count($categories['duplicates']), 'Limpar duplicatas']
        ]);

        // Cálculo de correções necessárias
        $correctionsToCreate = 
            count($categories['partial_pressure_only']) +  // Precisam de título
            count($categories['partial_title_only']) +     // Precisam de pressão
            (count($categories['missing_both']) * 2);      // Precisam de ambas

        $this->line('');
        $this->info("🎯 Correções a serem criadas: {$correctionsToCreate}");
        $this->info("🎯 Total final esperado: " . ($stats['actual_corrections'] + $correctionsToCreate));

        if ($correctionsToCreate + $stats['actual_corrections'] != $stats['expected_corrections']) {
            $this->warn('⚠️ Atenção: Cálculo não confere! Verificar duplicatas.');
        }
    }

    /**
     * 🚀 Executar correção forçada
     */
    private function executeForceComplete(array $analysis): array
    {
        $batchSize = (int) $this->option('batch-size');
        $categories = $analysis['categories'];
        $allArticles = $analysis['all_tire_articles'];

        $results = [
            'pressure_created' => 0,
            'title_created' => 0,
            'duplicates_removed' => 0,
            'errors' => []
        ];

        // 1. Limpar duplicatas primeiro
        if (!empty($categories['duplicates'])) {
            $this->info('🧹 Limpando duplicatas...');
            $results['duplicates_removed'] = $this->cleanDuplicates($categories['duplicates']);
        }

        // 2. Criar correções de título para quem só tem pressão
        if (!empty($categories['partial_pressure_only'])) {
            $this->info('📝 Criando correções de título...');
            $results['title_created'] += $this->createMissingCorrections(
                $categories['partial_pressure_only'],
                ArticleCorrection::TYPE_TITLE_YEAR_FIX,
                $allArticles,
                $batchSize
            );
        }

        // 3. Criar correções de pressão para quem só tem título
        if (!empty($categories['partial_title_only'])) {
            $this->info('🔧 Criando correções de pressão...');
            $results['pressure_created'] += $this->createMissingCorrections(
                $categories['partial_title_only'],
                ArticleCorrection::TYPE_TIRE_PRESSURE_FIX,
                $allArticles,
                $batchSize
            );
        }

        // 4. Criar ambas as correções para quem não tem nenhuma
        if (!empty($categories['missing_both'])) {
            $this->info('🎯 Criando ambas as correções para artigos sem nenhuma...');
            
            // Pressão
            $results['pressure_created'] += $this->createMissingCorrections(
                $categories['missing_both'],
                ArticleCorrection::TYPE_TIRE_PRESSURE_FIX,
                $allArticles,
                $batchSize
            );
            
            // Título
            $results['title_created'] += $this->createMissingCorrections(
                $categories['missing_both'],
                ArticleCorrection::TYPE_TITLE_YEAR_FIX,
                $allArticles,
                $batchSize
            );
        }

        return $results;
    }

    /**
     * 📝 Criar correções faltantes em lotes
     */
    private function createMissingCorrections(array $slugs, string $correctionType, $allArticles, int $batchSize): int
    {
        $created = 0;
        $chunks = array_chunk($slugs, $batchSize);
        $totalChunks = count($chunks);

        $progressBar = $this->output->createProgressBar(count($slugs));
        $progressBar->start();

        foreach ($chunks as $chunkIndex => $chunk) {
            $this->info("\n🔄 Processando lote " . ($chunkIndex + 1) . "/{$totalChunks} ({$correctionType})");

            DB::beginTransaction();
            try {
                foreach ($chunk as $slug) {
                    $article = $allArticles->get($slug);
                    
                    if (!$article) {
                        $this->warn("⚠️ Artigo não encontrado: {$slug}");
                        $progressBar->advance();
                        continue;
                    }

                    // Double-check para evitar duplicatas
                    $existing = ArticleCorrection::where('article_slug', $slug)
                        ->where('correction_type', $correctionType)
                        ->exists();

                    if ($existing && $this->option('skip-existing')) {
                        $progressBar->advance();
                        continue;
                    }

                    if ($existing) {
                        // Se já existe e não está pulando, deletar primeiro
                        ArticleCorrection::where('article_slug', $slug)
                            ->where('correction_type', $correctionType)
                            ->delete();
                    }

                    $originalData = $this->prepareOriginalData($article, $correctionType);
                    
                    $correction = ArticleCorrection::create([
                        'article_slug' => $slug,
                        'correction_type' => $correctionType,
                        'original_data' => $originalData,
                        'description' => 'Correção criada via force-complete-corrections',
                        'status' => ArticleCorrection::STATUS_PENDING,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    if ($correction) {
                        $created++;
                    }

                    $progressBar->advance();
                }

                DB::commit();
                
                // Pausa pequena entre lotes para não sobrecarregar
                if ($chunkIndex < $totalChunks - 1) {
                    usleep(100000); // 0.1 segundo
                }

            } catch (\Exception $e) {
                DB::rollback();
                $this->error("❌ Erro no lote " . ($chunkIndex + 1) . ": " . $e->getMessage());
                Log::error("Erro ao criar correções em lote", [
                    'chunk_index' => $chunkIndex,
                    'correction_type' => $correctionType,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $progressBar->finish();
        $this->line('');

        return $created;
    }

    /**
     * 🧹 Limpar duplicatas
     */
    private function cleanDuplicates(array $duplicatedSlugs): int
    {
        $removed = 0;

        foreach ($duplicatedSlugs as $slug) {
            try {
                // Para cada tipo de correção, manter apenas a mais recente
                foreach ([ArticleCorrection::TYPE_TIRE_PRESSURE_FIX, ArticleCorrection::TYPE_TITLE_YEAR_FIX] as $type) {
                    $corrections = ArticleCorrection::where('article_slug', $slug)
                        ->where('correction_type', $type)
                        ->orderBy('created_at', 'desc')
                        ->get();

                    if ($corrections->count() > 1) {
                        // Manter o primeiro (mais recente), deletar os outros
                        $toDelete = $corrections->skip(1);
                        foreach ($toDelete as $correction) {
                            $correction->delete();
                            $removed++;
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->error("❌ Erro ao limpar duplicatas para {$slug}: " . $e->getMessage());
            }
        }

        return $removed;
    }

    /**
     * 📋 Preparar dados originais
     */
    private function prepareOriginalData($article, string $correctionType): array
    {
        $baseData = [
            'title' => $article->title,
            'domain' => $article->domain,
            'template' => $article->template ?? 'when_to_change_tires',
            'vehicle_data' => $article->vehicle_data ?? [],
            'force_complete_correction' => true,
            'batch_created_at' => now()->toISOString(),
            'command_executed' => 'force-complete-corrections'
        ];

        if ($correctionType === ArticleCorrection::TYPE_TIRE_PRESSURE_FIX) {
            return array_merge($baseData, [
                'current_content' => [
                    'introducao' => $article->content['introducao'] ?? '',
                    'consideracoes_finais' => $article->content['consideracoes_finais'] ?? ''
                ],
                'current_pressures' => [
                    'empty_front' => $article->vehicle_data['pressures']['empty_front'] ?? 0,
                    'empty_rear' => $article->vehicle_data['pressures']['empty_rear'] ?? 0,
                    'loaded_front' => $article->vehicle_data['pressures']['loaded_front'] ?? 0,
                    'loaded_rear' => $article->vehicle_data['pressures']['loaded_rear'] ?? 0,
                    'max_front' => $article->vehicle_data['pressures']['max_front'] ?? 0,
                    'max_rear' => $article->vehicle_data['pressures']['max_rear'] ?? 0,
                    'spare' => $article->vehicle_data['pressures']['spare'] ?? 0,
                    'pressure_display' => $article->vehicle_data['pressure_display'] ?? '',
                    'pressure_loaded_display' => $article->vehicle_data['pressure_loaded_display'] ?? ''
                ]
            ]);
        }

        if ($correctionType === ArticleCorrection::TYPE_TITLE_YEAR_FIX) {
            return array_merge($baseData, [
                'current_seo' => [
                    'page_title' => $article->seo_data['page_title'] ?? '',
                    'meta_description' => $article->seo_data['meta_description'] ?? ''
                ],
                'current_content' => [
                    'perguntas_frequentes' => $article->content['perguntas_frequentes'] ?? []
                ]
            ]);
        }

        return $baseData;
    }

    /**
     * 📊 Exibir resultados finais
     */
    private function displayResults(array $results): void
    {
        $this->line('');
        $this->info('🎉 RESULTADOS DA CORREÇÃO FORÇADA');
        $this->line('═══════════════════════════════════');

        $this->table(['Operação', 'Quantidade', 'Status'], [
            ['Correções de pressão criadas', $results['pressure_created'], 
                $results['pressure_created'] > 0 ? '✅' : '➖'],
            ['Correções de título criadas', $results['title_created'], 
                $results['title_created'] > 0 ? '✅' : '➖'],
            ['Duplicatas removidas', $results['duplicates_removed'], 
                $results['duplicates_removed'] > 0 ? '🧹' : '➖'],
            ['Total de correções criadas', $results['pressure_created'] + $results['title_created'], '📊']
        ]);

        // Verificação final
        $this->line('');
        $this->info('🔍 Verificação final...');
        
        $finalStats = $this->getFinalStats();
        $this->table(['Métrica', 'Valor', 'Meta', 'Status'], [
            ['Artigos de pneus', $finalStats['total_articles'], '969', 
                $finalStats['total_articles'] >= 969 ? '✅' : '⚠️'],
            ['Correções totais', $finalStats['total_corrections'], '1938', 
                $finalStats['total_corrections'] >= 1938 ? '✅' : '❌'],
            ['Correções de pressão', $finalStats['pressure_corrections'], '969', 
                $finalStats['pressure_corrections'] >= 969 ? '✅' : '❌'],
            ['Correções de título', $finalStats['title_corrections'], '969', 
                $finalStats['title_corrections'] >= 969 ? '✅' : '❌']
        ]);

        if ($finalStats['total_corrections'] >= 1938) {
            $this->info('🎉 META ATINGIDA! Todos os artigos agora têm suas correções completas.');
        } else {
            $gap = 1938 - $finalStats['total_corrections'];
            $this->warn("⚠️ Ainda faltam {$gap} correções para atingir a meta de 1.938");
            
            $this->line('');
            $this->info('💡 Próximos passos recomendados:');
            $this->info('1. Executar: php artisan diagnose-correction-gaps --fix');
            $this->info('2. Verificar logs de erro para identificar problemas');
            $this->info('3. Executar novamente este comando se necessário');
        }

        // Log dos resultados
        Log::info('Force complete corrections executado', [
            'pressure_created' => $results['pressure_created'],
            'title_created' => $results['title_created'],
            'duplicates_removed' => $results['duplicates_removed'],
            'final_stats' => $finalStats,
            'meta_achieved' => $finalStats['total_corrections'] >= 1938
        ]);
    }

    /**
     * 📊 Obter estatísticas finais
     */
    private function getFinalStats(): array
    {
        $totalArticles = TempArticle::where('domain', 'when_to_change_tires')
            ->where('status', 'draft')
            ->count();

        $pressureCorrections = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
            ->count();

        $titleCorrections = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
            ->count();

        return [
            'total_articles' => $totalArticles,
            'pressure_corrections' => $pressureCorrections,
            'title_corrections' => $titleCorrections,
            'total_corrections' => $pressureCorrections + $titleCorrections
        ];
    }
}