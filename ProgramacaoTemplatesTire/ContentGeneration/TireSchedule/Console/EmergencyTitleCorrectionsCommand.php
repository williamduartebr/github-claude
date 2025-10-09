<?php

namespace Src\ContentGeneration\TireSchedule\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Src\ContentGeneration\TireSchedule\Infrastructure\Eloquent\TireArticleCorrection as ArticleCorrection;

class EmergencyTitleCorrectionsCommand extends Command
{
    /**
     * Nome e assinatura do comando.
     */
    protected $signature = 'emergency-title-corrections 
                           {--batch-size=100 : Tamanho do lote}
                           {--dry-run : Apenas mostrar o que seria feito}
                           {--force : Força execução}';

    /**
     * Descrição do comando.
     */
    protected $description = 'Correção emergencial: cria correções de título para os 913 artigos que só têm pressão';

    /**
     * Execute o comando.
     */
    public function handle()
    {
        $this->info('🚨 CORREÇÃO EMERGENCIAL - Criando Correções de Título Faltantes');
        $this->line('');

        // Baseado no diagnóstico: 913 artigos têm só pressão, precisam de título
        $articlesNeedingTitle = $this->findArticlesNeedingTitleCorrection();
        
        $this->info("🎯 Encontrados {$articlesNeedingTitle->count()} artigos precisando de correção de título");
        
        if ($articlesNeedingTitle->isEmpty()) {
            $this->info('✅ Todos os artigos já têm correções de título!');
            return Command::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->showDryRunPreview($articlesNeedingTitle);
            return Command::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm("Criar correções de título para {$articlesNeedingTitle->count()} artigos?")) {
            $this->info('❌ Operação cancelada.');
            return Command::SUCCESS;
        }

        $results = $this->createTitleCorrections($articlesNeedingTitle);
        $this->displayResults($results, $articlesNeedingTitle->count());

        return Command::SUCCESS;
    }

    /**
     * 🔍 Encontrar artigos que só têm correção de pressão
     */
    private function findArticlesNeedingTitleCorrection()
    {
        // Buscar slugs que têm correção de pressão
        $slugsWithPressure = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
            ->distinct('article_slug')
            ->pluck('article_slug')
            ->toArray();

        // Buscar slugs que têm correção de título
        $slugsWithTitle = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
            ->distinct('article_slug')
            ->pluck('article_slug')
            ->toArray();

        // Slugs que têm pressão mas não têm título
        $slugsNeedingTitle = array_diff($slugsWithPressure, $slugsWithTitle);

        $this->info("🔍 Debug: Slugs com pressão: " . count($slugsWithPressure));
        $this->info("🔍 Debug: Slugs com título: " . count($slugsWithTitle));
        $this->info("🔍 Debug: Slugs precisando título: " . count($slugsNeedingTitle));

        // Buscar os artigos correspondentes - TESTANDO DIFERENTES QUERIES
        $foundBySlug = TempArticle::whereIn('slug', $slugsNeedingTitle)->count();
        $foundByDomain = TempArticle::where('domain', 'when_to_change_tires')
            ->whereIn('slug', $slugsNeedingTitle)->count();
        $foundByTemplate = TempArticle::where('template', 'when_to_change_tires')
            ->whereIn('slug', $slugsNeedingTitle)->count();

        $this->info("🔍 Debug: Encontrados por slug: {$foundBySlug}");
        $this->info("🔍 Debug: Encontrados por domain: {$foundByDomain}");
        $this->info("🔍 Debug: Encontrados por template: {$foundByTemplate}");

        // Usar a query que retorna mais resultados
        if ($foundByTemplate > $foundByDomain && $foundByTemplate > $foundBySlug) {
            $this->info("✅ Usando query por template");
            return TempArticle::where('template', 'when_to_change_tires')
                ->whereIn('slug', $slugsNeedingTitle)
                ->get(['slug', 'title', 'domain', 'template', 'vehicle_data', 'seo_data', 'content']);
        } elseif ($foundByDomain > $foundBySlug) {
            $this->info("✅ Usando query por domain");
            return TempArticle::where('domain', 'when_to_change_tires')
                ->whereIn('slug', $slugsNeedingTitle)
                ->get(['slug', 'title', 'domain', 'template', 'vehicle_data', 'seo_data', 'content']);
        } else {
            $this->info("✅ Usando query apenas por slug");
            return TempArticle::whereIn('slug', $slugsNeedingTitle)
                ->get(['slug', 'title', 'domain', 'template', 'vehicle_data', 'seo_data', 'content']);
        }
    }

