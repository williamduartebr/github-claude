<?php

namespace Src\ContentGeneration\TireCalibration\Application\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;
use Carbon\Carbon;

/**
 * PickupArticleFixService - Corrigir pickups com conteúdo incompleto
 * 
 * Service especializado para corrigir pickups que passaram pelo processo V4
 * mas ficaram com article_refined.content incompleto.
 * 
 * BASEADO NO TEMPLATE FUNCIONAL:
 * - Toyota Hilux 2023 (exemplo de pickup completo e bem estruturado)
 * - Utiliza Claude 3.5 Sonnet para gerar conteúdo específico
 * - Preserva estrutura e metadados existentes
 * - Foco apenas na seção 'content' que está incompleta
 * 
 * ESTRATÉGIA DE CORREÇÃO:
 * 1. Detectar seções faltantes no content
 * 2. Gerar conteúdo usando template Hilux como referência
 * 3. Adaptar para marca/modelo específico
 * 4. Preservar dados técnicos existentes
 * 5. Manter compatibilidade V4
 * 
 * @author Claude Sonnet 4  
 * @version 1.0
 */
class PickupArticleFixService
{
    private const CLAUDE_API_URL = 'https://api.anthropic.com/v1/messages';
    private const CLAUDE_MODEL = 'claude-3-5-sonnet-20241022';
    private const MAX_TOKENS = 4000;
    private const TIMEOUT_SECONDS = 60;

    /**
     * Template base da Toyota Hilux (exemplo que funciona perfeitamente)
     */
    private const HILUX_TEMPLATE_SECTIONS = [
        'introducao' => true,
        'especificacoes_por_versao' => true,
        'tabela_carga_completa' => true,
        'localizacao_etiqueta' => true,
        'condicoes_especiais' => true,
        'conversao_unidades' => true,
        'cuidados_recomendacoes' => true,
        'impacto_pressao' => true,
        'perguntas_frequentes' => true,
        'consideracoes_finais' => true,
    ];

