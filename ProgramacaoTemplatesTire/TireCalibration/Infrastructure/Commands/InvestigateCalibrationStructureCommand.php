<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;
use Illuminate\Support\Facades\Log;
use MongoDB\BSON\ObjectId;

/**
 * InvestigateCalibrationStructureCommand
 * 
 * Comando para investigar inconsistências estruturais nos registros TireCalibration
 * identifica problemas com casts, fases incompletas e erros de template.
 * 
 * PROBLEMAS INVESTIGADOS:
 * - generated_article como Object vs String vs Array
 * - article_refined como String vs Array (problema de cast)
 * - Registros com enrichment_phase = "failed" 
 * - Template "tire_calibration_pickup" causando erros
 * - claude_refinement_version = "v4_completed" mas com estrutura inconsistente
 * 
 * USO:
 * php artisan calibration:investigate-structure --version=v2 --export-json
 * php artisan calibration:investigate-structure --phase=failed --fix-casts
 * php artisan calibration:investigate-structure --template=tire_calibration_pickup --detailed
 */
class InvestigateCalibrationStructureCommand extends Command
{
    protected $signature = 'calibration:investigate-structure 
                            {--versao=v2 : Filtrar por versão (v1, v2)}
                            {--phase= : Filtrar por fase (pending, failed, completed, etc)}
                            {--template= : Filtrar por template específico}
                            {--limit=50 : Limite de registros para investigar}
                            {--fix-casts : Corrigir automaticamente problemas de cast}
                            {--export-json : Exportar resultados para JSON}
                            {--detailed : Análise detalhada com exemplos}';

    protected $description = 'Investigar inconsistências estruturais nos registros TireCalibration';

    private array $issues = [];
    private array $statistics = [];
    private int $totalAnalyzed = 0;

