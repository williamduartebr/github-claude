<?php

namespace Src\ContentGeneration\TireCalibration\Application\Services;

use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * PickupArticleFixService V4
 * 
 * Service responsável pela correção definitiva de artigos pickup com estruturas JSON incorretas.
 * 
 * PROBLEMA IDENTIFICADO:
 * - localizacao_etiqueta como string em vez de object estruturado
 * - Outras seções (condicoes_especiais, conversao_unidades, etc.) como string
 * - ViewModel falha ao processar strings como arrays
 * 
 * SOLUÇÃO:
 * - Análise precisa de estruturas incorretas
 * - Harmonização com command
 * - Correção via Claude API com estruturas definidas
 * - Rate limiting e retry logic robustos
 */
class PickupArticleFixService
{
    /**
     * Estruturas requeridas para template pickup
     * 
     * Define a estrutura correta que cada seção deve ter para evitar
     * o erro fatal no TireCalibrationPickupViewModel
     */
    private const REQUIRED_PICKUP_STRUCTURES = [
        'localizacao_etiqueta' => 'object',      // CRÍTICO: deve ser object, não string
        'condicoes_especiais' => 'array',        // Array de objects
        'conversao_unidades' => 'object',        // Object com tabela_conversao
        'cuidados_recomendacoes' => 'array',     // Array de objects  
        'impacto_pressao' => 'object'            // Object com subcalibrado/ideal/sobrecalibrado
    ];

    /**
     * Template correto para localizacao_etiqueta
     * 
     * Esta é a estrutura exata que o ViewModel espera receber
     */
    private const CORRECT_LOCALIZACAO_ETIQUETA_STRUCTURE = [
        'local_principal' => 'string',
        'descricao' => 'string',
        'locais_alternativos' => 'array',
        'observacao' => 'string'
    ];

    private string $apiKey;
    private string $apiUrl;
    private int $maxRetries;
    private int $requestTimeout;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key');
        $this->apiUrl = config('services.anthropic.api_url', 'https://api.anthropic.com/v1/messages');
        $this->maxRetries = config('tire_calibration.claude_max_retries', 3);
        $this->requestTimeout = config('tire_calibration.claude_timeout', 45);