    /**
     * 👀 Mostrar preview do dry-run
     */
    private function showDryRunPreview($articles)
    {
        $this->info('🔍 [DRY RUN] Correções que seriam criadas:');
        
        $previewData = [];
        foreach ($articles->take(15) as $article) {
            $vehicleName = $article->vehicle_data['vehicle_name'] ?? 'N/A';
            $hasPlaceholders = $this->hasPlaceholders($article);
            
            $previewData[] = [
                substr($article->slug, 0, 50) . '...',
                $vehicleName,
                $hasPlaceholders ? '🚨 Sim' : '✅ Não'
            ];
        }

        $this->table(['Slug', 'Veículo', 'Tem Placeholders N/A'], $previewData);
        
        if ($articles->count() > 15) {
            $remaining = $articles->count() - 15;
            $this->info("... e mais {$remaining} artigos");
        }

        // Estatísticas do que seria corrigido
        $withPlaceholders = $articles->filter(function ($article) {
            return $this->hasPlaceholders($article);
        })->count();

        $this->line('');
        $this->info('📊 Resumo do que seria corrigido:');
        $this->info("• Total de correções a criar: {$articles->count()}");
        $this->info("• Artigos com placeholders N/A: {$withPlaceholders}");
        $this->info("• Artigos que só precisam de otimização: " . ($articles->count() - $withPlaceholders));
    }

