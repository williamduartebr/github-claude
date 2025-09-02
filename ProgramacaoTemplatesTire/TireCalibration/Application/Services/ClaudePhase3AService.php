<?php

namespace Src\ContentGeneration\TireCalibration\Application\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;

/**
 * ClaudePhase3AService - Refinamento de Conteúdo Editorial
 * 
 * Responsável por enriquecer apenas o conteúdo editorial:
 * - Introdução contextualizada com dados do mercado brasileiro
 * - Considerações finais personalizadas para o modelo
 * - Perguntas frequentes específicas e técnicas
 * - Meta description atrativa SEM pressões PSI
 * 
 * @version V4 Phase 3A - Editorial Content Only
 */
class ClaudePhase3AService
{
    private const CLAUDE_API_URL = 'https://api.anthropic.com/v1/messages';
    // private const MODEL = 'claude-3-7-sonnet-20250219';
    private const MODEL = 'claude-3-5-sonnet-20240620';
    private const MAX_TOKENS = 2500; // Menor para foco editorial
    private const TEMPERATURE = 0.3; // Mais criativo para conteúdo editorial

    private string $apiKey;
    private int $timeout;
    private int $maxRetries;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key', '');
        $this->timeout = config('services.anthropic.timeout', 60);
        $this->maxRetries = config('services.anthropic.max_retries', 2);
    }

    /**
     * Executar refinamento Fase 3A - Conteúdo Editorial APENAS
     */
    public function enhanceEditorialContent(TireCalibration $calibration): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Claude API Key não configurada');
        }

        if (!$calibration->needsClaudePhase3A()) {
            throw new \Exception('Registro não está pronto para Fase 3A');
        }

        try {
            $calibration->startClaudePhase3A();

            $baseArticle = $this->extractBaseArticle($calibration->generated_article);
            $vehicleInfo = $this->extractVehicleContext($calibration, $baseArticle);

            Log::info('ClaudePhase3AService: Iniciando refinamento editorial', [
                'tire_calibration_id' => $calibration->_id,
                'vehicle' => $vehicleInfo['display_name'],
                'phase' => '3A - Editorial Content Only'
            ]);

            $enhancements = $this->generateEditorialEnhancements($vehicleInfo, $baseArticle);

            $calibration->completeClaudePhase3A($enhancements);

            Log::info('ClaudePhase3AService: Refinamento editorial concluído com sucesso', [
                'tire_calibration_id' => $calibration->_id,
                'vehicle' => $vehicleInfo['display_name'],
                'enhanced_sections' => array_keys($enhancements),
                'meta_description_length' => strlen($enhancements['meta_description'] ?? ''),
                'intro_word_count' => str_word_count($enhancements['introducao'] ?? ''),
                'faqs_count' => count($enhancements['perguntas_frequentes'] ?? [])
            ]);

            return $enhancements;
        } catch (\Exception $e) {
            $calibration->markFailed("Claude Phase 3A failed: " . $e->getMessage());
            Log::error('ClaudePhase3AService: Erro no refinamento editorial', [
                'tire_calibration_id' => $calibration->_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Gerar enhancements editoriais via Claude API
     */
    private function generateEditorialEnhancements(array $vehicleInfo, array $baseArticle): array
    {
        $maxAttempts = $this->maxRetries;
        $attempt = 1;

        while ($attempt <= $maxAttempts) {
            try {
                $prompt = $this->buildEditorialPrompt($vehicleInfo, $baseArticle, $attempt);
                $response = $this->makeClaudeRequest($prompt);
                $enhancements = $this->parseClaudeResponse($response);

                // Validação específica para editorial
                $this->validateEditorialResponse($enhancements, $vehicleInfo);

                Log::info("ClaudePhase3AService: Conteúdo editorial validado na tentativa {$attempt}");
                return $enhancements;
            } catch (\Exception $e) {
                Log::warning("ClaudePhase3AService: Tentativa {$attempt} falhou: " . $e->getMessage());

                if ($attempt >= $maxAttempts) {
                    throw new \Exception("Falha após {$maxAttempts} tentativas: " . $e->getMessage());
                }

                $attempt++;
                sleep(2); // Pequeno delay entre tentativas
            }
        }
    }

    private function buildEditorialPrompt(array $vehicleInfo, array $baseArticle, int $attempt): string
    {
        $vehicleName = $vehicleInfo['display_name'];
        $make = $vehicleInfo['make'];
        $model = $vehicleInfo['model'];
        $category = $vehicleInfo['category'];

        $urgencyText = $attempt > 1 ?
            "🚨 TENTATIVA #{$attempt}: A tentativa anterior falhou. Use limites mais conservadores!" : "";

        // Contexto específico por marca
        $brandContext = $this->getBrandSpecificContext($make, $model, $category);

        return <<<EOT
    {$urgencyText}

    Você é um especialista em marketing automotivo brasileiro e jornalismo especializado.

    MISSÃO FASE 3A: Criar APENAS conteúdo editorial atrativo para {$vehicleName}

    {$brandContext}

    ⚠️ LIMITES REALISTAS (Google 2025):
    - Meta description: 120-300 caracteres (Google aceita até 320!)
    - Introdução: 100-300 palavras (muito flexível)
    - Considerações finais: 80-250 palavras (flexível)
    - FAQs: 3-8 perguntas, respostas mínimo 25 palavras cada

    🎯 JSON OBRIGATÓRIO - CONTEÚDO EDITORIAL OTIMIZADO:

    ```json
    {
    "meta_description": "Meta description atrativa para {$vehicleName} entre 120-300 caracteres. Pode ser mais descritiva e completa. PROIBIDO apenas: pressões PSI numéricas. Foque em benefícios, economia, segurança, características do modelo.",
    
    "introducao": "Introdução contextualizada de 100-300 palavras sobre {$vehicleName}. DEVE CONTER: posição no mercado brasileiro, características que o diferenciam, tecnologias específicas, público-alvo, importância da calibragem para este modelo. Use dados reais quando possível.",
    
    "consideracoes_finais": "Conclusão de 80-250 palavras específica para {$vehicleName}. DEVE RESUMIR: benefícios principais da calibragem correta para este modelo, características especiais mencionadas, call-to-action motivacional, reforço dos pontos principais.",
    
    "perguntas_frequentes": [
        {
        "pergunta": "Qual a pressão ideal do {$vehicleName}?",
        "resposta": "Resposta mínimo 25 palavras sobre pressões recomendadas, características do modelo, sistema TPMS se aplicável, diferenças entre versões."
        },
        {
        "pergunta": "Com que frequência verificar a pressão no {$vehicleName}?",
        "resposta": "Resposta considerando perfil de uso, público-alvo, características de manutenção do modelo."
        },
        {
        "pergunta": "Como a calibragem afeta o consumo do {$vehicleName}?",
        "resposta": "Resposta técnica sobre economia de combustível específica do modelo, motor, peso."
        },
        {
        "pergunta": "O {$vehicleName} tem sistema TPMS?",
        "resposta": "Resposta sobre sistema de monitoramento, versões que possuem, como funciona."
        }
    ]
    }
    🔥 REGRAS FLEXÍVEIS FASE 3A:

    Meta description: Pode ser descritiva até 300 caracteres
    Introdução: Contextualize bem o modelo (100-300 palavras)
    Considerações: Conclusão sólida (80-250 palavras)
    FAQs: Entre 3-8 perguntas relevantes, respostas mínimo 25 palavras
    Linguagem: Natural, envolvente, informativa

    💡 FOCO NA QUALIDADE:

    Conteúdo específico para o modelo
    Informações úteis para o proprietário
    Linguagem acessível mas técnica quando necessário
    SEO natural, não forçado

    EOT;
    }

    /**
     * Obter contexto específico por marca para enriquecer o prompt
     */
    private function getBrandSpecificContext(string $make, string $model, string $category): string
    {
        $contexts = [
            'chevrolet' => "A Chevrolet no Brasil é sinônimo de tradição e inovação. Com modelos como Onix (líder de vendas), Tracker, S10, a marca foca em tecnologia acessível e economia. Mencione: MyLink, OnStar, motores Ecotec, tradição nacional.",

            'volkswagen' => "Volkswagen representa engenharia alemã adaptada ao Brasil. Com Polo, Golf, T-Cross, Tiguan, foca em segurança e tecnologia TSI/MPI. Mencione: tradição alemã, motores TSI, sistema de segurança, qualidade de acabamento.",

            'toyota' => "Toyota é referência em confiabilidade e durabilidade no Brasil. Corolla, RAV4, Hilux, Prius dominam seus segmentos. Mencione: confiabilidade japonesa, tecnologia híbrida, valor de revenda, Toyota Safety Sense.",

            'honda' => "Honda combina tecnologia e esportividade. Civic, HR-V, City são referências em tecnologia e consumo. Mencione: motores VTEC, Honda Sensing, tradição esportiva, eficiência energética.",

            'ford' => "Ford representa performance e robustez americana. Ka, EcoSport, Ranger focam em versatilidade. Mencione: tradição americana, tecnologia EcoBoost, robustez, sistema SYNC.",

            'fiat' => "Fiat é sinônimo de economia e praticidade urbana. Argo, Mobi, Toro focam no custo-benefício brasileiro. Mencione: economia urbana, design italiano, praticidade, tradição no Brasil.",

            'hyundai' => "Hyundai representa tecnologia coreana e garantia estendida. HB20, Creta, Tucson focam em equipamentos e valor. Mencione: garantia estendida, tecnologia coreana, equipamentos de série, design moderno."
        ];

        return $contexts[strtolower($make)] ?? "Esta marca representa qualidade e tradição no mercado automotivo brasileiro.";
    }

    /**
     * Obter público-alvo típico por marca/modelo
     */
    private function getTargetAudience(string $make, string $model): string
    {
        $audiences = [
            'chevrolet_onix' => 'jovens urbanos, primeiros compradores, famílias jovens',
            'volkswagen_polo' => 'classe média, foco em qualidade, perfil urbano/rodoviário',
            'toyota_corolla' => 'executivos, famílias, foco em confiabilidade',
            'honda_civic' => 'entusiastas, jovens adultos, tecnologia',
            'ford_ka' => 'economia urbana, jovens, primeiro carro',
            'fiat_argo' => 'economia, praticidade urbana, custo-benefício',
        ];

        $key = strtolower($make . '_' . $model);
        return $audiences[$key] ?? 'motoristas brasileiros que valorizam qualidade e economia';
    }


    /**
     * Validar resposta específica da Fase 3A com limites realistas do Google
     */
    private function validateEditorialResponse(array $enhancements, array $vehicleInfo): void
    {
        $errors = [];

        // ✅ VALIDAR meta_description com limites REAIS do Google
        if (empty($enhancements['meta_description'])) {
            $errors[] = 'Meta description não foi gerada';
        } else {
            $metaLength = strlen($enhancements['meta_description']);
            // ✅ Google aceita até 320 caracteres! Limites realistas: 120-300
            if ($metaLength < 120 || $metaLength > 330) {
                $errors[] = "Meta description com {$metaLength} caracteres (aceito: 120-300)";
            }

            // Verificar se contém pressões PSI (proibido)
            if (preg_match('/\d+\s*PSI/i', $enhancements['meta_description'])) {
                $errors[] = 'Meta description contém pressões PSI (proibido na Fase 3A)';
            }

            // Verificar se é chamativa (palavras de ação) - opcional
            $actionWords = ['descubra', 'aprenda', 'economize', 'melhore', 'otimize', 'garanta', 'conquiste', 'domine', 'saiba', 'confira', 'veja', 'entenda', 'complete', 'guia'];
            $hasAction = false;
            foreach ($actionWords as $word) {
                if (stripos($enhancements['meta_description'], $word) !== false) {
                    $hasAction = true;
                    break;
                }
            }
            // Apenas log de aviso se não tiver palavras de ação
            if (!$hasAction) {
                Log::info('ClaudePhase3AService: Meta description sem palavras de ação explícitas (OK)', [
                    'meta_description' => substr($enhancements['meta_description'], 0, 100) . '...'
                ]);
            }
        }

        // ✅ VALIDAR introdução com limites muito flexíveis
        if (empty($enhancements['introducao'])) {
            $errors[] = 'Introdução não foi gerada';
        } else {
            $introWordCount = str_word_count($enhancements['introducao']);
            // ✅ Limites muito flexíveis: 100-300 palavras
            if ($introWordCount < 100 || $introWordCount > 300) {
                $errors[] = "Introdução com {$introWordCount} palavras (aceito: 100-300)";
            }

            // Verificar se menciona a marca (tolerante)
            if (
                stripos($enhancements['introducao'], $vehicleInfo['make']) === false &&
                stripos($enhancements['introducao'], $vehicleInfo['model']) === false
            ) {
                Log::warning('ClaudePhase3AService: Introdução não menciona marca/modelo explicitamente', [
                    'vehicle' => $vehicleInfo['display_name']
                ]);
                // Não é mais erro, apenas warning
            }
        }

        // ✅ VALIDAR considerações finais com limites muito flexíveis  
        if (empty($enhancements['consideracoes_finais'])) {
            $errors[] = 'Considerações finais não foram geradas';
        } else {
            $finalWordCount = str_word_count($enhancements['consideracoes_finais']);
            // ✅ Limites muito flexíveis: 80-250 palavras
            if ($finalWordCount < 80 || $finalWordCount > 250) {
                $errors[] = "Considerações finais com {$finalWordCount} palavras (aceito: 80-250)";
            }
        }

        // ✅ VALIDAR perguntas frequentes - muito mais flexível
        if (empty($enhancements['perguntas_frequentes'])) {
            $errors[] = 'Perguntas frequentes não foram geradas';
        } else {
            $faqs = $enhancements['perguntas_frequentes'];
            // ✅ Aceitar 3-8 FAQs (muito flexível)
            if (count($faqs) < 3 || count($faqs) > 8) {
                $errors[] = 'Deve ter entre 3 e 8 perguntas frequentes, encontradas: ' . count($faqs);
            }

            foreach ($faqs as $index => $faq) {
                if (empty($faq['pergunta']) || empty($faq['resposta'])) {
                    $errors[] = "FAQ #{$index} incompleta (pergunta ou resposta vazia)";
                    continue;
                }

                $respostaWordCount = str_word_count($faq['resposta']);
                // ✅ Mínimo muito baixo: 25 palavras
                if ($respostaWordCount < 25) {
                    $errors[] = "FAQ #{$index} resposta muito curta ({$respostaWordCount} palavras, mínimo 25)";
                }

                // ✅ Verificação de especificidade - apenas warning
                $vehicleName = $vehicleInfo['display_name'];
                $mentionsVehicle = (stripos($faq['pergunta'], $vehicleInfo['make']) !== false ||
                    stripos($faq['pergunta'], $vehicleInfo['model']) !== false ||
                    stripos($faq['resposta'], $vehicleInfo['make']) !== false ||
                    stripos($faq['resposta'], $vehicleInfo['model']) !== false);

                if (!$mentionsVehicle) {
                    Log::info("ClaudePhase3AService: FAQ #{$index} genérica (aceitável)", [
                        'pergunta' => substr($faq['pergunta'], 0, 50) . '...'
                    ]);
                }
            }
        }

        // ✅ Se chegou até aqui, validação passou
        if (empty($errors)) {
            Log::info('ClaudePhase3AService: Validação editorial passou com limites realistas', [
                'meta_length' => strlen($enhancements['meta_description']),
                'intro_words' => str_word_count($enhancements['introducao']),
                'final_words' => str_word_count($enhancements['consideracoes_finais']),
                'faqs_count' => count($enhancements['perguntas_frequentes']),
                'validation_version' => 'flexible_v2'
            ]);
            return;
        }

        // Se há erros, lançar exceção
        throw new \Exception('Validação Fase 3A falhou: ' . implode('; ', $errors));
    }

    /**
     * Extrair dados do artigo base
     */
    private function extractBaseArticle($generatedArticle): array
    {
        if (is_array($generatedArticle)) {
            return $generatedArticle;
        }

        if (is_string($generatedArticle)) {
            $decoded = json_decode($generatedArticle, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    /**
     * Extrair contexto do veículo
     */
    private function extractVehicleContext(TireCalibration $calibration, array $baseArticle): array
    {
        return [
            'make' => $calibration->vehicle_make ?? 'Veículo',
            'model' => $calibration->vehicle_model ?? 'Modelo',
            'year' => $calibration->vehicle_year,
            'display_name' => $this->buildDisplayName($calibration),
            'category' => $calibration->main_category ?? 'car',
            'is_motorcycle' => str_contains($calibration->main_category ?? '', 'motorcycle'),
            'is_electric' => ($calibration->main_category ?? '') === 'car_electric',
            'is_pickup' => ($calibration->main_category ?? '') === 'pickup',
        ];
    }

    /**
     * Construir nome de exibição do veículo
     */
    private function buildDisplayName(TireCalibration $calibration): string
    {
        $parts = array_filter([
            $calibration->vehicle_make,
            $calibration->vehicle_model,
            $calibration->vehicle_year
        ]);

        return implode(' ', $parts) ?: 'Veículo';
    }

    /**
     * Fazer requisição para Claude API
     */
    private function makeClaudeRequest(string $prompt): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01'
            ])
            ->post(self::CLAUDE_API_URL, [
                'model' => self::MODEL,
                'max_tokens' => self::MAX_TOKENS,
                'temperature' => self::TEMPERATURE,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ]
            ]);

        if (!$response->successful()) {
            throw new \Exception("Claude API Error: HTTP {$response->status()} - {$response->body()}");
        }

        return $response->json();
    }

    /**
     * Parse da resposta da Claude API
     */
    // ✅ DEPOIS (parsing robusto multi-estratégia):
    private function parseClaudeResponse(array $response): array
    {
        $text = $response['content'][0]['text'] ?? '';

        if (empty($text)) {
            throw new \Exception('Resposta da Claude API está vazia');
        }

        // ESTRATÉGIA 1: JSON dentro de code block ```json
        if (preg_match('/```json\s*(.*?)\s*```/s', $text, $matches)) {
            $jsonString = trim($matches[1]);
            $json = json_decode($jsonString, true);

            if ($json && json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }

            Log::warning('ClaudePhase3AService: JSON em code block inválido', [
                'json_error' => json_last_error_msg(),
                'json_preview' => substr($jsonString, 0, 200)
            ]);
        }

        // ESTRATÉGIA 2: JSON dentro de code block sem especificar linguagem
        if (preg_match('/```\s*(.*?)\s*```/s', $text, $matches)) {
            $jsonString = trim($matches[1]);

            // Verificar se parece JSON (começa com { ou [)
            if (preg_match('/^\s*[{\[]/', $jsonString)) {
                $json = json_decode($jsonString, true);

                if ($json && json_last_error() === JSON_ERROR_NONE) {
                    Log::info('ClaudePhase3AService: JSON recuperado de code block genérico');
                    return $json;
                }
            }
        }

        // ESTRATÉGIA 3: Buscar JSON no meio do texto
        if (preg_match('/\{.*\}/s', $text, $matches)) {
            $jsonString = $matches[0];
            $json = json_decode($jsonString, true);

            if ($json && json_last_error() === JSON_ERROR_NONE) {
                Log::info('ClaudePhase3AService: JSON recuperado do meio do texto');
                return $json;
            }
        }

        // ESTRATÉGIA 4: Tentar limpar e parsear texto inteiro
        $cleanText = trim($text);
        $cleanText = preg_replace('/^[^{]*/', '', $cleanText); // Remove texto antes do primeiro {
        $cleanText = preg_replace('/[^}]*$/', '', $cleanText); // Remove texto depois do último }

        if (!empty($cleanText)) {
            $json = json_decode($cleanText, true);

            if ($json && json_last_error() === JSON_ERROR_NONE) {
                Log::info('ClaudePhase3AService: JSON recuperado após limpeza');
                return $json;
            }
        }

        // ESTRATÉGIA 5: Buscar campos JSON individuais (última tentativa)
        $extractedData = $this->extractJsonFieldsManually($text);
        if (!empty($extractedData)) {
            Log::warning('ClaudePhase3AService: JSON reconstruído manualmente - pode ter problemas');
            return $extractedData;
        }

        // Se todas as estratégias falharam, logar detalhes para debug
        Log::error('ClaudePhase3AService: Todas as estratégias de parsing JSON falharam', [
            'text_preview' => substr($text, 0, 500),
            'text_length' => strlen($text),
            'has_json_block' => str_contains($text, '```json'),
            'has_code_block' => str_contains($text, '```'),
            'has_braces' => str_contains($text, '{'),
        ]);

        throw new \Exception('Resposta da Claude API não contém JSON válido após 5 estratégias de parsing');
    }

    /**
     * Extração manual de campos JSON como última tentativa
     * Quando o JSON está malformado mas os campos individuais são legíveis
     */
    private function extractJsonFieldsManually(string $text): array
    {
        $fields = [];

        try {
            // Extrair meta_description
            if (preg_match('/"meta_description":\s*"([^"]*)"/', $text, $matches)) {
                $fields['meta_description'] = $matches[1];
            }

            // Extrair introducao
            if (preg_match('/"introducao":\s*"([^"]*(?:"[^"]*"[^"]*)*)"/', $text, $matches)) {
                $fields['introducao'] = $matches[1];
            }

            // Extrair consideracoes_finais
            if (preg_match('/"consideracoes_finais":\s*"([^"]*(?:"[^"]*"[^"]*)*)"/', $text, $matches)) {
                $fields['consideracoes_finais'] = $matches[1];
            }

            // Extrair perguntas_frequentes (mais complexo)
            if (preg_match('/"perguntas_frequentes":\s*\[(.*?)\]/s', $text, $matches)) {
                $faqText = $matches[1];
                $faqs = [];

                // Buscar pares pergunta/resposta
                if (preg_match_all('/\{\s*"pergunta":\s*"([^"]*)",\s*"resposta":\s*"([^"]*(?:"[^"]*"[^"]*)*)"/', $faqText, $faqMatches, PREG_SET_ORDER)) {
                    foreach ($faqMatches as $faqMatch) {
                        $faqs[] = [
                            'pergunta' => $faqMatch[1],
                            'resposta' => $faqMatch[2]
                        ];
                    }
                }

                if (!empty($faqs)) {
                    $fields['perguntas_frequentes'] = $faqs;
                }
            }

            // Só retornar se conseguiu pelo menos 3 campos essenciais
            $requiredFields = ['meta_description', 'introducao', 'consideracoes_finais'];
            $foundRequired = count(array_intersect($requiredFields, array_keys($fields)));

            if ($foundRequired >= 3) {
                Log::info('ClaudePhase3AService: Extração manual bem-sucedida', [
                    'fields_extracted' => array_keys($fields),
                    'faqs_count' => count($fields['perguntas_frequentes'] ?? [])
                ]);
                return $fields;
            }
        } catch (\Exception $e) {
            Log::warning('ClaudePhase3AService: Falha na extração manual', [
                'error' => $e->getMessage()
            ]);
        }

        return [];
    }

    /**
     * Teste de conectividade da API
     */
    public function testApiConnection(): array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01'
                ])
                ->post(self::CLAUDE_API_URL, [
                    'model' => self::MODEL,
                    'max_tokens' => 50,
                    'messages' => [
                        ['role' => 'user', 'content' => 'Teste de conectividade - responda apenas: OK']
                    ]
                ]);

            return [
                'success' => $response->successful(),
                'message' => $response->successful() ? 'Claude API Phase 3A conectada' : 'Erro: ' . $response->status(),
                'model' => self::MODEL,
                'phase' => '3A - Editorial'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage(),
                'phase' => '3A - Editorial'
            ];
        }
    }

    /**
     * Estatísticas do serviço Fase 3A
     */
    public function getPhase3AStats(): array
    {
        $readyFor3A = TireCalibration::readyForClaudePhase3A()->count();
        $processing3A = TireCalibration::where('enrichment_phase', TireCalibration::PHASE_CLAUDE_3A_PROCESSING)->count();
        $completed3A = TireCalibration::where('enrichment_phase', TireCalibration::PHASE_CLAUDE_3A_COMPLETED)->count();

        return [
            'service' => 'ClaudePhase3AService',
            'version' => 'v4_editorial_only',
            'ready_for_processing' => $readyFor3A,
            'currently_processing' => $processing3A,
            'completed' => $completed3A,
            'api_configured' => !empty($this->apiKey),
            'success_rate' => ($completed3A + $processing3A) > 0 ? round(($completed3A / ($completed3A + $processing3A)) * 100, 2) : 0,
            'focus_areas' => ['meta_description', 'introducao', 'consideracoes_finais', 'perguntas_frequentes'],
            'validation_enabled' => true,
            'max_retries' => $this->maxRetries,
        ];
    }
}