        if (empty($this->apiKey)) {
            throw new Exception('Anthropic API key não configurada');
        }
    }

    /**
     * Analisar seções faltando ou com estrutura incorreta
     * 
     * Este método detecta com precisão os pickups que precisam de correção,
     * harmonizando com a análise do command
     */
    public function analyzeMissingSections(TireCalibration $calibration): array
    {
        $content = $this->extractArticleContent($calibration);

        if (empty($content)) {
            return [
                'needs_fix' => false,
                'reason' => 'Sem conteúdo de artigo para analisar',
                'missing_sections' => 0,
                'incorrect_structures' => []
            ];
        }

        $incorrectStructures = [];
        $structuralIssues = 0;

        // Analisar cada seção requerida
        foreach (self::REQUIRED_PICKUP_STRUCTURES as $section => $expectedType) {
            $issue = $this->validateSectionStructure($content, $section, $expectedType);

            if ($issue) {
                $incorrectStructures[] = $issue;
                $structuralIssues++;
            }
        }

        // Se localizacao_etiqueta está incorreta, é crítico
        $isCritical = $this->isCriticalStructureError($incorrectStructures);

        return [
            'needs_fix' => $structuralIssues > 0,
            'is_critical' => $isCritical,
            'missing_sections' => $structuralIssues,
            'incorrect_structures' => $incorrectStructures,
            'analysis_details' => [
                'vehicle' => "{$calibration->vehicle_make} {$calibration->vehicle_model}",
                'template_confirmed' => $this->isPickupTemplate($content),
                'content_source' => $this->getContentSource($calibration)
            ]
        ];
    }

    /**
     * Corrigir artigo pickup com estruturas incorretas
     * 
     * Utiliza Claude API para corrigir as estruturas JSON mantendo o conteúdo
     */
    public function fixPickupArticle(TireCalibration $calibration): array
    {
        try {
            $analysis = $this->analyzeMissingSections($calibration);

            if (!$analysis['needs_fix']) {
                return [
                    'success' => false,
                    'reason' => 'Artigo não precisa de correção',
                    'analysis' => $analysis
                ];
            }

            // Extrair conteúdo base
            $currentContent = $this->extractArticleContent($calibration);

            // Buscar referência Toyota Hilux como template
            $referenceContent = $this->getPickupReference();

            // Preparar prompt para Claude API
            $prompt = $this->buildCorrectionPrompt($currentContent, $analysis['incorrect_structures'], $referenceContent);

            // Chamar Claude API com retry logic
            $correctedContent = $this->callClaudeApiWithRetry($prompt, $calibration);

            // Validar estrutura corrigida
            $validation = $this->validateCorrectedStructure($correctedContent);

            if (!$validation['is_valid']) {
                throw new Exception("Estrutura corrigida inválida: " . implode(', ', $validation['errors']));
            }

            // Salvar correção
            $this->saveCorrectedArticle($calibration, $correctedContent, $analysis);

            Log::info('PickupArticleFixService: Artigo corrigido com sucesso', [
                'calibration_id' => $calibration->_id,
                'vehicle' => "{$calibration->vehicle_make} {$calibration->vehicle_model}",
                'structures_fixed' => count($analysis['incorrect_structures']),
                'fixed_sections' => array_column($analysis['incorrect_structures'], 'section')
            ]);

            return [
                'success' => true,
                'structures_fixed' => count($analysis['incorrect_structures']),
                'fixed_sections' => array_column($analysis['incorrect_structures'], 'section'),
                'validation' => $validation,
                'analysis' => $analysis
            ];
        } catch (Exception $e) {
            Log::error('PickupArticleFixService: Erro na correção', [
                'calibration_id' => $calibration->_id,
                'vehicle' => "{$calibration->vehicle_make} {$calibration->vehicle_model}",
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'analysis' => $analysis ?? null
            ];
        }
    }

    /**
     * Validar estrutura de seção específica
     */
    private function validateSectionStructure(array $content, string $section, string $expectedType): ?array
    {
        if (!isset($content[$section])) {
            return [
                'section' => $section,
                'issue' => 'missing',
                'expected_type' => $expectedType,
                'actual_type' => 'null',
                'description' => "Seção {$section} está ausente"
            ];
        }

        $actualValue = $content[$section];
        $actualType = $this->getValueType($actualValue);

        // Verificações específicas por seção
        if ($section === 'localizacao_etiqueta') {
            return $this->validateLocalizacaoEtiqueta($actualValue, $actualType);
        }

        if ($actualType !== $expectedType) {
            return [
                'section' => $section,
                'issue' => 'wrong_type',
                'expected_type' => $expectedType,
                'actual_type' => $actualType,
                'description' => "Seção {$section} deveria ser {$expectedType} mas é {$actualType}"
            ];
        }

        return null;
    }

    /**
     * Validação específica para localizacao_etiqueta
     * 
     * Esta é a validação mais crítica pois é onde o ViewModel falha
     */
    private function validateLocalizacaoEtiqueta($value, string $actualType): ?array
    {
        if ($actualType === 'string') {
            // CRÍTICO: localizacao_etiqueta como string causa erro fatal
            return [
                'section' => 'localizacao_etiqueta',
                'issue' => 'critical_structure_error',
                'expected_type' => 'object',
                'actual_type' => 'string',
                'description' => 'CRÍTICO: localizacao_etiqueta como string causa erro fatal no ViewModel',
                'sample_content' => substr($value, 0, 100) . '...',
                'required_structure' => self::CORRECT_LOCALIZACAO_ETIQUETA_STRUCTURE
            ];
        }

        if ($actualType === 'object' || $actualType === 'array') {
            // Verificar se tem as chaves requeridas
            $requiredKeys = array_keys(self::CORRECT_LOCALIZACAO_ETIQUETA_STRUCTURE);
            $missingKeys = [];

            foreach ($requiredKeys as $key) {
                if (!isset($value[$key])) {
                    $missingKeys[] = $key;
                }
            }

            if (!empty($missingKeys)) {
                return [
                    'section' => 'localizacao_etiqueta',
                    'issue' => 'incomplete_structure',
                    'expected_type' => 'object',
                    'actual_type' => $actualType,
                    'description' => 'localizacao_etiqueta tem estrutura incompleta',
                    'missing_keys' => $missingKeys,
                    'required_structure' => self::CORRECT_LOCALIZACAO_ETIQUETA_STRUCTURE
                ];
            }
        }

        return null;
    }

    /**
     * Extrair conteúdo do artigo (priorizar article_refined)
     */
    private function extractArticleContent(TireCalibration $calibration): array
    {
        // Priorizar article_refined.content se existir
        if (!empty($calibration->article_refined)) {
            $refined = is_array($calibration->article_refined)
                ? $calibration->article_refined
                : json_decode($calibration->article_refined, true);

            if (!empty($refined['content'])) {
                return $refined['content'];
            }
        }

        // Fallback para generated_article.content
        if (!empty($calibration->generated_article)) {
            $generated = is_array($calibration->generated_article)
                ? $calibration->generated_article
                : json_decode($calibration->generated_article, true);

            if (!empty($generated['content'])) {
                return $generated['content'];
            }
        }

        return [];
    }

    /**
     * Verificar se é erro crítico (localizacao_etiqueta incorreta)
     */
    private function isCriticalStructureError(array $incorrectStructures): bool
    {
        foreach ($incorrectStructures as $structure) {
            if (
                $structure['section'] === 'localizacao_etiqueta' &&
                $structure['issue'] === 'critical_structure_error'
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Confirmar se é template pickup
     */
    private function isPickupTemplate(array $content): bool
    {
        // Verificar se tem características de pickup
        $pickupIndicators = [
            'tabela_carga',
            'capacidade_carga',
            'uso_trabalho',
            'tração'
        ];

        foreach ($pickupIndicators as $indicator) {
            if (isset($content[$indicator])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Identificar fonte do conteúdo
     */
    private function getContentSource(TireCalibration $calibration): string
    {
        if (!empty($calibration->article_refined)) {
            return 'article_refined';
        }
        if (!empty($calibration->generated_article)) {
            return 'generated_article';
        }
        return 'unknown';
    }

    /**
     * Obter tipo de valor PHP
     */
    private function getValueType($value): string
    {
        if (is_array($value)) {
            // Distinguir entre array e object
            return (array_keys($value) !== range(0, count($value) - 1)) ? 'object' : 'array';
        }

        return gettype($value);
    }

    /**
     * Obter referência Toyota Hilux como template
     */
    private function getPickupReference(): array
    {
        // Buscar Toyota Hilux como referência de estrutura correta
        $reference = TireCalibration::where('vehicle_make', 'Toyota')
            ->where('vehicle_model', 'Hilux')
            ->where('main_category', 'pickup')
            ->whereNotNull('article_refined')
            ->first();

        if ($reference && !empty($reference->article_refined)) {
            $content = is_array($reference->article_refined)
                ? $reference->article_refined
                : json_decode($reference->article_refined, true);

            if (!empty($content['content'])) {
                return $content['content'];
            }
        }

        // Se não encontrar, retornar estrutura modelo
        return $this->getDefaultPickupStructure();
    }

    /**
     * Estrutura modelo padrão para pickup
     */
    private function getDefaultPickupStructure(): array
    {
        return [
            'localizacao_etiqueta' => [
                'local_principal' => 'Coluna da porta do motorista',
                'descricao' => 'A etiqueta oficial de pressão está localizada na coluna da porta do motorista.',
                'locais_alternativos' => [
                    'Manual do proprietário na seção "Especificações Técnicas"',
                    'Display digital do painel (se disponível)',
                    'Tampa do tanque de combustível'
                ],
                'observacao' => 'Use sempre os valores oficiais da etiqueta como referência.'
            ],
            'condicoes_especiais' => [
                [
                    'situacao' => 'Com carga',
                    'descricao' => 'Para transporte de carga na caçamba',
                    'ajuste_pressao' => 'Aumentar 3-5 PSI no eixo traseiro'
                ]
            ],
            'conversao_unidades' => [
                'tabela_conversao' => [
                    ['psi' => '30', 'kgf_cm2' => '2.11', 'bar' => '2.07']
                ]
            ]
        ];
    }

    /**
     * Construir prompt para correção Claude
     * 
     * Atualizado para ser mais flexível e aceitar estruturas array OU object
     */
    private function buildCorrectionPrompt(array $currentContent, array $incorrectStructures, array $referenceContent): string
    {
        $vehicle = $currentContent['vehicle_info']['full_name'] ?? 'Pickup';

        $structureIssues = "ESTRUTURAS COM PROBLEMAS:\n";
        foreach ($incorrectStructures as $issue) {
            $structureIssues .= "- {$issue['section']}: {$issue['description']}\n";
        }

        return "Você é um especialista em correção de estruturas JSON para artigos de calibragem de pneus.

TAREFA: Corrigir as estruturas JSON incorretas no artigo de calibragem para {$vehicle}, mantendo TODO o conteúdo original.

{$structureIssues}

IMPORTANTE: O ViewModel aceita tanto ARRAY quanto OBJECT para as seções. O crítico é que NÃO sejam strings.

ESTRUTURAS ACEITAS:

localizacao_etiqueta - DEVE SER OBJECT (nunca string):
```json
{
  \"localizacao_etiqueta\": {
    \"local_principal\": \"string\",
    \"descricao\": \"string\", 
    \"locais_alternativos\": [\"array de strings\"],
    \"observacao\": \"string\"
  }
}
```

Outras seções - podem ser ARRAY ou OBJECT (nunca string):
- condicoes_especiais: array de objects OU object com propriedades
- conversao_unidades: object com tabela OU array de conversões  
- cuidados_recomendacoes: array de objects OU object com categorias
- impacto_pressao: object com tipos OU array de impactos

ARTIGO ATUAL (com estruturas incorretas):
```json
" . json_encode($currentContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "
```

REFERÊNCIA (estrutura que funciona):
```json
" . json_encode($referenceContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "
```

INSTRUÇÕES:
1. MANTER todo conteúdo textual existente
2. CONVERTER strings para array/object conforme necessário
3. Priorizar localizacao_etiqueta como object estruturado
4. Para outras seções: manter como object se já estiver funcionando
5. NUNCA alterar informações técnicas ou dados do veículo
6. Retornar JSON válido completo
7. Foque apenas nas seções que são STRINGS e precisam virar array/object

Retorne APENAS o JSON corrigido, sem explicações:";
    }

    /**
     * Chamar Claude API com retry logic
     */
    private function callClaudeApiWithRetry(string $prompt, TireCalibration $calibration): array
    {
        $attempt = 1;
        $lastError = null;

        while ($attempt <= $this->maxRetries) {
            try {
                Log::info("PickupArticleFixService: Tentativa {$attempt} para {$calibration->vehicle_make} {$calibration->vehicle_model}");

                $response = Http::timeout($this->requestTimeout)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'x-api-key' => $this->apiKey,
                        'anthropic-version' => '2023-06-01'
                    ])
                    ->post($this->apiUrl, [
                        'model' => 'claude-3-5-sonnet-20241022',
                        'max_tokens' => 4000,
                        'temperature' => 0.1,
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => $prompt
                            ]
                        ]
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $content = $data['content'][0]['text'] ?? '';

                    // Extrair e validar JSON
                    $correctedContent = $this->extractJsonFromResponse($content);

                    if (!empty($correctedContent)) {
                        return $correctedContent;
                    }

                    throw new Exception('Resposta Claude não contém JSON válido');
                }

                $lastError = "API Error: " . $response->status() . " - " . $response->body();
            } catch (Exception $e) {
                $lastError = $e->getMessage();
                Log::warning("PickupArticleFixService: Tentativa {$attempt} falhou", [
                    'error' => $lastError,
                    'vehicle' => "{$calibration->vehicle_make} {$calibration->vehicle_model}"
                ]);
            }

            if ($attempt < $this->maxRetries) {
                sleep(pow(2, $attempt)); // Backoff exponencial: 2s, 4s, 8s
            }

            $attempt++;
        }

        throw new Exception("Falha após {$this->maxRetries} tentativas. Último erro: {$lastError}");
    }

    /**
     * Extrair JSON da resposta Claude
     */
    private function extractJsonFromResponse(string $content): array
    {
        // Remover markdown code blocks se presentes
        $content = preg_replace('/```json\s*|\s*```/', '', $content);
        $content = trim($content);

        // Tentar decode direto
        $decoded = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Tentar extrair JSON do meio do texto
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $decoded = json_decode($matches[0], true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    /**
     * Validar estrutura corrigida
     */
    private function validateCorrectedStructure(array $correctedContent): array
    {
        $errors = [];
        $isValid = true;

        // Validar cada estrutura requerida
        foreach (self::REQUIRED_PICKUP_STRUCTURES as $section => $expectedType) {
            if (!isset($correctedContent[$section])) {
                $errors[] = "Seção {$section} ainda ausente";
                $isValid = false;
                continue;
            }

            $actualType = $this->getValueType($correctedContent[$section]);

            if ($actualType !== $expectedType) {
                $errors[] = "Seção {$section} ainda tem tipo incorreto: {$actualType} (esperado: {$expectedType})";
                $isValid = false;
            }

            // Validação específica para localizacao_etiqueta
            if ($section === 'localizacao_etiqueta' && $actualType === 'object') {
                $requiredKeys = array_keys(self::CORRECT_LOCALIZACAO_ETIQUETA_STRUCTURE);
                foreach ($requiredKeys as $key) {
                    if (!isset($correctedContent[$section][$key])) {
                        $errors[] = "localizacao_etiqueta não tem chave requerida: {$key}";
                        $isValid = false;
                    }
                }
            }
        }

        return [
            'is_valid' => $isValid,
            'errors' => $errors,
            'validated_sections' => array_keys(self::REQUIRED_PICKUP_STRUCTURES)
        ];
    }

    /**
     * Salvar artigo corrigido com estrutura correta
     * 
     * Salva no formato EXATO igual ao RefineWithClaudePhase3BCommand
     */
    private function saveCorrectedArticle(TireCalibration $calibration, array $correctedContent, array $analysis): void
    {
        // Extrair dados do artigo original para manter compatibilidade
        $originalArticle = $this->extractOriginalArticleStructure($calibration);

        // Construir article_refined no formato CORRETO (igual ao Audi A3)
        $articleRefined = [
            'title' => $originalArticle['title'] ?? $this->generateTitle($calibration),
            'slug' => $originalArticle['slug'] ?? $this->generateSlug($calibration),
            'template' => 'tire_calibration_pickup',
            'category_id' => 1,
            'category_name' => 'Calibragem de Pneus',
            'category_slug' => 'calibragem-pneus',

            // SEO data
            'seo_data' => $originalArticle['seo_data'] ?? $this->generateSeoData($calibration),

            // Dados do veículo
            'vehicle_data' => $originalArticle['vehicle_data'] ?? $this->extractVehicleData($calibration),

            // Entidades extraídas
            'extracted_entities' => $originalArticle['extracted_entities'] ?? $this->generateExtractedEntities($calibration),

            // CONTEÚDO CORRIGIDO
            'content' => $correctedContent,

            // Metadados formatados
            'formated_updated_at' => now()->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y'),
            'canonical_url' => $this->generateCanonicalUrl($calibration),

            // Enhancement metadata (igual ao Phase3B)
            'enhancement_metadata' => [
                'enhanced_by' => 'pickup-fix-service',
                'enhanced_at' => now()->toISOString(),
                'enhanced_areas' => array_keys($correctedContent),
                'model_used' => 'claude-3-5-sonnet-20241022',
                'structures_fixed' => count($analysis['incorrect_structures']),
                'fixed_sections' => array_column($analysis['incorrect_structures'], 'section'),
                'validation_passed' => true,
                'pickup_fix_success' => true,
                'total_api_calls' => 1,
                'fix_version' => '1.0'
            ]
        ];

        $calibration->update([
            'article_refined' => $articleRefined,
            'claude_refinement_version' => 'v4_pickup_fixed',
            'enrichment_phase' => TireCalibration::PHASE_CLAUDE_COMPLETED,
            'updated_at' => now(),

            // Metadados específicos de correção pickup (fora do article_refined)
            'pickup_fix_metadata' => [
                'fixed_at' => now()->toISOString(),
                'fixed_by' => 'PickupArticleFixService',
                'claude_model' => 'claude-3-5-sonnet-20241022',
                'sections_count' => count($correctedContent),
                'fix_version' => '1.0',
            ]
        ]);
    }

    /**
     * Extrair estrutura do artigo original (de generated_article ou existing article_refined)
     */
    private function extractOriginalArticleStructure(TireCalibration $calibration): array
    {
        // Se já tem article_refined, usar como base
        if (!empty($calibration->article_refined)) {
            $existing = is_array($calibration->article_refined)
                ? $calibration->article_refined
                : json_decode($calibration->article_refined, true);

            if (!empty($existing)) {
                return $existing;
            }
        }

        // Fallback: usar generated_article
        $generatedArticle = is_array($calibration->generated_article)
            ? $calibration->generated_article
            : json_decode($calibration->generated_article, true);

        return $generatedArticle ?? [];
    }

    /**
     * Gerar dados SEO
     */
    private function generateSeoData(TireCalibration $calibration): array
    {
        $vehicleFullName = "{$calibration->vehicle_make} {$calibration->vehicle_model}";

        return [
            'page_title' => "Calibragem do Pneu do {$vehicleFullName} – Guia Completo",
            'meta_description' => "Guia completo de calibragem dos pneus do {$vehicleFullName}. Pressões específicas e dicas especializadas.",
            'h1' => "Calibragem do Pneu do {$vehicleFullName} – Guia Completo",
            'primary_keyword' => "calibragem pneu " . strtolower("{$calibration->vehicle_make} {$calibration->vehicle_model}"),
            'secondary_keywords' => [
                "como calibrar pneu {$calibration->vehicle_make} {$calibration->vehicle_model}",
                "pressão pneu {$calibration->vehicle_make}",
                "calibrar pneu {$calibration->vehicle_model}",
                "procedimento calibragem {$calibration->vehicle_make}"
            ],
            'og_title' => "Calibragem do Pneu do {$vehicleFullName} – Guia Completo",
            'og_description' => "Procedimento completo de calibragem dos pneus do {$vehicleFullName}. Pressões específicas e dicas especializadas.",
            'canonical_url' => $this->generateCanonicalUrl($calibration)
        ];
    }

    /**
     * Gerar entidades extraídas
     */
    private function generateExtractedEntities(TireCalibration $calibration): array
    {
        return [
            'marca' => $calibration->vehicle_make,
            'modelo' => $calibration->vehicle_model,
            'categoria' => $calibration->main_category,
            'motorizacao' => '2.0 Turbo', // Padrão para pickup
            'combustivel' => 'Diesel'     // Padrão para pickup
        ];
    }

    /**
     * Gerar título para o artigo
     */
    private function generateTitle(TireCalibration $calibration): string
    {
        return "Calibragem do Pneu do {$calibration->vehicle_make} {$calibration->vehicle_model} – Guia Completo";
    }

    /**
     * Gerar slug para o artigo
     */
    private function generateSlug(TireCalibration $calibration): string
    {
        $make = strtolower($calibration->vehicle_make);
        $model = strtolower(str_replace(' ', '-', $calibration->vehicle_model));
        return "calibragem-pneu-{$make}-{$model}";
    }

    /**
     * Gerar URL canônica
     */
    private function generateCanonicalUrl(TireCalibration $calibration): string
    {
        $slug = $this->generateSlug($calibration);
        return "https://mercadoveiculos.com.br/info/{$slug}";
    }

    /**
     * Verificar conectividade com API
     */
    public function testApiConnection(): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01'
                ])
                ->post($this->apiUrl, [
                    'model' => 'claude-3-5-sonnet-20241022',
                    'max_tokens' => 10,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => 'test'
                        ]
                    ]
                ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'response_time' => $response->handlerStats()['total_time'] ?? null
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Estatísticas do service
     */
    public function getStats(): array
    {
        $pickupsTotal = TireCalibration::where('main_category', 'pickup')->count();
        $pickupsWithRefined = TireCalibration::where('main_category', 'pickup')
            ->whereNotNull('article_refined')->count();
        $pickupsV4Completed = TireCalibration::where('main_category', 'pickup')
            ->where('claude_refinement_version', 'v4_completed')->count();

        return [
            'service' => 'PickupArticleFixService',
            'version' => 'v4_structure_correction',
            'pickups_total' => $pickupsTotal,
            'pickups_with_refined' => $pickupsWithRefined,
            'pickups_v4_completed' => $pickupsV4Completed,
            'api_configured' => !empty($this->apiKey),
            'required_structures' => array_keys(self::REQUIRED_PICKUP_STRUCTURES),
            'critical_structure' => 'localizacao_etiqueta'
        ];
    }
}