    /**
     * 📝 Criar correções de título em lotes
     */
    private function createTitleCorrections($articles)
    {
        $batchSize = (int) $this->option('batch-size');
        $chunks = $articles->chunk($batchSize);
        
        $results = [
            'created' => 0,
            'errors' => 0,
            'skipped' => 0
        ];

        $progressBar = $this->output->createProgressBar($articles->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('Iniciando...');
        $progressBar->start();

        foreach ($chunks as $chunkIndex => $chunk) {
            $progressBar->setMessage("Lote " . ($chunkIndex + 1) . "/" . $chunks->count());

            foreach ($chunk as $article) {
                try {
                    // Verificar se já existe (double-check)
                    $existing = ArticleCorrection::where('article_slug', $article->slug)
                        ->where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
                        ->exists();

                    if ($existing) {
                        $results['skipped']++;
                        $progressBar->advance();
                        continue;
                    }

                    // Preparar dados para correção de título
                    $originalData = $this->prepareTitleCorrectionData($article);

                    // Criar correção
                    $correction = ArticleCorrection::create([
                        'article_slug' => $article->slug,
                        'correction_type' => ArticleCorrection::TYPE_TITLE_YEAR_FIX,
                        'original_data' => $originalData,
                        'description' => 'Correção emergencial de título criada para completar par com pressão',
                        'status' => ArticleCorrection::STATUS_PENDING,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    if ($correction) {
                        $results['created']++;
                    } else {
                        $results['errors']++;
                    }

                } catch (\Exception $e) {
                    $results['errors']++;
                    Log::error("Erro ao criar correção de título emergencial", [
                        'slug' => $article->slug,
                        'error' => $e->getMessage()
                    ]);
                }

                $progressBar->advance();
            }

            // Pausa pequena entre lotes
            if ($chunkIndex < $chunks->count() - 1) {
                usleep(50000); // 0.05 segundos
            }
        }

        $progressBar->finish();
        $this->line('');

        return $results;
    }

    /**
     * 📋 Preparar dados para correção de título
     */
    private function prepareTitleCorrectionData($article): array
    {
        return [
            'title' => $article->title,
            'domain' => $article->domain,
            'template' => $article->template ?? 'when_to_change_tires',
            'vehicle_data' => $article->vehicle_data ?? [],
            'current_seo' => [
                'page_title' => $article->seo_data['page_title'] ?? '',
                'meta_description' => $article->seo_data['meta_description'] ?? ''
            ],
            'current_content' => [
                'perguntas_frequentes' => $article->content['perguntas_frequentes'] ?? []
            ],
            'emergency_creation' => true,
            'created_via' => 'emergency-title-corrections',
            'emergency_timestamp' => now()->toISOString(),
            'reason' => 'Completar par com correção de pressão existente'
        ];
    }

    /**
     * 🔍 Verificar se artigo tem placeholders
     */
    private function hasPlaceholders($article): bool
    {
        $seoData = $article->seo_data ?? [];
        $content = $article->content ?? [];

        // Verificar title e meta description
        if (strpos($seoData['page_title'] ?? '', 'N/A N/A N/A') !== false ||
            strpos($seoData['meta_description'] ?? '', 'N/A N/A N/A') !== false) {
            return true;
        }

        // Verificar FAQs
        $faqs = $content['perguntas_frequentes'] ?? [];
        if (is_array($faqs)) {
            foreach ($faqs as $faq) {
                if (strpos($faq['pergunta'] ?? '', 'N/A N/A N/A') !== false ||
                    strpos($faq['resposta'] ?? '', 'N/A N/A N/A') !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 📊 Exibir resultados
     */
    private function displayResults(array $results, int $totalArticles)
    {
        $this->line('');
        $this->info('🎉 RESULTADO DA CORREÇÃO EMERGENCIAL');
        $this->line('═══════════════════════════════════════');

        $this->table(['Métrica', 'Valor', 'Status'], [
            ['Artigos processados', $totalArticles, '📊'],
            ['Correções criadas', $results['created'], 
                $results['created'] > 0 ? '✅' : '❌'],
            ['Pulados (já existiam)', $results['skipped'], 
                $results['skipped'] > 0 ? '⚠️' : '➖'],
            ['Erros', $results['errors'], 
                $results['errors'] == 0 ? '✅' : '❌']
        ]);

        // Verificação final
        $this->line('');
        $this->info('🔍 Verificação pós-correção...');
        
        $finalCount = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)->count();
        $expectedCount = 969; // Todos os artigos deveriam ter correção de título
        
        $this->info("📊 Correções de título após correção: {$finalCount}");
        $this->info("🎯 Meta esperada: {$expectedCount}");
        
        if ($finalCount >= $expectedCount) {
            $this->info('🎉 META ATINGIDA! Todos os artigos agora têm correção de título.');
        } else {
            $gap = $expectedCount - $finalCount;
            $this->warn("⚠️ Ainda faltam {$gap} correções de título para atingir a meta");
        }

        // Verificar meta total (1938)
        $totalCorrections = ArticleCorrection::whereIn('correction_type', [
            ArticleCorrection::TYPE_TIRE_PRESSURE_FIX,
            ArticleCorrection::TYPE_TITLE_YEAR_FIX
        ])->count();

        $this->line('');
        $this->info("🎯 TOTAL GERAL DE CORREÇÕES: {$totalCorrections}/1938");
        
        if ($totalCorrections >= 1938) {
            $this->info('🏆 MISSÃO CUMPRIDA! Meta de 1.938 correções atingida!');
        } else {
            $remaining = 1938 - $totalCorrections;
            $this->warn("⚠️ Faltam {$remaining} correções para atingir 1.938 total");
        }

        // Log dos resultados
        Log::info('Correção emergencial de títulos executada', [
            'processed' => $totalArticles,
            'created' => $results['created'],
            'skipped' => $results['skipped'],
            'errors' => $results['errors'],
            'final_title_count' => $finalCount,
            'total_corrections' => $totalCorrections
        ]);
    }
}