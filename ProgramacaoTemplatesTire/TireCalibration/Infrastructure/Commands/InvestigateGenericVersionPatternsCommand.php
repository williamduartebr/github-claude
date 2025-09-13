<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;

/**
 * InvestigateGenericVersionPatternsCommand
 * 
 * Detecta padrões genéricos de versões geradas pela IA nos artigos
 * 
 * PADRÕES GENÉRICOS IDENTIFICADOS:
 * - "Comfort", "Style", "Premium" 
 * - "Base", "Intermediária", "Top"
 * - Versões que não correspondem à realidade do modelo
 * 
 * @version 1.0 - Detecção de Versões Genéricas
 */
class InvestigateGenericVersionPatternsCommand extends Command
{
    protected $signature = 'tire-calibration:investigate-generic-versions 
                           {--limit=300 : Limite de registros para investigar}
                           {--template= : Filtrar por template específico}
                           {--category= : Filtrar por categoria específica}
                           {--export-csv=false : Exportar resultados para CSV}
                           {--show-versions=false : Mostrar todas as versões encontradas}';

    protected $description = 'Investiga padrões genéricos de versões nos artigos TireCalibration';

    private array $genericPatterns = [];
    private array $suspiciousVersions = [];
    private array $statistics = [];

    // Padrões genéricos conhecidos
    private array $knownGenericPatterns = [
        'comfort', 'style', 'premium',
        'base', 'básica', 'básico',
        'intermediária', 'intermediário', 'inter',
        'top', 'topo', 'superior',
        'entry', 'entrada', 'inicial',
        'standard', 'padrão',
        'completa', 'completo',
        'plus', 'max', 'pro',
        'versão base', 'versão premium'
    ];

    public function handle(): int
    {
        $this->info('🔍 Investigando padrões genéricos de versões...');
        
        $limit = (int) $this->option('limit');
        $template = $this->option('template');
        $category = $this->option('category');
        $exportCsv = $this->option('export-csv') === 'true';
        $showVersions = $this->option('show-versions') === 'true';

        // 1. Coletar dados
        $this->info("📊 Coletando dados (limite: {$limit})...");
        $records = $this->collectData($limit, $template, $category);
        
        if ($records->isEmpty()) {
            $this->warn('❌ Nenhum registro encontrado para investigação');
            return 1;
        }

        $this->info("✅ {$records->count()} registros coletados");

        // 2. Analisar padrões
        $this->info('🔍 Analisando padrões de versões...');
        $this->analyzeVersionPatterns($records);

        // 3. Exibir relatório
        $this->displayReport($showVersions);

        // 4. Exportar CSV se solicitado
        if ($exportCsv) {
            $this->exportToCsv();
        }

        return 0;
    }

