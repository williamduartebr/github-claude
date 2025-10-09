<?php

namespace Src\ContentGeneration\ReviewSchedule\Domain\Services;

use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\ReviewSchedule\Domain\Entities\ReviewScheduleArticle;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\ContentTemplates\CarMaintenanceTemplate;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\ContentTemplates\MotorcycleMaintenanceTemplate;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\ContentTemplates\HybridVehicleMaintenanceTemplate;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\ContentTemplates\ElectricVehicleMaintenanceTemplate;

class ArticleContentGeneratorService
{
    private CarMaintenanceTemplate $carTemplate;
    private MotorcycleMaintenanceTemplate $motorcycleTemplate;
    private ElectricVehicleMaintenanceTemplate $electricTemplate;
    private HybridVehicleMaintenanceTemplate $hybridTemplate;
    private VehicleTypeDetectorService $typeDetector;

    public function __construct(
        CarMaintenanceTemplate $carTemplate,
        MotorcycleMaintenanceTemplate $motorcycleTemplate,
        ElectricVehicleMaintenanceTemplate $electricTemplate,
        HybridVehicleMaintenanceTemplate $hybridTemplate,
        VehicleTypeDetectorService $typeDetector
    ) {
        $this->carTemplate = $carTemplate;
        $this->motorcycleTemplate = $motorcycleTemplate;
        $this->electricTemplate = $electricTemplate;
        $this->hybridTemplate = $hybridTemplate;
        $this->typeDetector = $typeDetector;
    }

