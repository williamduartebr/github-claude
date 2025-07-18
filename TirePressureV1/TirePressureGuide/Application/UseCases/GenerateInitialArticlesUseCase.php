<?php

namespace Src\ContentGeneration\TirePressureGuide\Application\UseCases;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\InitialArticleGeneratorService;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;
use Src\ContentGeneration\TirePressureGuide\Application\DTOs\VehicleDataDTO;

/**
 * Use Case para geração inicial de artigos de calibragem (Etapa 1)
 * 
 * Orquestra o processo de geração de artigos completos a partir de dados de veículos
 * Coordena a validação, geração de conteúdo e persistência na TirePressureArticle
 */
class GenerateInitialArticlesUseCase
{
    protected InitialArticleGeneratorService $articleGenerator;

    public function __construct(InitialArticleGeneratorService $articleGenerator)
    {
        $this->articleGenerator = $articleGenerator;
    }

    /**
     * Executar geração de artigo para um veículo
     * 
     * @param array $vehicleData Dados do veículo do CSV
     * @param string $batchId ID do lote de processamento
     * @param bool $dryRun Simulação sem persistir
     * @return bool Sucesso da operação
     */
    public function execute(array $vehicleData, string $batchId, bool $dryRun = false): bool
    {
        try {
            // 1. Validar dados do veículo
            $validationResult = $this->validateVehicleData($vehicleData);
            if (!$validationResult['valid']) {
                Log::warning("Dados de veículo inválidos", [
                    'vehicle' => $vehicleData['vehicle_identifier'] ?? 'Desconhecido',
                    'errors' => $validationResult['errors']
                ]);
                return false;
            }

            // 2. Verificar se artigo já existe (a menos que seja para sobrescrever)
            $existingArticle = $this->checkExistingArticle($vehicleData);
            if ($existingArticle && !$dryRun) {
                Log::info("Artigo já existe, pulando", [
                    'vehicle' => $vehicleData['vehicle_identifier'],
                    'existing_id' => $existingArticle->_id
                ]);
                return true; // Considera sucesso, mas não processa
            }

            // 3. Preparar dados para geração
            $enrichedData = $this->enrichVehicleData($vehicleData);

            // 4. Simular geração se for dry run
            if ($dryRun) {
                return $this->simulateGeneration($enrichedData, $batchId);
            }

            // 5. Gerar artigo usando transação
            $article = $this->generateArticleWithTransaction($enrichedData, $batchId);

            if ($article) {
                // 6. Validar qualidade do artigo gerado
                $qualityCheck = $this->validateArticleQuality($article);
                
                // 7. Atualizar score de qualidade
                $article->content_score = $qualityCheck['score'];
                $article->quality_checked = true;
                
                if (!empty($qualityCheck['issues'])) {
                    $article->quality_issues = $qualityCheck['issues'];
                }
                
                $article->save();

                Log::info("Artigo gerado com sucesso", [
                    'vehicle' => $vehicleData['vehicle_identifier'],
                    'article_id' => $article->_id,
                    'quality_score' => $qualityCheck['score']
                ]);

                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error("Erro no use case de geração de artigo", [
                'vehicle' => $vehicleData['vehicle_identifier'] ?? 'Desconhecido',
                'batch_id' => $batchId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    /**
     * Executar geração em lote
     * 
     * @param array $vehiclesBatch Array de veículos
     * @param string $batchId ID do lote
     * @param bool $dryRun Simulação
     * @return array Resultados do processamento
     */
    public function executeBatch(array $vehiclesBatch, string $batchId, bool $dryRun = false): array
    {
        $results = [
            'total' => count($vehiclesBatch),
            'successful' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        foreach ($vehiclesBatch as $index => $vehicleData) {
            try {
                $success = $this->execute($vehicleData, $batchId, $dryRun);
                
                if ($success) {
                    $results['successful']++;
                } else {
                    $results['failed']++;
                }

            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'vehicle' => $vehicleData['vehicle_identifier'] ?? "Índice {$index}",
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Validar dados do veículo
     */
    protected function validateVehicleData(array $vehicleData): array
    {
        $errors = [];
        $required = ['make', 'model', 'year', 'tire_size'];

        // Verificar campos obrigatórios
        foreach ($required as $field) {
            if (empty($vehicleData[$field])) {
                $errors[] = "Campo obrigatório ausente: {$field}";
            }
        }

        // Validar ano
        if (!empty($vehicleData['year'])) {
            $year = (int) $vehicleData['year'];
            if ($year < 1990 || $year > date('Y') + 2) {
                $errors[] = "Ano inválido: {$year}";
            }
        }

        // Validar pressões básicas
        if (empty($vehicleData['pressure_empty_front']) || $vehicleData['pressure_empty_front'] <= 0) {
            $errors[] = "Pressão dianteira vazia inválida";
        }

        if (empty($vehicleData['pressure_empty_rear']) || $vehicleData['pressure_empty_rear'] <= 0) {
            $errors[] = "Pressão traseira vazia inválida";
        }

        // Validar consistência de pressões
        if (!empty($vehicleData['pressure_empty_front']) && !empty($vehicleData['pressure_max_front'])) {
            if ($vehicleData['pressure_max_front'] <= $vehicleData['pressure_empty_front']) {
                $errors[] = "Pressão máxima dianteira deve ser maior que vazia";
            }
        }

        if (!empty($vehicleData['pressure_empty_rear']) && !empty($vehicleData['pressure_max_rear'])) {
            if ($vehicleData['pressure_max_rear'] <= $vehicleData['pressure_empty_rear']) {
                $errors[] = "Pressão máxima traseira deve ser maior que vazia";
            }
        }

        // Validar ranges de pressão
        $pressureFields = ['pressure_empty_front', 'pressure_empty_rear', 'pressure_max_front', 'pressure_max_rear'];
        foreach ($pressureFields as $field) {
            if (!empty($vehicleData[$field])) {
                $pressure = (float) $vehicleData[$field];
                if ($pressure < 10 || $pressure > 60) {
                    $errors[] = "Pressão fora do range válido (10-60 PSI): {$field} = {$pressure}";
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Verificar se artigo já existe
     */
    protected function checkExistingArticle(array $vehicleData): ?TirePressureArticle
    {
        return TirePressureArticle::where('make', $vehicleData['make'])
                                 ->where('model', $vehicleData['model'])
                                 ->where('year', $vehicleData['year'])
                                 ->first();
    }

    /**
     * Enriquecer dados do veículo
     */
    protected function enrichVehicleData(array $vehicleData): array
    {
        // Garantir que dados estruturados estejam presentes
        if (!isset($vehicleData['vehicle_data'])) {
            $vehicleData['vehicle_data'] = [];
        }

        // Enrichment básico
        $vehicleData['vehicle_identifier'] = $vehicleData['vehicle_identifier'] ?? 
                                           "{$vehicleData['make']} {$vehicleData['model']} {$vehicleData['year']}";

        // Detectar tipo de veículo se não definido
        if (!isset($vehicleData['is_motorcycle'])) {
            $vehicleData['is_motorcycle'] = $this->detectMotorcycle($vehicleData);
        }

        // Definir categoria principal se não definida
        if (!isset($vehicleData['main_category'])) {
            $vehicleData['main_category'] = $vehicleData['is_motorcycle'] ? 'Motocicletas' : 'Carros';
        }

        // Definir tipo de veículo se não definido
        if (!isset($vehicleData['vehicle_type'])) {
            $vehicleData['vehicle_type'] = $vehicleData['is_motorcycle'] ? 'motorcycle' : 'car';
        }

        // Garantir pressões padrão se não definidas
        $this->ensureDefaultPressures($vehicleData);

        // Adicionar dados estruturados
        $vehicleData['vehicle_data'] = array_merge($vehicleData['vehicle_data'], [
            'make' => $vehicleData['make'],
            'model' => $vehicleData['model'],
            'year' => $vehicleData['year'],
            'tire_size' => $vehicleData['tire_size'],
            'is_motorcycle' => $vehicleData['is_motorcycle'],
            'vehicle_type' => $vehicleData['vehicle_type'],
            'main_category' => $vehicleData['main_category'],
            'pressure_display' => $this->formatPressureDisplay($vehicleData),
            'pressure_empty_display' => $this->formatEmptyPressureDisplay($vehicleData),
            'pressure_loaded_display' => $this->formatLoadedPressureDisplay($vehicleData),
            'enriched_at' => now()->toISOString()
        ]);

        return $vehicleData;
    }

    /**
     * Detectar se é motocicleta
     */
    protected function detectMotorcycle(array $vehicleData): bool
    {
        // Verificar categoria
        $category = strtolower($vehicleData['category'] ?? '');
        if (in_array($category, ['motorcycle', 'moto', 'motocicleta'])) {
            return true;
        }

        // Verificar tamanho do pneu (padrões típicos de moto)
        $tireSize = $vehicleData['tire_size'] ?? '';
        if (preg_match('/\d{2,3}\/\d{2}-\d{2}/', $tireSize) && 
            (strpos($tireSize, 'dianteiro') !== false || strpos($tireSize, 'traseiro') !== false)) {
            return true;
        }

        return false;
    }

    /**
     * Garantir pressões padrão
     */
    protected function ensureDefaultPressures(array &$vehicleData): void
    {
        $defaults = $vehicleData['is_motorcycle'] ? [
            'pressure_empty_front' => 28,
            'pressure_empty_rear' => 32,
            'pressure_light_front' => 30,
            'pressure_light_rear' => 34,
            'pressure_max_front' => 32,
            'pressure_max_rear' => 36,
            'pressure_spare' => null // Motos não têm estepe
        ] : [
            'pressure_empty_front' => 30,
            'pressure_empty_rear' => 28,
            'pressure_light_front' => 32,
            'pressure_light_rear' => 30,
            'pressure_max_front' => 36,
            'pressure_max_rear' => 34,
            'pressure_spare' => 35
        ];

        foreach ($defaults as $field => $defaultValue) {
            if (empty($vehicleData[$field]) && $defaultValue !== null) {
                $vehicleData[$field] = $defaultValue;
            }
        }
    }

    /**
     * Formatar exibição de pressão
     */
    protected function formatPressureDisplay(array $vehicleData): string
    {
        $front = $vehicleData['pressure_empty_front'] ?? 30;
        $rear = $vehicleData['pressure_empty_rear'] ?? 28;
        
        if ($vehicleData['is_motorcycle']) {
            return "Dianteiro: {$front} PSI / Traseiro: {$rear} PSI";
        }
        
        return "Dianteiros: {$front} PSI / Traseiros: {$rear} PSI";
    }

    /**
     * Formatar pressão para veículo vazio
     */
    protected function formatEmptyPressureDisplay(array $vehicleData): string
    {
        $front = $vehicleData['pressure_empty_front'] ?? 30;
        $rear = $vehicleData['pressure_empty_rear'] ?? 28;
        return "{$front}/{$rear} PSI";
    }

    /**
     * Formatar pressão para veículo carregado
     */
    protected function formatLoadedPressureDisplay(array $vehicleData): string
    {
        $front = $vehicleData['pressure_max_front'] ?? 36;
        $rear = $vehicleData['pressure_max_rear'] ?? 34;
        return "{$front}/{$rear} PSI";
    }

    /**
     * Simular geração (dry run)
     */
    protected function simulateGeneration(array $vehicleData, string $batchId): bool
    {
        Log::info("SIMULAÇÃO: Geração de artigo", [
            'vehicle' => $vehicleData['vehicle_identifier'],
            'batch_id' => $batchId,
            'template' => $this->articleGenerator->getTemplateForVehicle($vehicleData),
            'is_motorcycle' => $vehicleData['is_motorcycle']
        ]);

        // Simular validações que seriam feitas
        $mockArticleContent = [
            'sections' => [
                'introduction' => ['title' => 'Introdução', 'content' => 'Mock content'],
                'pressure_table' => ['title' => 'Tabela', 'content' => []],
                'conclusion' => ['title' => 'Conclusão', 'content' => 'Mock content']
            ]
        ];

        $mockScore = $this->articleGenerator->calculateContentScore($mockArticleContent);
        
        Log::info("SIMULAÇÃO: Score calculado", [
            'vehicle' => $vehicleData['vehicle_identifier'],
            'mock_score' => $mockScore
        ]);

        return true; // Simulação sempre retorna sucesso
    }

    /**
     * Gerar artigo com transação
     */
    protected function generateArticleWithTransaction(array $vehicleData, string $batchId): ?TirePressureArticle
    {
        try {
            return DB::transaction(function () use ($vehicleData, $batchId) {
                return $this->articleGenerator->generateArticle($vehicleData, $batchId);
            });
        } catch (\Exception $e) {
            Log::error("Erro na transação de geração de artigo", [
                'vehicle' => $vehicleData['vehicle_identifier'],
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Validar qualidade do artigo gerado
     */
    protected function validateArticleQuality(TirePressureArticle $article): array
    {
        $issues = [];
        $score = $article->content_score ?? 5.0;

        // Verificar dados básicos
        if (empty($article->title)) {
            $issues[] = 'Título ausente';
            $score -= 1.0;
        }

        if (empty($article->wordpress_slug)) {
            $issues[] = 'Slug WordPress ausente';
            $score -= 0.5;
        }

        if (empty($article->meta_description)) {
            $issues[] = 'Meta descrição ausente';
            $score -= 0.5;
        }

        // Verificar conteúdo estruturado
        $articleContent = $article->article_content ?? [];
        
        if (empty($articleContent['sections'])) {
            $issues[] = 'Seções de conteúdo ausentes';
            $score -= 2.0;
        } else {
            // Verificar seções obrigatórias
            $requiredSections = ['introduction', 'pressure_table', 'how_to_calibrate', 'conclusion'];
            foreach ($requiredSections as $section) {
                if (!isset($articleContent['sections'][$section])) {
                    $issues[] = "Seção obrigatória ausente: {$section}";
                    $score -= 0.5;
                }
            }
        }

        // Verificar dados de pressão
        if (empty($article->pressure_empty_front) || empty($article->pressure_empty_rear)) {
            $issues[] = 'Pressões básicas ausentes';
            $score -= 1.0;
        }

        // Verificar URL e SEO
        if (empty($article->canonical_url)) {
            $issues[] = 'URL canônica ausente';
            $score -= 0.3;
        }

        if (empty($article->seo_keywords) || count($article->seo_keywords) < 3) {
            $issues[] = 'Palavras-chave SEO insuficientes';
            $score -= 0.3;
        }

        // Garantir score mínimo
        $score = max(1.0, min(10.0, $score));

        return [
            'score' => round($score, 1),
            'issues' => $issues,
            'quality_level' => $this->getQualityLevel($score)
        ];
    }

    /**
     * Determinar nível de qualidade
     */
    protected function getQualityLevel(float $score): string
    {
        if ($score >= 8.5) return 'excellent';
        if ($score >= 7.0) return 'good';
        if ($score >= 5.5) return 'average';
        if ($score >= 4.0) return 'poor';
        return 'very_poor';
    }

    /**
     * Obter estatísticas de geração
     */
    public function getGenerationStatistics(): array
    {
        return TirePressureArticle::getGenerationStatistics();
    }

    /**
     * Obter artigos prontos para refinamento Claude (Etapa 2)
     */
    public function getArticlesReadyForClaude(int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return TirePressureArticle::readyForClaude()
                                 ->orderBy('created_at', 'asc')
                                 ->limit($limit)
                                 ->get();
    }

    /**
     * Marcar artigo como processado
     */
    public function markAsProcessed(TirePressureArticle $article): void
    {
        $article->markAsGenerated();
        
        Log::info("Artigo marcado como processado", [
            'article_id' => $article->_id,
            'vehicle' => $article->vehicle_full_name,
            'status' => $article->generation_status
        ]);
    }

    /**
     * Validar lote antes do processamento
     */
    public function validateBatch(array $vehiclesBatch): array
    {
        $validationResults = [
            'total' => count($vehiclesBatch),
            'valid' => 0,
            'invalid' => 0,
            'errors' => []
        ];

        foreach ($vehiclesBatch as $index => $vehicleData) {
            $validation = $this->validateVehicleData($vehicleData);
            
            if ($validation['valid']) {
                $validationResults['valid']++;
            } else {
                $validationResults['invalid']++;
                $validationResults['errors'][] = [
                    'index' => $index,
                    'vehicle' => $vehicleData['vehicle_identifier'] ?? "Índice {$index}",
                    'errors' => $validation['errors']
                ];
            }
        }

        return $validationResults;
    }

    /**
     * Limpar artigos órfãos ou com problemas
     */
    public function cleanupProblematicArticles(): array
    {
        $results = [
            'deleted' => 0,
            'fixed' => 0,
            'errors' => []
        ];

        try {
            // Artigos sem dados básicos
            $problematicArticles = TirePressureArticle::where(function ($query) {
                $query->whereNull('make')
                      ->orWhereNull('model')
                      ->orWhereNull('year')
                      ->orWhereNull('wordpress_slug');
            })->get();

            foreach ($problematicArticles as $article) {
                try {
                    if (empty($article->make) || empty($article->model) || empty($article->year)) {
                        // Deletar artigos sem dados básicos
                        $article->delete();
                        $results['deleted']++;
                    } elseif (empty($article->wordpress_slug)) {
                        // Tentar corrigir slug
                        $article->wordpress_slug = $article->generateWordPressSlug();
                        $article->save();
                        $results['fixed']++;
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = "Erro ao processar artigo {$article->_id}: " . $e->getMessage();
                }
            }

        } catch (\Exception $e) {
            $results['errors'][] = "Erro geral na limpeza: " . $e->getMessage();
        }

        return $results;
    }
}