    /**
     * Corrigir conteúdo incompleto de pickup
     */
    public function fixIncompletePickupContent(TireCalibration $calibration): array
    {
        try {
            Log::info('PickupArticleFixService: Iniciando correção', [
                'id' => $calibration->_id,
                'vehicle' => "{$calibration->vehicle_make} {$calibration->vehicle_model}",
                'category' => $calibration->main_category
            ]);

            // 1. Analisar o que está faltando
            $missingAnalysis = $this->analyzeMissingSections($calibration);
            
            if (empty($missingAnalysis['missing_sections'])) {
                return [
                    'success' => true,
                    'action' => 'skipped',
                    'message' => 'Conteúdo já está completo',
                    'analysis' => $missingAnalysis
                ];
            }

            // 2. Preparar prompt para Claude 3.5
            $prompt = $this->buildClaudePrompt($calibration, $missingAnalysis);
            
            // 3. Chamar Claude API
            $claudeResponse = $this->callClaudeApi($prompt);
            
            // 4. Processar resposta e extrair JSON
            $newContent = $this->extractContentFromResponse($claudeResponse);
            
            // 5. Mesclar com conteúdo existente
            $finalContent = $this->mergeWithExistingContent($calibration, $newContent);
            
            // 6. Atualizar registro
            $this->updateCalibrationContent($calibration, $finalContent);
            
            Log::info('PickupArticleFixService: Correção concluída com sucesso', [
                'id' => $calibration->_id,
                'sections_fixed' => array_keys($missingAnalysis['missing_sections']),
                'total_sections' => count($finalContent)
            ]);
            
            return [
                'success' => true,
                'action' => 'fixed',
                'message' => 'Conteúdo corrigido com sucesso',
                'sections_fixed' => array_keys($missingAnalysis['missing_sections']),
                'final_sections_count' => count($finalContent)
            ];
            
        } catch (\Exception $e) {
            Log::error('PickupArticleFixService: Erro na correção', [
                'id' => $calibration->_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception("Erro ao corrigir pickup: {$e->getMessage()}");
        }
    }

    /**
     * Analisar seções faltantes
     */
    public function analyzeMissingSections(TireCalibration $calibration): array
    {
        $existingContent = $calibration->article_refined['content'] ?? [];
        $missingSections = [];
        $incompleteSections = [];

        foreach (self::HILUX_TEMPLATE_SECTIONS as $section => $required) {
            if (!isset($existingContent[$section]) || empty($existingContent[$section])) {
                $missingSections[$section] = 'completely_missing';
            } elseif ($this->isSectionIncomplete($section, $existingContent[$section])) {
                $incompleteSections[$section] = 'incomplete';
            }
        }

        return [
            'missing_sections' => $missingSections,
            'incomplete_sections' => $incompleteSections,
            'existing_sections' => array_keys($existingContent),
            'needs_fix' => !empty($missingSections) || !empty($incompleteSections)
        ];
    }

    /**
     * Verificar se seção está incompleta
     */
    private function isSectionIncomplete(string $section, $content): bool
    {
        if ($section === 'introducao') {
            // Introdução deve ter pelo menos 200 caracteres
            return is_string($content) && strlen($content) < 200;
        }
        
        if ($section === 'perguntas_frequentes') {
            // FAQs deve ter pelo menos 3 perguntas
            return !is_array($content) || count($content) < 3;
        }
        
        if ($section === 'especificacoes_por_versao') {
            // Deve ter pelo menos 2 versões
            return !is_array($content) || count($content) < 2;
        }

        if ($section === 'localizacao_etiqueta') {
            // CRÍTICO: localizacao_etiqueta deve ser object, não string
            return is_string($content);
        }

        if ($section === 'condicoes_especiais') {
            // Deve ser array, não string
            return is_string($content) || !is_array($content);
        }

        if ($section === 'conversao_unidades') {
            // Deve ter estrutura object com tabela
            return !is_array($content) || !isset($content['tabela_conversao']);
        }

        if ($section === 'cuidados_recomendacoes') {
            // Deve ser array, não string
            return is_string($content) || !is_array($content);
        }

        if ($section === 'impacto_pressao') {
            // Deve ter estrutura completa com subcalibrado/ideal/sobrecalibrado
            return !is_array($content) || !isset($content['subcalibrado']);
        }

        return false;
    }

    /**
     * Construir prompt para Claude 3.5
     */
    private function buildClaudePrompt(TireCalibration $calibration, array $analysis): string
    {
        $vehicleInfo = $this->extractVehicleInfo($calibration);
        $existingContent = $calibration->article_refined['content'] ?? [];
        
        $prompt = "Você é um especialista em calibração de pneus e redação técnica. Precisa COMPLETAR o conteúdo de um artigo sobre calibração de pneus para pickup que ficou incompleto.

VEÍCULO ALVO:
- Marca: {$vehicleInfo['make']}
- Modelo: {$vehicleInfo['model']}  
- Ano: {$vehicleInfo['year']}
- Categoria: Pickup
- Pressão Recomendada: {$vehicleInfo['pressure_display']}
- Tamanho do Pneu: {$vehicleInfo['tire_size']}

SEÇÕES QUE PRECISAM SER CRIADAS/COMPLETADAS:
" . implode(', ', array_keys($analysis['missing_sections'])) . "

TEMPLATE DE REFERÊNCIA (baseado na Toyota Hilux que funciona perfeitamente):
Use esta estrutura como referência, mas ADAPTE para o veículo específico:

```json
{
    \"introducao\": \"Texto completo sobre o veículo, destacando características de pickup, capacidade de carga, versatilidade urbano/off-road, importância da calibragem correta para segurança e economia.\",
    
    \"especificacoes_por_versao\": [
        {
            \"versao\": \"[MODELO] Base\",
            \"medida_pneus\": \"{$vehicleInfo['tire_size']}\",
            \"indice_carga_velocidade\": \"112S\",
            \"pressao_dianteiro_normal\": \"{$vehicleInfo['pressure_front']}\",
            \"pressao_traseiro_normal\": \"{$vehicleInfo['pressure_rear']}\",
            \"pressao_dianteiro_carregado\": \"{$vehicleInfo['pressure_front_loaded']}\",
            \"pressao_traseiro_carregado\": \"{$vehicleInfo['pressure_rear_loaded']}\"
        },
        {
            \"versao\": \"[MODELO] Topo de Linha\",
            \"medida_pneus\": \"{$vehicleInfo['tire_size']}\",
            \"indice_carga_velocidade\": \"112S\",
            \"pressao_dianteiro_normal\": \"{$vehicleInfo['pressure_front']}\",
            \"pressao_traseiro_normal\": \"{$vehicleInfo['pressure_rear']}\",
            \"pressao_dianteiro_carregado\": \"{$vehicleInfo['pressure_front_loaded']}\",
            \"pressao_traseiro_carregado\": \"{$vehicleInfo['pressure_rear_loaded']}\"
        }
    ],
    
    \"perguntas_frequentes\": [
        {
            \"pergunta\": \"Qual a pressão ideal do {$vehicleInfo['make']} {$vehicleInfo['model']} em PSI?\",
            \"resposta\": \"Para o {$vehicleInfo['make']} {$vehicleInfo['model']}, use {$vehicleInfo['pressure_display']} para uso normal. Com carga máxima, ajuste conforme especificado.\"
        },
        {
            \"pergunta\": \"Com que frequência verificar a pressão na {$vehicleInfo['model']}?\",
            \"resposta\": \"Verifique semanalmente devido ao uso intensivo típico de pickups. Sistema TPMS auxilia no monitoramento.\"
        },
        {
            \"pergunta\": \"Como ajustar pressão para off-road na {$vehicleInfo['model']}?\",
            \"resposta\": \"Para off-road pesado, reduza 5 PSI em todos os pneus. Sempre recalibre após uso off-road.\"
        },
        {
            \"pergunta\": \"O {$vehicleInfo['make']} {$vehicleInfo['model']} tem sistema TPMS?\",
            \"resposta\": \"Versões mais recentes possuem TPMS que monitora pressão em tempo real e alerta sobre variações.\"
        },
        {
            \"pergunta\": \"Posso usar pneus all-terrain com mesma pressão?\",
            \"resposta\": \"Sim, mantenha as mesmas pressões com pneus all-terrain, ideais para uso misto da pickup.\"
        }
    ],
    
    \"consideracoes_finais\": \"O {$vehicleInfo['make']} {$vehicleInfo['model']} é uma pickup [DESTAQUE AS CARACTERÍSTICAS]. Manter a pressão correta ({$vehicleInfo['pressure_display']}) garante máxima performance, economia e segurança para uso urbano, rodoviário e off-road.\"
}
```

INSTRUÇÕES ESPECÍFICAS:
1. ADAPTE todo o conteúdo para o {$vehicleInfo['make']} {$vehicleInfo['model']}
2. Use dados técnicos reais (pressões, tamanho de pneu) fornecidos
3. Mantenha o tom técnico mas acessível
4. Foque nos benefícios específicos de pickups (capacidade de carga, off-road, versatilidade)
5. Retorne APENAS o JSON válido, sem explicações adicionais

CONTEÚDO EXISTENTE (para preservar):
" . json_encode($existingContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "

Gere o JSON completo com TODAS as seções necessárias:";

        return $prompt;
    }

    /**
     * Extrair informações do veículo
     */
    private function extractVehicleInfo(TireCalibration $calibration): array
    {
        $vehicleData = $calibration->article_refined['vehicle_data'] ?? [];
        $pressureSpecs = $vehicleData['pressure_specifications'] ?? [];
        
        return [
            'make' => $calibration->vehicle_make ?? $vehicleData['make'] ?? 'Veículo',
            'model' => $calibration->vehicle_model ?? $vehicleData['model'] ?? 'Pickup',
            'year' => $calibration->vehicle_year ?? $vehicleData['year'] ?? '2023',
            'tire_size' => $pressureSpecs['tire_size'] ?? $vehicleData['tire_size'] ?? '265/65 R17',
            'pressure_display' => $pressureSpecs['pressure_display'] ?? 'Dianteiros: 35 PSI / Traseiros: 35 PSI',
            'pressure_front' => $pressureSpecs['pressure_empty_front'] ?? 35,
            'pressure_rear' => $pressureSpecs['pressure_empty_rear'] ?? 35,
            'pressure_front_loaded' => $pressureSpecs['pressure_max_front'] ?? 38,
            'pressure_rear_loaded' => $pressureSpecs['pressure_max_rear'] ?? 42,
        ];
    }

    /**
     * Chamar Claude API
     */
    private function callClaudeApi(string $prompt): array
    {
        $apiKey = config('services.claude.api_key');
        
        if (!$apiKey) {
            throw new \Exception('Claude API key não configurada');
        }

        $response = Http::timeout(self::TIMEOUT_SECONDS)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'content-type' => 'application/json',
                'anthropic-version' => '2023-06-01'
            ])
            ->post(self::CLAUDE_API_URL, [
                'model' => self::CLAUDE_MODEL,
                'max_tokens' => self::MAX_TOKENS,
                'temperature' => 0.3,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ]
            ]);