    public function generateArticle(array $vehicleData): ReviewScheduleArticle
    {
        try {
            // Validar dados de entrada
            if (empty($vehicleData['make']) || empty($vehicleData['model'])) {
                throw new \Exception('Dados do veículo incompletos: make e model são obrigatórios');
            }

            // CORREÇÃO: Detectar tipo de veículo corretamente
            $vehicleType = $this->typeDetector->detectVehicleType($vehicleData);
            $vehicleSubcategory = $this->typeDetector->detectVehicleSubcategory(
                $vehicleData['category'] ?? '',
                $vehicleType
            );

            // NOVO: Enriquecer dados do veículo com o tipo detectado
            $enrichedVehicleData = $this->enrichVehicleData($vehicleData, $vehicleType, $vehicleSubcategory);

            Log::info("Gerando artigo com tipo detectado", [
                'make' => $vehicleData['make'],
                'model' => $vehicleData['model'],
                'category' => $vehicleData['category'] ?? '',
                'detected_type' => $vehicleType,
                'subcategory' => $vehicleSubcategory,
                'template_selected' => $this->getTemplateNameForType($vehicleType)
            ]);

            // Selecionar template baseado no tipo detectado
            $template = $this->selectTemplate($vehicleType);

            // Gerar conteúdo usando o template correto
            $generatedContent = $this->generateContentFromTemplate($template, $enrichedVehicleData, $vehicleType, $vehicleSubcategory);

            // Criar título inteligente baseado no tipo
            $title = $this->generateIntelligentTitle($enrichedVehicleData);

            // Criar entidade ReviewScheduleArticle
            $article = new ReviewScheduleArticle(
                $title,
                $enrichedVehicleData,
                $generatedContent
            );

            Log::info("Artigo gerado com sucesso", [
                'title' => $title,
                'vehicle_type' => $vehicleType,
                'template_used' => $this->getTemplateNameForType($vehicleType),
                'vehicle' => $vehicleData['make'] . ' ' . $vehicleData['model']
            ]);

            return $article;

        } catch (\Exception $e) {
            Log::error("Erro ao gerar artigo", [
                'vehicle' => ($vehicleData['make'] ?? '') . ' ' . ($vehicleData['model'] ?? ''),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * NOVO MÉTODO: Enriquecer dados do veículo com informações detectadas
     */
    private function enrichVehicleData(array $vehicleData, string $vehicleType, string $subcategory): array
    {
        $enrichedData = $vehicleData;
        
        // Garantir que o vehicle_type está correto
        $enrichedData['vehicle_type'] = $vehicleType;
        $enrichedData['subcategory'] = $subcategory;
        
        // Adicionar informações de detecção
        $enrichedData['detection_confidence'] = $this->calculateDetectionConfidence($vehicleData, $vehicleType);
        $enrichedData['detection_methods'] = $this->getDetectionMethods($vehicleData, $vehicleType);
        
        // Enriquecer com dados específicos do tipo
        $enrichedData = $this->addTypeSpecificData($enrichedData, $vehicleType);
        
        return $enrichedData;
    }

    /**
     * NOVO MÉTODO: Adicionar dados específicos por tipo de veículo
     */
    private function addTypeSpecificData(array $vehicleData, string $vehicleType): array
    {
        switch ($vehicleType) {
            case 'electric':
                $vehicleData['fuel_type'] = 'electric';
                $vehicleData['extracted_fuel_type'] = 'elétrico';
                $vehicleData['has_engine_oil'] = false;
                if (empty($vehicleData['recommended_oil'])) {
                    $vehicleData['recommended_oil'] = 'Não usa óleo motor';
                }
                break;
                
            case 'hybrid':
                $vehicleData['fuel_type'] = 'hybrid';
                $vehicleData['extracted_fuel_type'] = 'híbrido';
                $vehicleData['has_engine_oil'] = true;
                if (empty($vehicleData['recommended_oil'])) {
                    $vehicleData['recommended_oil'] = '0W20 Sintético';
                }
                break;
                
            case 'motorcycle':
                $vehicleData['fuel_type'] = 'gasoline';
                $vehicleData['extracted_fuel_type'] = 'gasolina';
                $vehicleData['has_engine_oil'] = true;
                if (empty($vehicleData['recommended_oil'])) {
                    $vehicleData['recommended_oil'] = '10W40 Semissintético';
                }
                break;
                
            case 'car':
            default:
                if (empty($vehicleData['fuel_type'])) {
                    $vehicleData['fuel_type'] = 'flex';
                    $vehicleData['extracted_fuel_type'] = 'flex';
                }
                $vehicleData['has_engine_oil'] = true;
                if (empty($vehicleData['recommended_oil'])) {
                    $vehicleData['recommended_oil'] = '5W30 Sintético';
                }
                break;
        }
        
        return $vehicleData;
    }

    /**
     * MÉTODO CORRIGIDO: Selecionar template baseado no tipo de veículo
     */
    private function selectTemplate(string $vehicleType)
    {
        return match($vehicleType) {
            'motorcycle' => $this->motorcycleTemplate,
            'electric' => $this->electricTemplate,
            'hybrid' => $this->hybridTemplate,
            'car' => $this->carTemplate,
            default => $this->carTemplate
        };
    }

    /**
     * NOVO MÉTODO: Gerar título inteligente baseado no tipo de veículo
     */
    private function generateIntelligentTitle(array $vehicleData): string
    {
        $make = $vehicleData['make'] ?? '';
        $model = $vehicleData['model'] ?? '';
        $year = $vehicleData['year'] ?? '';
        $vehicleType = $vehicleData['vehicle_type'] ?? 'car';

        $titleTemplates = [
            'car' => "Cronograma de Revisões do {$make} {$model} {$year}",
            'motorcycle' => "Cronograma de Revisões da {$make} {$model} {$year}",
            'electric' => "Cronograma de Revisões do {$make} {$model} {$year}",
            'hybrid' => "Cronograma de Revisões do {$make} {$model} {$year}"
        ];

        return $titleTemplates[$vehicleType] ?? $titleTemplates['car'];
    }

    /**
     * NOVO MÉTODO: Calcular confiança da detecção
     */
    private function calculateDetectionConfidence(array $vehicleData, string $detectedType): string
    {
        $confidence = 0;
        
        // Categoria mapeada (+40 pontos)
        $category = strtolower($vehicleData['category'] ?? '');
        if (strpos($category, $detectedType) !== false || 
            ($detectedType === 'motorcycle' && strpos($category, 'motorcycle') !== false) ||
            ($detectedType === 'electric' && strpos($category, 'electric') !== false) ||
            ($detectedType === 'hybrid' && strpos($category, 'hybrid') !== false)) {
            $confidence += 40;
        }
        
        // Padrão de pneu correspondente (+30 pontos)
        $tireSize = $vehicleData['tire_size'] ?? '';
        if ($detectedType === 'motorcycle' && 
            (strpos($tireSize, 'dianteiro') !== false && strpos($tireSize, 'traseiro') !== false)) {
            $confidence += 30;
        } elseif ($detectedType === 'car' && 
                 preg_match('/\d{3}\/\d{2}\s*R\d{2}/', $tireSize)) {
            $confidence += 30;
        }
        
        // Óleo corresponde (+20 pontos)
        $recommendedOil = strtolower($vehicleData['recommended_oil'] ?? '');
        if ($detectedType === 'electric' && ($recommendedOil === 'na' || strpos($recommendedOil, 'não usa') !== false)) {
            $confidence += 20;
        } elseif ($detectedType === 'motorcycle' && 
                 (strpos($recommendedOil, '10w40') !== false || strpos($recommendedOil, '20w50') !== false)) {
            $confidence += 20;
        } elseif ($detectedType === 'hybrid' && strpos($recommendedOil, '0w20') !== false) {
            $confidence += 20;
        }
        
        // Marca corresponde (+10 pontos)
        $make = strtolower($vehicleData['make'] ?? '');
        $electricBrands = ['tesla', 'byd', 'nio', 'xpeng'];
        $motorcycleBrands = ['ducati', 'kawasaki', 'yamaha', 'suzuki', 'honda'];
        
        if ($detectedType === 'electric' && in_array($make, $electricBrands)) {
            $confidence += 10;
        } elseif ($detectedType === 'motorcycle' && in_array($make, $motorcycleBrands)) {
            $confidence += 10;
        }

        return match(true) {
            $confidence >= 80 => 'high',
            $confidence >= 60 => 'medium',
            $confidence >= 40 => 'low',
            default => 'very_low'
        };
    }

    /**
     * NOVO MÉTODO: Obter métodos de detecção utilizados
     */
    private function getDetectionMethods(array $vehicleData, string $detectedType): array
    {
        $methods = [];
        
        $category = strtolower($vehicleData['category'] ?? '');
        if (strpos($category, $detectedType) !== false ||
            ($detectedType === 'motorcycle' && strpos($category, 'motorcycle') !== false)) {
            $methods[] = 'category_mapping';
        }
        
        $tireSize = $vehicleData['tire_size'] ?? '';
        if ($detectedType === 'motorcycle' && 
            (strpos($tireSize, 'dianteiro') !== false && strpos($tireSize, 'traseiro') !== false)) {
            $methods[] = 'tire_pattern';
        }
        
        $recommendedOil = strtolower($vehicleData['recommended_oil'] ?? '');
        if ($detectedType === 'electric' && ($recommendedOil === 'na' || strpos($recommendedOil, 'não usa') !== false)) {
            $methods[] = 'oil_type_inference';
        }
        
        return empty($methods) ? ['default_classification'] : $methods;
    }

    /**
     * NOVO MÉTODO: Obter nome do template para logging
     */
    private function getTemplateNameForType(string $vehicleType): string
    {
        return match($vehicleType) {
            'motorcycle' => 'MotorcycleMaintenanceTemplate',
            'electric' => 'ElectricVehicleMaintenanceTemplate',
            'hybrid' => 'HybridVehicleMaintenanceTemplate',
            'car' => 'CarMaintenanceTemplate',
            default => 'CarMaintenanceTemplate'
        };
    }

    /**
     * MÉTODO PÚBLICO: Obter estatísticas de geração
     */
    public function getGenerationStats(): array
    {
        return [
            'available_templates' => [
                'car' => get_class($this->carTemplate),
                'motorcycle' => get_class($this->motorcycleTemplate),
                'electric' => get_class($this->electricTemplate),
                'hybrid' => get_class($this->hybridTemplate)
            ],
            'detector_service' => get_class($this->typeDetector)
        ];
    }

    /**
     * MÉTODO PÚBLICO: Validar se pode gerar artigo para o veículo
     */
    public function canGenerateArticle(array $vehicleData): bool
    {
        if (empty($vehicleData['make']) || empty($vehicleData['model'])) {
            return false;
        }

        try {
            $vehicleType = $this->typeDetector->detectVehicleType($vehicleData);
            return in_array($vehicleType, ['car', 'motorcycle', 'electric', 'hybrid']);
        } catch (\Exception $e) {
            Log::warning("Erro ao validar veículo para geração", [
                'vehicle' => $vehicleData,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * MÉTODO CORRIGIDO: Gerar conteúdo usando métodos existentes do template
     */
    private function generateContentFromTemplate($template, array $vehicleData, string $vehicleType, string $vehicleSubcategory): array
    {
        try {
            return [
                'introducao' => $this->safeTemplateCall($template, 'generateIntroduction', $vehicleData),
                'visao_geral_revisoes' => $this->safeTemplateCall($template, 'generateOverviewTable', $vehicleData),
                'cronograma_detalhado' => $this->safeTemplateCall($template, 'generateDetailedSchedule', $vehicleData),
                'manutencao_preventiva' => $this->safeTemplateCall($template, 'generatePreventiveMaintenance', $vehicleData),
                'pecas_atencao' => $this->safeTemplateCall($template, 'generateCriticalParts', $vehicleData),
                'especificacoes_tecnicas' => $this->safeTemplateCall($template, 'generateTechnicalSpecs', $vehicleData),
                'garantia_recomendacoes' => $this->safeTemplateCall($template, 'generateWarrantyInfo', $vehicleData),
                'perguntas_frequentes' => $this->safeTemplateCall($template, 'generateFAQs', $vehicleData),
                'consideracoes_finais' => $this->safeTemplateCall($template, 'generateConclusion', $vehicleData)
            ];
        } catch (\Exception $e) {
            // Log do erro específico da seção que falhou
            Log::error("Erro na geração de conteúdo: " . $e->getMessage(), [
                'vehicle_data' => $vehicleData,
                'vehicle_type' => $vehicleType,
                'template_class' => get_class($template),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw com contexto adicional
            throw new \Exception("Falha na geração de conteúdo para {$vehicleData['make']} {$vehicleData['model']} {$vehicleData['year']}: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * MÉTODO AUXILIAR: Chamada segura para métodos do template
     */
    private function safeTemplateCall($template, string $method, array $vehicleData)
    {
        try {
            if (!method_exists($template, $method)) {
                throw new \Exception("Método {$method} não existe no template " . get_class($template));
            }

            return $template->$method($vehicleData);
        } catch (\Exception $e) {
            // Log específico da seção que falhou
            Log::error("Erro no método {$method} do template " . get_class($template), [
                'error' => $e->getMessage(),
                'vehicle_data' => $vehicleData,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            // Para manter a compatibilidade, retornar fallback baseado no tipo de método
            return $this->getMethodFallback($method, $vehicleData);
        }
    }

    /**
     * MÉTODO AUXILIAR: Fallback para métodos que falharam
     */
    private function getMethodFallback(string $method, array $vehicleData)
    {
        $vehicleName = $this->getVehicleName($vehicleData);

        return match ($method) {
            'generateIntroduction' => "Manter o seu {$vehicleName} em dia com as revisões é fundamental para garantir sua durabilidade, segurança e bom funcionamento.",
            'generateOverviewTable' => $this->getDefaultOverviewTable($vehicleData),
            'generateDetailedSchedule' => $this->getDefaultDetailedSchedule($vehicleData),
            'generatePreventiveMaintenance' => $this->getDefaultPreventiveMaintenance($vehicleData),
            'generateCriticalParts' => $this->getDefaultCriticalParts($vehicleData),
            'generateTechnicalSpecs' => $this->getDefaultTechnicalSpecs($vehicleData),
            'generateWarrantyInfo' => $this->getDefaultWarrantyInfo($vehicleData),
            'generateFAQs' => $this->getDefaultFAQs($vehicleData),
            'generateConclusion' => "Seguir o cronograma de revisões do {$vehicleName} é a melhor forma de manter sua performance e valor."
        };
    }

    /**
     * MÉTODO AUXILIAR: Obter nome do veículo
     */
    private function getVehicleName(array $vehicleData): string
    {
        $make = $vehicleData['make'] ?? '';
        $model = $vehicleData['model'] ?? '';
        $year = $vehicleData['year'] ?? '';

        return trim("{$make} {$model} {$year}") ?: 'veículo';
    }

    /**
     * MÉTODOS DE FALLBACK para estruturas padrão
     */
    private function getDefaultOverviewTable(array $vehicleData): array
    {
        return [
            [
                'revisao' => '1ª Revisão',
                'intervalo' => '10.000 km ou 12 meses',
                'principais_servicos' => 'Primeira revisão, verificações básicas',
                'estimativa_custo' => 'R$ 200 - R$ 300'
            ],
            [
                'revisao' => '2ª Revisão', 
                'intervalo' => '20.000 km ou 24 meses',
                'principais_servicos' => 'Óleo, filtros, verificações gerais',
                'estimativa_custo' => 'R$ 300 - R$ 450'
            ]
        ];
    }

    private function getDefaultDetailedSchedule(array $vehicleData): array
    {
        return [
            [
                'numero_revisao' => 1,
                'intervalo' => '10.000 km ou 12 meses',
                'km' => '10.000',
                'servicos_principais' => ['Troca de óleo e filtro', 'Verificações básicas'],
                'verificacoes_complementares' => ['Sistema elétrico', 'Freios'],
                'estimativa_custo' => 'R$ 200 - R$ 300',
                'observacoes' => 'Primeira revisão preventiva'
            ]
        ];
    }

    private function getDefaultPreventiveMaintenance(array $vehicleData): array
    {
        return [
            'verificacoes_mensais' => [
                'Verificar nível de óleo',
                'Calibrar pneus',
                'Testar luzes'
            ],
            'verificacoes_trimestrais' => [
                'Fluido de freio',
                'Desgaste dos pneus',
                'Sistema elétrico'
            ],
            'verificacoes_anuais' => [
                'Revisão completa',
                'Troca de filtros',
                'Verificação de suspensão'
            ]
        ];
    }

    private function getDefaultCriticalParts(array $vehicleData): array
    {
        return [
            [
                'componente' => 'Sistema de Freios',
                'intervalo_recomendado' => 'Verificação a cada 10.000 km',
                'observacao' => 'Fundamental para a segurança'
            ],
            [
                'componente' => 'Óleo do Motor',
                'intervalo_recomendado' => 'Troca a cada 10.000 km',
                'observacao' => 'Protege o motor contra desgaste'
            ]
        ];
    }

    private function getDefaultTechnicalSpecs(array $vehicleData): array
    {
        return [
            'capacidade_oleo' => '4.0 litros',
            'tipo_oleo_recomendado' => '5W30 Sintético',
            'intervalo_troca_oleo' => '10.000 km ou 12 meses',
            'fluido_freio' => 'DOT 4',
            'pressao_pneus' => 'Consultar manual do proprietário'
        ];
    }

    private function getDefaultWarrantyInfo(array $vehicleData): array
    {
        return [
            'prazo_garantia' => '3 anos ou 100.000 km',
            'observacoes_importantes' => 'Seguir cronograma de revisões para manter garantia',
            'dicas_vida_util' => [
                'Fazer revisões em dia',
                'Usar peças originais',
                'Seguir recomendações do fabricante'
            ]
        ];
    }

    private function getDefaultFAQs(array $vehicleData): array
    {
        $vehicleName = $this->getVehicleName($vehicleData);
        
        return [
            [
                'pergunta' => "Com que frequência devo revisar o {$vehicleName}?",
                'resposta' => 'Recomenda-se seguir o cronograma do fabricante, geralmente a cada 10.000 km ou 12 meses.'
            ],
            [
                'pergunta' => 'Posso fazer manutenção em qualquer oficina?',
                'resposta' => 'Para manter a garantia, é recomendado usar concessionárias autorizadas ou oficinas especializadas.'
            ]
        ];
    }
    public function previewGeneration(array $vehicleData): array
    {
        $vehicleType = $this->typeDetector->detectVehicleType($vehicleData);
        $subcategory = $this->typeDetector->detectVehicleSubcategory(
            $vehicleData['category'] ?? '',
            $vehicleType
        );
        
        $enrichedData = $this->enrichVehicleData($vehicleData, $vehicleType, $subcategory);
        $title = $this->generateIntelligentTitle($enrichedData);
        
        return [
            'title' => $title,
            'vehicle_type' => $vehicleType,
            'subcategory' => $subcategory,
            'template' => $this->getTemplateNameForType($vehicleType),
            'detection_confidence' => $enrichedData['detection_confidence'],
            'enriched_data' => $enrichedData
        ];
    }
}