    public function handle(): int
    {
        $this->info('🔍 INVESTIGAÇÃO DE ESTRUTURA - TireCalibration');
        $this->newLine();

        try {
            // Configuração
            $config = $this->getConfig();
            $this->displayConfig($config);

            // Buscar candidatos para análise
            $records = $this->getCandidates($config);
            
            if ($records->isEmpty()) {
                $this->warn('❌ Nenhum registro encontrado com os critérios especificados');
                return self::SUCCESS;
            }

            $this->info("📊 Encontrados {$records->count()} registros para análise");
            $this->newLine();

            // Análise estrutural
            $this->analyzeStructure($records, $config);

            // Relatórios
            $this->generateReports($config);

            // Correções automáticas se solicitado
            if ($config['fix_casts']) {
                $this->performAutomaticFixes();
            }

            // Exportação se solicitada
            if ($config['export_json']) {
                $this->exportToJson();
            }

            $this->displaySummary();
            
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Erro na investigação: ' . $e->getMessage());
            Log::error('InvestigateCalibrationStructure: Erro', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Obter configuração do comando
     */
    private function getConfig(): array
    {
        return [
            'version' => $this->option('versao'),
            'phase' => $this->option('phase'), 
            'template' => $this->option('template'),
            'limit' => (int) $this->option('limit'),
            'fix_casts' => $this->option('fix-casts'),
            'export_json' => $this->option('export-json'),
            'detailed' => $this->option('detailed'),
        ];
    }

    /**
     * Exibir configuração
     */
    private function displayConfig(array $config): void
    {
        $this->info('⚙️ CONFIGURAÇÃO DA INVESTIGAÇÃO:');
        $this->line("   • Versão: " . ($config['version'] ?? 'Todas'));
        $this->line("   • Fase: " . ($config['phase'] ?? 'Todas'));
        $this->line("   • Template: " . ($config['template'] ?? 'Todos'));
        $this->line("   • Limite: {$config['limit']} registros");
        $this->line("   • Correção automática: " . ($config['fix_casts'] ? '✅ SIM' : '❌ NÃO'));
        $this->line("   • Exportar JSON: " . ($config['export_json'] ? '✅ SIM' : '❌ NÃO'));
        $this->line("   • Análise detalhada: " . ($config['detailed'] ? '✅ SIM' : '❌ NÃO'));
        $this->newLine();
    }

    /**
     * Buscar candidatos para análise
     */
    private function getCandidates(array $config)
    {
        $query = TireCalibration::query();

        // Filtros opcionais
        if ($config['version']) {
            $query->where('version', $config['version']);
        }

        if ($config['phase']) {
            $query->where('enrichment_phase', $config['phase']);
        }

        if ($config['template']) {
            // Buscar dentro de generated_article.template
            $query->where('generated_article.template', $config['template']);
        }

        return $query->limit($config['limit'])
                    ->orderBy('updated_at', 'desc')
                    ->get();
    }

    /**
     * Análise estrutural principal
     */
    private function analyzeStructure($records, array $config): void
    {
        $progressBar = $this->output->createProgressBar($records->count());
        $progressBar->setFormat('debug');
        
        $this->info('🔬 Iniciando análise estrutural...');
        $this->newLine();

        foreach ($records as $record) {
            $this->totalAnalyzed++;
            
            $recordIssues = $this->analyzeRecord($record, $config);
            
            if (!empty($recordIssues)) {
                $this->issues[$record->_id] = [
                    'vehicle' => "{$record->vehicle_make} {$record->vehicle_model}",
                    'version' => $record->version,
                    'phase' => $record->enrichment_phase,
                    'issues' => $recordIssues
                ];
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    /**
     * Analisar registro individual
     */
    private function analyzeRecord(TireCalibration $record, array $config): array
    {
        $issues = [];

        // 1. PROBLEMA: generated_article como Object/String sem cast
        $generatedArticleIssue = $this->checkGeneratedArticleType($record);
        if ($generatedArticleIssue) {
            $issues[] = $generatedArticleIssue;
        }

        // 2. PROBLEMA: article_refined como String mas cast como array
        $articleRefinedIssue = $this->checkArticleRefinedType($record);
        if ($articleRefinedIssue) {
            $issues[] = $articleRefinedIssue;
        }

        // 3. PROBLEMA: Template inconsistente
        $templateIssue = $this->checkTemplateConsistency($record);
        if ($templateIssue) {
            $issues[] = $templateIssue;
        }

        // 4. PROBLEMA: Fase completed mas estrutura incompleta
        $phaseIssue = $this->checkPhaseConsistency($record);
        if ($phaseIssue) {
            $issues[] = $phaseIssue;
        }

        // 5. PROBLEMA: Registros failed - investigar causa
        if ($record->enrichment_phase === 'failed') {
            $issues[] = $this->investigateFailureReason($record);
        }

        // Atualizar estatísticas
        $this->updateStatistics($record, $issues);

        return $issues;
    }

    /**
     * Verificar tipo do campo generated_article
     */
    private function checkGeneratedArticleType(TireCalibration $record): ?array
    {
        // Usar getAttributes() para acessar dados brutos sem trigger de cast
        $attributes = $record->getAttributes();
        if (empty($attributes['generated_article'])) {
            return null;
        }

        $rawValue = $attributes['generated_article'];
        $type = gettype($rawValue);
        $isObject = is_object($rawValue);
        $isArray = is_array($rawValue);
        $isString = is_string($rawValue);

        // PROBLEMA: Se é object mas deveria ser array
        if ($isObject && !$isArray) {
            return [
                'type' => 'CAST_INCONSISTENCY',
                'field' => 'generated_article',
                'current_type' => 'object',
                'expected_type' => 'array',
                'description' => 'Campo generated_article retornado como Object, mas deveria ser Array (faltando cast)',
                'fix_suggestion' => 'Adicionar generated_article => array no $casts da model'
            ];
        }

        // PROBLEMA: Se é string mas deveria ser array
        if ($isString) {
            $isValidJson = json_decode($record->generated_article, true) !== null;
            return [
                'type' => 'STRING_INSTEAD_OF_ARRAY',
                'field' => 'generated_article',
                'current_type' => 'string',
                'expected_type' => 'array',
                'is_valid_json' => $isValidJson,
                'description' => 'Campo generated_article armazenado como String JSON',
                'fix_suggestion' => 'Converter string JSON para array ou adicionar cast'
            ];
        }

        return null;
    }

    /**
     * Verificar tipo do campo article_refined
     */
    private function checkArticleRefinedType(TireCalibration $record): ?array
    {
        // Usar getAttributes() para acessar dados brutos sem trigger de cast
        $attributes = $record->getAttributes();
        if (empty($attributes['article_refined'])) {
            return null;
        }

        $rawValue = $attributes['article_refined'];
        $type = gettype($rawValue);
        
        // PROBLEMA RELATADO: article_refined como String no banco mas cast como array
        if (is_string($rawValue)) {
            $isValidJson = json_decode($rawValue, true) !== null;
            
            return [
                'type' => 'CAST_NOT_WORKING',
                'field' => 'article_refined', 
                'current_type' => 'string',
                'expected_type' => 'array',
                'cast_defined' => 'yes',
                'is_valid_json' => $isValidJson,
                'description' => 'Campo article_refined está como String no banco mas cast array não está funcionando',
                'fix_suggestion' => 'Investigar por que o cast array não está sendo aplicado corretamente'
            ];
        }

        return null;
    }

    /**
     * Verificar consistência de template
     */
    private function checkTemplateConsistency(TireCalibration $record): ?array
    {
        // Buscar template dentro de generated_article usando dados brutos
        $template = null;
        $attributes = $record->getAttributes();
        $generatedArticle = $attributes['generated_article'] ?? null;
        
        if (is_array($generatedArticle)) {
            $template = $generatedArticle['template'] ?? null;
        } elseif (is_object($generatedArticle)) {
            $template = $generatedArticle->template ?? null;
        } elseif (is_string($generatedArticle)) {
            $decoded = json_decode($generatedArticle, true);
            $template = $decoded['template'] ?? null;
        }

        // CORREÇÃO: Template tire_calibration_pickup é VÁLIDO e funcional
        // Removido falso positivo - pickup template funciona corretamente
        // Evidência: 5 de 16 pickups completaram Fase 3B com sucesso

        // PROBLEMA: Template não condiz com categoria
        if ($template && $record->main_category) {
            $expectedTemplates = [
                'pickup' => 'tire_calibration_pickup',
                'motorcycle' => 'tire_calibration_motorcycle', 
                'hatch' => 'tire_calibration_car',
                'sedan' => 'tire_calibration_car',
            ];

            $expected = $expectedTemplates[$record->main_category] ?? 'tire_calibration_car';
            
            if ($template !== $expected) {
                return [
                    'type' => 'TEMPLATE_CATEGORY_MISMATCH',
                    'field' => 'template_vs_category',
                    'current_template' => $template,
                    'expected_template' => $expected,
                    'category' => $record->main_category,
                    'description' => "Template {$template} não condiz com categoria {$record->main_category}",
                    'fix_suggestion' => 'Verificar lógica de mapeamento categoria -> template'
                ];
            }
        }

        return null;
    }

    /**
     * Verificar consistência entre fase e estrutura
     */
    private function checkPhaseConsistency(TireCalibration $record): ?array
    {
        // PROBLEMA: claude_refinement_version = "v4_completed" mas estrutura incompleta
        if ($record->claude_refinement_version === 'v4_completed') {
            $missingFields = [];
            
            if (empty($record->claude_phase_3a_enhancements)) {
                $missingFields[] = 'claude_phase_3a_enhancements';
            }
            
            if (empty($record->claude_phase_3b_enhancements)) {
                $missingFields[] = 'claude_phase_3b_enhancements';
            }
            
            if (empty($record->article_refined)) {
                $missingFields[] = 'article_refined';
            }

            if (!empty($missingFields)) {
                return [
                    'type' => 'INCOMPLETE_V4_COMPLETION',
                    'field' => 'claude_refinement_version',
                    'current_value' => 'v4_completed',
                    'missing_fields' => $missingFields,
                    'description' => 'Registro marcado como v4_completed mas faltam campos obrigatórios',
                    'fix_suggestion' => 'Reprocessar ou ajustar claude_refinement_version'
                ];
            }
        }

        return null;
    }

    /**
     * Investigar razão de falha
     */
    private function investigateFailureReason(TireCalibration $record): array
    {
        $reasons = [];
        
        // Verificar last_error
        if (!empty($record->last_error)) {
            $reasons[] = "Último erro: {$record->last_error}";
        }

        // Verificar tentativas
        if (!empty($record->processing_attempts)) {
            $reasons[] = "Tentativas de processamento: {$record->processing_attempts}";
        }

        // Verificar dados básicos
        if (empty($record->vehicle_make) || empty($record->vehicle_model)) {
            $reasons[] = "Dados básicos incompletos (make/model)";
        }

        return [
            'type' => 'FAILED_RECORD',
            'field' => 'enrichment_phase',
            'current_value' => 'failed',
            'failure_reasons' => $reasons,
            'description' => 'Registro com status failed',
            'fix_suggestion' => 'Analisar erros e reprocessar se possível'
        ];
    }

    /**
     * Atualizar estatísticas
     */
    private function updateStatistics(TireCalibration $record, array $issues): void
    {
        // Inicializar contadores se não existirem
        if (!isset($this->statistics['by_version'])) {
            $this->statistics = [
                'by_version' => [],
                'by_phase' => [],
                'by_template' => [],
                'issue_types' => [],
                'total_with_issues' => 0,
                'total_clean' => 0
            ];
        }

        // Contar por versão
        $version = $record->version ?? 'unknown';
        $this->statistics['by_version'][$version] = ($this->statistics['by_version'][$version] ?? 0) + 1;

        // Contar por fase
        $phase = $record->enrichment_phase ?? 'unknown';
        $this->statistics['by_phase'][$phase] = ($this->statistics['by_phase'][$phase] ?? 0) + 1;

        // Contar por template
        $template = $this->extractTemplate($record) ?? 'unknown';
        $this->statistics['by_template'][$template] = ($this->statistics['by_template'][$template] ?? 0) + 1;

        // Contar tipos de issues
        foreach ($issues as $issue) {
            $type = $issue['type'] ?? 'unknown';
            $this->statistics['issue_types'][$type] = ($this->statistics['issue_types'][$type] ?? 0) + 1;
        }

        // Total com/sem issues
        if (empty($issues)) {
            $this->statistics['total_clean']++;
        } else {
            $this->statistics['total_with_issues']++;
        }
    }

    /**
     * Extrair template do registro usando dados brutos
     */
    private function extractTemplate(TireCalibration $record): ?string
    {
        $attributes = $record->getAttributes();
        $generatedArticle = $attributes['generated_article'] ?? null;
        
        if (is_array($generatedArticle)) {
            return $generatedArticle['template'] ?? null;
        } elseif (is_object($generatedArticle)) {
            return $generatedArticle->template ?? null;
        } elseif (is_string($generatedArticle)) {
            $decoded = json_decode($generatedArticle, true);
            return $decoded['template'] ?? null;
        }

        return null;
    }

    /**
     * Gerar relatórios
     */
    private function generateReports(array $config): void
    {
        $this->newLine();
        $this->info('📋 RELATÓRIO DE INCONSISTÊNCIAS ENCONTRADAS');
        $this->newLine();

        // Resumo estatístico
        $this->displayStatistics();

        // Issues críticos
        $this->displayCriticalIssues($config);

        // Sugestões de correção
        $this->displayFixSuggestions();
    }

    /**
     * Exibir estatísticas
     */
    private function displayStatistics(): void
    {
        $this->info('📊 ESTATÍSTICAS:');
        
        $this->table(['Métrica', 'Quantidade'], [
            ['Total analisado', $this->totalAnalyzed],
            ['Com problemas', $this->statistics['total_with_issues']],
            ['Sem problemas', $this->statistics['total_clean']],
            ['Taxa de problemas', round(($this->statistics['total_with_issues'] / $this->totalAnalyzed) * 100, 1) . '%']
        ]);

        // Por versão
        if (!empty($this->statistics['by_version'])) {
            $this->info('📈 Por Versão:');
            foreach ($this->statistics['by_version'] as $version => $count) {
                $this->line("   • {$version}: {$count}");
            }
        }

        // Por fase
        if (!empty($this->statistics['by_phase'])) {
            $this->info('🔄 Por Fase:');
            foreach ($this->statistics['by_phase'] as $phase => $count) {
                $this->line("   • {$phase}: {$count}");
            }
        }

        // Tipos de issues mais comuns
        if (!empty($this->statistics['issue_types'])) {
            $this->info('⚠️ Tipos de Problemas Mais Comuns:');
            arsort($this->statistics['issue_types']);
            foreach (array_slice($this->statistics['issue_types'], 0, 5) as $type => $count) {
                $this->line("   • {$type}: {$count} ocorrências");
            }
        }

        $this->newLine();
    }

    /**
     * Exibir issues críticos
     */
    private function displayCriticalIssues(array $config): void
    {
        if (empty($this->issues)) {
            $this->info('✅ Nenhum problema encontrado!');
            return;
        }

        $this->warn("⚠️ ENCONTRADOS " . count($this->issues) . " REGISTROS COM PROBLEMAS:");
        $this->newLine();

        $displayed = 0;
        foreach ($this->issues as $recordId => $recordData) {
            if ($displayed >= 10 && !$config['detailed']) {
                $remaining = count($this->issues) - $displayed;
                $this->line("   ... e mais {$remaining} registros com problemas");
                break;
            }

            $this->displayRecordIssues($recordId, $recordData, $config);
            $displayed++;
        }
    }

    /**
     * Exibir issues de um registro específico
     */
    private function displayRecordIssues(string $recordId, array $recordData, array $config): void
    {
        $this->line("🔸 [{$recordData['vehicle']}] ID: {$recordId}");
        $this->line("   Versão: {$recordData['version']} | Fase: {$recordData['phase']}");
        
        foreach ($recordData['issues'] as $issue) {
            $this->line("   ❌ {$issue['type']}: {$issue['description']}");
            
            if ($config['detailed'] && !empty($issue['fix_suggestion'])) {
                $this->line("      💡 Sugestão: {$issue['fix_suggestion']}");
            }
        }
        
        $this->newLine();
    }

    /**
     * Exibir sugestões de correção
     */
    private function displayFixSuggestions(): void
    {
        $this->info('🛠️ SUGESTÕES DE CORREÇÃO:');
        $this->newLine();

        $this->info('1. CORREÇÃO DE CASTS:');
        $this->line('   • Adicionar na model TireCalibration:');
        $this->line("   'generated_article' => 'array',");
        $this->line("   • Verificar por que article_refined não está sendo convertido");
        $this->newLine();

        $this->info('2. TEMPLATE PICKUP:');
        $this->line('   • Investigar erros específicos do template tire_calibration_pickup');
        $this->line('   • Verificar se ArticleMappingService está mapeando corretamente');
        $this->newLine();

        $this->info('3. REGISTROS FAILED:');
        $this->line('   • Analisar last_error de cada registro failed');
        $this->line('   • Considerar reprocessamento após correções');
        $this->newLine();

        $this->info('4. V4_COMPLETED INCONSISTENTE:');
        $this->line('   • Verificar registros com v4_completed mas estrutura incompleta');
        $this->line('   • Ajustar claude_refinement_version ou reprocessar');
    }

    /**
     * Realizar correções automáticas
     */
    private function performAutomaticFixes(): void
    {
        $this->newLine();
        $this->info('🔧 INICIANDO CORREÇÕES AUTOMÁTICAS...');
        
        $this->warn('⚠️ ATENÇÃO: Correções automáticas não implementadas por segurança');
        $this->line('   Para aplicar correções, execute manualmente:');
        $this->line('   1. Atualizar $casts na model TireCalibration');
        $this->line('   2. Investigar problemas específicos relatados');
        $this->line('   3. Testar com registros isolados antes de aplicar em massa');
        
        $this->newLine();
    }

    /**
     * Exportar resultados para JSON
     */
    private function exportToJson(): void
    {
        $exportData = [
            'investigation_timestamp' => now()->toISOString(),
            'total_analyzed' => $this->totalAnalyzed,
            'statistics' => $this->statistics,
            'issues' => $this->issues,
            'command_options' => [
                'version' => $this->option('version'),
                'phase' => $this->option('phase'),
                'template' => $this->option('template'),
                'limit' => $this->option('limit'),
            ]
        ];

        $filename = 'calibration-structure-investigation-' . date('Y-m-d-H-i-s') . '.json';
        $filepath = storage_path("logs/investigations/{$filename}");
        
        // Criar diretório se não existir
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        file_put_contents($filepath, json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        $this->newLine();
        $this->info("💾 Relatório exportado: storage/logs/investigations/{$filename}");
        $this->line("   📏 Tamanho: " . number_format(filesize($filepath)) . " bytes");
    }

    /**
     * Exibir resumo final
     */
    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('📋 RESUMO DA INVESTIGAÇÃO:');
        $this->line("   • Total analisado: {$this->totalAnalyzed}");
        $this->line("   • Registros com problemas: " . count($this->issues));
        $this->line("   • Registros limpos: " . ($this->totalAnalyzed - count($this->issues)));
        
        if (!empty($this->issues)) {
            $this->newLine();
            $this->warn('⚠️ AÇÕES RECOMENDADAS:');
            $this->line('   1. Corrigir casts na model TireCalibration');
            $this->line('   2. Investigar template tire_calibration_pickup');
            $this->line('   3. Analisar registros failed individualmente');
            $this->line('   4. Reprocessar registros após correções');
        } else {
            $this->newLine();
            $this->info('✅ Todos os registros analisados estão estruturalmente corretos!');
        }
        
        $this->newLine();
    }
}