<?php

namespace Src\TirePressureCorrection\Infrastructure\Services;


use Illuminate\Support\Facades\Log;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\TirePressureCorrection\Domain\Entities\TirePressureCorrection;

/**
 * Service para correção de pressões de pneus via Claude API
 */
class TirePressureCorrectionService
{
    protected ClaudeSonnetService $claudeService;
    
    public function __construct(ClaudeSonnetService $claudeService)
    {
        $this->claudeService = $claudeService;
    }
    
    /**
     * Corrigir pressões de um artigo
     */
    public function correctArticle(Article $article): bool
    {
        try {
            // Verificar se foi corrigido recentemente
            if (TirePressureCorrection::wasRecentlyCorrected($article->_id, 24)) {
                Log::info('TirePressureCorrectionService: Artigo já foi corrigido recentemente', [
                    'article_id' => $article->_id,
                    'slug' => $article->slug
                ]);
                return false;
            }
            
            // Criar registro de correção
            $correction = TirePressureCorrection::createForArticle($article);
            $correction->markAsProcessing();
            
            // Extrair dados do veículo
            $vehicleData = $this->extractVehicleData($article);
            
            if (!$vehicleData) {
                $correction->markAsFailed('Dados do veículo não encontrados');
                return false;
            }
            
            // Obter pressões corretas via Claude
            $correctedPressures = $this->getPressuresFromClaude($vehicleData);
            
            if (!$correctedPressures) {
                $correction->markAsFailed('Erro ao obter pressões do Claude');
                return false;
            }
            
            // Aplicar correções no artigo
            $fieldsUpdated = $this->applyCorrections($article, $correctedPressures);
            
            // Marcar correção como concluída
            $correction->markAsCompleted($correctedPressures, $fieldsUpdated);
            
            Log::info('TirePressureCorrectionService: Correção concluída', [
                'article_id' => $article->_id,
                'slug' => $article->slug,
                'fields_updated' => count($fieldsUpdated)
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('TirePressureCorrectionService: Erro na correção', [
                'article_id' => $article->_id,
                'error' => $e->getMessage()
            ]);
            
            if (isset($correction)) {
                $correction->markAsFailed($e->getMessage());
            }
            
            return false;
        }
    }
    
    /**
     * Extrair dados do veículo do artigo
     */
    protected function extractVehicleData(Article $article): ?array
    {
        $extractedEntities = data_get($article, 'extracted_entities');
        
        if (!$extractedEntities) {
            return null;
        }
        
        $marca = data_get($extractedEntities, 'marca');
        $modelo = data_get($extractedEntities, 'modelo');
        $ano = data_get($extractedEntities, 'ano');
        
        if (!$marca || !$modelo) {
            return null;
        }
        
        return [
            'marca' => $marca,
            'modelo' => $modelo,
            'ano' => $ano,
            'tipo_veiculo' => data_get($extractedEntities, 'tipo_veiculo'),
            'categoria' => data_get($extractedEntities, 'categoria'),
            'medida_pneu' => data_get($extractedEntities, 'medida_pneu')
        ];
    }
    
    /**
     * Obter pressões corretas do Claude
     */
    protected function getPressuresFromClaude(array $vehicleData): ?array
    {
        $prompt = $this->buildClaudePrompt($vehicleData);
        
        try {
            $response = $this->claudeService->generateContent($prompt, [
                'max_tokens' => 500,
                'temperature' => 0.1
            ]);
            
            return $this->parseClaudeResponse($response);
            
        } catch (\Exception $e) {
            Log::error('TirePressureCorrectionService: Erro ao chamar Claude', [
                'error' => $e->getMessage(),
                'vehicle' => "{$vehicleData['marca']} {$vehicleData['modelo']}"
            ]);
            return null;
        }
    }
    
    /**
     * Construir prompt para Claude
     */
    protected function buildClaudePrompt(array $vehicleData): string
    {
        $isMotorcycle = $vehicleData['tipo_veiculo'] === 'motorcycle' || 
                       str_contains(strtolower($vehicleData['categoria'] ?? ''), 'motorcycle');
        
        return "Você é um especialista em pressão de pneus. Preciso das pressões corretas para:

VEÍCULO: {$vehicleData['marca']} {$vehicleData['modelo']} {$vehicleData['ano']}
TIPO: " . ($isMotorcycle ? "Motocicleta" : "Carro") . "
MEDIDA DO PNEU: {$vehicleData['medida_pneu']}

Forneça APENAS as pressões recomendadas pelo fabricante em PSI.

IMPORTANTE:
- Para motocicletas, as pressões geralmente variam de 22 a 42 PSI
- Para carros, as pressões geralmente variam de 28 a 36 PSI
- Sempre números inteiros
- Baseie-se nos manuais oficiais

Responda EXATAMENTE neste formato JSON:
{
    \"empty_front\": 0,
    \"empty_rear\": 0,
    \"loaded_front\": 0,
    \"loaded_rear\": 0
}";
    }
    
    /**
     * Parsear resposta do Claude
     */
    protected function parseClaudeResponse(string $response): ?array
    {
        // Extrair JSON da resposta
        if (preg_match('/\{[^}]+\}/', $response, $matches)) {
            $json = $matches[0];
            $data = json_decode($json, true);
            
            if (json_last_error() === JSON_ERROR_NONE && $this->validatePressures($data)) {
                return $data;
            }
        }
        
        Log::error('TirePressureCorrectionService: Resposta inválida do Claude', [
            'response' => $response
        ]);
        
        return null;
    }
    
    /**
     * Validar pressões retornadas
     */
    protected function validatePressures(array $pressures): bool
    {
        $required = ['empty_front', 'empty_rear', 'loaded_front', 'loaded_rear'];
        
        foreach ($required as $field) {
            if (!isset($pressures[$field]) || !is_numeric($pressures[$field])) {
                return false;
            }
            
            // Validar faixa razoável (10-100 PSI)
            if ($pressures[$field] < 10 || $pressures[$field] > 100) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Aplicar correções no artigo
     */
    protected function applyCorrections(Article $article, array $pressures): array
    {
        $fieldsUpdated = [];
        $content = $article->content;
        
        // 1. Atualizar campos diretos do artigo
        $article->pressure_empty_front = (string)$pressures['empty_front'];
        $article->pressure_empty_rear = (string)$pressures['empty_rear'];
        $article->pressure_light_front = (string)$pressures['loaded_front'];
        $article->pressure_light_rear = (string)$pressures['loaded_rear'];
        $fieldsUpdated[] = 'pressure_fields';
        
        // 2. Atualizar pressoes_recomendadas no procedimento_verificacao
        if (isset($content['procedimento_verificacao']['verificacao_pressao']['pressoes_recomendadas'])) {
            $content['procedimento_verificacao']['verificacao_pressao']['pressoes_recomendadas']['vazio'] = 
                "{$pressures['empty_front']} PSI (dianteiro) / {$pressures['empty_rear']} PSI (traseiro)";
            
            $content['procedimento_verificacao']['verificacao_pressao']['pressoes_recomendadas']['com_carga'] = 
                "{$pressures['loaded_front']} PSI (dianteiro) / {$pressures['loaded_rear']} PSI (traseiro)";
            
            $fieldsUpdated[] = 'procedimento_verificacao.pressoes_recomendadas';
        }
        
        // 3. Atualizar vehicle_data
        if (isset($content['vehicle_data'])) {
            $content['vehicle_data']['pressures'] = [
                'empty_front' => $pressures['empty_front'],
                'empty_rear' => $pressures['empty_rear'],
                'loaded_front' => $pressures['loaded_front'],
                'loaded_rear' => $pressures['loaded_rear']
            ];
            
            $content['vehicle_data']['pressure_display'] = 
                "{$pressures['empty_front']}/{$pressures['empty_rear']} PSI";
            
            $content['vehicle_data']['pressure_loaded_display'] = 
                "{$pressures['loaded_front']}/{$pressures['loaded_rear']} PSI";
            
            $fieldsUpdated[] = 'vehicle_data.pressures';
        }
        
        // 4. Aplicar substituições via regex em campos de texto
        $oldPressurePattern = $this->getOldPressurePattern($article);
        $newPressureText = "{$pressures['empty_front']}/{$pressures['empty_rear']} PSI";
        
        // Campos para substituição via regex
        $textFields = [
            'fatores_durabilidade.calibragem_inadequada.pressao_recomendada',
            'fatores_durabilidade.calibragem_inadequada.descricao',
            'consideracoes_finais'
        ];
        
        foreach ($textFields as $field) {
            $value = data_get($content, $field);
            if ($value && $oldPressurePattern) {
                $newValue = preg_replace($oldPressurePattern, $newPressureText, $value);
                if ($newValue !== $value) {
                    data_set($content, $field, $newValue);
                    $fieldsUpdated[] = $field;
                }
            }
        }
        
        // 5. Atualizar perguntas frequentes
        if (isset($content['perguntas_frequentes']) && is_array($content['perguntas_frequentes'])) {
            foreach ($content['perguntas_frequentes'] as $index => $faq) {
                if (isset($faq['resposta']) && $oldPressurePattern) {
                    $newResposta = preg_replace($oldPressurePattern, $newPressureText, $faq['resposta']);
                    if ($newResposta !== $faq['resposta']) {
                        $content['perguntas_frequentes'][$index]['resposta'] = $newResposta;
                        $fieldsUpdated[] = "perguntas_frequentes.{$index}.resposta";
                    }
                }
            }
        }
        
        // 6. Atualizar SEO meta description
        $seoData = $article->seo_data;
        if (isset($seoData['meta_description']) && $oldPressurePattern) {
            $newMetaDescription = preg_replace($oldPressurePattern, $newPressureText, $seoData['meta_description']);
            if ($newMetaDescription !== $seoData['meta_description']) {
                $seoData['meta_description'] = $newMetaDescription;
                $article->seo_data = $seoData;
                $fieldsUpdated[] = 'seo_data.meta_description';
            }
        }
        
        // Salvar alterações
        $article->content = $content;
        $article->save();
        
        return array_unique($fieldsUpdated);
    }
    
    /**
     * Obter padrão regex para pressões antigas
     */
    protected function getOldPressurePattern(Article $article): ?string
    {
        // Tentar obter pressões antigas dos campos diretos
        $oldFront = $article->pressure_empty_front;
        $oldRear = $article->pressure_empty_rear;
        
        if ($oldFront && $oldRear) {
            // Criar padrão que captura variações como "29/36 PSI", "29 / 36 PSI", etc.
            return '/\b' . preg_quote($oldFront) . '\s*\/\s*' . preg_quote($oldRear) . '\s*PSI\b/i';
        }
        
        // Padrão genérico para qualquer pressão no formato XX/YY PSI
        return '/\b\d{1,3}\s*\/\s*\d{1,3}\s*PSI\b/i';
    }
}