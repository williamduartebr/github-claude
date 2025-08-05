<?php

namespace Src\ContentGeneration\TirePressureGuide\Application\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;

/**
 * Modified TirePressureGuideApplicationService
 * 
 * PRINCIPAIS MUDANÇAS:
 * - convertToTempArticleFormat() agora monta conteúdo do formato ideal_tire_pressure_car.json
 * - Consolidação das seções refinadas pelo Claude
 * - Estrutura compatível com IdealTirePressureCarViewModel
 */
class TirePressureGuideApplicationService
{
    /**
     * Obter estatísticas dos artigos de calibragem
     */
    public function getArticleStats(): array
    {
        try {
            return [
                'total' => TirePressureArticle::count(),
                'generated' => TirePressureArticle::where('generation_status', 'generated')->count(),
                'claude_enhanced' => TirePressureArticle::where('generation_status', 'claude_enhanced')->count(),
                'published' => TirePressureArticle::where('generation_status', 'published')->count(),
                'sections_ready' => TirePressureArticle::withSectionsReadyForRefinement()->count(),
                'sections_complete' => TirePressureArticle::withSectionsComplete()->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter estatísticas dos artigos de calibragem: ' . $e->getMessage());
            return [
                'total' => 0,
                'generated' => 0,
                'claude_enhanced' => 0,
                'published' => 0,
                'sections_ready' => 0,
                'sections_complete' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obter estatísticas do TempArticle
     */
    public function getTempArticleStats(): array
    {
        $total = TempArticle::count();
        $tirePressureGuide = TempArticle::where('source', 'tire_pressure_guide')->count();

        return [
            'total' => $total,
            'tire_pressure_guide' => $tirePressureGuide
        ];
    }

    /**
     * Publicar artigos para TempArticle collection
     */
    public function publishToTempArticles(
        string $status = 'claude_enhanced',
        int $limit = 100,
        bool $dryRun = false,
        ?callable $progressCallback = null
    ): object {
        $results = (object)[
            'published' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        try {
            // Buscar artigos que já foram refinados pelo Claude (seções completas)
            $articles = TirePressureArticle::where('generation_status', $status)
                ->withSectionsComplete() // Usar scope correto - artigos com refinamento completo
                ->orderBy('sections_last_refined_at', 'desc')
                ->limit($limit)
                ->get();

            $totalArticles = $articles->count();
            $processed = 0;

            foreach ($articles as $article) {
                try {
                    // Verificar se já existe no TempArticle
                    if ($this->tempArticleExists($article->slug)) {
                        $results->skipped++;
                        $results->errors[] = "Artigo já existe no TempArticle: {$article->slug}";
                        continue;
                    }

                    // Verificar se possui seções refinadas completas
                    if (!$this->hasCompletedSectionsRefinement($article)) {
                        $results->failed++;
                        $results->errors[] = "Artigo sem refinamento completo das seções: {$article->slug}";
                        continue;
                    }

                    if (!$dryRun) {
                        $tempArticleData = $this->convertToTempArticleFormat($article);

                        $tempArticle = new TempArticle();
                        $tempArticle->fill($tempArticleData);

                        if ($tempArticle->save()) {
                            $results->published++;
                        } else {
                            $results->failed++;
                            $results->errors[] = "Falha ao salvar no TempArticle: {$article->slug}";
                        }
                    } else {
                        $results->published++; // Simular para dry run
                    }
                } catch (\Exception $e) {
                    $results->failed++;
                    $results->errors[] = "Erro ao publicar {$article->slug}: " . $e->getMessage();
                }

                $processed++;
                if ($progressCallback) {
                    $progressCallback($processed, $totalArticles);
                }
            }
        } catch (\Exception $e) {
            $results->errors[] = "Erro ao buscar artigos: " . $e->getMessage();
        }

        return $results;
    }

    /**
     * Verificar se artigo já existe no TempArticle
     */
    private function tempArticleExists(string $slug): bool
    {
        try {
            return TempArticle::where('slug', $slug)->exists();
        } catch (\Exception $e) {
            Log::error('Erro ao verificar existência no TempArticle: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar se possui refinamento completo das seções
     */
    private function hasCompletedSectionsRefinement($article): bool
    {
        // Verificar se tem todas as 6 seções refinadas
        $sectionsRefined = $article->sections_refined ?? [];

        if (count($sectionsRefined) < 6) {
            return false;
        }

        // Verificar se todas as seções obrigatórias estão preenchidas
        $requiredSections = [
            'sections_intro',
            'sections_pressure_table',
            'sections_how_to_calibrate',
            'sections_middle_content',
            'sections_faq',
            'sections_conclusion'
        ];

        foreach ($requiredSections as $section) {
            if (empty($article->$section)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Converter artigo para formato TempArticle 
     * 
     * NOVA IMPLEMENTAÇÃO: Monta conteúdo no formato ideal_tire_pressure_car.json
     * Consolidando seções refinadas pelo Claude
     */
    private function convertToTempArticleFormat($article): array
    {
        // 1. Construir conteúdo final consolidando seções refinadas
        $finalContent = $this->buildFinalContentFromRefinedSections($article);

        // 2. Extrair entidades do veículo para o formato esperado
        $extractedEntities = [
            'marca' => $article->make ?? '',
            'modelo' => $article->model ?? '',
            'ano' => (string)($article->year ?? ''),
            'medida_pneu' => $article->tire_size ?? '',
            'versao' => 'Todas as versões',
            'tipo_veiculo' => $this->mapVehicleType($article->vehicle_data['vehicle_type'] ?? 'car'),
            'categoria' => $article->category ?? 'hatch',
            'combustivel' => 'flex',
            'pressao_dianteira' => $article->pressure_light_front ?? 30,
            'pressao_traseira' => $article->pressure_light_rear ?? 28,
            'pressao_sobressalente' => $article->pressure_spare ?? 32
        ];

        // 3. Dados SEO otimizados para pressão ideal
        $seoFormatted = [
            'page_title' => $this->generateIdealPressureTitle($article),
            'meta_description' => $this->generateIdealPressureMetaDescription($article),
            'url_slug' => $this->generateIdealPressureSlug($article),
            'h1' => $this->generateIdealPressureH1($article),
            'h2_tags' => $this->extractH2TagsFromRefinedContent($finalContent),
            'primary_keyword' => $this->generateIdealPressurePrimaryKeyword($article),
            'secondary_keywords' => $this->generateIdealPressureSecondaryKeywords($article),
            'meta_robots' => 'index,follow',
            'canonical_url' => $this->generateIdealPressureCanonicalUrl($article)
        ];

        // 4. Retornar formato TempArticle
        return [
            'title' => $seoFormatted['page_title'],
            'slug' => $seoFormatted['url_slug'],
            'content' => $finalContent, // Conteúdo no formato ideal_tire_pressure_car.json
            'excerpt' => $this->generateExcerptFromRefinedContent($finalContent),
            'category' => 'Pressão Ideal dos Pneus',
            'tags' => $this->generateTagsFromArticle($article),
            'status' => 'draft',
            'author' => 'Sistema TirePressureGuide',
            'source' => 'tire_pressure_guide',
            'source_id' => $article->_id,
            'template' => $article->template_used ?? 'ideal_tire_pressure_car',
            'extracted_entities' => $extractedEntities,
            'seo_data' => $seoFormatted,
            'meta_data' => [
                'vehicle_type' => $article->vehicle_data['vehicle_type'] ?? 'car',
                'generation_method' => 'claude_enhanced_sections',
                'content_score' => $this->calculateFinalContentScore($finalContent),
                'sections_used' => array_keys($article->sections_refined ?? []),
                'original_generation_date' => $article->processed_at,
                'claude_enhancement_date' => $article->claude_last_enhanced_at,
                'tire_pressures' => [
                    'front' => $article->pressure_light_front,
                    'rear' => $article->pressure_light_rear,
                    'spare' => $article->pressure_spare
                ]
            ],
            'published_at' => null, // Será definido na publicação
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    /**
     * Construir conteúdo final consolidando seções refinadas pelo Claude
     * 
     * FORMATO: ideal_tire_pressure_car.json (compatível com IdealTirePressureCarViewModel)
     */
    private function buildFinalContentFromRefinedSections($article): array
    {
        // Usar conteúdo base do article_content como estrutura
        $baseContent = $article->article_content ?? [];

        // Consolidar seções refinadas pelo Claude
        $refinedSections = $article->sections_refined ?? [];

        // Se existem seções refinadas, usar elas; senão manter conteúdo original
        $finalContent = $baseContent;

        // Substituir seções refinadas pelo Claude
        if (!empty($refinedSections['intro'])) {
            $finalContent['introducao'] = $refinedSections['intro']['content'] ?? $baseContent['introducao'] ?? '';
        }

        if (!empty($refinedSections['pressure_table'])) {
            $finalContent['tabela_pressoes'] = $this->mergeRefinedPressureTable(
                $baseContent['tabela_pressoes'] ?? [],
                $refinedSections['pressure_table']
            );
        }

        if (!empty($refinedSections['how_to_calibrate'])) {
            $finalContent['procedimento_calibragem'] = $this->buildRefinedCalibrationProcedure(
                $refinedSections['how_to_calibrate']
            );
        }

        if (!empty($refinedSections['middle_content'])) {
            $finalContent = $this->mergeRefinedMiddleContent(
                $finalContent,
                $refinedSections['middle_content']
            );
        }

        if (!empty($refinedSections['faq'])) {
            $finalContent['perguntas_frequentes'] = $refinedSections['faq']['content'] ?? $baseContent['perguntas_frequentes'] ?? [];
        }

        if (!empty($refinedSections['conclusion'])) {
            $finalContent['consideracoes_finais'] = $refinedSections['conclusion']['content'] ?? $baseContent['consideracoes_finais'] ?? '';
        }

        // Garantir estrutura mínima compatível com IdealTirePressureCarViewModel
        $finalContent = $this->ensureCompatibleStructure($finalContent, $article);

        return $finalContent;
    }

    /**
     * Garantir estrutura compatível com IdealTirePressureCarViewModel
     */
    private function ensureCompatibleStructure(array $content, $article): array
    {
        // Garantir seções obrigatórias esperadas pelo ViewModel
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
            if (!isset($content[$section])) {
                $content[$section] = $this->generateDefaultSectionContent($section, $article);
            }
        }

        return $content;
    }

    /**
     * Gerar conteúdo padrão para seções faltantes
     */
    private function generateDefaultSectionContent(string $section, $article): mixed
    {
        $make = $article->make ?? 'Veículo';
        $model = $article->model ?? '';
        $year = $article->year ?? '';

        switch ($section) {
            case 'introducao':
                return "Descubra a pressão ideal dos pneus do seu {$make} {$model} {$year} para máxima segurança e economia.";

            case 'especificacoes_pneus':
                return [
                    'medida_original' => $article->tire_size ?? '',
                    'medida_opcional' => '',
                    'indice_carga' => '82',
                    'indice_velocidade' => 'H',
                    'tipo_construcao' => 'Radial',
                    'marca_original' => 'Bridgestone, Pirelli'
                ];

            case 'tabela_pressoes':
                return $this->generateDefaultPressureTable($article);

            case 'conversao_unidades':
                return $this->generateDefaultUnitConversion();

            case 'localizacao_etiqueta':
                return [
                    'local_principal' => 'Soleira da porta do motorista',
                    'local_alternativo' => 'Manual do proprietário',
                    'informacoes_contidas' => [
                        'Pressões para diferentes cargas',
                        'Tamanho original dos pneus'
                    ]
                ];

            case 'beneficios_calibragem':
                return [
                    'seguranca' => ['Maior aderência', 'Melhor frenagem'],
                    'economia' => ['Menor consumo', 'Maior vida útil dos pneus'],
                    'desempenho' => ['Melhor dirigibilidade', 'Maior conforto']
                ];

            case 'dicas_manutencao':
                return [
                    'frequencia_calibragem' => 'A cada 15 dias',
                    'horario_ideal' => 'Pela manhã, com pneus frios',
                    'cuidados_especiais' => [
                        'Verifique o pneu sobressalente',
                        'Use tampas nas válvulas'
                    ]
                ];

            case 'alertas_importantes':
                return [
                    [
                        'tipo' => 'warning',
                        'titulo' => 'Nunca calibre com pneus quentes',
                        'descricao' => 'Pneus aquecidos mostram pressão incorreta'
                    ]
                ];

            case 'perguntas_frequentes':
                return [
                    [
                        'question' => "Qual a pressão ideal para o {$make} {$model}?",
                        'answer' => 'Consulte a tabela de pressões específica para seu veículo.'
                    ]
                ];

            case 'consideracoes_finais':
                return "Manter a pressão correta dos pneus é essencial para a segurança e economia do seu {$make} {$model}.";

            default:
                return [];
        }
    }

    /**
     * Gerar tabela de pressões padrão
     */
    private function generateDefaultPressureTable($article): array
    {
        $frontPressure = $article->pressure_light_front ?? 30;
        $rearPressure = $article->pressure_light_rear ?? 28;
        $maxFrontPressure = ($frontPressure + 4);
        $maxRearPressure = ($rearPressure + 4);

        return [
            'versoes' => [
                [
                    'nome_versao' => 'Todas as versões',
                    'motor' => '1.6 Flex',
                    'medida_pneu' => $article->tire_size ?? '',
                    'pressao_dianteira_normal' => "{$frontPressure} PSI",
                    'pressao_traseira_normal' => "{$rearPressure} PSI",
                    'pressao_dianteira_carregado' => "{$maxFrontPressure} PSI",
                    'pressao_traseira_carregado' => "{$maxRearPressure} PSI",
                    'observacao' => 'Pressões para uso normal e com carga'
                ]
            ],
            'condicoes_uso' => [
                [
                    'situacao' => 'Uso urbano normal',
                    'ocupantes' => '1-2 pessoas',
                    'bagagem' => 'Leve',
                    'ajuste_dianteira' => "{$frontPressure} PSI",
                    'ajuste_traseira' => "{$rearPressure} PSI",
                    'beneficios' => 'Máximo conforto e economia'
                ]
            ]
        ];
    }

    /**
     * Gerar conversão de unidades padrão
     */
    private function generateDefaultUnitConversion(): array
    {
        return [
            'tabela_conversao' => [
                ['psi' => '28', 'bar' => '1.9', 'kgf_cm2' => '1.9'],
                ['psi' => '30', 'bar' => '2.1', 'kgf_cm2' => '2.1'],
                ['psi' => '32', 'bar' => '2.2', 'kgf_cm2' => '2.2'],
                ['psi' => '34', 'bar' => '2.3', 'kgf_cm2' => '2.3'],
                ['psi' => '36', 'bar' => '2.5', 'kgf_cm2' => '2.5']
            ],
            'observacao' => 'Conversão aproximada entre unidades de pressão.'
        ];
    }

    // ===== MÉTODOS AUXILIARES PARA FORMATAÇÃO =====

    /**
     * Mapear tipo de veículo
     */
    private function mapVehicleType(string $type): string
    {
        return match (strtolower($type)) {
            'motorcycle' => 'moto',
            'car' => 'carro',
            default => 'carro'
        };
    }

    /**
     * Gerar título para pressão ideal
     */
    private function generateIdealPressureTitle($article): string
    {
        $make = $article->make ?? '';
        $model = $article->model ?? '';
        $year = $article->year ?? '';

        return "Pressão Ideal dos Pneus - {$make} {$model} {$year} | Guia Completo";
    }

    /**
     * Gerar meta description para pressão ideal
     */
    private function generateIdealPressureMetaDescription($article): string
    {
        $make = $article->make ?? '';
        $model = $article->model ?? '';
        $year = $article->year ?? '';
        $frontPressure = $article->pressure_light_front ?? 30;
        $rearPressure = $article->pressure_light_rear ?? 28;

        return "Pressão ideal dos pneus do {$make} {$model} {$year}: {$frontPressure}/{$rearPressure} PSI. Tabela completa, dicas de calibragem e economia de combustível.";
    }

    /**
     * Gerar slug para pressão ideal
     */
    private function generateIdealPressureSlug($article): string
    {
        $make = Str::slug($article->make ?? '');
        $model = Str::slug($article->model ?? '');
        $year = $article->year ?? '';

        return "pressao-ideal-pneu-{$make}-{$model}-{$year}";
    }

    /**
     * Gerar H1 para pressão ideal
     */
    private function generateIdealPressureH1($article): string
    {
        $make = $article->make ?? '';
        $model = $article->model ?? '';
        $year = $article->year ?? '';

        return "Pressão Ideal dos Pneus do {$make} {$model} {$year}";
    }

    /**
     * Extrair H2 tags do conteúdo refinado
     */
    private function extractH2TagsFromRefinedContent(array $content): array
    {
        $h2Tags = [];

        // Mapear seções para H2s
        $sectionToH2Map = [
            'introducao' => 'Por que a Pressão Correta é Importante',
            'tabela_pressoes' => 'Tabela de Pressões Recomendadas',
            'procedimento_calibragem' => 'Como Calibrar Corretamente',
            'beneficios_calibragem' => 'Benefícios da Calibragem Adequada',
            'dicas_manutencao' => 'Dicas de Manutenção',
            'perguntas_frequentes' => 'Perguntas Frequentes',
            'consideracoes_finais' => 'Considerações Finais'
        ];

        foreach ($sectionToH2Map as $section => $h2) {
            if (!empty($content[$section])) {
                $h2Tags[] = $h2;
            }
        }

        return $h2Tags;
    }

    /**
     * Gerar palavra-chave primária para pressão ideal
     */
    private function generateIdealPressurePrimaryKeyword($article): string
    {
        $make = strtolower($article->make ?? '');
        $model = strtolower($article->model ?? '');
        $year = $article->year ?? '';

        return "pressão ideal pneu {$make} {$model} {$year}";
    }

    /**
     * Gerar palavras-chave secundárias para pressão ideal
     */
    private function generateIdealPressureSecondaryKeywords($article): array
    {
        $make = strtolower($article->make ?? '');
        $model = strtolower($article->model ?? '');

        return [
            "calibragem {$make} {$model}",
            "pressão pneu {$make}",
            "tabela pressão pneu",
            "calibragem pneu carro",
            "pressão ideal pneu",
            "economia combustível",
            "manutenção pneu",
            "segurança automotiva"
        ];
    }

    /**
     * Gerar URL canônica para pressão ideal
     */
    private function generateIdealPressureCanonicalUrl($article): string
    {
        $slug = $this->generateIdealPressureSlug($article);
        return config('app.url') . "/info/{$slug}/";
    }

    /**
     * Gerar excerpt do conteúdo refinado
     */
    private function generateExcerptFromRefinedContent(array $content): string
    {
        $intro = $content['introducao'] ?? '';

        if (strlen($intro) > 160) {
            return Str::limit($intro, 157) . '...';
        }

        return $intro;
    }

    /**
     * Gerar tags do artigo
     */
    private function generateTagsFromArticle($article): array
    {
        return [
            'pressão pneu',
            'calibragem',
            $article->make ?? '',
            $article->model ?? '',
            'manutenção automotiva',
            'economia combustível',
            'segurança'
        ];
    }

    /**
     * Calcular score final do conteúdo
     */
    private function calculateFinalContentScore(array $content): float
    {
        $score = 5.0;

        // Verificar seções principais
        if (!empty($content['introducao'])) $score += 0.5;
        if (!empty($content['tabela_pressoes'])) $score += 1.0;
        if (!empty($content['perguntas_frequentes'])) $score += 0.5;
        if (!empty($content['consideracoes_finais'])) $score += 0.5;

        // Verificar qualidade da tabela
        if (!empty($content['tabela_pressoes']['versoes'])) $score += 0.5;
        if (!empty($content['tabela_pressoes']['condicoes_uso'])) $score += 0.5;

        // Verificar benefícios e dicas
        if (!empty($content['beneficios_calibragem'])) $score += 0.3;
        if (!empty($content['dicas_manutencao'])) $score += 0.3;

        return min(10.0, $score);
    }

    // ===== MÉTODOS AUXILIARES PARA MERGE DE SEÇÕES REFINADAS =====

    /**
     * Fazer merge da tabela de pressões refinada
     */
    private function mergeRefinedPressureTable(array $baseTable, array $refinedSection): array
    {
        // Se a seção refinada tem uma tabela melhorada, usar ela
        if (!empty($refinedSection['content']['tabela_otimizada'])) {
            return $refinedSection['content']['tabela_otimizada'];
        }

        // Senão, manter a base e aplicar melhorias pontuais
        $mergedTable = $baseTable;

        if (!empty($refinedSection['content']['versoes_melhoradas'])) {
            $mergedTable['versoes'] = $refinedSection['content']['versoes_melhoradas'];
        }

        if (!empty($refinedSection['content']['condicoes_otimizadas'])) {
            $mergedTable['condicoes_uso'] = $refinedSection['content']['condicoes_otimizadas'];
        }

        return $mergedTable;
    }

    /**
     * Construir procedimento de calibragem refinado
     */
    private function buildRefinedCalibrationProcedure(array $refinedSection): array
    {
        if (!empty($refinedSection['content']['passos_refinados'])) {
            return [
                'passos' => $refinedSection['content']['passos_refinados'],
                'dicas_especiais' => $refinedSection['content']['dicas_especiais'] ?? [],
                'equipamento_recomendado' => $refinedSection['content']['equipamento'] ?? ''
            ];
        }

        // Fallback para estrutura básica
        return [
            'passos' => $refinedSection['content'] ?? [],
            'dicas_especiais' => [],
            'equipamento_recomendado' => 'Calibrador digital'
        ];
    }

    /**
     * Fazer merge do conteúdo do meio refinado
     */
    private function mergeRefinedMiddleContent(array $baseContent, array $refinedSection): array
    {
        // Integrar dicas melhoradas
        if (!empty($refinedSection['content']['dicas_melhoradas'])) {
            $baseContent['dicas_manutencao'] = array_merge(
                $baseContent['dicas_manutencao'] ?? [],
                $refinedSection['content']['dicas_melhoradas']
            );
        }

        // Integrar alertas refinados
        if (!empty($refinedSection['content']['alertas_refinados'])) {
            $baseContent['alertas_importantes'] = $refinedSection['content']['alertas_refinados'];
        }

        // Integrar benefícios melhorados
        if (!empty($refinedSection['content']['beneficios_melhorados'])) {
            $baseContent['beneficios_calibragem'] = $refinedSection['content']['beneficios_melhorados'];
        }

        return $baseContent;
    }
}