        if (!$response->successful()) {
            throw new \Exception('Erro na API Claude: ' . $response->body());
        }

        $data = $response->json();
        
        if (!isset($data['content'][0]['text'])) {
            throw new \Exception('Resposta da API Claude inválida');
        }

        return [
            'content' => $data['content'][0]['text'],
            'usage' => $data['usage'] ?? [],
        ];
    }

    /**
     * Extrair conteúdo JSON da resposta do Claude
     */
    private function extractContentFromResponse(array $response): array
    {
        $text = $response['content'];
        
        // ESTRATÉGIA 1: JSON dentro de code block
        if (preg_match('/```(?:json)?\s*(.*?)\s*```/s', $text, $matches)) {
            $jsonString = trim($matches[1]);
            $json = json_decode($jsonString, true);

            if ($json && json_last_error() === JSON_ERROR_NONE) {
                Log::info('PickupArticleFixService: JSON extraído de code block');
                return $json;
            }
        }

        // ESTRATÉGIA 2: JSON no início/meio do texto
        if (preg_match('/\{.*\}/s', $text, $matches)) {
            $jsonString = $matches[0];
            $json = json_decode($jsonString, true);

            if ($json && json_last_error() === JSON_ERROR_NONE) {
                Log::info('PickupArticleFixService: JSON extraído do texto');
                return $json;
            }
        }

        // ESTRATÉGIA 3: Tentar todo o texto como JSON
        $json = json_decode($text, true);
        if ($json && json_last_error() === JSON_ERROR_NONE) {
            Log::info('PickupArticleFixService: Texto completo é JSON válido');
            return $json;
        }

        throw new \Exception('Não foi possível extrair JSON válido da resposta do Claude: ' . json_last_error_msg());
    }

    /**
     * Mesclar novo conteúdo com existente
     * 
     * ⚠️ TRATA: article_refined pode ser string JSON do MongoDB
     */
    private function mergeWithExistingContent(TireCalibration $calibration, array $newContent): array
    {
        // Usar método seguro para garantir array
        $articleRefined = $this->ensureArrayFromField($calibration, 'article_refined');
        $existingContent = $articleRefined['content'] ?? [];
        
        // Priorizar conteúdo novo, mas preservar seções que já estavam completas
        $mergedContent = $existingContent;
        
        foreach ($newContent as $section => $content) {
            if (!empty($content)) {
                // Sempre sobrescrever com conteúdo novo se disponível
                $mergedContent[$section] = $content;
                
                Log::info('PickupArticleFixService: Seção atualizada', [
                    'section' => $section,
                    'type' => gettype($content),
                    'size' => is_string($content) ? strlen($content) : count($content)
                ]);
            }
        }
        
        return $mergedContent;
    }

    /**
     * Atualizar registro com novo conteúdo
     * 
     * ⚠️ IMPORTANTE: Força array no update para evitar problemas de cast
     */
    private function updateCalibrationContent(TireCalibration $calibration, array $finalContent): void
    {
        // Garantir que temos array válido
        $articleRefined = $this->ensureArrayFromField($calibration, 'article_refined');
        
        // Preservar toda estrutura article_refined, apenas atualizando 'content'
        $articleRefined['content'] = $finalContent;
        
        // Adicionar metadados da correção
        $articleRefined['pickup_fix_metadata'] = [
            'fixed_at' => now()->toISOString(),
            'fixed_by' => 'PickupArticleFixService',
            'claude_model' => self::CLAUDE_MODEL,
            'sections_count' => count($finalContent),
            'fix_version' => '1.0'
        ];
        
        // FORÇAR cast no update - converter explicitamente para array
        $updateData = [
            'article_refined' => $articleRefined, // Laravel deve aplicar cast aqui
            'content_quality_score' => $this->calculateContentQuality($finalContent),
            'last_content_update' => now(),
        ];
        
        // Debug do tipo antes do update
        Log::info('PickupArticleFixService: Atualizando registro', [
            'id' => $calibration->_id,
            'article_refined_type' => gettype($articleRefined),
            'is_array' => is_array($articleRefined),
            'sections_count' => count($finalContent)
        ]);
        
        $calibration->update($updateData);
        
        // Verificar se update funcionou corretamente
        $calibration->refresh();
        $updatedArticle = $this->ensureArrayFromField($calibration, 'article_refined');
        
        Log::info('PickupArticleFixService: Update verificado', [
            'id' => $calibration->_id,
            'updated_sections_count' => count($updatedArticle['content'] ?? []),
            'success' => !empty($updatedArticle['content'])
        ]);
    }

    /**
     * Calcular score de qualidade do conteúdo
     */
    private function calculateContentQuality(array $content): int
    {
        $score = 0;
        $maxScore = 10;
        
        // Verificar seções essenciais (6 pontos)
        $essentialSections = ['introducao', 'perguntas_frequentes', 'consideracoes_finais'];
        foreach ($essentialSections as $section) {
            if (!empty($content[$section])) {
                $score += 2;
            }
        }
        
        // Verificar seções técnicas (4 pontos)
        $technicalSections = ['especificacoes_por_versao', 'tabela_carga_completa'];
        foreach ($technicalSections as $section) {
            if (!empty($content[$section])) {
                $score += 2;
            }
        }
        
        return min($score, $maxScore);
    }

    /**
     * Detectar registros pickup que precisam de correção
     */
    public function detectIncompletePickups(int $limit = 50): \Illuminate\Support\Collection
    {
        return TireCalibration::where('main_category', 'pickup')
            ->where('claude_refinement_version', 'v4_completed')
            ->whereNotNull('article_refined')
            ->get()
            ->filter(function ($calibration) {
                $analysis = $this->analyzeMissingSections($calibration);
                return $analysis['needs_fix'];
            })
            ->take($limit);
    }

    /**
     * Estatísticas dos pickups incompletos
     */
    public function getIncompletePickupsStats(): array
    {
        $totalPickups = TireCalibration::where('main_category', 'pickup')
            ->where('claude_refinement_version', 'v4_completed')
            ->count();
            
        $incompletePickups = $this->detectIncompletePickups(1000);
        
        $sectionProblems = [];
        
        foreach ($incompletePickups as $pickup) {
            $analysis = $this->analyzeMissingSections($pickup);
            
            foreach ($analysis['missing_sections'] as $section => $status) {
                $sectionProblems[$section] = ($sectionProblems[$section] ?? 0) + 1;
            }
        }
        
        return [
            'total_pickups_v4_completed' => $totalPickups,
            'incomplete_pickups_count' => $incompletePickups->count(),
            'incomplete_percentage' => $totalPickups > 0 ? round(($incompletePickups->count() / $totalPickups) * 100, 1) : 0,
            'most_missing_sections' => $sectionProblems,
            'needs_attention' => $incompletePickups->count() > 0
        ];
    }

    /**
     * Testar conexão com Claude API
     */
    public function testApiConnection(): array
    {
        try {
            $testPrompt = "Responda apenas com: {\"status\": \"ok\", \"message\": \"Claude API funcionando\"}";
            
            $response = $this->callClaudeApi($testPrompt);
            $testResult = $this->extractContentFromResponse($response);
            
            if (isset($testResult['status']) && $testResult['status'] === 'ok') {
                return [
                    'success' => true,
                    'message' => 'Claude API conectada e funcionando',
                    'model' => self::CLAUDE_MODEL
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Claude API respondeu mas formato inesperado'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao conectar com Claude API: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Executar limpeza de registros travados
     */
    public function cleanupStuckRecords(): int
    {
        $stuckRecords = TireCalibration::where('claude_refinement_version', 'v4_pickup_fixing')
            ->where('updated_at', '<', now()->subHours(2))
            ->get();
            
        $cleanedCount = 0;
        
        foreach ($stuckRecords as $record) {
            $record->update([
                'claude_refinement_version' => 'v4_completed',
                'last_error' => 'Limpeza automática - processo travado'
            ]);
            
            $cleanedCount++;
            
            Log::warning('PickupArticleFixService: Registro travado limpo', [
                'id' => $record->_id,
                'vehicle' => "{$record->vehicle_make} {$record->vehicle_model}"
            ]);
        }
        
        return $cleanedCount;
    }
}