    /**
     * Coletar dados para investigação
     */
    private function collectData(int $limit, ?string $template, ?string $category)
    {
        $query = TireCalibration::where(function($q) {
            $q->whereNotNull('generated_article')
              ->orWhereNotNull('article_refined');
        });

        if ($template) {
            // Buscar tanto em generated_article quanto article_refined
            $query->where(function($q) use ($template) {
                $q->whereRaw("JSON_EXTRACT(generated_article, '$.template') = ?", [$template])
                  ->orWhereRaw("JSON_EXTRACT(article_refined, '$.template') = ?", [$template]);
            });
        }

        if ($category) {
            $query->where('main_category', $category);
        }

        return $query->limit($limit)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Analisar padrões de versões nos artigos
     */
    private function analyzeVersionPatterns($records): void
    {
        $this->statistics = [
            'total_analyzed' => $records->count(),
            'with_versions' => 0,
            'generic_patterns_found' => 0,
            'suspicious_articles' => 0,
            'templates_analyzed' => [],
            'categories_analyzed' => [],
            'version_frequency' => [],
            'brand_patterns' => [],
        ];

        foreach ($records as $record) {
            $this->analyzeRecord($record);
        }
    }

    /**
     * Analisar registro individual
     */
    private function analyzeRecord(TireCalibration $record): void
    {
        $recordData = [
            'id' => $record->id,
            'vehicle_make' => $record->vehicle_make,
            'vehicle_model' => $record->vehicle_model,
            'main_category' => $record->main_category,
            'enrichment_phase' => $record->enrichment_phase,
        ];

        // Atualizar estatísticas básicas
        $this->updateBasicStatistics($recordData);

        // Analisar generated_article
        $generatedVersions = $this->extractVersionsFromArticle($record->generated_article, 'generated_article');
        if ($generatedVersions) {
            $this->analyzeVersions($recordData, $generatedVersions, 'generated_article');
        }

        // Analisar article_refined
        $refinedVersions = $this->extractVersionsFromArticle($record->article_refined, 'article_refined');
        if ($refinedVersions) {
            $this->analyzeVersions($recordData, $refinedVersions, 'article_refined');
        }
    }

    /**
     * Extrair versões do artigo
     */
    private function extractVersionsFromArticle($article, string $source): ?array
    {
        $articleData = $this->parseArticleData($article);
        
        if (!$articleData) {
            return null;
        }

        $versions = [];

        // Buscar em especificacoes_por_versao
        if (isset($articleData['content']['especificacoes_por_versao'])) {
            foreach ($articleData['content']['especificacoes_por_versao'] as $spec) {
                if (isset($spec['versao'])) {
                    $versions[] = $spec['versao'];
                }
            }
        }

        // Buscar em tabela_carga_completa
        if (isset($articleData['content']['tabela_carga_completa']['condicoes'])) {
            foreach ($articleData['content']['tabela_carga_completa']['condicoes'] as $condicao) {
                if (isset($condicao['versao'])) {
                    $versions[] = $condicao['versao'];
                }
            }
        }

        return empty($versions) ? null : array_unique($versions);
    }

    /**
     * Analisar versões encontradas
     */
    private function analyzeVersions(array $recordData, array $versions, string $source): void
    {
        $this->statistics['with_versions']++;
        
        $suspiciousCount = 0;
        $genericPatterns = [];

        foreach ($versions as $version) {
            // Contar frequência
            $versionKey = strtolower(trim($version));
            $this->statistics['version_frequency'][$versionKey] = 
                ($this->statistics['version_frequency'][$versionKey] ?? 0) + 1;

            // Verificar se é genérico
            $genericPattern = $this->detectGenericPattern($version);
            if ($genericPattern) {
                $suspiciousCount++;
                $genericPatterns[] = [
                    'version' => $version,
                    'pattern' => $genericPattern,
                    'confidence' => $this->calculateGenericConfidence($version)
                ];
            }
        }

        // Se encontrou padrões genéricos
        if ($suspiciousCount > 0) {
            $this->statistics['generic_patterns_found'] += $suspiciousCount;
            
            if ($suspiciousCount >= 2) { // Se 2+ versões são genéricas
                $this->statistics['suspicious_articles']++;
                
                $this->suspiciousVersions[] = [
                    'record_id' => $recordData['id'],
                    'vehicle' => "{$recordData['vehicle_make']} {$recordData['vehicle_model']}",
                    'category' => $recordData['main_category'],
                    'source' => $source,
                    'versions' => $versions,
                    'generic_patterns' => $genericPatterns,
                    'suspicious_count' => $suspiciousCount,
                    'total_versions' => count($versions),
                    'generic_ratio' => round(($suspiciousCount / count($versions)) * 100, 1)
                ];
            }
        }

        // Analisar padrões por marca
        $brand = strtolower($recordData['vehicle_make']);
        if (!isset($this->statistics['brand_patterns'][$brand])) {
            $this->statistics['brand_patterns'][$brand] = [
                'total_articles' => 0,
                'with_generic' => 0,
                'common_patterns' => []
            ];
        }
        
        $this->statistics['brand_patterns'][$brand]['total_articles']++;
        if ($suspiciousCount > 0) {
            $this->statistics['brand_patterns'][$brand]['with_generic']++;
            foreach ($genericPatterns as $gp) {
                $pattern = $gp['pattern'];
                $this->statistics['brand_patterns'][$brand]['common_patterns'][$pattern] = 
                    ($this->statistics['brand_patterns'][$brand]['common_patterns'][$pattern] ?? 0) + 1;
            }
        }
    }

    /**
     * Detectar se uma versão segue padrão genérico
     */
    private function detectGenericPattern(string $version): ?string
    {
        $versionLower = strtolower(trim($version));
        
        // Verificar padrões conhecidos
        foreach ($this->knownGenericPatterns as $pattern) {
            if (strpos($versionLower, $pattern) !== false) {
                return $pattern;
            }
        }

        // Verificar padrões específicos
        if (preg_match('/comfort|style|premium/', $versionLower)) {
            return 'comfort-style-premium-pattern';
        }

        if (preg_match('/versão\s+(base|básica|premium|top)/', $versionLower)) {
            return 'versao-generic-pattern';
        }

        if (preg_match('/^(base|basic|standard|premium|top|entry)$/i', $versionLower)) {
            return 'single-generic-word';
        }

        return null;
    }

    /**
     * Calcular confiança de que é genérico
     */
    private function calculateGenericConfidence(string $version): float
    {
        $versionLower = strtolower(trim($version));
        $confidence = 0.0;

        // Padrões altamente genéricos
        if (in_array($versionLower, ['comfort', 'style', 'premium'])) {
            $confidence += 0.9;
        }

        // Palavras genéricas
        $genericWords = ['base', 'básica', 'premium', 'top', 'comfort', 'style'];
        foreach ($genericWords as $word) {
            if (strpos($versionLower, $word) !== false) {
                $confidence += 0.3;
            }
        }

        // Ausência de números/códigos específicos
        if (!preg_match('/\d/', $version)) {
            $confidence += 0.2;
        }

        // Ausência de termos técnicos
        if (!preg_match('/(turbo|tsi|tfsi|hdi|dci|4x4|awd|sport|rs|amg|m\s|s\s)/i', $version)) {
            $confidence += 0.1;
        }

        return min(1.0, $confidence);
    }

    /**
     * Atualizar estatísticas básicas
     */
    private function updateBasicStatistics(array $recordData): void
    {
        // Templates
        // Categoria
        $category = $recordData['main_category'];
        $this->statistics['categories_analyzed'][$category] = 
            ($this->statistics['categories_analyzed'][$category] ?? 0) + 1;
    }

    /**
     * Parse de dados do artigo
     */
    private function parseArticleData($article): ?array
    {
        if (is_array($article)) {
            return $article;
        }

        if (is_string($article)) {
            $decoded = json_decode($article, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * Exibir relatório completo
     */
    private function displayReport(bool $showVersions): void
    {
        $this->info('');
        $this->info('📋 RELATÓRIO DE PADRÕES GENÉRICOS');
        $this->info('═══════════════════════════════════════');

        $this->displayGeneralStatistics();
        $this->displaySuspiciousArticles();
        $this->displayBrandAnalysis();
        
        if ($showVersions) {
            $this->displayVersionFrequency();
        }
    }

    /**
     * Estatísticas gerais
     */
    private function displayGeneralStatistics(): void
    {
        $stats = $this->statistics;
        
        $this->info('');
        $this->info('📊 ESTATÍSTICAS GERAIS:');
        $this->line("• Total analisado: {$stats['total_analyzed']}");
        $this->line("• Com versões definidas: {$stats['with_versions']}");
        $this->line("• Padrões genéricos encontrados: {$stats['generic_patterns_found']}");
        $this->line("• Artigos suspeitos: {$stats['suspicious_articles']}");
        
        if ($stats['with_versions'] > 0) {
            $suspiciousRate = round(($stats['suspicious_articles'] / $stats['with_versions']) * 100, 2);
            $this->line("• Taxa de suspeita: {$suspiciousRate}%");
        }
    }

    /**
     * Artigos suspeitos
     */
    private function displaySuspiciousArticles(): void
    {
        if (empty($this->suspiciousVersions)) {
            $this->info('✅ Nenhum artigo suspeito encontrado!');
            return;
        }

        $this->error('');
        $this->error('🔴 ARTIGOS COM VERSÕES GENÉRICAS:');
        
        // Ordenar por ratio genérico
        usort($this->suspiciousVersions, function($a, $b) {
            return $b['generic_ratio'] <=> $a['generic_ratio'];
        });

        foreach (array_slice($this->suspiciousVersions, 0, 20) as $suspicious) {
            $this->error("");
            $this->line("🚨 ID: {$suspicious['record_id']} | {$suspicious['vehicle']}");
            $this->line("   Categoria: {$suspicious['category']} | Fonte: {$suspicious['source']}");
            $this->line("   Ratio genérico: {$suspicious['generic_ratio']}% ({$suspicious['suspicious_count']}/{$suspicious['total_versions']})");
            $this->line("   Versões: " . implode(', ', $suspicious['versions']));
            
            $patterns = array_column($suspicious['generic_patterns'], 'pattern');
            $this->line("   Padrões: " . implode(', ', array_unique($patterns)));
        }

        if (count($this->suspiciousVersions) > 20) {
            $remaining = count($this->suspiciousVersions) - 20;
            $this->line("");
            $this->line("... e mais {$remaining} artigos suspeitos");
        }
    }

    /**
     * Análise por marca
     */
    private function displayBrandAnalysis(): void
    {
        $this->info('');
        $this->info('🏷️ ANÁLISE POR MARCA:');
        
        foreach ($this->statistics['brand_patterns'] as $brand => $data) {
            if ($data['with_generic'] > 0) {
                $rate = round(($data['with_generic'] / $data['total_articles']) * 100, 1);
                $this->line("• {$brand}: {$data['with_generic']}/{$data['total_articles']} ({$rate}%)");
                
                $topPatterns = array_slice($data['common_patterns'], 0, 3, true);
                foreach ($topPatterns as $pattern => $count) {
                    $this->line("    - {$pattern}: {$count}x");
                }
            }
        }
    }

    /**
     * Frequência de versões
     */
    private function displayVersionFrequency(): void
    {
        $this->info('');
        $this->info('📈 VERSÕES MAIS FREQUENTES:');
        
        arsort($this->statistics['version_frequency']);
        
        foreach (array_slice($this->statistics['version_frequency'], 0, 30, true) as $version => $count) {
            $this->line("• {$version}: {$count}x");
        }
    }

    /**
     * Exportar para CSV
     */
    private function exportToCsv(): void
    {
        if (empty($this->suspiciousVersions)) {
            $this->info('📄 Nenhum dado suspeito para exportar');
            return;
        }

        $filename = 'generic_version_patterns_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path("app/exports/{$filename}");
        
        $directory = dirname($filepath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = fopen($filepath, 'w');
        
        // Header CSV
        fputcsv($file, [
            'Record ID',
            'Vehicle',
            'Category',
            'Source',
            'Generic Ratio %',
            'Suspicious Count',
            'Total Versions',
            'Versions',
            'Generic Patterns'
        ]);

        // Dados
        foreach ($this->suspiciousVersions as $item) {
            fputcsv($file, [
                $item['record_id'],
                $item['vehicle'],
                $item['category'],
                $item['source'],
                $item['generic_ratio'],
                $item['suspicious_count'],
                $item['total_versions'],
                implode('; ', $item['versions']),
                implode('; ', array_column($item['generic_patterns'], 'pattern'))
            ]);
        }

        fclose($file);
        
        $this->info("📄 Relatório exportado: {$filename}");
        $this->line("   Localização: {$filepath}");
    }
}