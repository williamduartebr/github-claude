<?php

namespace Src\ContentGeneration\TireSchedule\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Src\ContentGeneration\TireSchedule\Infrastructure\Eloquent\TireArticleCorrection as ArticleCorrection;

class VerifyCorrectionCreationCommand extends Command
{
    /**
     * Nome e assinatura do comando.
     */
    protected $signature = 'verify-correction-creation 
                           {--test-create : Testar criação de correções em uma amostra}
                           {--analyze-existing : Analisar padrões nas correções existentes}
                           {--check-services : Verificar se os services estão criando correções corretamente}
                           {--sample-size=10 : Tamanho da amostra para testes}
                           {--force : Força execução}';

    /**
     * Descrição do comando.
     */
    protected $description = 'Verifica e testa o processo de criação de correções para identificar onde está falhando';

    /**
     * Execute o comando.
     */
    public function handle()
    {
        $this->info('🔬 Verificação do Processo de Criação de Correções');
        $this->line('');

        if ($this->option('analyze-existing')) {
            $this->analyzeExistingCorrections();
        }

        if ($this->option('check-services')) {
            $this->checkServices();
        }

        if ($this->option('test-create')) {
            $this->testCorrectionCreation();
        }

        if (!$this->option('analyze-existing') && !$this->option('check-services') && !$this->option('test-create')) {
            // Executar todas as verificações
            $this->analyzeExistingCorrections();
            $this->line('');
            $this->checkServices();
            $this->line('');
            $this->testCorrectionCreation();
        }

        return Command::SUCCESS;
    }

    /**
     * 📊 Analisar correções existentes para identificar padrões
     */
    private function analyzeExistingCorrections()
    {
        $this->info('📊 Analisando correções existentes...');

        // Buscar todas as correções
        $allCorrections = ArticleCorrection::whereIn('correction_type', [
            ArticleCorrection::TYPE_TIRE_PRESSURE_FIX,
            ArticleCorrection::TYPE_TITLE_YEAR_FIX
        ])->get();

        // Análise por tipo
        $byType = $allCorrections->groupBy('correction_type');
        $pressureCorrections = $byType->get(ArticleCorrection::TYPE_TIRE_PRESSURE_FIX, collect());
        $titleCorrections = $byType->get(ArticleCorrection::TYPE_TITLE_YEAR_FIX, collect());

        $this->table(['Tipo de Correção', 'Quantidade', 'Mais Antiga', 'Mais Recente'], [
            [
                'Pressão (tire_pressure_fix)',
                $pressureCorrections->count(),
                $pressureCorrections->min('created_at') ?? 'N/A',
                $pressureCorrections->max('created_at') ?? 'N/A'
            ],
            [
                'Título (title_year_fix)',
                $titleCorrections->count(),
                $titleCorrections->min('created_at') ?? 'N/A',
                $titleCorrections->max('created_at') ?? 'N/A'
            ]
        ]);

        // Análise temporal - verificar se há períodos onde um tipo não foi criado
        $this->line('');
        $this->info('📅 Análise temporal das criações:');

        $pressureByDate = $pressureCorrections->groupBy(function ($correction) {
            return $correction->created_at->format('Y-m-d');
        });

        $titleByDate = $titleCorrections->groupBy(function ($correction) {
            return $correction->created_at->format('Y-m-d');
        });

        $allDates = $pressureByDate->keys()->merge($titleByDate->keys())->unique()->sort()->take(10);

        $temporalData = [];
        foreach ($allDates as $date) {
            $temporalData[] = [
                $date,
                $pressureByDate->get($date, collect())->count(),
                $titleByDate->get($date, collect())->count(),
                abs($pressureByDate->get($date, collect())->count() - $titleByDate->get($date, collect())->count())
            ];
        }

        $this->table(['Data', 'Pressão', 'Título', 'Diferença'], $temporalData);

        // Análise de qual service/comando criou as correções
        $this->line('');
        $this->info('🔍 Análise de origem das correções:');

        $pressureOrigins = $pressureCorrections->map(function ($correction) {
            return $correction->original_data['created_via'] ?? 
                   $correction->description ?? 
                   'origem_desconhecida';
        })->countBy();

        $titleOrigins = $titleCorrections->map(function ($correction) {
            return $correction->original_data['created_via'] ?? 
                   $correction->description ?? 
                   'origem_desconhecida';
        })->countBy();

        $this->info('📈 Origens das correções de pressão:');
        foreach ($pressureOrigins as $origin => $count) {
            $this->line("  • {$origin}: {$count}");
        }

        $this->info('📈 Origens das correções de título:');
        foreach ($titleOrigins as $origin => $count) {
            $this->line("  • {$origin}: {$count}");
        }

        // Buscar padrões nos slugs que têm apenas um tipo de correção
        $this->line('');
        $this->info('🔍 Analisando artigos com correções incompletas...');

        $slugsWithCorrections = $allCorrections->groupBy('article_slug');
        $incompleteArticles = [];

        foreach ($slugsWithCorrections as $slug => $corrections) {
            $types = $corrections->pluck('correction_type')->unique();
            
            if ($types->count() == 1) {
                $incompleteArticles[] = [
                    'slug' => substr($slug, 0, 50) . '...',
                    'tipo_existente' => $types->first(),
                    'tipo_faltante' => $types->first() == ArticleCorrection::TYPE_TIRE_PRESSURE_FIX ? 
                        'title_year_fix' : 'tire_pressure_fix',
                    'created_at' => $corrections->first()->created_at->format('Y-m-d H:i')
                ];
            }
        }

        if (!empty($incompleteArticles)) {
            $this->warn("⚠️ Encontrados " . count($incompleteArticles) . " artigos com correções incompletas:");
            $this->table(['Slug', 'Tipo Existente', 'Tipo Faltante', 'Criado em'], 
                array_slice($incompleteArticles, 0, 10));
            
            if (count($incompleteArticles) > 10) {
                $this->info("... e mais " . (count($incompleteArticles) - 10) . " artigos");
            }
        }
    }

