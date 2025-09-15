<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Carbon\Carbon;

/**
 * InvestigateGenericVersionPatternsTempArticleCommand v1.0
 * 
 * NOVA ABORDAGEM - FLAGS FORA DO METADATA
 * 
 * Campos específicos para correção:
 * - needs_version_correction (boolean)
 * - version_correction_priority (string: high|medium|low)
 * - version_issues_detected (array)
 * - correction_flagged_at (datetime)
 * - version_corrected_at (datetime) 
 * - corrected_by (string)
 * 
 * @author Claude Sonnet 4
 * @version 1.0 - Campos dedicados para correção
 */
class InvestigateGenericVersionPatternsTempArticleCommand extends Command
{
    protected $signature = 'temp-article:investigate-generic-versions
                            {--limit=500 : Número máximo de registros para analisar}
                            {--dry-run : Executar sem modificar dados}
                            {--flag-for-correction : Marcar registros para correção}
                            {--export-csv : Exportar resultados para CSV}
                            {--detailed : Mostrar exemplos detalhados}
                            {--status=draft : Status dos TempArticles}
                            {--force-all : Processar todos os registros}';

    protected $description = 'Investigar e marcar versões genéricas usando campos dedicados';

    private int $totalAnalyzed = 0;
    private int $genericVersionsFound = 0;
    private int $flaggedForCorrection = 0;
    private array $statisticsData = [];
    private array $detailedExamples = [];

    private const GENERIC_PATTERNS = [
        'comfort' => ['Comfort', 'comfort', 'COMFORT'],
        'style' => ['Style', 'style', 'STYLE'], 
        'premium' => ['Premium', 'premium', 'PREMIUM'],
        'base' => ['Base', 'base', 'BASE'],
        'entry' => ['Entry', 'entry', 'ENTRY'],
        'standard' => ['Standard', 'standard', 'STANDARD']
    ];

