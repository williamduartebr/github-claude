<?php

namespace Src\ContentGeneration\ReviewSchedule\Console;

use Illuminate\Console\Command;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\Eloquent\ReviewScheduleArticle;

class QuickContentCheck extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'review-schedule:quick-content-check 
                            {--limit=200 : Limit number of articles to check}
                            {--vehicle-type= : Filter by vehicle type}';

    /**
     * The console command description.
     */
    protected $description = 'Quick check of cronograma_detalhado and visao_geral_revisoes';

    public function handle()
    {
        $limit = (int)$this->option('limit');
        $vehicleType = $this->option('vehicle-type');

        $this->info('⚡ Verificação rápida de conteúdo...');

        $query = ReviewScheduleArticle::limit($limit);
        
        if ($vehicleType) {
            $this->info("🔍 Filtrando por tipo: {$vehicleType}");
        }

        $articles = $query->get();
        
        $this->info("📊 Verificando {$articles->count()} artigos...");
        
        $stats = [
            'total' => 0,
            'cronograma' => [
                'ok' => 0,
                'missing' => 0,
                'incomplete' => 0
            ],
            'overview' => [
                'ok' => 0,
                'missing' => 0,
                'invalid' => 0
            ],
            'both_ok' => 0,
            'need_fixes' => 0,
            'by_vehicle_type' => []
        ];

        foreach ($articles as $article) {
            $stats['total']++;
            $content = $this->getContentArray($article);
            
            if (!$content) {
                $stats['need_fixes']++;
                continue;
            }

            // Aplicar filtro de tipo se especificado
            $vehicleInfo = $content['extracted_entities'] ?? [];
            $articleVehicleType = strtolower($vehicleInfo['tipo_veiculo'] ?? 'car');
            
            if ($vehicleType && strpos($articleVehicleType, strtolower($vehicleType)) === false) {
                $stats['total']--; // Não contar este artigo
                continue;
            }

            // Contar por tipo de veículo
            if (!isset($stats['by_vehicle_type'][$articleVehicleType])) {
                $stats['by_vehicle_type'][$articleVehicleType] = [
                    'total' => 0,
                    'problems' => 0
                ];
            }
            $stats['by_vehicle_type'][$articleVehicleType]['total']++;

            // Verificar cronograma
            $cronogramaStatus = $this->checkCronograma($content);
            $stats['cronograma'][$cronogramaStatus]++;

            // Verificar overview
            $overviewStatus = $this->checkOverview($content);
            $stats['overview'][$overviewStatus]++;

            // Verificar se ambos estão OK
            if ($cronogramaStatus === 'ok' && $overviewStatus === 'ok') {
                $stats['both_ok']++;
            } else {
                $stats['need_fixes']++;
                $stats['by_vehicle_type'][$articleVehicleType]['problems']++;
            }
        }

        $this->displayQuickResults($stats);
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

    private function checkCronograma(array $content): string
    {
        if (!isset($content['cronograma_detalhado']) || 
            !is_array($content['cronograma_detalhado']) || 
            empty($content['cronograma_detalhado'])) {
            return 'missing';
        }

        $schedule = $content['cronograma_detalhado'];
        
        // Verificar se tem pelo menos 3 revisões com campos básicos
        if (count($schedule) < 3) {
            return 'incomplete';
        }

        // Verificar se primeira revisão tem campos essenciais
        $firstRevision = $schedule[0];
        if (!isset($firstRevision['numero_revisao']) || 
            !isset($firstRevision['intervalo']) || 
            !isset($firstRevision['estimativa_custo'])) {
            return 'incomplete';
        }

        return 'ok';
    }

    private function checkOverview(array $content): string
    {
        if (!isset($content['visao_geral_revisoes'])) {
            return 'missing';
        }

        $overview = $content['visao_geral_revisoes'];
        
        if (empty($overview)) {
            return 'missing';
        }

        // Se é string, verificar se tem tamanho mínimo
        if (is_string($overview)) {
            return strlen(trim($overview)) >= 100 ? 'ok' : 'invalid';
        }

        // Se é array, verificar estrutura básica
        if (is_array($overview)) {
            if (count($overview) < 3) {
                return 'invalid';
            }
            
            $firstItem = $overview[0] ?? null;
            if (!is_array($firstItem) || 
                !isset($firstItem['revisao']) || 
                !isset($firstItem['intervalo'])) {
                return 'invalid';
            }
            
            return 'ok';
        }

        return 'invalid';
    }

    private function displayQuickResults(array $stats): void
    {
        $this->newLine();
        $this->info('📈 RESULTADO DA VERIFICAÇÃO RÁPIDA:');

        // Estatísticas gerais
        $bothOkPercentage = $stats['total'] > 0 ? 
            round(($stats['both_ok'] / $stats['total']) * 100, 1) : 0;

        $this->table(
            ['Seção', 'OK', 'Problemas', '% OK'],
            [
                [
                    'Cronograma Detalhado',
                    $stats['cronograma']['ok'],
                    $stats['cronograma']['missing'] + $stats['cronograma']['incomplete'],
                    round(($stats['cronograma']['ok'] / max($stats['total'], 1)) * 100, 1) . '%'
                ],
                [
                    'Visão Geral',
                    $stats['overview']['ok'],
                    $stats['overview']['missing'] + $stats['overview']['invalid'],
                    round(($stats['overview']['ok'] / max($stats['total'], 1)) * 100, 1) . '%'
                ],
                [
                    'Ambas Seções',
                    $stats['both_ok'],
                    $stats['need_fixes'],
                    $bothOkPercentage . '%'
                ]
            ]
        );

        // Detalhamento dos problemas
        $this->newLine();
        $this->info('🔍 DETALHAMENTO DOS PROBLEMAS:');
        
        $this->table(
            ['Tipo de Problema', 'Cronograma', 'Overview'],
            [
                ['Ausente/Vazio', $stats['cronograma']['missing'], $stats['overview']['missing']],
                ['Incompleto/Inválido', $stats['cronograma']['incomplete'], $stats['overview']['invalid']]
            ]
        );

        // Estatísticas por tipo de veículo
        if (!empty($stats['by_vehicle_type'])) {
            $this->newLine();
            $this->info('🚗 PROBLEMAS POR TIPO DE VEÍCULO:');
            
            $vehicleTable = [];
            foreach ($stats['by_vehicle_type'] as $type => $data) {
                $problemPercentage = $data['total'] > 0 ? 
                    round(($data['problems'] / $data['total']) * 100, 1) : 0;
                
                $vehicleTable[] = [
                    $type,
                    $data['total'],
                    $data['problems'],
                    $problemPercentage . '%'
                ];
            }
            
            $this->table(['Tipo de Veículo', 'Total', 'Com Problemas', '% Problemas'], $vehicleTable);
        }

        // Resumo e recomendações
        $this->newLine();
        if ($stats['both_ok'] === $stats['total']) {
            $this->info("🎉 Excelente! Todos os artigos estão com cronograma e overview OK!");
        } else {
            $this->warn("⚠️ {$stats['need_fixes']} de {$stats['total']} artigos precisam de correção!");
            
            $this->info('💡 COMANDOS RECOMENDADOS:');
            
            if ($stats['cronograma']['missing'] + $stats['cronograma']['incomplete'] > 0) {
                $problemCount = $stats['cronograma']['missing'] + $stats['cronograma']['incomplete'];
                $this->line("📋 Cronograma: {$problemCount} problemas");
                $this->line("   php artisan review-schedule:fix-detailed-schedule --limit={$problemCount} --force");
            }
            
            if ($stats['overview']['missing'] + $stats['overview']['invalid'] > 0) {
                $problemCount = $stats['overview']['missing'] + $stats['overview']['invalid'];
                $this->line("📊 Overview: {$problemCount} problemas");
                $this->line("   php artisan review-schedule:fix-overview --limit={$problemCount} --force");
            }
            
            $this->newLine();
            $this->line("🔧 Corrigir tudo de uma vez:");
            $this->line("   php artisan review-schedule:fix-detailed-schedule --limit={$stats['total']} --force");
            $this->line("   php artisan review-schedule:fix-overview --limit={$stats['total']} --force");
        }

        $this->newLine();
        $this->info('📋 VERIFICAÇÃO APÓS CORREÇÃO:');
        $this->line("   php artisan review-schedule:quick-content-check --limit={$stats['total']}");
    }
}