    /**
     * 🔧 Verificar se os services estão funcionando corretamente
     */
    private function checkServices()
    {
        $this->info('🔧 Verificando funcionamento dos services...');

        // 1. Verificar TireCorrectionOrchestrator
        try {
            $orchestrator = app(\Src\ContentGeneration\TireSchedule\Infrastructure\Services\TireCorrectionOrchestrator::class);
            $this->info('✅ TireCorrectionOrchestrator instanciado com sucesso');

            // Testar métodos disponíveis do orchestrator
            $stats = $orchestrator->getConsolidatedStats();
            $this->info("✅ getConsolidatedStats funcionando: " . count($stats) . " keys retornadas");

        } catch (\Exception $e) {
            $this->error("❌ Erro no TireCorrectionOrchestrator: " . $e->getMessage());
        }

        // 2. Verificar TireCorrectionService (legado)
        try {
            $tireService = app(\Src\ContentGeneration\TireSchedule\Infrastructure\Services\TireCorrectionService::class);
            $this->info('✅ TireCorrectionService instanciado com sucesso');

            $slugs = $tireService->getAllTireArticleSlugs(5);
            $this->info("✅ TireCorrectionService->getAllTireArticleSlugs retornou " . count($slugs) . " slugs");

        } catch (\Exception $e) {
            $this->error("❌ Erro no TireCorrectionService: " . $e->getMessage());
        }

        // 3. Verificar TitleYearCorrectionService
        try {
            $titleService = app(\Src\ContentGeneration\TireSchedule\Infrastructure\Services\TitleYearCorrectionService::class);
            $this->info('✅ TitleYearCorrectionService instanciado com sucesso');

            $slugs = $titleService->getAllTireArticleSlugs(5);
            $this->info("✅ TitleYearCorrectionService->getAllTireArticleSlugs retornou " . count($slugs) . " slugs");

        } catch (\Exception $e) {
            $this->error("❌ Erro no TitleYearCorrectionService: " . $e->getMessage());
        }

        // 4. Verificar TireDataValidationService
        try {
            $validationService = app(\Src\ContentGeneration\TireSchedule\Infrastructure\Services\MicroServices\TireDataValidationService::class);
            $this->info('✅ TireDataValidationService instanciado com sucesso');

            // Testar com um artigo real
            $sampleArticle = TempArticle::where('domain', 'when_to_change_tires')
                ->where('status', 'draft')
                ->first();

            if ($sampleArticle) {
                $validation = $validationService->validateArticleIntegrity($sampleArticle);
                $this->info("✅ Validação de amostra: " . 
                    ($validation['needs_any_correction'] ? 'Precisa correção' : 'OK'));
            }

        } catch (\Exception $e) {
            $this->error("❌ Erro no TireDataValidationService: " . $e->getMessage());
        }

        // 5. Verificar ArticleCorrection model
        try {
            $testCorrection = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->first();
            
            if ($testCorrection) {
                $this->info('✅ ArticleCorrection model funcionando');
                $this->info("  • Exemplo de correção: {$testCorrection->article_slug}");
                $this->info("  • Status: {$testCorrection->status}");
            } else {
                $this->warn('⚠️ Nenhuma correção de pressão encontrada para teste');
            }

        } catch (\Exception $e) {
            $this->error("❌ Erro no ArticleCorrection model: " . $e->getMessage());
        }
    }

