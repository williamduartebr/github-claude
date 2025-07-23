<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Services;


use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\ClaudeHaikuService;

/**
 * Service SIMPLES para correção do vehicle_data
 * 
 * RESPONSABILIDADE: Corrigir dados de pressão usando Claude 3 Haiku
 * INPUT: vehicle_data atual (com dados incorretos de script)
 * OUTPUT: vehicle_data corrigido com pressões reais
 */
class VehicleDataCorrectionService
{
    protected ClaudeHaikuService $claudeService;

    public function __construct(ClaudeHaikuService $claudeService)
    {
        $this->claudeService = $claudeService;
    }

    /**
     * Corrigir vehicle_data usando Claude
     * 
     * @param array $currentVehicleData Dados atuais do veículo
     * @return array Dados corrigidos
     */
    public function correctVehicleData(array $currentVehicleData): array
    {
        Log::info('VehicleDataCorrectionService: Iniciando correção', [
            'vehicle' => "{$currentVehicleData['make']} {$currentVehicleData['model']} {$currentVehicleData['year']}"
        ]);

        try {
            // 1. Construir prompt para Claude
            $prompt = $this->buildCorrectionPrompt($currentVehicleData);

            // 2. Chamar Claude 3 Haiku
            $response = $this->claudeService->generateContent($prompt, [
                'max_tokens' => 1500,
                'temperature' => 0.1,  // Máxima consistência
                'timeout' => 30
            ]);

            // 3. Parsear resposta JSON
            $correctedData = $this->parseClaudeResponse($response, $currentVehicleData);

            // 4. Validar dados corrigidos
            $this->validateCorrectedData($correctedData);

            Log::info('VehicleDataCorrectionService: Correção concluída', [
                'vehicle' => $correctedData['vehicle_full_name'],
                'segment' => $correctedData['vehicle_segment']
            ]);

            return $correctedData;

        } catch (\Exception $e) {
            Log::error('VehicleDataCorrectionService: Erro na correção', [
                'vehicle' => "{$currentVehicleData['make']} {$currentVehicleData['model']} {$currentVehicleData['year']}",
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Construir prompt para Claude corrigir os dados
     */
    protected function buildCorrectionPrompt(array $vehicleData): string
    {
        return "Você é um especialista em pressão de pneus automotivos. Preciso que corrija os dados técnicos do veículo abaixo.

VEÍCULO: {$vehicleData['make']} {$vehicleData['model']} {$vehicleData['year']}
PNEU: {$vehicleData['tire_size']}

DADOS ATUAIS (GERADOS POR SCRIPT - INCORRETOS):
```json
" . json_encode($vehicleData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "
```

CORREÇÕES NECESSÁRIAS:

1. **PRESSÕES REAIS** (busque dados técnicos oficiais):
   - pressure_empty_front: Pressão dianteira pneus vazios (PSI)
   - pressure_empty_rear: Pressão traseira pneus vazios (PSI)
   - pressure_light_front: Pressão dianteira carga leve (PSI)
   - pressure_light_rear: Pressão traseira carga leve (PSI)
   - pressure_max_front: Pressão dianteira carga máxima (PSI)
   - pressure_max_rear: Pressão traseira carga máxima (PSI)
   - pressure_spare: Pressão pneu estepe (PSI)

2. **SEGMENTO CORRETO**:
   - vehicle_segment: Classificar como A, B, C, D, E, F (Fiat 500e = B)

3. **CAMPOS DERIVADOS**:
   - pressure_display: 'Dianteiros: X PSI / Traseiros: Y PSI' (usar pressure_light)
   - empty_pressure_display: 'X/Y PSI' (usar pressure_empty)
   - loaded_pressure_display: 'X/Y PSI' (usar pressure_max)

4. **CARACTERÍSTICAS**:
   - is_premium: true/false (baseado no modelo)
   - has_tpms: true/false (veículos 2015+ geralmente têm)

RESPONDA APENAS COM JSON VÁLIDO (todos os campos originais + correções):

```json
{";
    }

    /**
     * Parsear resposta do Claude
     */
    protected function parseClaudeResponse(string $response, array $originalData): array
    {
        // Extrair JSON da resposta
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}') + 1;
        
        if ($jsonStart === false || $jsonEnd === false) {
            throw new \Exception('Resposta do Claude não contém JSON válido');
        }

        $jsonContent = substr($response, $jsonStart, $jsonEnd - $jsonStart);
        $correctedData = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Erro ao decodificar JSON do Claude: ' . json_last_error_msg());
        }

        // Manter campos originais que não foram corrigidos
        return array_merge($originalData, $correctedData);
    }

    /**
     * Validações básicas dos dados corrigidos
     */
    protected function validateCorrectedData(array $data): void
    {
        $requiredFields = [
            'pressure_empty_front', 'pressure_empty_rear',
            'pressure_light_front', 'pressure_light_rear',
            'pressure_max_front', 'pressure_max_rear',
            'pressure_spare', 'vehicle_segment'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \Exception("Campo obrigatório ausente: {$field}");
            }
        }

        // Validar faixas de pressão
        if ($data['pressure_light_front'] < 20 || $data['pressure_light_front'] > 50) {
            throw new \Exception("Pressão dianteira fora da faixa válida: {$data['pressure_light_front']}");
        }

        if ($data['pressure_light_rear'] < 20 || $data['pressure_light_rear'] > 50) {
            throw new \Exception("Pressão traseira fora da faixa válida: {$data['pressure_light_rear']}");
        }

        // Validar segmento
        $validSegments = ['A', 'B', 'C', 'D', 'E', 'F', 'SUV', 'PICKUP', 'VAN'];
        if (!in_array($data['vehicle_segment'], $validSegments)) {
            throw new \Exception("Segmento inválido: {$data['vehicle_segment']}");
        }
    }
}