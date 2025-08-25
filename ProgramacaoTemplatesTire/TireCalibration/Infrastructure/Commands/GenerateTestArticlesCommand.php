<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Src\ContentGeneration\TireCalibration\Application\Services\TestArticleService;
use Carbon\Carbon;

/**
 * GenerateTestArticlesCommand - Geração de artigos mock para validação
 * 
 * Command para desenvolvimento e testes:
 * - Gera artigos de teste em formato JSON
 * - Valida estrutura antes da implementação
 * - Testa templates diferentes
 * - Debug da arquitetura sem dados reais
 * 
 * ⚠️ IMPORTANTE: Apenas para desenvolvimento, não produção
 * 
 * USO:
 * php artisan tire-calibration:generate-test-articles
 * php artisan tire-calibration:generate-test-articles --category=sedan
 * php artisan tire-calibration:generate-test-articles --save-to-disk
 * php artisan tire-calibration:generate-test-articles --validate-only
 * 
 * @author Claude Sonnet 4
 * @version 1.0
 */
class GenerateTestArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tire-calibration:generate-test-articles
                            {--category= : Gerar apenas para categoria específica}
                            {--save-to-disk : Salvar arquivos JSON no storage}
                            {--validate-only : Apenas validar estrutura dos testes}
                            {--output-path=test-articles : Pasta no storage para salvar arquivos}
                            {--format=json : Formato de saída (json|table)}';

    /**
     * The console command description.
     */
    protected $description = 'Gerar artigos mock para validação da estrutura e desenvolvimento';

    private TestArticleService $testService;
    
    public function __construct(TestArticleService $testService)
    {
        parent::__construct();
        $this->testService = $testService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧪 GERANDO ARTIGOS DE TESTE - VALIDAÇÃO DA ESTRUTURA');
        $this->info('📅 ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();

        try {
            // 1. Obter configurações
            $config = $this->getConfig();
            $this->displayConfig($config);

            // 2. Se apenas validação, testar estrutura
            if ($config['validate_only']) {
                return $this->validateTestStructure();
            }

            // 3. Gerar artigos de teste
            $testResults = $this->generateTestArticles($config);

            // 4. Validar artigos gerados
            $validationResults = $this->validateGeneratedArticles($testResults);

            // 5. Salvar em disco se solicitado
            if ($config['save_to_disk']) {
                $this->saveTestArticlesToDisk($testResults, $config);
            }

            // 6. Exibir resultados
            $this->displayResults($testResults, $validationResults, $config);

            Log::info('GenerateTestArticlesCommand: Execução concluída', [
                'articles_generated' => count($testResults),
                'validation_results' => $validationResults['summary'],
                'config' => $config
            ]);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Erro durante geração: ' . $e->getMessage());
            Log::error('GenerateTestArticlesCommand: Erro fatal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Obter configurações do command
     */
    private function getConfig(): array
    {
        $category = $this->option('category');
        $outputPath = $this->option('output-path');
        $format = $this->option('format');

        // Validações
        $validCategories = ['sedan', 'suv', 'hatch', 'pickup', 'motorcycle', 'car_electric'];
        if ($category && !in_array($category, $validCategories)) {
            throw new \InvalidArgumentException("Categoria inválida. Disponíveis: " . implode(', ', $validCategories));
        }

        $validFormats = ['json', 'table'];
        if (!in_array($format, $validFormats)) {
            throw new \InvalidArgumentException("Formato inválido. Disponíveis: " . implode(', ', $validFormats));
        }

        return [
            'category' => $category,
            'save_to_disk' => $this->option('save-to-disk'),
            'validate_only' => $this->option('validate-only'),
            'output_path' => $outputPath,
            'format' => $format,
        ];
    }

    /**
     * Exibir configuração
     */
    private function displayConfig(array $config): void
    {
        $this->info('⚙️  CONFIGURAÇÃO DE TESTE:');
        $this->line("   • Categoria: " . ($config['category'] ?? 'Todas (6 categorias)'));
        $this->line("   • Salvar em disco: " . ($config['save_to_disk'] ? '✅ SIM' : '❌ NÃO'));
        $this->line("   • Validar apenas: " . ($config['validate_only'] ? '✅ SIM' : '❌ NÃO'));
        $this->line("   • Pasta de saída: {$config['output_path']}/");
        $this->line("   • Formato: {$config['format']}");
        $this->newLine();
    }

    /**
     * Validar apenas estrutura de teste
     */
    private function validateTestStructure(): int
    {
        $this->info('🔍 VALIDANDO ESTRUTURA DE TESTE...');
        $this->newLine();

        try {
            $stats = $this->testService->getTestStats();
            
            $this->info('📊 ESTATÍSTICAS DE TESTE:');
            $this->line("   • Categorias disponíveis: " . count($stats['available_categories']));
            $this->line("   • Veículos de teste: {$stats['total_test_vehicles']}");
            $this->line("   • Templates disponíveis: " . count($stats['template_types']));
            $this->line("   • Versão do serviço: {$stats['service_version']}");
            $this->newLine();

            $this->info('🗂️  CATEGORIAS DE TESTE:');
            foreach ($stats['available_categories'] as $category) {
                $this->line("   • {$category}");
            }
            $this->newLine();

            $this->info('🎨 TEMPLATES DISPONÍVEIS:');
            foreach ($stats['template_types'] as $template) {
                $this->line("   • {$template}");
            }
            $this->newLine();

            $this->info('✅ Estrutura de teste válida e pronta para uso!');
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Erro na validação da estrutura: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Gerar artigos de teste
     */
    private function generateTestArticles(array $config): array
    {
        $this->info('🏭 GERANDO ARTIGOS DE TESTE...');
        $this->newLine();

        $testResults = [];

        if ($config['category']) {
            // Gerar apenas categoria específica
            $this->line("📝 Gerando artigo de teste: {$config['category']}");
            try {
                $testResults[$config['category']] = $this->testService->generateTestArticle($config['category']);
                $this->info("   ✅ {$config['category']}: Gerado com sucesso");
            } catch (\Exception $e) {
                $this->error("   ❌ {$config['category']}: {$e->getMessage()}");
                $testResults[$config['category']] = ['error' => $e->getMessage()];
            }
        } else {
            // Gerar todas as categorias
            $this->line("📝 Gerando artigos para todas as categorias...");
            $testResults = $this->testService->generateAllTestArticles();
            
            foreach ($testResults as $category => $result) {
                if (isset($result['error'])) {
                    $this->error("   ❌ {$category}: {$result['error']}");
                } else {
                    $wordCount = $result['test_metadata']['word_count'] ?? 0;
                    $template = $result['template'] ?? 'N/A';
                    $this->info("   ✅ {$category}: {$wordCount} palavras - Template: {$template}");
                }
            }
        }

        $this->newLine();
        return $testResults;
    }

    /**
     * Validar artigos gerados
     */
    private function validateGeneratedArticles(array $testResults): array
    {
        $this->info('🔍 VALIDANDO ARTIGOS GERADOS...');
        $this->newLine();

        $validationResults = [
            'individual' => [],
            'summary' => [
                'total' => 0,
                'valid' => 0,
                'invalid' => 0,
                'errors' => 0,
                'avg_score' => 0
            ]
        ];

        foreach ($testResults as $category => $article) {
            $validationResults['summary']['total']++;

            if (isset($article['error'])) {
                $validationResults['summary']['errors']++;
                $validationResults['individual'][$category] = [
                    'is_valid' => false,
                    'error' => $article['error']
                ];
                continue;
            }

            try {
                $validation = $this->testService->validateTestArticle($article);
                $validationResults['individual'][$category] = $validation;

                if ($validation['is_valid']) {
                    $validationResults['summary']['valid']++;
                    $this->info("   ✅ {$category}: Válido (Score: {$validation['structure_score']}/100)");
                } else {
                    $validationResults['summary']['invalid']++;
                    $this->warn("   ⚠️  {$category}: Inválido - " . count($validation['errors']) . " erro(s)");
                }

                $validationResults['summary']['avg_score'] += $validation['structure_score'];

                // Mostrar warnings se houver
                if (!empty($validation['warnings'])) {
                    foreach ($validation['warnings'] as $warning) {
                        $this->line("      • Warning: {$warning}");
                    }
                }

            } catch (\Exception $e) {
                $validationResults['summary']['errors']++;
                $validationResults['individual'][$category] = [
                    'is_valid' => false,
                    'error' => $e->getMessage()
                ];
                $this->error("   ❌ {$category}: Erro na validação - {$e->getMessage()}");
            }
        }

        // Calcular score médio
        if ($validationResults['summary']['total'] > 0) {
            $validationResults['summary']['avg_score'] = round(
                $validationResults['summary']['avg_score'] / $validationResults['summary']['total'],
                1
            );
        }

        $this->newLine();
        return $validationResults;
    }

    /**
     * Salvar artigos em disco
     */
    private function saveTestArticlesToDisk(array $testResults, array $config): void
    {
        $this->info('💾 SALVANDO ARTIGOS EM DISCO...');
        $this->newLine();

        $outputPath = $config['output_path'];
        $timestamp = now()->format('Y-m-d_H-i-s');

        try {
            // Criar diretório se não existir
            if (!Storage::disk('local')->exists($outputPath)) {
                Storage::disk('local')->makeDirectory($outputPath);
            }

            $savedFiles = [];

            foreach ($testResults as $category => $article) {
                if (isset($article['error'])) {
                    continue; // Pular artigos com erro
                }

                $filename = "{$outputPath}/test_article_{$category}_{$timestamp}.json";
                
                // Adicionar metadados de arquivo
                $article['_file_metadata'] = [
                    'generated_at' => now()->toISOString(),
                    'category' => $category,
                    'filename' => $filename,
                    'command' => 'tire-calibration:generate-test-articles'
                ];

                $jsonContent = json_encode($article, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                Storage::disk('local')->put($filename, $jsonContent);

                $savedFiles[] = $filename;
                $this->line("   ✅ {$category}: storage/app/{$filename}");
            }

            // Salvar índice dos arquivos
            $indexFile = "{$outputPath}/index_{$timestamp}.json";
            $index = [
                'generated_at' => now()->toISOString(),
                'command' => 'tire-calibration:generate-test-articles',
                'total_files' => count($savedFiles),
                'files' => $savedFiles,
                'categories' => array_keys($testResults)
            ];

            Storage::disk('local')->put($indexFile, json_encode($index, JSON_PRETTY_PRINT));
            $this->info("   📋 Índice: storage/app/{$indexFile}");

        } catch (\Exception $e) {
            $this->error("❌ Erro ao salvar arquivos: {$e->getMessage()}");
        }

        $this->newLine();
    }

    /**
     * Exibir resultados finais
     */
    private function displayResults(array $testResults, array $validationResults, array $config): void
    {
        $this->info('📈 RESULTADOS DOS TESTES:');
        $this->newLine();

        // Estatísticas de validação
        $summary = $validationResults['summary'];
        $this->line("📊 <fg=blue>Total gerado:</fg=blue> {$summary['total']}");
        $this->line("✅ <fg=green>Válidos:</fg=green> {$summary['valid']}");
        $this->line("⚠️  <fg=yellow>Inválidos:</fg=yellow> {$summary['invalid']}");
        $this->line("❌ <fg=red>Erros:</fg=red> {$summary['errors']}");
        $this->line("🎯 <fg=magenta>Score médio:</fg=magenta> {$summary['avg_score']}/100");
        $this->newLine();

        // Estatísticas de conteúdo
        $totalWords = 0;
        $templates = [];
        
        foreach ($testResults as $category => $article) {
            if (!isset($article['error'])) {
                $totalWords += $article['test_metadata']['word_count'] ?? 0;
                $templates[] = $article['template'] ?? 'unknown';
            }
        }

        $avgWords = count($testResults) > 0 ? round($totalWords / count($testResults)) : 0;
        $uniqueTemplates = array_unique($templates);

        $this->line("📝 <fg=cyan>Palavras total:</fg=cyan> {$totalWords}");
        $this->line("📖 <fg=cyan>Média por artigo:</fg=cyan> {$avgWords} palavras");
        $this->line("🎨 <fg=cyan>Templates usados:</fg=cyan> " . count($uniqueTemplates) . " (" . implode(', ', $uniqueTemplates) . ")");
        $this->newLine();

        // Mostrar detalhes por categoria se formato table
        if ($config['format'] === 'table' && !empty($testResults)) {
            $this->displayResultsTable($testResults, $validationResults);
        }

        // Recomendações
        $this->displayRecommendations($validationResults);
    }

    /**
     * Exibir resultados em tabela
     */
    private function displayResultsTable(array $testResults, array $validationResults): void
    {
        $tableData = [];

        foreach ($testResults as $category => $article) {
            $validation = $validationResults['individual'][$category] ?? [];

            if (isset($article['error'])) {
                $tableData[] = [
                    'Categoria' => $category,
                    'Status' => '❌ Erro',
                    'Palavras' => '-',
                    'Template' => '-',
                    'Score' => '-',
                    'Observações' => substr($article['error'], 0, 50) . '...'
                ];
            } else {
                $status = ($validation['is_valid'] ?? false) ? '✅ Válido' : '⚠️ Inválido';
                $words = $article['test_metadata']['word_count'] ?? 0;
                $template = $article['template'] ?? 'N/A';
                $score = $validation['structure_score'] ?? 0;
                $observations = '';

                if (!empty($validation['warnings'])) {
                    $observations = count($validation['warnings']) . ' warning(s)';
                } elseif (!empty($validation['errors'])) {
                    $observations = count($validation['errors']) . ' erro(s)';
                } else {
                    $observations = 'OK';
                }

                $tableData[] = [
                    'Categoria' => $category,
                    'Status' => $status,
                    'Palavras' => $words,
                    'Template' => $template,
                    'Score' => $score . '/100',
                    'Observações' => $observations
                ];
            }
        }

        $this->table(
            ['Categoria', 'Status', 'Palavras', 'Template', 'Score', 'Observações'],
            $tableData
        );
        $this->newLine();
    }

    /**
     * Exibir recomendações
     */
    private function displayRecommendations(array $validationResults): void
    {
        $summary = $validationResults['summary'];
        
        $this->info('💡 RECOMENDAÇÕES:');

        if ($summary['valid'] === $summary['total']) {
            $this->line('   ✅ Todos os artigos de teste estão válidos!');
            $this->line('   ✅ Estrutura JSON está consistente');
            $this->line('   ✅ Pronto para implementação na arquitetura');
        } else {
            $this->line('   ⚠️  Alguns artigos têm problemas:');
            
            if ($summary['invalid'] > 0) {
                $this->line('   • Verifique campos obrigatórios ausentes');
                $this->line('   • Ajuste validações no TestArticleService');
            }
            
            if ($summary['errors'] > 0) {
                $this->line('   • Corrija erros de geração no TestArticleService');
                $this->line('   • Verifique logs para detalhes');
            }
        }

        if ($summary['avg_score'] < 80) {
            $this->line('   • Score médio baixo - melhore estrutura dos templates');
        }

        $this->newLine();
        $this->info('🚀 PRÓXIMOS PASSOS:');
        $this->line('   1. Ajuste TestArticleService se necessário');
        $this->line('   2. Execute: php artisan tire-calibration:generate-articles');
        $this->line('   3. Compare estrutura real vs. teste');
        $this->line('   4. Monitore: php artisan tire-calibration:stats');
    }
}