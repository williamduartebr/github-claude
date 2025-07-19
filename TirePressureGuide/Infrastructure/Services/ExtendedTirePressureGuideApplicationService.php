<?php

namespace Src\ContentGeneration\TirePressureGuide\Application\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;

/**
 * Extended TirePressureGuideApplicationService
 * 
 * NOVOS MÉTODOS:
 * - getCandidateArticlesForTesting() - Para filtros específicos de teste
 * - publishToTempArticlesWithFilters() - Publicação com filtros avançados
 * - getRecentlyPublishedTempArticles() - Para testes pós-publicação
 * - validateContentStructure() - Validação de estrutura
 */
class ExtendedTirePressureGuideApplicationService extends TirePressureGuideApplicationService
{
    /**
     * Obter artigos candidatos para teste com filtros
     */
    public function getCandidateArticlesForTesting(
        string $status = 'claude_enhanced',
        int $limit = 50,
        ?string $filterMake = null,
        ?string $filterYear = null
    ): Collection {
        try {
            $query = TirePressureArticle::where('generation_status', $status);

            // Aplicar filtros
            if ($filterMake) {
                $query->where('make', 'like', "%{$filterMake}%");
            }

            if ($filterYear) {
                $query->where('year', $filterYear);
            }

            // Priorizar artigos com seções completas
            $query->withSectionsComplete();

            // Ordenar por qualidade e data de refinamento
            $query->orderBy('content_score', 'desc')
                  ->orderBy('sections_last_refined_at', 'desc');

            return $query->limit($limit)->get();

        } catch (\Exception $e) {
            Log::error('Erro ao obter artigos candidatos para teste: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Publicar para TempArticle com filtros avançados
     */
    public function publishToTempArticlesWithFilters(
        string $status = 'claude_enhanced',
        int $limit = 100,
        bool $dryRun = false,
        bool $overwrite = false,
        ?string $filterMake = null,
        ?string $filterYear = null,
        ?callable $progressCallback = null
    ): object {
        $results = (object)[
            'published' => 0,
            'failed' => 0,
            'skipped' => 0,
            'overwritten' => 0,
            'errors' => []
        ];

        try {
            // Obter artigos candidatos com filtros
            $articles = $this->getCandidateArticlesForTesting($status, $limit, $filterMake, $filterYear);

            $totalArticles = $articles->count();
            $processed = 0;

            foreach ($articles as $article) {
                try {
                    // Verificar se já existe no TempArticle
                    $existingTempArticle = $this->findExistingTempArticle($article->slug);
                    
                    if ($existingTempArticle && !$overwrite) {
                        $results->skipped++;
                        $results->errors[] = "Artigo já existe (use --overwrite): {$article->slug}";
                        continue;
                    }

                    // Validar se possui estrutura adequada
                    $structureValidation = $this->validateArticleForPublication($article);
                    if (!$structureValidation['is_valid']) {
                        $results->failed++;
                        $results->errors[] = "Estrutura inválida para {$article->slug}: " . 
                                           implode(', ', $structureValidation['critical_errors']);
                        continue;
                    }

                    if (!$dryRun) {
                        $tempArticleData = $this->convertToTempArticleFormat($article);

                        if ($existingTempArticle && $overwrite) {
                            // Atualizar artigo existente
                            $existingTempArticle->fill($tempArticleData);
                            if ($existingTempArticle->save()) {
                                $results->overwritten++;
                            } else {
                                $results->failed++;
                                $results->errors[] = "Falha ao atualizar TempArticle: {$article->slug}";
                            }
                        } else {
                            // Criar novo artigo
                            $tempArticle = new TempArticle();
                            $tempArticle->fill($tempArticleData);

                            if ($tempArticle->save()) {
                                $results->published++;
                            } else {
                                $results->failed++;
                                $results->errors[] = "Falha ao salvar novo TempArticle: {$article->slug}";
                            }
                        }
                    } else {
                        $results->published++; // Simular para dry run
                    }

                } catch (\Exception $e) {
                    $results->failed++;
                    $results->errors[] = "Erro ao processar {$article->slug}: " . $e->getMessage();
                }

                $processed++;
                if ($progressCallback) {
                    $progressCallback($processed, $totalArticles);
                }
            }

        } catch (\Exception $e) {
            $results->errors[] = "Erro ao buscar artigos com filtros: " . $e->getMessage();
        }

        return $results;
    }

    /**
     * Encontrar TempArticle existente
     */
    private function findExistingTempArticle(string $slug): ?TempArticle
    {
        try {
            return TempArticle::where('slug', $slug)
                             ->where('source', 'tire_pressure_guide')
                             ->first();
        } catch (\Exception $e) {
            Log::error('Erro ao buscar TempArticle existente: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validar artigo para publicação
     */
    private function validateArticleForPublication($article): array
    {
        $validation = [
            'is_valid' => true,
            'warnings' => [],
            'critical_errors' => []
        ];

        // Verificações críticas
        if (empty($article->make)) {
            $validation['critical_errors'][] = 'Marca ausente';
            $validation['is_valid'] = false;
        }

        if (empty($article->model)) {
            $validation['critical_errors'][] = 'Modelo ausente';
            $validation['is_valid'] = false;
        }

        if (empty($article->pressure_light_front) || $article->pressure_light_front <= 0) {
            $validation['critical_errors'][] = 'Pressão dianteira inválida';
            $validation['is_valid'] = false;
        }

        if (empty($article->pressure_light_rear) || $article->pressure_light_rear <= 0) {
            $validation['critical_errors'][] = 'Pressão traseira inválida';
            $validation['is_valid'] = false;
        }

        // Verificar conteúdo mínimo
        $content = $article->article_content ?? [];
        if (empty($content)) {
            $validation['critical_errors'][] = 'Conteúdo ausente';
            $validation['is_valid'] = false;
        }

        // Verificações de qualidade
        if (($article->content_score ?? 0) < 5.0) {
            $validation['warnings'][] = 'Score de qualidade baixo';
        }

        // Se status é claude_enhanced, verificar refinamento
        if ($article->generation_status === 'claude_enhanced') {
            if (!$this->hasCompletedSectionsRefinement($article)) {
                $validation['warnings'][] = 'Refinamento das seções incompleto';
            }
        }

        return $validation;
    }

    /**
     * Obter TempArticles publicados recentemente
     */
    public function getRecentlyPublishedTempArticles(int $limit = 10): Collection
    {
        try {
            return TempArticle::where('source', 'tire_pressure_guide')
                             ->where('template', 'ideal_tire_pressure_car')
                             ->orderBy('created_at', 'desc')
                             ->limit($limit)
                             ->get();
        } catch (\Exception $e) {
            Log::error('Erro ao obter TempArticles recentes: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Validar estrutura de conteúdo para compatibilidade com ViewModels
     */
    public function validateContentStructure(array $content, string $template = 'ideal_tire_pressure_car'): array
    {
        $validation = [
            'is_compatible' => true,
            'required_sections_present' => 0,
            'optional_sections_present' => 0,
            'missing_sections' => [],
            'structure_issues' => []
        ];

        if ($template === 'ideal_tire_pressure_car') {
            return $this->validateCarContentStructure($content);
        } elseif ($template === 'ideal_tire_pressure_motorcycle') {
            return $this->validateMotorcycleContentStructure($content);
        }

        return $validation;
    }

    /**
     * Validar estrutura de conteúdo para carros
     */
    private function validateCarContentStructure(array $content): array
    {
        $validation = [
            'is_compatible' => true,
            'required_sections_present' => 0,
            'optional_sections_present' => 0,
            'missing_sections' => [],
            'structure_issues' => []
        ];

        // Seções obrigatórias para IdealTirePressureCarViewModel
        $requiredSections = [
            'introducao' => 'string',
            'especificacoes_pneus' => 'array',
            'tabela_pressoes' => 'array',
            'conversao_unidades' => 'array',
            'localizacao_etiqueta' => 'array',
            'beneficios_calibragem' => 'array',
            'dicas_manutencao' => 'array',
            'alertas_importantes' => 'array',
            'perguntas_frequentes' => 'array',
            'consideracoes_finais' => 'string'
        ];

        foreach ($requiredSections as $section => $expectedType) {
            if (!isset($content[$section])) {
                $validation['missing_sections'][] = $section;
                $validation['is_compatible'] = false;
            } elseif (empty($content[$section])) {
                $validation['structure_issues'][] = "Seção '{$section}' está vazia";
            } else {
                $validation['required_sections_present']++;
                
                // Validar tipo específico
                $actualType = is_array($content[$section]) ? 'array' : 'string';
                if ($actualType !== $expectedType) {
                    $validation['structure_issues'][] = "Seção '{$section}' deveria ser {$expectedType}, encontrado {$actualType}";
                }
            }
        }

        // Validações específicas
        $this->validateSpecificCarSections($content, $validation);

        return $validation;
    }

    /**
     * Validar seções específicas para carros
     */
    private function validateSpecificCarSections(array $content, array &$validation): void
    {
        // Validar tabela_pressoes
        if (!empty($content['tabela_pressoes'])) {
            $table = $content['tabela_pressoes'];
            
            if (empty($table['versoes']) && empty($table['condicoes_uso'])) {
                $validation['structure_issues'][] = 'Tabela de pressões deve ter versões ou condições de uso';
                $validation['is_compatible'] = false;
            }

            // Validar estrutura das versões
            if (!empty($table['versoes']) && is_array($table['versoes'])) {
                foreach ($table['versoes'] as $index => $version) {
                    $requiredFields = ['nome_versao', 'pressao_dianteira_normal', 'pressao_traseira_normal'];
                    foreach ($requiredFields as $field) {
                        if (empty($version[$field])) {
                            $validation['structure_issues'][] = "Versão {$index}: campo '{$field}' ausente";
                        }
                    }
                }
            }

            // Validar condições de uso
            if (!empty($table['condicoes_uso']) && is_array($table['condicoes_uso'])) {
                foreach ($table['condicoes_uso'] as $index => $condition) {
                    if (empty($condition['situacao'])) {
                        $validation['structure_issues'][] = "Condição {$index}: situação ausente";
                    }
                }
            }
        }

        // Validar especificacoes_pneus
        if (!empty($content['especificacoes_pneus'])) {
            $specs = $content['especificacoes_pneus'];
            if (empty($specs['medida_original'])) {
                $validation['structure_issues'][] = 'Especificações devem ter medida_original';
            }
        }

        // Validar conversao_unidades
        if (!empty($content['conversao_unidades'])) {
            $conversion = $content['conversao_unidades'];
            if (empty($conversion['tabela_conversao']) || !is_array($conversion['tabela_conversao'])) {
                $validation['structure_issues'][] = 'Conversão de unidades deve ter tabela_conversao array';
            }
        }

        // Validar perguntas_frequentes
        if (!empty($content['perguntas_frequentes'])) {
            $faq = $content['perguntas_frequentes'];
            if (is_array($faq)) {
                foreach ($faq as $index => $item) {
                    if (empty($item['question']) || empty($item['answer'])) {
                        $validation['structure_issues'][] = "FAQ {$index}: deve ter 'question' e 'answer'";
                    }
                }
            }
        }

        // Validar beneficios_calibragem
        if (!empty($content['beneficios_calibragem'])) {
            $benefits = $content['beneficios_calibragem'];
            $expectedCategories = ['seguranca', 'economia', 'desempenho'];
            foreach ($expectedCategories as $category) {
                if (empty($benefits[$category]) || !is_array($benefits[$category])) {
                    $validation['structure_issues'][] = "Benefícios devem ter categoria '{$category}' como array";
                }
            }
        }
    }

    /**
     * Validar estrutura de conteúdo para motocicletas
     */
    private function validateMotorcycleContentStructure(array $content): array
    {
        $validation = [
            'is_compatible' => true,
            'required_sections_present' => 0,
            'optional_sections_present' => 0,
            'missing_sections' => [],
            'structure_issues' => []
        ];

        // Seções específicas para motocicletas
        $requiredSections = [
            'introducao' => 'string',
            'especificacoes_pneus' => 'array',
            'tabela_pressoes' => 'array',
            'conversao_unidades' => 'array',
            'localizacao_informacoes' => 'array',
            'beneficios_calibragem' => 'array',
            'consideracoes_especiais' => 'array',
            'dicas_manutencao' => 'array',
            'alertas_criticos' => 'array',
            'procedimento_calibragem' => 'array',
            'perguntas_frequentes' => 'array',
            'consideracoes_finais' => 'string'
        ];

        foreach ($requiredSections as $section => $expectedType) {
            if (!isset($content[$section])) {
                $validation['missing_sections'][] = $section;
                $validation['is_compatible'] = false;
            } elseif (empty($content[$section])) {
                $validation['structure_issues'][] = "Seção '{$section}' está vazia";
            } else {
                $validation['required_sections_present']++;
            }
        }

        return $validation;
    }

    /**
     * Gerar relatório de compatibilidade de estrutura
     */
    public function generateCompatibilityReport(int $limit = 100): array
    {
        $report = [
            'total_analyzed' => 0,
            'compatible' => 0,
            'incompatible' => 0,
            'by_template' => [],
            'common_issues' => [],
            'recommendations' => []
        ];

        try {
            $tempArticles = TempArticle::where('source', 'tire_pressure_guide')
                                    ->limit($limit)
                                    ->get();

            foreach ($tempArticles as $article) {
                $report['total_analyzed']++;
                
                $template = $article->template ?? 'ideal_tire_pressure_car';
                if (!isset($report['by_template'][$template])) {
                    $report['by_template'][$template] = [
                        'total' => 0,
                        'compatible' => 0,
                        'issues' => []
                    ];
                }
                $report['by_template'][$template]['total']++;

                $validation = $this->validateContentStructure($article->content ?? [], $template);

                if ($validation['is_compatible']) {
                    $report['compatible']++;
                    $report['by_template'][$template]['compatible']++;
                } else {
                    $report['incompatible']++;
                    
                    // Coletar issues comuns
                    foreach ($validation['structure_issues'] as $issue) {
                        if (!isset($report['common_issues'][$issue])) {
                            $report['common_issues'][$issue] = 0;
                        }
                        $report['common_issues'][$issue]++;
                    }

                    $report['by_template'][$template]['issues'] = array_merge(
                        $report['by_template'][$template]['issues'],
                        $validation['structure_issues']
                    );
                }
            }

            // Ordenar issues mais comuns
            arsort($report['common_issues']);

            // Gerar recomendações
            $report['recommendations'] = $this->generateRecommendations($report);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório de compatibilidade: ' . $e->getMessage());
            $report['error'] = $e->getMessage();
        }

        return $report;
    }

    /**
     * Gerar recomendações baseadas no relatório
     */
    private function generateRecommendations(array $report): array
    {
        $recommendations = [];

        // Taxa de compatibilidade
        $compatibilityRate = $report['total_analyzed'] > 0 
            ? ($report['compatible'] / $report['total_analyzed']) * 100 
            : 0;

        if ($compatibilityRate < 80) {
            $recommendations[] = "Taxa de compatibilidade baixa ({$compatibilityRate}%). Revisar processo de geração de conteúdo.";
        }

        // Issues mais comuns
        if (!empty($report['common_issues'])) {
            $topIssue = array_key_first($report['common_issues']);
            $count = $report['common_issues'][$topIssue];
            $recommendations[] = "Issue mais comum: '{$topIssue}' ({$count} ocorrências). Priorizar correção.";
        }

        // Por template
        foreach ($report['by_template'] as $template => $data) {
            $templateRate = $data['total'] > 0 ? ($data['compatible'] / $data['total']) * 100 : 0;
            if ($templateRate < 70) {
                $recommendations[] = "Template '{$template}' com baixa compatibilidade ({$templateRate}%). Necessita revisão.";
            }
        }

        return $recommendations;
    }

    /**
     * Corrigir estruturas automaticamente
     */
    public function autoFixStructureIssues(int $limit = 50): object
    {
        $results = (object)[
            'analyzed' => 0,
            'fixed' => 0,
            'failed' => 0,
            'errors' => []
        ];

        try {
            $tempArticles = TempArticle::where('source', 'tire_pressure_guide')
                                    ->limit($limit)
                                    ->get();

            foreach ($tempArticles as $article) {
                $results->analyzed++;

                try {
                    $content = $article->content ?? [];
                    $template = $article->template ?? 'ideal_tire_pressure_car';
                    
                    $validation = $this->validateContentStructure($content, $template);
                    
                    if (!$validation['is_compatible']) {
                        $fixedContent = $this->applyAutoFixes($content, $validation, $template);
                        
                        if ($fixedContent !== $content) {
                            $article->content = $fixedContent;
                            $article->save();
                            $results->fixed++;
                        }
                    }

                } catch (\Exception $e) {
                    $results->failed++;
                    $results->errors[] = "Erro ao corrigir {$article->slug}: " . $e->getMessage();
                }
            }

        } catch (\Exception $e) {
            $results->errors[] = "Erro geral na correção automática: " . $e->getMessage();
        }

        return $results;
    }

    /**
     * Aplicar correções automáticas
     */
    private function applyAutoFixes(array $content, array $validation, string $template): array
    {
        $fixedContent = $content;

        // Corrigir seções ausentes
        foreach ($validation['missing_sections'] as $missingSection) {
            $fixedContent[$missingSection] = $this->generateDefaultSectionContent($missingSection, null);
        }

        // Correções específicas por template
        if ($template === 'ideal_tire_pressure_car') {
            $fixedContent = $this->applyCarSpecificFixes($fixedContent);
        }

        return $fixedContent;
    }

    /**
     * Aplicar correções específicas para carros
     */
    private function applyCarSpecificFixes(array $content): array
    {
        // Corrigir tabela_pressoes se estiver malformada
        if (isset($content['tabela_pressoes']) && is_array($content['tabela_pressoes'])) {
            $table = $content['tabela_pressoes'];
            
            // Garantir estrutura mínima
            if (empty($table['versoes']) && empty($table['condicoes_uso'])) {
                $content['tabela_pressoes']['versoes'] = [
                    [
                        'nome_versao' => 'Todas as versões',
                        'motor' => '1.6 Flex',
                        'medida_pneu' => '',
                        'pressao_dianteira_normal' => '30 PSI',
                        'pressao_traseira_normal' => '28 PSI',
                        'pressao_dianteira_carregado' => '34 PSI',
                        'pressao_traseira_carregado' => '32 PSI',
                        'observacao' => 'Pressões padrão'
                    ]
                ];
            }
        }

        // Corrigir especificacoes_pneus
        if (isset($content['especificacoes_pneus']) && empty($content['especificacoes_pneus']['medida_original'])) {
            $content['especificacoes_pneus']['medida_original'] = '185/65 R15';
        }

        // Corrigir conversao_unidades
        if (isset($content['conversao_unidades']) && empty($content['conversao_unidades']['tabela_conversao'])) {
            $content['conversao_unidades'] = $this->generateDefaultUnitConversion();
        }

        // Corrigir perguntas_frequentes se não estiver no formato correto
        if (isset($content['perguntas_frequentes']) && is_array($content['perguntas_frequentes'])) {
            foreach ($content['perguntas_frequentes'] as &$faq) {
                if (is_array($faq) && (empty($faq['question']) || empty($faq['answer']))) {
                    $faq = [
                        'question' => 'Qual a pressão recomendada?',
                        'answer' => 'Consulte a tabela de pressões específica para seu veículo.'
                    ];
                }
            }
        }

        return $content;
    }

    /**
     * Obter estatísticas de compatibilidade
     */
    public function getCompatibilityStats(): array
    {
        try {
            $totalTempArticles = TempArticle::where('source', 'tire_pressure_guide')->count();
            
            if ($totalTempArticles === 0) {
                return [
                    'total' => 0,
                    'compatibility_rate' => 0,
                    'by_template' => [],
                    'needs_analysis' => true
                ];
            }

            // Analisar uma amostra
            $sampleSize = min(100, $totalTempArticles);
            $report = $this->generateCompatibilityReport($sampleSize);

            $compatibilityRate = $report['total_analyzed'] > 0 
                ? round(($report['compatible'] / $report['total_analyzed']) * 100, 2)
                : 0;

            return [
                'total' => $totalTempArticles,
                'sample_analyzed' => $report['total_analyzed'],
                'compatibility_rate' => $compatibilityRate,
                'compatible_count' => $report['compatible'],
                'incompatible_count' => $report['incompatible'],
                'by_template' => $report['by_template'],
                'top_issues' => array_slice($report['common_issues'], 0, 5, true),
                'recommendations' => $report['recommendations']
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao obter estatísticas de compatibilidade: ' . $e->getMessage());
            return [
                'total' => 0,
                'compatibility_rate' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Exportar relatório detalhado de compatibilidade
     */
    public function exportCompatibilityReport(string $format = 'json'): string
    {
        $report = $this->generateCompatibilityReport(200);
        $detailedReport = [
            'generated_at' => now()->toISOString(),
            'summary' => [
                'total_analyzed' => $report['total_analyzed'],
                'compatibility_rate' => $report['total_analyzed'] > 0 
                    ? round(($report['compatible'] / $report['total_analyzed']) * 100, 2) 
                    : 0,
                'compatible' => $report['compatible'],
                'incompatible' => $report['incompatible']
            ],
            'by_template' => $report['by_template'],
            'common_issues' => $report['common_issues'],
            'recommendations' => $report['recommendations'],
            'compatibility_stats' => $this->getCompatibilityStats()
        ];

        switch ($format) {
            case 'json':
                return json_encode($detailedReport, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
            case 'text':
                return $this->formatReportAsText($detailedReport);
            
            default:
                return json_encode($detailedReport);
        }
    }

    /**
     * Formatar relatório como texto
     */
    private function formatReportAsText(array $report): string
    {
        $text = "RELATÓRIO DE COMPATIBILIDADE - TIRE PRESSURE GUIDE\n";
        $text .= "Gerado em: {$report['generated_at']}\n";
        $text .= str_repeat("=", 60) . "\n\n";
        
        $text .= "RESUMO:\n";
        $text .= "  Total analisado: {$report['summary']['total_analyzed']}\n";
        $text .= "  Taxa de compatibilidade: {$report['summary']['compatibility_rate']}%\n";
        $text .= "  Compatíveis: {$report['summary']['compatible']}\n";
        $text .= "  Incompatíveis: {$report['summary']['incompatible']}\n\n";

        $text .= "POR TEMPLATE:\n";
        foreach ($report['by_template'] as $template => $data) {
            $rate = $data['total'] > 0 ? round(($data['compatible'] / $data['total']) * 100, 2) : 0;
            $text .= "  {$template}: {$rate}% ({$data['compatible']}/{$data['total']})\n";
        }

        $text .= "\nISSUES MAIS COMUNS:\n";
        foreach (array_slice($report['common_issues'], 0, 10, true) as $issue => $count) {
            $text .= "  {$count}x: {$issue}\n";
        }

        $text .= "\nRECOMENDAÇÕES:\n";
        foreach ($report['recommendations'] as $recommendation) {
            $text .= "  - {$recommendation}\n";
        }

        return $text;
    }
}