    public function handle(): int
    {
        $this->displayHeader();
        
        try {
            $config = $this->getConfiguration();
            $this->displayConfiguration($config);

            $tempArticles = $this->getTempArticlesForAnalysis($config);
            
            if ($tempArticles->isEmpty()) {
                $this->info('Nenhum TempArticle encontrado.');
                return self::SUCCESS;
            }

            $this->info("Iniciando análise de {$tempArticles->count()} TempArticles...");
            $this->newLine();

            foreach ($tempArticles as $tempArticle) {
                $this->analyzeTempArticle($tempArticle, $config);
            }

            $this->displayResults($config);

            if ($config['export_csv']) {
                $this->exportToCsv();
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Erro durante análise: " . $e->getMessage());
            Log::error('InvestigateGenericVersionPatterns failed', [
                'error' => $e->getMessage()
            ]);
            return self::FAILURE;
        }
    }

    private function getConfiguration(): array
    {
        return [
            'limit' => $this->option('force-all') ? null : (int) $this->option('limit'),
            'dry_run' => $this->option('dry-run'),
            'flag_for_correction' => $this->option('flag-for-correction'),
            'export_csv' => $this->option('export-csv'),
            'detailed' => $this->option('detailed'),
            'status' => $this->option('status'),
            'force_all' => $this->option('force-all')
        ];
    }

    private function getTempArticlesForAnalysis(array $config)
    {
        $query = TempArticle::where('status', $config['status'])
            ->whereNotNull('content')
            ->orderBy('created_at', 'desc');

        if ($config['limit']) {
            $query->limit($config['limit']);
        }

        return $query->get();
    }

    private function analyzeTempArticle(TempArticle $tempArticle, array $config): void
    {
        $this->totalAnalyzed++;
        
        $content = $tempArticle->content ?? [];
        $issues = [];
        $hasGenericVersions = false;

        // Analisar especificacoes_por_versao
        if (isset($content['especificacoes_por_versao']) && is_array($content['especificacoes_por_versao'])) {
            $versionIssues = $this->analyzeVersionSpecs($content['especificacoes_por_versao']);
            if (!empty($versionIssues)) {
                $hasGenericVersions = true;
                $issues['especificacoes_por_versao'] = $versionIssues;
            }
        }

        // Analisar tabela_carga_completa
        if (isset($content['tabela_carga_completa']['condicoes']) && is_array($content['tabela_carga_completa']['condicoes'])) {
            $loadTableIssues = $this->analyzeLoadTable($content['tabela_carga_completa']['condicoes']);
            if (!empty($loadTableIssues)) {
                $hasGenericVersions = true;
                $issues['tabela_carga_completa'] = $loadTableIssues;
            }
        }

        if ($hasGenericVersions) {
            $this->genericVersionsFound++;
            $this->processGenericVersionFound($tempArticle, $issues, $config);
        }

        $this->collectStatistics($tempArticle, $hasGenericVersions, $issues);
    }

    private function analyzeVersionSpecs(array $specs): array
    {
        $issues = [];

        foreach ($specs as $index => $spec) {
            $versionName = $spec['versao'] ?? '';
            
            if ($this->isGenericVersion($versionName)) {
                $issues[] = [
                    'index' => $index,
                    'version_original' => $versionName,
                    'pattern_type' => $this->getGenericPatternType($versionName),
                    'issue_type' => 'generic_version_name'
                ];
            }

            if ($this->hasDuplicateSpecs($specs, $index)) {
                $issues[] = [
                    'index' => $index,
                    'version_original' => $versionName,
                    'issue_type' => 'duplicate_specifications'
                ];
            }
        }

        return $issues;
    }

    private function analyzeLoadTable(array $conditions): array
    {
        $issues = [];

        foreach ($conditions as $index => $condition) {
            $versionName = $condition['versao'] ?? '';
            
            if ($this->isGenericVersion($versionName)) {
                $issues[] = [
                    'index' => $index,
                    'version_original' => $versionName,
                    'pattern_type' => $this->getGenericPatternType($versionName),
                    'issue_type' => 'generic_version_in_load_table'
                ];
            }
        }

        return $issues;
    }

    private function isGenericVersion(string $versionName): bool
    {
        foreach (self::GENERIC_PATTERNS as $patterns) {
            foreach ($patterns as $pattern) {
                if (stripos($versionName, $pattern) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    private function getGenericPatternType(string $versionName): string
    {
        foreach (self::GENERIC_PATTERNS as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (stripos($versionName, $pattern) !== false) {
                    return $type;
                }
            }
        }
        return 'unknown';
    }

    private function hasDuplicateSpecs(array $specs, int $currentIndex): bool
    {
        $currentSpec = $specs[$currentIndex];
        $compareFields = ['medida_pneus', 'pressao_dianteiro_normal', 'pressao_traseiro_normal'];

        foreach ($specs as $index => $otherSpec) {
            if ($index === $currentIndex) continue;

            $isDuplicate = true;
            foreach ($compareFields as $field) {
                if (($currentSpec[$field] ?? null) !== ($otherSpec[$field] ?? null)) {
                    $isDuplicate = false;
                    break;
                }
            }

            if ($isDuplicate) return true;
        }

        return false;
    }

    private function processGenericVersionFound(TempArticle $tempArticle, array $issues, array $config): void
    {
        $vehicleKey = $this->getVehicleKey($tempArticle);
        
        if ($config['flag_for_correction'] && !$config['dry_run']) {
            $this->flagForCorrection($tempArticle, $issues);
        }

        if ($config['detailed'] && count($this->detailedExamples) < 5) {
            $this->detailedExamples[] = [
                'temp_article_id' => $tempArticle->id,
                'vehicle' => $vehicleKey,
                'title' => $tempArticle->title ?? 'Sem título',
                'issues' => $issues,
                'created_at' => $tempArticle->created_at->format('d/m/Y H:i')
            ];
        }
    }

    /**
     * NOVA ABORDAGEM: Marcar usando campos dedicados fora do metadata
     */
    private function flagForCorrection(TempArticle $tempArticle, array $issues): void
    {
        try {
            $tempArticle->update([
                'needs_version_correction' => true,
                'version_correction_priority' => $this->calculatePriority($issues),
                'version_issues_detected' => $issues,
                'correction_flagged_at' => now(),
                'version_corrected_at' => null,
                'corrected_by' => null
            ]);
            
            $this->flaggedForCorrection++;

        } catch (\Exception $e) {
            Log::error('Falha ao marcar TempArticle para correção', [
                'temp_article_id' => $tempArticle->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function calculatePriority(array $issues): string
    {
        $totalIssues = 0;
        $hasSpecIssues = false;
        $hasLoadTableIssues = false;

        foreach ($issues as $section => $sectionIssues) {
            $totalIssues += count($sectionIssues);
            
            if ($section === 'especificacoes_por_versao') {
                $hasSpecIssues = true;
            } elseif ($section === 'tabela_carga_completa') {
                $hasLoadTableIssues = true;
            }
        }

        if ($hasSpecIssues && $hasLoadTableIssues) {
            return 'high';
        } elseif ($totalIssues >= 3) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    private function getVehicleKey(TempArticle $tempArticle): string
    {
        $entities = $tempArticle->extracted_entities ?? [];
        $marca = $entities['marca'] ?? 'Unknown';
        $modelo = $entities['modelo'] ?? 'Unknown';
        
        return "{$marca} {$modelo}";
    }

    private function collectStatistics(TempArticle $tempArticle, bool $hasGenericVersions, array $issues): void
    {
        $vehicleKey = $this->getVehicleKey($tempArticle);
        
        if (!isset($this->statisticsData[$vehicleKey])) {
            $this->statisticsData[$vehicleKey] = [
                'total_articles' => 0,
                'articles_with_generic' => 0,
                'total_issues' => 0,
                'issue_types' => []
            ];
        }

        $this->statisticsData[$vehicleKey]['total_articles']++;
        
        if ($hasGenericVersions) {
            $this->statisticsData[$vehicleKey]['articles_with_generic']++;
            
            foreach ($issues as $section => $sectionIssues) {
                $this->statisticsData[$vehicleKey]['total_issues'] += count($sectionIssues);
                
                foreach ($sectionIssues as $issue) {
                    $issueType = $issue['issue_type'];
                    if (!isset($this->statisticsData[$vehicleKey]['issue_types'][$issueType])) {
                        $this->statisticsData[$vehicleKey]['issue_types'][$issueType] = 0;
                    }
                    $this->statisticsData[$vehicleKey]['issue_types'][$issueType]++;
                }
            }
        }
    }

    private function displayHeader(): void
    {
        $this->info('INVESTIGAÇÃO DE VERSÕES GENÉRICAS EM TEMP ARTICLES v1.0');
        $this->info('Análise com campos dedicados para correção');
        $this->info(now()->format('d/m/Y H:i:s'));
        $this->newLine();
    }

    private function displayConfiguration(array $config): void
    {
        $this->line('CONFIGURAÇÃO:');
        $this->line('   • Limite: ' . ($config['force_all'] ? 'TODOS' : $config['limit']));
        $this->line('   • Status: ' . $config['status']);
        $this->line('   • Modo: ' . ($config['dry_run'] ? 'SIMULAÇÃO' : 'EXECUÇÃO'));
        $this->line('   • Marcar para correção: ' . ($config['flag_for_correction'] ? 'SIM' : 'NÃO'));
        $this->line('   • Exportar CSV: ' . ($config['export_csv'] ? 'SIM' : 'NÃO'));
        $this->newLine();
        
        $this->info('PADRÕES GENÉRICOS:');
        foreach (self::GENERIC_PATTERNS as $type => $patterns) {
            $this->line("   • {$type}: " . implode(', ', $patterns));
        }
        $this->newLine();
    }

    private function displayResults(array $config): void
    {
        $this->newLine();
        $this->info('RESULTADOS:');
        $this->line("   • Total analisado: {$this->totalAnalyzed}");
        $this->line("   • Com versões genéricas: {$this->genericVersionsFound}");
        
        if ($config['flag_for_correction']) {
            $this->line("   • Marcados para correção: {$this->flaggedForCorrection}");
        }
        
        $successRate = $this->totalAnalyzed > 0 ? 
            round((($this->totalAnalyzed - $this->genericVersionsFound) / $this->totalAnalyzed) * 100, 1) : 0;
        
        $this->line("   • Taxa de conformidade: {$successRate}%");
        $this->newLine();

        if (!empty($this->statisticsData)) {
            $this->displayVehicleStatistics();
        }

        if ($config['detailed'] && !empty($this->detailedExamples)) {
            $this->displayDetailedExamples();
        }

        $this->displayRecommendations($config);
    }

    private function displayVehicleStatistics(): void
    {
        $this->info('ESTATÍSTICAS POR VEÍCULO:');
        
        $sortedStats = collect($this->statisticsData)
            ->sortByDesc(fn($stats) => $stats['articles_with_generic'])
            ->take(10);

        foreach ($sortedStats as $vehicle => $stats) {
            $percentage = round(($stats['articles_with_generic'] / $stats['total_articles']) * 100, 1);
            $this->line("   {$vehicle}:");
            $this->line("      Artigos: {$stats['total_articles']} | Problemas: {$stats['articles_with_generic']} ({$percentage}%)");
            $this->line("      Issues: {$stats['total_issues']}");
        }
        $this->newLine();
    }

    private function displayDetailedExamples(): void
    {
        $this->info('EXEMPLOS DETALHADOS:');
        
        foreach ($this->detailedExamples as $example) {
            $this->line("   {$example['vehicle']} - {$example['title']}");
            $this->line("      ID: {$example['temp_article_id']}");
            $this->line("      Criado: {$example['created_at']}");
            
            foreach ($example['issues'] as $section => $issues) {
                $this->line("      {$section}: " . count($issues) . " issues");
            }
            $this->newLine();
        }
    }

    private function displayRecommendations(array $config): void
    {
        $this->line('RECOMENDAÇÕES:');
        
        if ($this->genericVersionsFound === 0) {
            $this->line('   Excelente! Nenhuma versão genérica encontrada.');
        } elseif ($this->genericVersionsFound < ($this->totalAnalyzed * 0.1)) {
            $this->line('   Poucos casos encontrados. Execute correção nos flagged.');
        } else {
            $this->line('   Alto número de versões genéricas! Implementar correção em massa.');
        }
        
        $this->newLine();
        $this->line('PRÓXIMOS PASSOS:');
        
        if ($config['flag_for_correction'] && $this->flaggedForCorrection > 0) {
            $this->line('   1. Execute comando de correção para os registros marcados');
            $this->line("   2. Monitore os {$this->flaggedForCorrection} registros flagged");
        } else {
            $this->line('   1. Execute novamente com --flag-for-correction');
            $this->line('   2. Implemente comando de correção');
        }
    }

    private function exportToCsv(): void
    {
        try {
            $filename = 'generic_versions_' . now()->format('Y-m-d_H-i-s') . '.csv';
            $filepath = storage_path('app/reports/' . $filename);
            
            if (!is_dir(dirname($filepath))) {
                mkdir(dirname($filepath), 0755, true);
            }

            $handle = fopen($filepath, 'w');
            
            fputcsv($handle, [
                'Veículo',
                'Total Artigos', 
                'Com Genéricos',
                'Percentual',
                'Total Issues'
            ]);

            foreach ($this->statisticsData as $vehicle => $stats) {
                $percentage = round(($stats['articles_with_generic'] / $stats['total_articles']) * 100, 1);
                
                fputcsv($handle, [
                    $vehicle,
                    $stats['total_articles'],
                    $stats['articles_with_generic'], 
                    $percentage . '%',
                    $stats['total_issues']
                ]);
            }

            fclose($handle);
            $this->info("Relatório exportado: {$filename}");
            
        } catch (\Exception $e) {
            $this->error("Erro ao exportar CSV: " . $e->getMessage());
        }
    }
}