    /**
     * 🧪 Testar criação de correções em uma amostra
     */
    private function testCorrectionCreation()
    {
        $this->info('🧪 Testando criação de correções em amostra...');
        
        $sampleSize = (int) $this->option('sample-size');

        // Buscar artigos que não têm correções
        $articlesWithoutCorrections = $this->findArticlesWithoutCorrections($sampleSize);

        if ($articlesWithoutCorrections->isEmpty()) {
            $this->info('✅ Todos os artigos já possuem correções!');
            return;
        }

        $this->info("🎯 Testando com {$articlesWithoutCorrections->count()} artigos sem correções...");

        $results = [
            'pressure_created' => 0,
            'pressure_failed' => 0,
            'title_created' => 0,
            'title_failed' => 0,
            'errors' => []
        ];

        foreach ($articlesWithoutCorrections as $article) {
            $this->info("📝 Testando: {$article->slug}");

            // Testar criação de correção de pressão
            try {
                $pressureResult = $this->testCreatePressureCorrection($article);
                if ($pressureResult['success']) {
                    $results['pressure_created']++;
                    $this->info("  ✅ Pressão: criada com sucesso");
                } else {
                    $results['pressure_failed']++;
                    $this->error("  ❌ Pressão: " . $pressureResult['error']);
                    $results['errors'][] = "Pressão {$article->slug}: " . $pressureResult['error'];
                }
            } catch (\Exception $e) {
                $results['pressure_failed']++;
                $this->error("  ❌ Pressão: Exceção - " . $e->getMessage());
                $results['errors'][] = "Pressão {$article->slug}: " . $e->getMessage();
            }

            // Testar criação de correção de título
            try {
                $titleResult = $this->testCreateTitleCorrection($article);
                if ($titleResult['success']) {
                    $results['title_created']++;
                    $this->info("  ✅ Título: criada com sucesso");
                } else {
                    $results['title_failed']++;
                    $this->error("  ❌ Título: " . $titleResult['error']);
                    $results['errors'][] = "Título {$article->slug}: " . $titleResult['error'];
                }
            } catch (\Exception $e) {
                $results['title_failed']++;
                $this->error("  ❌ Título: Exceção - " . $e->getMessage());
                $results['errors'][] = "Título {$article->slug}: " . $e->getMessage();
            }

            $this->line('');
        }

        // Exibir resultados
        $this->info('📊 Resultados do teste de criação:');
        $this->table(['Tipo', 'Criadas', 'Falharam', 'Taxa Sucesso'], [
            [
                'Pressão',
                $results['pressure_created'],
                $results['pressure_failed'],
                $this->calculateSuccessRate($results['pressure_created'], $results['pressure_failed'])
            ],
            [
                'Título',
                $results['title_created'],
                $results['title_failed'],
                $this->calculateSuccessRate($results['title_created'], $results['title_failed'])
            ]
        ]);

        // Exibir erros se houver
        if (!empty($results['errors'])) {
            $this->line('');
            $this->warn('❌ Erros encontrados:');
            foreach (array_slice($results['errors'], 0, 5) as $error) {
                $this->error("  • {$error}");
            }
            
            if (count($results['errors']) > 5) {
                $this->info("  ... e mais " . (count($results['errors']) - 5) . " erros");
            }
        }

        // Análise dos resultados
        $this->line('');
        $this->analyzeTestResults($results);
    }

    /**
     * 🔍 Buscar artigos sem correções
     */
    private function findArticlesWithoutCorrections(int $limit)
    {
        // Buscar slugs que já têm correções
        $slugsWithCorrections = ArticleCorrection::whereIn('correction_type', [
            ArticleCorrection::TYPE_TIRE_PRESSURE_FIX,
            ArticleCorrection::TYPE_TITLE_YEAR_FIX
        ])->distinct('article_slug')->pluck('article_slug')->toArray();

        // Buscar artigos que não estão na lista
        return TempArticle::where('domain', 'when_to_change_tires')
            ->where('status', 'draft')
            ->whereNotIn('slug', $slugsWithCorrections)
            ->limit($limit)
            ->get();
    }

