<?php

namespace Src\ContentGeneration\ReviewSchedule\Console;

use Illuminate\Console\Command;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\Eloquent\ReviewScheduleArticle;

class FindRealOverviewProblems extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'review-schedule:find-real-problems 
                            {--limit=1000 : Limit number of articles to check}
                            {--show-examples=5 : Number of examples to show}';

    /**
     * The console command description.
     */
    protected $description = 'Find the actual 250 problematic overview articles';

    public function handle()
    {
        $limit = (int)$this->option('limit');
        $showExamples = (int)$this->option('show-examples');

        $this->info('🔍 Procurando os artigos realmente problemáticos...');

        $articles = ReviewScheduleArticle::limit($limit)->get();

        $this->info("📊 Analisando {$articles->count()} artigos...");

        $realProblems = [];
        $statusCounts = [
            'missing' => 0,
            'invalid' => 0,
            'ok' => 0
        ];

        foreach ($articles as $article) {
            $content = $this->getContentArray($article);

            if (!$content) {
                continue;
            }

            $status = $this->getDetailedOverviewStatus($content);
            $statusCounts[$status['status']]++;

            if ($status['status'] !== 'ok') {
                $realProblems[] = [
                    'id' => $article->_id ?? $article->id,
                    'title' => substr($article->title, 0, 50) . '...',
                    'vehicle' => $this->getVehicleInfo($content),
                    'status' => $status['status'],
                    'reason' => $status['reason'],
                    'overview_type' => $status['overview_type'],
                    'overview_sample' => $status['sample']
                ];
            }
        }

        $this->displayRealProblems($realProblems, $statusCounts, $showExamples);
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

    private function getDetailedOverviewStatus(array $content): array
    {
        // Verificar se existe
        if (!isset($content['visao_geral_revisoes'])) {
            return [
                'status' => 'missing',
                'reason' => 'Seção visao_geral_revisoes não existe',
                'overview_type' => 'undefined',
                'sample' => 'N/A'
            ];
        }

        $overview = $content['visao_geral_revisoes'];
        $overviewType = gettype($overview);

        // Verificar se é null
        if ($overview === null) {
            return [
                'status' => 'invalid',
                'reason' => 'visao_geral_revisoes é NULL',
                'overview_type' => 'NULL',
                'sample' => 'null'
            ];
        }

        // Verificar se está vazio
        if (empty($overview)) {
            return [
                'status' => 'invalid',
                'reason' => 'visao_geral_revisoes está vazio',
                'overview_type' => $overviewType,
                'sample' => $overviewType === 'array' ? '[]' : '""'
            ];
        }

        // Se é string
        if (is_string($overview)) {
            $length = strlen(trim($overview));
            if ($length < 100) {
                return [
                    'status' => 'invalid',
                    'reason' => "String muito curta ({$length} chars)",
                    'overview_type' => 'string',
                    'sample' => substr($overview, 0, 50) . '...'
                ];
            }

            return [
                'status' => 'ok',
                'reason' => 'String válida',
                'overview_type' => 'string',
                'sample' => substr($overview, 0, 50) . '...'
            ];
        }

        // Se é array
        if (is_array($overview)) {
            // Verificar quantidade mínima
            if (count($overview) < 3) {
                return [
                    'status' => 'invalid',
                    'reason' => 'Array com menos de 3 elementos (' . count($overview) . ')',
                    'overview_type' => 'array',
                    'sample' => 'Array[' . count($overview) . ']'
                ];
            }

            // Verificar estrutura do primeiro elemento
            $firstItem = $overview[0] ?? null;

            if (!is_array($firstItem)) {
                return [
                    'status' => 'invalid',
                    'reason' => 'Primeiro elemento não é array: ' . gettype($firstItem),
                    'overview_type' => 'array',
                    'sample' => 'Array[' . count($overview) . '] com ' . gettype($firstItem)
                ];
            }

            // Verificar campos obrigatórios
            $requiredFields = ['revisao', 'intervalo'];
            $missingFields = [];

            foreach ($requiredFields as $field) {
                if (!isset($firstItem[$field])) {
                    $missingFields[] = $field;
                }
            }

            if (!empty($missingFields)) {
                return [
                    'status' => 'invalid',
                    'reason' => 'Campos ausentes: ' . implode(', ', $missingFields),
                    'overview_type' => 'array',
                    'sample' => 'Campos presentes: ' . implode(', ', array_keys($firstItem))
                ];
            }

            return [
                'status' => 'ok',
                'reason' => 'Array tabular válido',
                'overview_type' => 'array',
                'sample' => 'Array[' . count($overview) . '] com campos: ' . implode(', ', array_keys($firstItem))
            ];
        }

        // Tipo não suportado
        return [
            'status' => 'invalid',
            'reason' => 'Tipo não suportado: ' . $overviewType,
            'overview_type' => $overviewType,
            'sample' => 'Tipo: ' . $overviewType
        ];
    }

    private function getVehicleInfo(array $content): string
    {
        $vehicleInfo = $content['extracted_entities'] ?? [];
        $marca = $vehicleInfo['marca'] ?? 'N/A';
        $modelo = $vehicleInfo['modelo'] ?? 'N/A';
        $ano = $vehicleInfo['ano'] ?? 'N/A';

        return "$marca $modelo $ano";
    }

    private function displayRealProblems(array $realProblems, array $statusCounts, int $showExamples): void
    {
        $this->newLine();
        $this->info('📊 RESULTADO DA BUSCA REAL:');

        $total = array_sum($statusCounts);

        $this->table(
            ['Status', 'Quantidade', 'Percentual'],
            [
                ['OK', $statusCounts['ok'], round(($statusCounts['ok'] / max($total, 1)) * 100, 1) . '%'],
                ['Inválidos', $statusCounts['invalid'], round(($statusCounts['invalid'] / max($total, 1)) * 100, 1) . '%'],
                ['Ausentes', $statusCounts['missing'], round(($statusCounts['missing'] / max($total, 1)) * 100, 1) . '%'],
                ['TOTAL', $total, '100%']
            ]
        );

        $totalProblems = $statusCounts['invalid'] + $statusCounts['missing'];

        if ($totalProblems === 0) {
            $this->info('✅ Não foram encontrados problemas reais!');
            $this->line('Todos os artigos têm visao_geral_revisoes válida.');

            $this->newLine();
            $this->info('🤔 POSSÍVEL CAUSA DA DISCREPÂNCIA ANTERIOR:');
            $this->line('1. Bug no comando quick-content-check');
            $this->line('2. Diferença de critérios entre comandos');
            $this->line('3. Cache ou estado inconsistente');

            return;
        }

        $this->newLine();
        $this->warn("⚠️ Encontrados {$totalProblems} artigos com problemas reais!");

        // Agrupar problemas por tipo
        $problemsByType = [];
        foreach ($realProblems as $problem) {
            $key = $problem['reason'];
            if (!isset($problemsByType[$key])) {
                $problemsByType[$key] = [];
            }
            $problemsByType[$key][] = $problem;
        }

        $this->newLine();
        $this->info('🔍 TIPOS DE PROBLEMAS ENCONTRADOS:');

        foreach ($problemsByType as $reason => $problems) {
            $this->line("• {$reason}: " . count($problems) . " artigos");
        }

        // Mostrar exemplos
        if ($showExamples > 0 && !empty($realProblems)) {
            $this->newLine();
            $this->info("📋 EXEMPLOS DE PROBLEMAS (primeiros {$showExamples}):");

            foreach (array_slice($realProblems, 0, $showExamples) as $problem) {
                $this->newLine();
                $this->line("ID: {$problem['id']}");
                $this->line("Veículo: {$problem['vehicle']}");
                $this->line("Status: {$problem['status']}");
                $this->line("Motivo: {$problem['reason']}");
                $this->line("Tipo: {$problem['overview_type']}");
                $this->line("Amostra: {$problem['overview_sample']}");
                $this->line(str_repeat('-', 50));
            }
        }

        $this->newLine();
        $this->info('🔧 COMANDO DE CORREÇÃO ESPECÍFICO:');
        $this->line("php artisan review-schedule:fix-overview --limit={$totalProblems} --force");
    }
}
