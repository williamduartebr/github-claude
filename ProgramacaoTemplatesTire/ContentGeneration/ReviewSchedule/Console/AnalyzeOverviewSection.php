<?php

namespace Src\ContentGeneration\ReviewSchedule\Console;

use Illuminate\Console\Command;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\Eloquent\ReviewScheduleArticle;

class AnalyzeOverviewSection extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'review-schedule:analyze-overview 
                            {--limit=100 : Limit number of articles to analyze}
                            {--show-examples : Show examples of problematic overviews}';

    /**
     * The console command description.
     */
    protected $description = 'Analyze visao_geral_revisoes section structure and content';

    private array $overviewProblems = [];
    private array $statistics = [
        'total_analyzed' => 0,
        'has_overview' => 0,
        'missing_overview' => 0,
        'empty_overview' => 0,
        'invalid_structure' => 0,
        'valid_overview' => 0,
        'structure_types' => [],
        'example_structures' => []
    ];

    public function handle()
    {
        $limit = (int)$this->option('limit');
        $showExamples = $this->option('show-examples');

        $this->info('🔍 Analisando seção visao_geral_revisoes...');

        $articles = ReviewScheduleArticle::limit($limit)->get();
        
        $this->info("📊 Analisando {$articles->count()} artigos...");
        
        $progressBar = $this->output->createProgressBar($articles->count());
        $progressBar->start();

        foreach ($articles as $article) {
            $this->analyzeOverviewSection($article);
            $this->statistics['total_analyzed']++;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->displayOverviewResults($showExamples);
    }

    private function analyzeOverviewSection($article): void
    {
        $content = $this->getContentArray($article);
        
        if (!$content) {
            $this->recordOverviewProblem($article, 'INVALID_CONTENT', 'Conteúdo não é um array válido');
            return;
        }

        // Verificar se visao_geral_revisoes existe
        if (!isset($content['visao_geral_revisoes'])) {
            $this->statistics['missing_overview']++;
            $this->recordOverviewProblem($article, 'MISSING_OVERVIEW', 'visao_geral_revisoes ausente');
            return;
        }

        $this->statistics['has_overview']++;
        $overview = $content['visao_geral_revisoes'];

        // Analisar estrutura da overview
        $this->analyzeOverviewStructure($article, $overview);
    }

    private function analyzeOverviewStructure($article, $overview): void
    {
        $structureType = gettype($overview);
        
        // Contar tipos de estrutura
        if (!isset($this->statistics['structure_types'][$structureType])) {
            $this->statistics['structure_types'][$structureType] = 0;
        }
        $this->statistics['structure_types'][$structureType]++;

        // Guardar exemplos de estruturas diferentes
        if (count($this->statistics['example_structures']) < 5) {
            $this->statistics['example_structures'][] = [
                'article_id' => $article->_id ?? $article->id,
                'type' => $structureType,
                'structure' => $this->getStructureSample($overview),
                'vehicle' => $this->getVehicleInfo($article)
            ];
        }

        switch ($structureType) {
            case 'string':
                $this->analyzeStringOverview($article, $overview);
                break;
            
            case 'array':
                $this->analyzeArrayOverview($article, $overview);
                break;
            
            case 'NULL':
                $this->statistics['empty_overview']++;
                $this->recordOverviewProblem($article, 'NULL_OVERVIEW', 'visao_geral_revisoes é NULL');
                break;
            
            default:
                $this->statistics['invalid_structure']++;
                $this->recordOverviewProblem($article, 'INVALID_STRUCTURE', "Tipo inválido: $structureType");
                break;
        }
    }

    private function analyzeStringOverview($article, string $overview): void
    {
        if (empty(trim($overview))) {
            $this->statistics['empty_overview']++;
            $this->recordOverviewProblem($article, 'EMPTY_STRING_OVERVIEW', 'visao_geral_revisoes é string vazia');
        } else {
            // Verificar se é uma string descritiva válida
            $length = strlen($overview);
            if ($length < 100) {
                $this->recordOverviewProblem($article, 'SHORT_STRING_OVERVIEW', "String muito curta ($length chars)");
            } else {
                $this->statistics['valid_overview']++;
            }
        }
    }

    private function analyzeArrayOverview($article, array $overview): void
    {
        if (empty($overview)) {
            $this->statistics['empty_overview']++;
            $this->recordOverviewProblem($article, 'EMPTY_ARRAY_OVERVIEW', 'visao_geral_revisoes é array vazio');
            return;
        }

        // Verificar se é um array de revisões (estrutura tabular)
        $firstItem = $overview[0] ?? null;
        
        if (is_array($firstItem)) {
            $this->analyzeTabularOverview($article, $overview);
        } else {
            $this->recordOverviewProblem($article, 'INVALID_ARRAY_STRUCTURE', 'Array não contém estrutura tabular');
        }
    }

    private function analyzeTabularOverview($article, array $overview): void
    {
        $issues = [];
        $expectedFields = ['revisao', 'intervalo', 'principais_servicos', 'estimativa_custo'];
        
        foreach ($overview as $index => $row) {
            if (!is_array($row)) {
                $issues[] = "Linha $index não é um array";
                continue;
            }

            // Verificar campos obrigatórios
            foreach ($expectedFields as $field) {
                if (!isset($row[$field])) {
                    $issues[] = "Linha $index: campo '$field' ausente";
                }
            }
        }

        if (count($overview) < 3) {
            $issues[] = "Menos de 3 revisões na tabela (" . count($overview) . ")";
        }

        if (empty($issues)) {
            $this->statistics['valid_overview']++;
        } else {
            $this->recordOverviewProblem($article, 'INCOMPLETE_TABLE', implode('; ', $issues));
        }
    }

    private function getStructureSample($overview)
    {
        if (is_string($overview)) {
            return substr($overview, 0, 100) . (strlen($overview) > 100 ? '...' : '');
        }
        
        if (is_array($overview)) {
            if (empty($overview)) {
                return 'Array vazio';
            }
            
            $first = $overview[0] ?? null;
            if (is_array($first)) {
                return 'Array tabular com campos: ' . implode(', ', array_keys($first));
            }
            
            return 'Array com ' . count($overview) . ' elementos: ' . gettype($first);
        }
        
        return gettype($overview);
    }

    private function getVehicleInfo($article): string
    {
        $content = $this->getContentArray($article);
        $vehicleInfo = $content['extracted_entities'] ?? [];
        
        $marca = $vehicleInfo['marca'] ?? 'N/A';
        $modelo = $vehicleInfo['modelo'] ?? 'N/A';
        $ano = $vehicleInfo['ano'] ?? 'N/A';
        
        return "$marca $modelo $ano";
    }

    private function getContentArray($article): ?array
    {
        $content = $article->content;
        
        if (is_array($content)) {
            return $content;
        }
        
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : null;
        }
        
        return null;
    }

    private function recordOverviewProblem($article, string $issueType, string $description): void
    {
        $content = $this->getContentArray($article);
        $vehicleInfo = $content['extracted_entities'] ?? [];
        
        $this->overviewProblems[] = [
            'id' => $article->_id ?? $article->id,
            'title' => $article->title,
            'vehicle' => [
                'marca' => $vehicleInfo['marca'] ?? 'N/A',
                'modelo' => $vehicleInfo['modelo'] ?? 'N/A',
                'ano' => $vehicleInfo['ano'] ?? 'N/A'
            ],
            'issue_type' => $issueType,
            'description' => $description
        ];
    }

    private function displayOverviewResults(bool $showExamples): void
    {
        $this->info('📊 RESULTADO DA ANÁLISE DA VISÃO GERAL:');
        
        $validPercentage = $this->statistics['has_overview'] > 0 ? 
            round(($this->statistics['valid_overview'] / $this->statistics['has_overview']) * 100, 1) : 0;

        $this->table(
            ['Status', 'Quantidade', 'Percentual'],
            [
                ['Total Analisado', $this->statistics['total_analyzed'], '100%'],
                ['Com Seção', $this->statistics['has_overview'], 
                 round(($this->statistics['has_overview'] / $this->statistics['total_analyzed']) * 100, 1) . '%'],
                ['Sem Seção', $this->statistics['missing_overview'], 
                 round(($this->statistics['missing_overview'] / $this->statistics['total_analyzed']) * 100, 1) . '%'],
                ['Válidas', $this->statistics['valid_overview'], $validPercentage . '%'],
                ['Vazias', $this->statistics['empty_overview'], 
                 round(($this->statistics['empty_overview'] / max($this->statistics['total_analyzed'], 1)) * 100, 1) . '%'],
                ['Estrutura Inválida', $this->statistics['invalid_structure'], 
                 round(($this->statistics['invalid_structure'] / max($this->statistics['total_analyzed'], 1)) * 100, 1) . '%']
            ]
        );

        // Mostrar tipos de estrutura encontrados
        if (!empty($this->statistics['structure_types'])) {
            $this->newLine();
            $this->info('📋 TIPOS DE ESTRUTURA ENCONTRADOS:');
            $typeTable = [];
            foreach ($this->statistics['structure_types'] as $type => $count) {
                $percentage = round(($count / $this->statistics['has_overview']) * 100, 1);
                $typeTable[] = [$type, $count, $percentage . '%'];
            }
            $this->table(['Tipo', 'Quantidade', 'Percentual'], $typeTable);
        }

        // Mostrar exemplos de estruturas
        if ($showExamples && !empty($this->statistics['example_structures'])) {
            $this->displayStructureExamples();
        }

        // Mostrar problemas encontrados
        if (!empty($this->overviewProblems)) {
            $this->displayOverviewProblems();
        }

        $this->displayRecommendations();
    }

    private function displayStructureExamples(): void
    {
        $this->newLine();
        $this->info('📝 EXEMPLOS DE ESTRUTURAS ENCONTRADAS:');
        
        foreach ($this->statistics['example_structures'] as $example) {
            $this->newLine();
            $this->line("Veículo: {$example['vehicle']}");
            $this->line("Tipo: {$example['type']}");
            $this->line("Estrutura: {$example['structure']}");
            $this->line(str_repeat('-', 50));
        }
    }

    private function displayOverviewProblems(): void
    {
        $problemCounts = [];
        foreach ($this->overviewProblems as $problem) {
            $type = $problem['issue_type'];
            $problemCounts[$type] = ($problemCounts[$type] ?? 0) + 1;
        }

        $this->newLine();
        $this->info('🔥 PROBLEMAS MAIS COMUNS:');
        $problemTable = [];
        arsort($problemCounts);
        foreach ($problemCounts as $type => $count) {
            $problemTable[] = [$type, $count];
        }
        $this->table(['Tipo de Problema', 'Ocorrências'], $problemTable);
    }

    private function displayRecommendations(): void
    {
        $this->newLine();
        $this->info('💡 RECOMENDAÇÕES:');
        
        if ($this->statistics['missing_overview'] > 0) {
            $this->line("• {$this->statistics['missing_overview']} artigos precisam de visao_geral_revisoes");
        }
        
        if ($this->statistics['empty_overview'] > 0) {
            $this->line("• {$this->statistics['empty_overview']} artigos têm overview vazia");
        }
        
        if ($this->statistics['invalid_structure'] > 0) {
            $this->line("• {$this->statistics['invalid_structure']} artigos têm estrutura inválida");
        }

        $this->newLine();
        $this->info('🔧 PRÓXIMOS PASSOS:');
        $this->line('1. Execute comando de correção: php artisan review-schedule:fix-overview');
        $this->line('2. Verifique templates generateOverviewTable()');
        $this->line('3. Valide após correção: php artisan review-schedule:analyze-overview');
    }
}