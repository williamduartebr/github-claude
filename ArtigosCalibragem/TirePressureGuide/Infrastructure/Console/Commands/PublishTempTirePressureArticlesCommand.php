<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Src\ContentGeneration\TirePressureGuide\Application\Services\TirePressureGuideApplicationService;

/**
 * Novo Command para publicar na TempArticle (para testes)
 * 
 * OBJETIVO: Testar a estrutura e template antes da publicação final
 * Permite validar se o formato ideal_tire_pressure_car.json está correto
 */
class PublishTempTirePressureArticlesCommand extends Command
{
    protected $signature = 'tire-pressure-guide:publish-temp 
                           {--status=claude_enhanced : Status dos artigos para publicar}
                           {--limit=50 : Número máximo de artigos para publicar}
                           {--filter-make= : Filtrar por marca específica}
                           {--filter-year= : Filtrar por ano específico}
                           {--dry-run : Mostrar o que seria publicado sem persistir}
                           {--confirm : Pular prompt de confirmação}
                           {--overwrite : Sobrescrever artigos existentes na TempArticle}
                           {--validate-structure : Validar estrutura antes de publicar}';

    protected $description = 'Publicar artigos de calibragem na TempArticle para teste da estrutura ideal_tire_pressure_car.json';

    public function handle(TirePressureGuideApplicationService $service): int
    {
        $status = $this->option('status');
        $limit = (int) $this->option('limit');
        $filterMake = $this->option('filter-make');
        $filterYear = $this->option('filter-year');
        $dryRun = $this->option('dry-run');
        $skipConfirm = $this->option('confirm');
        $overwrite = $this->option('overwrite');
        $validateStructure = $this->option('validate-structure');

        $this->info("🧪 TESTE: Publicando artigos na TempArticle - Formato ideal_tire_pressure_car.json");
        $this->line("================================================================================");

        // Mostrar configuração
        $this->info("Configuração:");
        $this->line("  Status fonte: {$status}");
        $this->line("  Limite: {$limit}");
        if ($filterMake) $this->line("  Filtro marca: {$filterMake}");
        if ($filterYear) $this->line("  Filtro ano: {$filterYear}");
        $this->line("  Sobrescrever: " . ($overwrite ? 'Sim' : 'Não'));
        $this->line("  Validar estrutura: " . ($validateStructure ? 'Sim' : 'Não'));

        if ($dryRun) {
            $this->warn("🔍 MODO DRY RUN - Nenhum artigo será publicado");
        }

        $this->newLine();

        // 1. Obter estatísticas atuais
        $stats = $service->getArticleStats();
        $this->info("📊 Estatísticas atuais:");
        $this->line("  Total artigos TirePressureArticle: {$stats['total']}");
        $this->line("  Gerados (Etapa 1): {$stats['generated']}");
        $this->line("  Refinados Claude (Etapa 2): {$stats['claude_enhanced']}");
        $this->line("  Seções completas: {$stats['sections_complete']}");

        // Verificar se há artigos disponíveis
        if ($stats[$status] === 0) {
            $this->warn("❌ Nenhum artigo encontrado com status: {$status}");
            return self::SUCCESS;
        }

        // 2. Obter artigos candidatos com filtros
        $candidates = $this->getCandidateArticles($service, $status, $limit, $filterMake, $filterYear);
        
        if ($candidates->isEmpty()) {
            $this->warn("❌ Nenhum artigo encontrado com os filtros aplicados");
            return self::SUCCESS;
        }

        $candidateCount = $candidates->count();
        $this->info("📋 Artigos candidatos encontrados: {$candidateCount}");

        // Mostrar preview dos candidatos
        $this->showCandidatesPreview($candidates);

        // 3. Validar estrutura se solicitado
        if ($validateStructure) {
            $this->info("🔍 Validando estrutura dos artigos...");
            $validationResults = $this->validateArticlesStructure($candidates);
            $this->showValidationResults($validationResults);
            
            if ($validationResults['critical_errors'] > 0) {
                $this->error("❌ Erros críticos encontrados! Publicação cancelada.");
                return self::FAILURE;
            }
        }

        // 4. Confirmação
        if (!$skipConfirm && !$dryRun) {
            $action = $overwrite ? 'sobrescrever/criar' : 'criar';
            if (!$this->confirm("Deseja {$action} {$candidateCount} artigos na TempArticle para teste?")) {
                $this->info("Publicação cancelada pelo usuário.");
                return self::SUCCESS;
            }
        }

        // 5. Executar publicação
        $this->info("🚀 Iniciando publicação...");
        $progressBar = $this->output->createProgressBar($candidateCount);
        $progressBar->setFormat('verbose');

        $results = $service->publishToTempArticlesWithFilters(
            $status,
            $limit,
            $dryRun,
            $overwrite,
            $filterMake,
            $filterYear,
            function ($current, $total) use ($progressBar) {
                $progressBar->setMaxSteps($total);
                $progressBar->setProgress($current);
            }
        );

        $progressBar->finish();
        $this->newLine(2);

        // 6. Mostrar resultados
        $this->showPublicationResults($results, $dryRun);

        // 7. Executar testes de estrutura se publicado
        if (!$dryRun && $results->published > 0) {
            $this->runStructureTests($service, $results);
        }

        return $results->failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Obter artigos candidatos com filtros
     */
    private function getCandidateArticles($service, $status, $limit, $filterMake, $filterYear)
    {
        return $service->getCandidateArticlesForTesting($status, $limit, $filterMake, $filterYear);
    }

    /**
     * Mostrar preview dos candidatos
     */
    private function showCandidatesPreview($candidates): void
    {
        $this->info("🔍 Preview dos artigos candidatos:");
        $this->newLine();

        $headers = ['Marca', 'Modelo', 'Ano', 'Pressões', 'Score', 'Status Seções'];
        $rows = [];

        foreach ($candidates->take(10) as $article) {
            $pressures = ($article->pressure_light_front ?? 30) . '/' . ($article->pressure_light_rear ?? 28) . ' PSI';
            $sectionsStatus = $this->getSectionsStatusSummary($article);
            
            $rows[] = [
                $article->make ?? 'N/A',
                $article->model ?? 'N/A',
                $article->year ?? 'N/A',
                $pressures,
                number_format($article->content_score ?? 0, 1),
                $sectionsStatus
            ];
        }

        $this->table($headers, $rows);

        if ($candidates->count() > 10) {
            $remaining = $candidates->count() - 10;
            $this->line("... e mais {$remaining} artigos");
        }

        $this->newLine();
    }

    /**
     * Obter resumo do status das seções
     */
    private function getSectionsStatusSummary($article): string
    {
        $sectionsRefined = $article->sections_refined ?? [];
        $totalSections = 6; // intro, pressure_table, how_to_calibrate, middle_content, faq, conclusion
        $refinedCount = count($sectionsRefined);
        
        if ($refinedCount === $totalSections) {
            return "✅ {$refinedCount}/{$totalSections}";
        } elseif ($refinedCount > 0) {
            return "⚠️ {$refinedCount}/{$totalSections}";
        } else {
            return "❌ 0/{$totalSections}";
        }
    }

    /**
     * Validar estrutura dos artigos
     */
    private function validateArticlesStructure($candidates): array
    {
        $results = [
            'total_validated' => 0,
            'structure_valid' => 0,
            'warnings' => 0,
            'critical_errors' => 0,
            'details' => []
        ];

        foreach ($candidates as $article) {
            $validation = $this->validateSingleArticleStructure($article);
            $results['total_validated']++;
            
            if ($validation['is_valid']) {
                $results['structure_valid']++;
            }
            
            $results['warnings'] += count($validation['warnings']);
            $results['critical_errors'] += count($validation['critical_errors']);
            
            if (!empty($validation['warnings']) || !empty($validation['critical_errors'])) {
                $results['details'][] = [
                    'article' => "{$article->make} {$article->model} {$article->year}",
                    'validation' => $validation
                ];
            }
        }

        return $results;
    }

    /**
     * Validar estrutura de um artigo específico
     */
    private function validateSingleArticleStructure($article): array
    {
        $validation = [
            'is_valid' => true,
            'warnings' => [],
            'critical_errors' => []
        ];

        // Verificar dados básicos
        if (empty($article->make)) {
            $validation['critical_errors'][] = 'Marca ausente';
            $validation['is_valid'] = false;
        }

        if (empty($article->model)) {
            $validation['critical_errors'][] = 'Modelo ausente';
            $validation['is_valid'] = false;
        }

        if (empty($article->year)) {
            $validation['warnings'][] = 'Ano ausente';
        }

        // Verificar pressões
        if (empty($article->pressure_light_front)) {
            $validation['critical_errors'][] = 'Pressão dianteira ausente';
            $validation['is_valid'] = false;
        }

        if (empty($article->pressure_light_rear)) {
            $validation['critical_errors'][] = 'Pressão traseira ausente';
            $validation['is_valid'] = false;
        }

        // Verificar conteúdo estruturado
        $content = $article->article_content ?? [];
        
        $requiredSections = ['introducao', 'tabela_pressoes', 'perguntas_frequentes'];
        foreach ($requiredSections as $section) {
            if (empty($content[$section])) {
                $validation['warnings'][] = "Seção '{$section}' ausente ou vazia";
            }
        }

        // Verificar seções refinadas se status for claude_enhanced
        if ($article->generation_status === 'claude_enhanced') {
            $sectionsRefined = $article->sections_refined ?? [];
            if (count($sectionsRefined) < 6) {
                $validation['warnings'][] = 'Nem todas as seções foram refinadas pelo Claude';
            }
        }

        return $validation;
    }

    /**
     * Mostrar resultados da validação
     */
    private function showValidationResults($results): void
    {
        $this->info("📋 Resultados da validação:");
        $this->line("  Total validado: {$results['total_validated']}");
        $this->line("  Estrutura válida: {$results['structure_valid']}");
        $this->line("  Avisos: {$results['warnings']}");
        $this->line("  Erros críticos: {$results['critical_errors']}");

        if (!empty($results['details'])) {
            $this->newLine();
            $this->warn("⚠️ Detalhes dos problemas encontrados:");
            
            foreach (array_slice($results['details'], 0, 5) as $detail) {
                $this->line("  📄 {$detail['article']}:");
                
                foreach ($detail['validation']['critical_errors'] as $error) {
                    $this->line("    ❌ {$error}");
                }
                
                foreach ($detail['validation']['warnings'] as $warning) {
                    $this->line("    ⚠️ {$warning}");
                }
            }
            
            if (count($results['details']) > 5) {
                $remaining = count($results['details']) - 5;
                $this->line("    ... e mais {$remaining} artigos com problemas");
            }
        }

        $this->newLine();
    }

    /**
     * Mostrar resultados da publicação
     */
    private function showPublicationResults($results, $dryRun): void
    {
        if ($dryRun) {
            $this->info("🔍 Resultados da simulação:");
        } else {
            $this->info("✅ Publicação concluída!");
        }

        $this->line("  Artigos publicados: {$results->published}");
        $this->line("  Artigos ignorados: {$results->skipped}");
        $this->line("  Artigos com falha: {$results->failed}");

        if ($results->failed > 0) {
            $this->newLine();
            $this->error("❌ Erros encontrados:");
            foreach (array_slice($results->errors, 0, 5) as $error) {
                $this->line("  - {$error}");
            }

            if (count($results->errors) > 5) {
                $remaining = count($results->errors) - 5;
                $this->line("  ... e mais {$remaining} erros");
            }
        }

        if (!$dryRun && $results->published > 0) {
            $this->newLine();
            $this->info("🎯 Próximos passos:");
            $this->line("  1. Verificar artigos na collection temp_articles");
            $this->line("  2. Testar templates com os novos dados");
            $this->line("  3. Validar estrutura ideal_tire_pressure_car.json");
            $this->line("  4. Executar: php artisan tire-pressure-guide:test-structure");
        }
    }

    /**
     * Executar testes de estrutura pós-publicação
     */
    private function runStructureTests($service, $results): void
    {
        $this->newLine();
        $this->info("🧪 Executando testes de estrutura nos artigos publicados...");

        // Buscar alguns artigos publicados para teste
        $publishedArticles = $service->getRecentlyPublishedTempArticles(5);

        if ($publishedArticles->isEmpty()) {
            $this->warn("⚠️ Nenhum artigo encontrado na TempArticle para teste");
            return;
        }

        $testResults = [];
        foreach ($publishedArticles as $tempArticle) {
            $testResult = $this->testSingleTempArticleStructure($tempArticle);
            $testResults[] = [
                'slug' => $tempArticle->slug,
                'result' => $testResult
            ];
        }

        // Mostrar resultados dos testes
        $this->showStructureTestResults($testResults);
    }

    /**
     * Testar estrutura de um TempArticle
     */
    private function testSingleTempArticleStructure($tempArticle): array
    {
        $content = $tempArticle->content ?? [];
        $result = [
            'compatible_with_vm' => true,
            'required_sections_present' => 0,
            'optional_sections_present' => 0,
            'issues' => []
        ];

        // Seções obrigatórias para IdealTirePressureCarViewModel
        $requiredSections = [
            'introducao',
            'especificacoes_pneus', 
            'tabela_pressoes',
            'conversao_unidades',
            'localizacao_etiqueta',
            'beneficios_calibragem',
            'dicas_manutencao',
            'alertas_importantes',
            'perguntas_frequentes',
            'consideracoes_finais'
        ];

        foreach ($requiredSections as $section) {
            if (!empty($content[$section])) {
                $result['required_sections_present']++;
            } else {
                $result['issues'][] = "Seção obrigatória ausente: {$section}";
                $result['compatible_with_vm'] = false;
            }
        }

        // Verificar estrutura da tabela de pressões
        if (!empty($content['tabela_pressoes'])) {
            if (empty($content['tabela_pressoes']['versoes']) && empty($content['tabela_pressoes']['condicoes_uso'])) {
                $result['issues'][] = 'Tabela de pressões sem versões ou condições de uso';
                $result['compatible_with_vm'] = false;
            }
        }

        // Verificar especificações dos pneus
        if (!empty($content['especificacoes_pneus'])) {
            $specs = $content['especificacoes_pneus'];
            if (empty($specs['medida_original'])) {
                $result['issues'][] = 'Especificações sem medida original do pneu';
            }
        }

        return $result;
    }

    /**
     * Mostrar resultados dos testes de estrutura
     */
    private function showStructureTestResults($testResults): void
    {
        $this->info("📊 Resultados dos testes de estrutura:");

        $compatibleCount = 0;
        foreach ($testResults as $test) {
            if ($test['result']['compatible_with_vm']) {
                $compatibleCount++;
            }

            $status = $test['result']['compatible_with_vm'] ? '✅' : '❌';
            $sectionsCount = $test['result']['required_sections_present'];
            
            $this->line("  {$status} {$test['slug']} - {$sectionsCount}/10 seções");

            if (!empty($test['result']['issues'])) {
                foreach (array_slice($test['result']['issues'], 0, 2) as $issue) {
                    $this->line("    ⚠️ {$issue}");
                }
            }
        }

        $this->newLine();
        $this->info("📈 Resumo dos testes:");
        $this->line("  Compatíveis com ViewModel: {$compatibleCount}/" . count($testResults));
        
        if ($compatibleCount === count($testResults)) {
            $this->info("🎉 Todos os artigos estão compatíveis com IdealTirePressureCarViewModel!");
        } else {
            $this->warn("⚠️ Alguns artigos precisam de ajustes na estrutura");
        }
    }
}