    /**
     * 🧪 Testar criação de correção de pressão
     */
    private function testCreatePressureCorrection($article): array
    {
        try {
            // Verificar se já existe
            $existing = ArticleCorrection::where('article_slug', $article->slug)
                ->where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->exists();

            if ($existing) {
                return ['success' => false, 'error' => 'Correção já existe'];
            }

            // Preparar dados originais
            $originalData = [
                'title' => $article->title,
                'domain' => $article->domain,
                'template' => $article->template ?? 'when_to_change_tires',
                'vehicle_data' => $article->vehicle_data ?? [],
                'current_content' => [
                    'introducao' => $article->content['introducao'] ?? '',
                    'consideracoes_finais' => $article->content['consideracoes_finais'] ?? ''
                ],
                'current_pressures' => $article->vehicle_data['pressures'] ?? [],
                'test_creation' => true,
                'test_timestamp' => now()->toISOString()
            ];

            // Criar correção
            $correction = ArticleCorrection::createCorrection(
                $article->slug,
                ArticleCorrection::TYPE_TIRE_PRESSURE_FIX,
                $originalData,
                'Correção de teste criada via verificação'
            );

            if ($correction) {
                return ['success' => true, 'correction_id' => $correction->_id];
            } else {
                return ['success' => false, 'error' => 'createCorrection retornou null'];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * 🧪 Testar criação de correção de título
     */
    private function testCreateTitleCorrection($article): array
    {
        try {
            // Verificar se já existe
            $existing = ArticleCorrection::where('article_slug', $article->slug)
                ->where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
                ->exists();

            if ($existing) {
                return ['success' => false, 'error' => 'Correção já existe'];
            }

            // Preparar dados originais
            $originalData = [
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
                'test_creation' => true,
                'test_timestamp' => now()->toISOString()
            ];

            // Criar correção
            $correction = ArticleCorrection::createCorrection(
                $article->slug,
                ArticleCorrection::TYPE_TITLE_YEAR_FIX,
                $originalData,
                'Correção de teste criada via verificação'
            );

            if ($correction) {
                return ['success' => true, 'correction_id' => $correction->_id];
            } else {
                return ['success' => false, 'error' => 'createCorrection retornou null'];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * 📈 Calcular taxa de sucesso
     */
    private function calculateSuccessRate(int $success, int $failed): string
    {
        $total = $success + $failed;
        if ($total == 0) return '0%';
        
        return round(($success / $total) * 100, 1) . '%';
    }

    /**
     * 🔎 Analisar resultados dos testes
     */
    private function analyzeTestResults(array $results)
    {
        $this->info('🔎 Análise dos resultados:');

        $pressureSuccess = $results['pressure_created'];
        $pressureFailed = $results['pressure_failed'];
        $titleSuccess = $results['title_created'];
        $titleFailed = $results['title_failed'];

        // Análise comparativa
        if ($pressureSuccess > 0 && $titleSuccess > 0) {
            $this->info('✅ Ambos os tipos de correção podem ser criados com sucesso');
        } elseif ($pressureSuccess > 0 && $titleSuccess == 0) {
            $this->error('🚨 PROBLEMA: Apenas correções de pressão funcionam, títulos falham sempre');
        } elseif ($pressureSuccess == 0 && $titleSuccess > 0) {
            $this->error('🚨 PROBLEMA: Apenas correções de título funcionam, pressões falham sempre');
        } else {
            $this->error('🚨 PROBLEMA CRÍTICO: Nenhum tipo de correção funciona');
        }

        // Análise de taxa de falhas
        $pressureFailRate = $pressureSuccess + $pressureFailed > 0 ? 
            ($pressureFailed / ($pressureSuccess + $pressureFailed)) * 100 : 0;
        $titleFailRate = $titleSuccess + $titleFailed > 0 ? 
            ($titleFailed / ($titleSuccess + $titleFailed)) * 100 : 0;

        if ($pressureFailRate > 50) {
            $this->warn("⚠️ Alta taxa de falha em correções de pressão: {$pressureFailRate}%");
        }

        if ($titleFailRate > 50) {
            $this->warn("⚠️ Alta taxa de falha em correções de título: {$titleFailRate}%");
        }

        // Recomendações baseadas nos resultados
        $this->line('');
        $this->info('💡 Recomendações:');

        if ($pressureFailed > 0 || $titleFailed > 0) {
            $this->warn('1. Verificar logs de erro para identificar causa raiz das falhas');
            $this->warn('2. Executar diagnose-correction-gaps --fix para corrigir gaps existentes');
        }

        if ($pressureFailRate > $titleFailRate + 20) {
            $this->warn('3. Focar na correção do TireCorrectionService (pressões)');
        } elseif ($titleFailRate > $pressureFailRate + 20) {
            $this->warn('3. Focar na correção do TitleYearCorrectionService (títulos)');
        }

        if ($pressureSuccess + $titleSuccess > 0) {
            $this->info('4. O sistema de criação funciona, o problema pode ser nos comandos de execução');
        }
    }
}