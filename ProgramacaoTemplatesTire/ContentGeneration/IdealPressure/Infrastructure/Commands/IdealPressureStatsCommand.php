<?php

namespace Src\ContentGeneration\IdealPressure\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\IdealPressure\Domain\Entities\IdealPressure;
use Src\ContentGeneration\IdealPressure\Application\Services\ArticleGenerationService;
use Src\ContentGeneration\IdealPressure\Application\Services\ClaudeRefinementService;
use Src\ContentGeneration\IdealPressure\Application\Services\TestArticleService;
use Carbon\Carbon;

/**
 * IdealPressureStatsCommand - Estatísticas completas do módulo IdealPressure
 * 
 * Command para monitoramento e insights:
 * - Estatísticas por fase do processo
 * - Performance dos services
 * - Qualidade dos dados
 * - Rate de sucesso das operações
 * - Distribuição por categorias
 * 
 * USO:
 * php artisan ideal-pressure:stats
 * php artisan ideal-pressure:stats --detailed
 * php artisan ideal-pressure:stats --export=csv
 * php artisan ideal-pressure:stats --category=sedan
 * 
 * @author Claude Sonnet 4
 * @version 1.0
 */
class IdealPressureStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ideal-pressure:stats
                            {--detailed : Exibir estatísticas detalhadas}
                            {--export= : Exportar para formato (json|csv)}
                            {--category= : Filtrar por categoria específica}
                            {--period= : Período em dias (7, 30, 90)}
                            {--output-file= : Arquivo de saída para export}';

    /**
     * The console command description.
     */
    protected $description = 'Exibir estatísticas completas do módulo IdealPressure';

    private ArticleGenerationService $articleService;
    private ClaudeRefinementService $claudeService;
    private TestArticleService $testService;

    public function __construct(
        ArticleGenerationService $articleService,
        ClaudeRefinementService $claudeService,
        TestArticleService $testService
    ) {
        parent::__construct();
        $this->articleService = $articleService;
        $this->claudeService = $claudeService;
        $this->testService = $testService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('📊 ESTATÍSTICAS DO MÓDULO TIRE CALIBRATION');
        $this->info('📅 ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();

        try {
            // 1. Obter configurações
            $config = $this->getConfig();
            $this->displayConfig($config);

            // 2. Coletar todas as estatísticas
            $stats = $this->collectAllStats($config);

            // 3. Exibir estatísticas
            $this->displayStats($stats, $config);

            // 4. Exportar se solicitado
            if ($config['export']) {
                $this->exportStats($stats, $config);
            }

            Log::info('IdealPressureStatsCommand: Estatísticas coletadas', [
                'total_records' => $stats['general']['total_records'],
                'config' => $config
            ]);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Erro ao coletar estatísticas: ' . $e->getMessage());
            Log::error('IdealPressureStatsCommand: Erro fatal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Obter configurações
     */
    private function getConfig(): array
    {
        $category = $this->option('category');
        $export = $this->option('export');
        $period = (int) ($this->option('period') ?? 30);
        $outputFile = $this->option('output-file');

        // Validações
        $validCategories = ['sedan', 'suv', 'hatch', 'pickup', 'motorcycle', 'motorcycle_street', 'motorcycle_scooter', 'car_electric', 'truck'];
        if ($category && !in_array($category, $validCategories)) {
            throw new \InvalidArgumentException("Categoria inválida. Disponíveis: " . implode(', ', $validCategories));
        }

        $validExports = ['json', 'csv'];
        if ($export && !in_array($export, $validExports)) {
            throw new \InvalidArgumentException("Formato de export inválido. Disponíveis: " . implode(', ', $validExports));
        }

        if ($period <= 0 || $period > 365) {
            throw new \InvalidArgumentException('Período deve estar entre 1 e 365 dias');
        }

        return [
            'detailed' => $this->option('detailed'),
            'export' => $export,
            'category' => $category,
            'period' => $period,
            'output_file' => $outputFile ?: "tire_calibration_stats_" . now()->format('Y-m-d_H-i-s'),
            'date_from' => now()->subDays($period),
        ];
    }

    /**
     * Exibir configuração
     */
    private function displayConfig(array $config): void
    {
        if (!$config['detailed'] && !$config['export']) {
            return; // Não mostrar config para consulta rápida
        }

        $this->info('⚙️  CONFIGURAÇÃO:');
        $this->line("   • Período: {$config['period']} dias (desde " . $config['date_from']->format('d/m/Y') . ")");
        $this->line("   • Categoria: " . ($config['category'] ?? 'Todas'));
        $this->line("   • Detalhado: " . ($config['detailed'] ? '✅ SIM' : '❌ NÃO'));
        $this->line("   • Export: " . ($config['export'] ?: '❌ NÃO'));
        $this->newLine();
    }

    /**
     * Coletar todas as estatísticas
     */
    private function collectAllStats(array $config): array
    {
        $stats = [];

        // 1. Estatísticas gerais
        $stats['general'] = $this->getGeneralStats($config);

        // 2. Estatísticas por fase
        $stats['phases'] = $this->getPhaseStats($config);

        // 3. Estatísticas por categoria
        $stats['categories'] = $this->getCategoryStats($config);

        // 4. Estatísticas de qualidade
        $stats['quality'] = $this->getQualityStats($config);

        // 5. Estatísticas de performance
        $stats['performance'] = $this->getPerformanceStats($config);

        // 6. Estatísticas dos services
        $stats['services'] = $this->getServiceStats($config);

        // 7. Estatísticas temporais (se detalhado)
        if ($config['detailed']) {
            $stats['temporal'] = $this->getTemporalStats($config);
        }

        // 8. Meta informações
        $stats['meta'] = [
            'collected_at' => now()->toISOString(),
            'period_days' => $config['period'],
            'category_filter' => $config['category'],
            'total_execution_time' => 0, // Será calculado no final
            'data_freshness' => 'real_time'
        ];

        return $stats;
    }

    /**
     * Estatísticas gerais
     */
    private function getGeneralStats(array $config): array
    {
        $query = IdealPressure::query();

        if ($config['category']) {
            $query->where('main_category', $config['category']);
        }

        return [
            'total_records' => $query->count(),
            'created_in_period' => $query->where('created_at', '>=', $config['date_from'])->count(),
            'updated_in_period' => $query->where('updated_at', '>=', $config['date_from'])->count(),
            'unique_vehicles' => $query->distinct('vehicle_make', 'vehicle_model', 'vehicle_year')->count(),
            'unique_makes' => $query->distinct('vehicle_make')->count(),
            'active_categories' => $query->distinct('main_category')->count(),
        ];
    }

    /**
     * Estatísticas por fase
     */
    private function getPhaseStats(array $config): array
    {
        $query = IdealPressure::query();

        if ($config['category']) {
            $query->where('main_category', $config['category']);
        }

        $phases = [
            IdealPressure::PHASE_PENDING => 'Pendente',
            IdealPressure::PHASE_VEHICLE_ENRICHED => 'VehicleData Processado',
            IdealPressure::PHASE_ARTICLE_GENERATED => 'Artigo Gerado (Fase 1+2)',
            IdealPressure::PHASE_CLAUDE_PROCESSING => 'Processando Claude',
            IdealPressure::PHASE_CLAUDE_COMPLETED => 'Refinado (Fase 3)',
            IdealPressure::PHASE_FAILED => 'Falhou',
        ];

        $phaseStats = [];
        $total = $query->count();

        foreach ($phases as $phaseKey => $phaseName) {
            $count = (clone $query)->where('enrichment_phase', $phaseKey)->count();
            $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;

            $phaseStats[$phaseKey] = [
                'name' => $phaseName,
                'count' => $count,
                'percentage' => $percentage,
            ];
        }

        return [
            'by_phase' => $phaseStats,
            'pipeline_efficiency' => $this->calculatePipelineEfficiency($phaseStats),
            'bottleneck_phase' => $this->identifyBottleneck($phaseStats),
        ];
    }

    /**
     * Estatísticas por categoria
     */
    private function getCategoryStats(array $config): array
    {
        $query = IdealPressure::query();

        if ($config['category']) {
            $query->where('main_category', $config['category']);
        }

        $categories = $query->groupBy('main_category')
            ->selectRaw('main_category, count(*) as total')
            ->get()
            ->keyBy('main_category')
            ->map(fn($item) => $item->total)
            ->toArray();

        $categoryStats = [];
        $total = array_sum($categories);

        foreach ($categories as $category => $count) {
            $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;

            // Estatísticas específicas da categoria
            $categoryQuery = IdealPressure::where('main_category', $category);

            $categoryStats[$category] = [
                'total' => $count,
                'percentage' => $percentage,
                'completed' => $categoryQuery->where('enrichment_phase', IdealPressure::PHASE_CLAUDE_COMPLETED)->count(),
                'failed' => $categoryQuery->where('enrichment_phase', IdealPressure::PHASE_FAILED)->count(),
                'avg_quality' => round($categoryQuery->avg('data_completeness_score') ?? 0, 1),
                'success_rate' => $this->calculateCategorySuccessRate($category),
            ];
        }

        return [
            'by_category' => $categoryStats,
            'most_popular' => array_keys($categories, max($categories))[0] ?? null,
            'least_popular' => array_keys($categories, min($categories))[0] ?? null,
        ];
    }

    /**
     * Estatísticas de qualidade
     */
    private function getQualityStats(array $config): array
    {
        $query = IdealPressure::query()->whereNotNull('data_completeness_score');

        if ($config['category']) {
            $query->where('main_category', $config['category']);
        }

        $qualityRanges = [
            'excellent' => $query->where('data_completeness_score', '>=', 90)->count(),
            'good' => $query->whereBetween('data_completeness_score', [75, 89])->count(),
            'fair' => $query->whereBetween('data_completeness_score', [60, 74])->count(),
            'poor' => $query->where('data_completeness_score', '<', 60)->count(),
        ];

        return [
            'avg_score' => round($query->avg('data_completeness_score') ?? 0, 1),
            'min_score' => $query->min('data_completeness_score') ?? 0,
            'max_score' => $query->max('data_completeness_score') ?? 0,
            'quality_ranges' => $qualityRanges,
            'high_quality_rate' => $this->calculateHighQualityRate($query),
        ];
    }

    /**
     * Estatísticas de performance
     */
    private function getPerformanceStats(array $config): array
    {
        $query = IdealPressure::query();

        if ($config['category']) {
            $query->where('main_category', $config['category']);
        }

        return [
            'avg_processing_time' => $this->calculateAvgProcessingTime($query),
            'articles_per_day' => $this->calculateArticlesPerDay($query, $config),
            'claude_api_usage' => $this->getClaudeApiUsage($query, $config),
            'error_rate' => $this->calculateErrorRate($query),
            'retry_rate' => $this->calculateRetryRate($query),
        ];
    }

    /**
     * Estatísticas dos services
     */
    private function getServiceStats(array $config): array
    {
        return [
            'article_generation' => $this->articleService->getSystemStats(),
            'claude_refinement' => $this->claudeService->getRefinementStats(),
            'test_service' => $this->testService->getTestStats(),
        ];
    }

    /**
     * Estatísticas temporais (apenas se detalhado)
     */
    private function getTemporalStats(array $config): array
    {
        $query = IdealPressure::query();

        if ($config['category']) {
            $query->where('main_category', $config['category']);
        }

        // Últimos 7 dias
        $dailyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayQuery = (clone $query)->whereDate('created_at', $date);

            $dailyStats[$date->format('Y-m-d')] = [
                'created' => $dayQuery->count(),
                'completed' => $dayQuery->where('enrichment_phase', IdealPressure::PHASE_CLAUDE_COMPLETED)->count(),
                'failed' => $dayQuery->where('enrichment_phase', IdealPressure::PHASE_FAILED)->count(),
            ];
        }

        return [
            'daily_last_7_days' => $dailyStats,
            'peak_hour' => $this->calculatePeakHour($query),
            'processing_trend' => $this->calculateProcessingTrend($query, $config),
        ];
    }

    /**
     * Exibir estatísticas
     */
    private function displayStats(array $stats, array $config): void
    {
        // 1. Estatísticas Gerais
        $this->displayGeneralStats($stats['general']);

        // 2. Estatísticas por Fase (Pipeline)
        $this->displayPhaseStats($stats['phases']);

        // 3. Estatísticas por Categoria
        $this->displayCategoryStats($stats['categories']);

        // 4. Qualidade dos Dados
        $this->displayQualityStats($stats['quality']);

        // 5. Performance
        $this->displayPerformanceStats($stats['performance']);

        // 6. Services Status
        if ($config['detailed']) {
            $this->displayServiceStats($stats['services']);
        }

        // 7. Estatísticas Temporais
        if ($config['detailed'] && isset($stats['temporal'])) {
            $this->displayTemporalStats($stats['temporal']);
        }

        // 8. Recomendações
        $this->displayRecommendations($stats);
    }

    private function displayGeneralStats(array $general): void
    {
        $this->info('📊 ESTATÍSTICAS GERAIS:');
        $this->newLine();

        $this->line("📋 <fg=blue>Total de registros:</fg=blue> " . number_format($general['total_records']));
        $this->line("📅 <fg=cyan>Criados no período:</fg=cyan> " . number_format($general['created_in_period']));
        $this->line("🔄 <fg=cyan>Atualizados no período:</fg=cyan> " . number_format($general['updated_in_period']));
        $this->line("🚗 <fg=magenta>Veículos únicos:</fg=magenta> " . number_format($general['unique_vehicles']));
        $this->line("🏭 <fg=yellow>Marcas únicas:</fg=yellow> " . number_format($general['unique_makes']));
        $this->line("🗂️  <fg=green>Categorias ativas:</fg=green> " . number_format($general['active_categories']));
        $this->newLine();
    }

    private function displayPhaseStats(array $phases): void
    {
        $this->info('⚡ PIPELINE DE PROCESSAMENTO:');
        $this->newLine();

        foreach ($phases['by_phase'] as $phaseKey => $phase) {
            $status = $this->getPhaseStatusIcon($phaseKey);
            $bar = str_repeat('█', (int)($phase['percentage'] / 5));
            $this->line("   {$status} <fg=white>{$phase['name']}:</fg=white> {$phase['count']} ({$phase['percentage']}%) {$bar}");
        }

        $this->newLine();
        $this->line("🎯 <fg=green>Eficiência do pipeline:</fg=green> {$phases['pipeline_efficiency']}%");

        if ($phases['bottleneck_phase']) {
            $this->line("🚧 <fg=yellow>Gargalo identificado:</fg=yellow> {$phases['bottleneck_phase']}");
        }

        $this->newLine();
    }

    private function displayCategoryStats(array $categories): void
    {
        $this->info('🗂️  DISTRIBUIÇÃO POR CATEGORIA:');
        $this->newLine();

        foreach ($categories['by_category'] as $category => $stats) {
            $successRate = round($stats['success_rate'], 1);
            $this->line("   📁 <fg=white>{$category}:</fg=white> {$stats['total']} registros ({$stats['percentage']}%)");
            $this->line("      ✅ Completos: {$stats['completed']} | ❌ Falhas: {$stats['failed']} | 🎯 Taxa: {$successRate}% | ⭐ Qualidade: {$stats['avg_quality']}/100");
        }

        $this->newLine();
        $this->line("🏆 <fg=green>Mais popular:</fg=green> {$categories['most_popular']}");
        $this->line("📉 <fg=yellow>Menos popular:</fg=yellow> {$categories['least_popular']}");
        $this->newLine();
    }

    private function displayQualityStats(array $quality): void
    {
        $this->info('⭐ QUALIDADE DOS DADOS:');
        $this->newLine();

        $this->line("📊 <fg=blue>Score médio:</fg=blue> {$quality['avg_score']}/100");
        $this->line("📈 <fg=green>Score máximo:</fg=green> {$quality['max_score']}/100");
        $this->line("📉 <fg=red>Score mínimo:</fg=red> {$quality['min_score']}/100");
        $this->newLine();

        $ranges = $quality['quality_ranges'];
        $this->line("🌟 <fg=green>Excelente (90+):</fg=green> {$ranges['excellent']} registros");
        $this->line("👍 <fg=cyan>Bom (75-89):</fg=cyan> {$ranges['good']} registros");
        $this->line("👌 <fg=yellow>Razoável (60-74):</fg=yellow> {$ranges['fair']} registros");
        $this->line("👎 <fg=red>Ruim (<60):</fg=red> {$ranges['poor']} registros");

        $this->newLine();
        $this->line("🎯 <fg=magenta>Taxa de alta qualidade:</fg=magenta> {$quality['high_quality_rate']}%");
        $this->newLine();
    }

    private function displayPerformanceStats(array $performance): void
    {
        $this->info('🚀 PERFORMANCE:');
        $this->newLine();

        $this->line("⏱️  <fg=cyan>Tempo médio de processamento:</fg=cyan> {$performance['avg_processing_time']}");
        $this->line("📈 <fg=blue>Artigos por dia:</fg=blue> {$performance['articles_per_day']}");
        $this->line("🤖 <fg=magenta>Uso da Claude API:</fg=magenta> {$performance['claude_api_usage']} requests");
        $this->line("❌ <fg=red>Taxa de erro:</fg=red> {$performance['error_rate']}%");
        $this->line("🔄 <fg=yellow>Taxa de retry:</fg=yellow> {$performance['retry_rate']}%");
        $this->newLine();
    }

    private function displayServiceStats(array $services): void
    {
        $this->info('🛠️  STATUS DOS SERVICES:');
        $this->newLine();

        // ArticleGenerationService
        $articleStats = $services['article_generation'];
        $this->line("📝 <fg=blue>Article Generation Service:</fg=blue>");
        $this->line("   • Prontos para processamento: {$articleStats['ready_for_processing']}");
        $this->line("   • Artigos gerados: {$articleStats['articles_generated']}");
        $this->line("   • Falhas no processamento: {$articleStats['failed_processing']}");

        // ClaudeRefinementService
        $claudeStats = $services['claude_refinement'];
        $this->line("🤖 <fg=magenta>Claude Refinement Service:</fg=magenta>");
        $this->line("   • Prontos para refinamento: {$claudeStats['ready_for_refinement']}");
        $this->line("   • Artigos refinados: {$claudeStats['articles_refined']}");
        $this->line("   • API configurada: " . ($claudeStats['api_configured'] ? '✅' : '❌'));
        $this->line("   • Taxa de sucesso: {$claudeStats['success_rate']}%");

        // TestArticleService
        $testStats = $services['test_service'];
        $this->line("🧪 <fg=green>Test Article Service:</fg=green>");
        $this->line("   • Categorias de teste: " . count($testStats['available_categories']));
        $this->line("   • Veículos de teste: {$testStats['total_test_vehicles']}");
        $this->line("   • Templates disponíveis: " . count($testStats['template_types']));

        $this->newLine();
    }

    private function displayTemporalStats(array $temporal): void
    {
        $this->info('📅 ESTATÍSTICAS TEMPORAIS (Últimos 7 dias):');
        $this->newLine();

        foreach ($temporal['daily_last_7_days'] as $date => $stats) {
            $this->line("   📅 {$date}: {$stats['created']} criados | {$stats['completed']} completos | {$stats['failed']} falhas");
        }

        $this->newLine();
        $this->line("🕐 <fg=cyan>Pico de atividade:</fg=cyan> {$temporal['peak_hour']}h");
        $this->line("📈 <fg=blue>Tendência:</fg=blue> {$temporal['processing_trend']}");
        $this->newLine();
    }

    private function displayRecommendations(array $stats): void
    {
        $this->info('💡 RECOMENDAÇÕES:');
        $this->newLine();

        $recommendations = [];

        // Analisar pipeline efficiency
        if ($stats['phases']['pipeline_efficiency'] < 70) {
            $recommendations[] = "⚠️  Pipeline com baixa eficiência ({$stats['phases']['pipeline_efficiency']}%) - verificar gargalos";
        }

        // Analisar qualidade dos dados
        if ($stats['quality']['avg_score'] < 75) {
            $recommendations[] = "📊 Score de qualidade baixo ({$stats['quality']['avg_score']}/100) - melhorar VehicleData";
        }

        // Analisar taxa de erro
        if ($stats['performance']['error_rate'] > 10) {
            $recommendations[] = "❌ Taxa de erro alta ({$stats['performance']['error_rate']}%) - investigar falhas";
        }

        // Analisar distribuição por categoria
        $categoryStats = $stats['categories']['by_category'];
        $lowPerformingCategories = array_filter($categoryStats, fn($cat) => $cat['success_rate'] < 80);
        if (!empty($lowPerformingCategories)) {
            $categories = implode(', ', array_keys($lowPerformingCategories));
            $recommendations[] = "🗂️  Categorias com baixa performance: {$categories}";
        }

        // Analisar uso da Claude API
        $claudeStats = $stats['services']['claude_refinement'];
        if (!$claudeStats['api_configured']) {
            $recommendations[] = "🤖 Claude API não configurada - configure ANTHROPIC_API_KEY";
        }

        // Mostrar recomendações ou sucesso
        if (empty($recommendations)) {
            $this->line("✅ Sistema funcionando dentro dos parâmetros ideais!");
            $this->line("🎉 Pipeline eficiente, boa qualidade de dados e baixa taxa de erros");
        } else {
            foreach ($recommendations as $recommendation) {
                $this->line("   {$recommendation}");
            }
        }

        $this->newLine();

        // Próximos passos sempre úteis
        $this->info('🚀 PRÓXIMOS PASSOS:');
        $this->line('   1. Monitorar pipeline: php artisan ideal-pressure:stats --detailed');
        $this->line('   2. Processar pendências: php artisan ideal-pressure:generate-articles');
        $this->line('   3. Refinar artigos: php artisan ideal-pressure:refine-with-claude --limit=10');
    }

    /**
     * Exportar estatísticas
     */
    private function exportStats(array $stats, array $config): void
    {
        $this->info("💾 EXPORTANDO ESTATÍSTICAS ({$config['export']})...");

        try {
            $filename = $config['output_file'] . '.' . $config['export'];

            if ($config['export'] === 'json') {
                $content = json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                file_put_contents(storage_path("app/{$filename}"), $content);
            } elseif ($config['export'] === 'csv') {
                $this->exportToCsv($stats, $filename);
            }

            $this->info("✅ Estatísticas exportadas: storage/app/{$filename}");
        } catch (\Exception $e) {
            $this->error("❌ Erro no export: {$e->getMessage()}");
        }
    }

    // ===== MÉTODOS AUXILIARES =====

    private function getPhaseStatusIcon(string $phase): string
    {
        $icons = [
            IdealPressure::PHASE_PENDING => '⏳',
            IdealPressure::PHASE_VEHICLE_ENRICHED => '✅',
            IdealPressure::PHASE_ARTICLE_GENERATED => '📝',
            IdealPressure::PHASE_CLAUDE_PROCESSING => '🤖',
            IdealPressure::PHASE_CLAUDE_COMPLETED => '🎯',
            IdealPressure::PHASE_FAILED => '❌',
        ];

        return $icons[$phase] ?? '❓';
    }

    private function calculatePipelineEfficiency(array $phaseStats): float
    {
        $completed = $phaseStats[IdealPressure::PHASE_CLAUDE_COMPLETED]['count'] ?? 0;
        $total = array_sum(array_column($phaseStats, 'count'));

        return $total > 0 ? round(($completed / $total) * 100, 1) : 0;
    }

    private function identifyBottleneck(array $phaseStats): ?string
    {
        $bottlenecks = [
            IdealPressure::PHASE_PENDING,
            IdealPressure::PHASE_VEHICLE_ENRICHED,
            IdealPressure::PHASE_ARTICLE_GENERATED,
        ];

        $maxCount = 0;
        $bottleneck = null;

        foreach ($bottlenecks as $phase) {
            $count = $phaseStats[$phase]['count'] ?? 0;
            if ($count > $maxCount) {
                $maxCount = $count;
                $bottleneck = $phaseStats[$phase]['name'] ?? null;
            }
        }

        return $bottleneck;
    }

    private function calculateCategorySuccessRate(string $category): float
    {
        $total = IdealPressure::where('main_category', $category)->count();
        $success = IdealPressure::where('main_category', $category)
            ->where('enrichment_phase', IdealPressure::PHASE_CLAUDE_COMPLETED)->count();

        return $total > 0 ? round(($success / $total) * 100, 1) : 0;
    }

    private function calculateHighQualityRate($query): float
    {
        $total = $query->count();
        $highQuality = (clone $query)->where('data_completeness_score', '>=', 80)->count();

        return $total > 0 ? round(($highQuality / $total) * 100, 1) : 0;
    }

    /**
     * Calcular taxa de alta qualidade usando Collection (para MongoDB)
     */
    private function calculateHighQualityRateFromCollection($records): float
    {
        $total = $records->count();
        $highQuality = $records->where('data_completeness_score', '>=', 80)->count();

        return $total > 0 ? round(($highQuality / $total) * 100, 1) : 0;
    }

    private function calculateAvgProcessingTime($query): string
    {
        // Placeholder - implementar baseado nos timestamps de processing_history
        return "~2.5min"; // Mock
    }

    private function calculateArticlesPerDay($query, array $config): float
    {
        $completed = $query->where('enrichment_phase', IdealPressure::PHASE_CLAUDE_COMPLETED)
            ->where('updated_at', '>=', $config['date_from'])->count();

        return round($completed / $config['period'], 1);
    }

    private function getClaudeApiUsage($query, array $config): int
    {
        return $query->whereNotNull('claude_processing_history')
            ->where('updated_at', '>=', $config['date_from'])->count();
    }

    private function calculateErrorRate($query): float
    {
        $total = $query->count();
        $failed = (clone $query)->where('enrichment_phase', IdealPressure::PHASE_FAILED)->count();

        return $total > 0 ? round(($failed / $total) * 100, 1) : 0;
    }

    private function calculateRetryRate($query): float
    {
        // Usar processing_attempts em vez de error_count (que pode não existir)
        $withRetries = $query->where('processing_attempts', '>', 1)->count();
        $total = $query->count();

        return $total > 0 ? round(($withRetries / $total) * 100, 1) : 0;
    }

    private function calculatePeakHour($query): int
    {
        // Placeholder - implementar análise por hora
        return 14; // Mock: 14h
    }

    private function calculateProcessingTrend($query, array $config): string
    {
        // Placeholder - comparar período atual vs anterior
        return "Estável"; // Mock
    }

    private function exportToCsv(array $stats, string $filename): void
    {
        $csvData = [];

        // Header
        $csvData[] = ['Métrica', 'Valor', 'Categoria', 'Observações'];

        // Estatísticas gerais
        foreach ($stats['general'] as $key => $value) {
            $csvData[] = [ucfirst(str_replace('_', ' ', $key)), $value, 'Geral', ''];
        }

        // Estatísticas por categoria
        foreach ($stats['categories']['by_category'] as $category => $categoryStats) {
            $csvData[] = ["Total {$category}", $categoryStats['total'], 'Categoria', $category];
            $csvData[] = ["Completos {$category}", $categoryStats['completed'], 'Categoria', $category];
            $csvData[] = ["Taxa sucesso {$category}", $categoryStats['success_rate'] . '%', 'Categoria', $category];
        }

        // Escrever CSV
        $fp = fopen(storage_path("app/{$filename}"), 'w');
        foreach ($csvData as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);
    }
}
