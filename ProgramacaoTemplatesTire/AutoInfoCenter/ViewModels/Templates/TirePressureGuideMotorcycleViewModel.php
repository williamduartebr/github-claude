<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Illuminate\Support\Str;
use Src\AutoInfoCenter\ViewModels\Templates\TemplateViewModel;

class TirePressureGuideMotorcycleViewModel extends TemplateViewModel
{
    /**
     * Nome do template a ser utilizado
     */
    protected string $templateName = 'tire_pressure_guide_motorcycle';

    /**
     * Processa dados específicos do template de guia de calibragem para motocicletas
     */
    protected function processTemplateSpecificData(): void
    {
        $content = $this->article->content;

        // Dados básicos processados
        $this->processedData['introduction'] = $content['introducao'] ?? '';
        $this->processedData['tire_specifications'] = $this->processTireSpecifications($content['especificacoes_oficiais'] ?? []);
        $this->processedData['pressure_table'] = $this->processPressureTable($content['tabela_pressoes'] ?? []);
        $this->processedData['calibration_procedure'] = $this->processCalibrationProcedure($content['procedimento_calibragem'] ?? []);
        $this->processedData['usage_recommendations'] = $this->processUsageRecommendations($content['recomendacoes_uso'] ?? []);
        $this->processedData['impact_comparison'] = $this->processImpactComparison($content['comparativo_impacto'] ?? []);
        $this->processedData['alternative_tires'] = $this->processAlternativeTires($content['pneus_alternativos'] ?? []);
        $this->processedData['required_equipment'] = $this->processRequiredEquipment($content['equipamentos_necessarios'] ?? []);
        $this->processedData['special_care'] = $this->processSpecialCare($content['cuidados_especiais'] ?? []);
        $this->processedData['problem_signs'] = $this->processProblemSigns($content['sinais_problemas'] ?? []);
        $this->processedData['faq'] = $content['perguntas_frequentes'] ?? [];
        $this->processedData['final_considerations'] = $content['consideracoes_finais'] ?? '';
        
        // Dados auxiliares
        $this->processedData['vehicle_info'] = $this->processVehicleInfo();
        $this->processedData['structured_data'] = $this->buildStructuredData();
        $this->processedData['seo_data'] = $this->processSeoData();
        $this->processedData['breadcrumbs'] = $this->getBreadcrumbs();
        $this->processedData['canonical_url'] = $this->getCanonicalUrl();
        $this->processedData['related_topics'] = $this->getRelatedTopics();
    }

    /**
     * Processa especificações oficiais dos pneus
     */
    private function processTireSpecifications(array $specs): array
    {
        if (empty($specs)) {
            return [];
        }

        $processed = [];

        // Pneu Dianteiro
        if (!empty($specs['pneu_dianteiro'])) {
            $processed['front_tire'] = [
                'size' => $specs['pneu_dianteiro']['medida_original'] ?? '',
                'type' => $specs['pneu_dianteiro']['tipo'] ?? '',
                'brand' => $specs['pneu_dianteiro']['marca_original'] ?? '',
                'load_index' => $specs['pneu_dianteiro']['indice_carga'] ?? '',
                'speed_rating' => $specs['pneu_dianteiro']['indice_velocidade'] ?? '',
                'recommended_pressure' => $specs['pneu_dianteiro']['pressao_recomendada'] ?? '',
                'max_pressure' => $specs['pneu_dianteiro']['pressao_maxima'] ?? ''
            ];
        }

        // Pneu Traseiro
        if (!empty($specs['pneu_traseiro'])) {
            $processed['rear_tire'] = [
                'size' => $specs['pneu_traseiro']['medida_original'] ?? '',
                'type' => $specs['pneu_traseiro']['tipo'] ?? '',
                'brand' => $specs['pneu_traseiro']['marca_original'] ?? '',
                'load_index' => $specs['pneu_traseiro']['indice_carga'] ?? '',
                'speed_rating' => $specs['pneu_traseiro']['indice_velocidade'] ?? '',
                'recommended_pressure' => $specs['pneu_traseiro']['pressao_recomendada'] ?? '',
                'max_pressure' => $specs['pneu_traseiro']['pressao_maxima'] ?? ''
            ];
        }

        return $processed;
    }

    /**
     * Processa tabela de pressões por condição de uso
     */
    private function processPressureTable(array $table): array
    {
        if (empty($table['condicoes_uso']) || !is_array($table['condicoes_uso'])) {
            return [];
        }

        $processed = [];

        foreach ($table['condicoes_uso'] as $condition) {
            if (!empty($condition['situacao'])) {
                $processed[] = [
                    'condition' => $condition['situacao'],
                    'occupants' => $condition['ocupantes'] ?? '',
                    'luggage' => $condition['bagagem'] ?? '',
                    'front_pressure' => $condition['pressao_dianteira'] ?? '',
                    'rear_pressure' => $condition['pressao_traseira'] ?? '',
                    'observation' => $condition['observacao'] ?? '',
                    'css_class' => $this->getConditionCssClass($condition['situacao'])
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa procedimento de calibragem passo a passo
     */
    private function processCalibrationProcedure(array $procedure): array
    {
        if (empty($procedure['passos']) || !is_array($procedure['passos'])) {
            return [];
        }

        $processed = [];

        foreach ($procedure['passos'] as $step) {
            if (!empty($step['titulo'])) {
                $processed[] = [
                    'number' => $step['numero'] ?? 1,
                    'title' => $step['titulo'],
                    'description' => $step['descricao'] ?? '',
                    'tips' => $step['dicas'] ?? [],
                    'icon_class' => $this->getStepIconClass($step['numero'] ?? 1)
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa recomendações de uso específicas
     */
    private function processUsageRecommendations(array $recommendations): array
    {
        if (empty($recommendations) || !is_array($recommendations)) {
            return [];
        }

        $processed = [];

        foreach ($recommendations as $rec) {
            if (!empty($rec['categoria'])) {
                $processed[] = [
                    'category' => $rec['categoria'],
                    'recommended_pressure' => $rec['pressao_recomendada'] ?? '',
                    'description' => $rec['descricao'] ?? '',
                    'technical_tip' => $rec['dica_tecnica'] ?? '',
                    'icon_class' => $this->getUsageIconClass($rec['categoria'])
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa comparativo de impacto por calibragem
     */
    private function processImpactComparison(array $comparison): array
    {
        if (empty($comparison)) {
            return [];
        }

        return [
            'under_inflated' => [
                'wear' => $comparison['subcalibrado']['desgaste'] ?? 0,
                'consumption' => $comparison['subcalibrado']['consumo'] ?? 0,
                'stability_asphalt' => $comparison['subcalibrado']['estabilidade_asfalto'] ?? 0,
                'comfort' => $comparison['subcalibrado']['conforto'] ?? 0
            ],
            'ideal' => [
                'wear' => $comparison['calibragem_ideal']['desgaste'] ?? 25,
                'consumption' => $comparison['calibragem_ideal']['consumo'] ?? 25,
                'stability_asphalt' => $comparison['calibragem_ideal']['estabilidade_asfalto'] ?? 90,
                'comfort' => $comparison['calibragem_ideal']['conforto'] ?? 80
            ],
            'over_inflated' => [
                'wear' => $comparison['sobrecalibrado']['desgaste'] ?? 0,
                'consumption' => $comparison['sobrecalibrado']['consumo'] ?? 0,
                'stability_asphalt' => $comparison['sobrecalibrado']['estabilidade_asfalto'] ?? 60,
                'comfort' => $comparison['sobrecalibrado']['conforto'] ?? 25
            ]
        ];
    }

    /**
     * Processa pneus alternativos
     */
    private function processAlternativeTires(array $alternatives): array
    {
        if (empty($alternatives) || !is_array($alternatives)) {
            return [];
        }

        $processed = [];

        foreach ($alternatives as $alt) {
            if (!empty($alt['categoria'])) {
                $processed[] = [
                    'category' => $alt['categoria'],
                    'front_pressure' => $alt['pressao_dianteiro'] ?? '',
                    'rear_pressure' => $alt['pressao_traseiro'] ?? '',
                    'description' => $alt['descricao'] ?? '',
                    'tags' => $alt['tags'] ?? [],
                    'icon_class' => $this->getAlternativeTireIconClass($alt['categoria'])
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa equipamentos necessários
     */
    private function processRequiredEquipment(array $equipment): array
    {
        if (empty($equipment) || !is_array($equipment)) {
            return [];
        }

        $processed = [];

        foreach ($equipment as $item) {
            if (!empty($item['item'])) {
                $processed[] = [
                    'item' => $item['item'],
                    'importance' => $item['importancia'] ?? '',
                    'characteristics' => $item['caracteristicas'] ?? '',
                    'average_price' => $item['preco_medio'] ?? '',
                    'recommendation' => $item['recomendacao'] ?? '',
                    'importance_class' => $this->getImportanceClass($item['importancia'] ?? '')
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa cuidados especiais
     */
    private function processSpecialCare(array $care): array
    {
        if (empty($care) || !is_array($care)) {
            return [];
        }

        $processed = [];

        foreach ($care as $careGroup) {
            if (!empty($careGroup['categoria']) && !empty($careGroup['cuidados'])) {
                $processed[] = [
                    'category' => $careGroup['categoria'],
                    'care_items' => $careGroup['cuidados'],
                    'icon_class' => $this->getCareIconClass($careGroup['categoria'])
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa sinais de problemas
     */
    private function processProblemSigns(array $signs): array
    {
        $processed = [];

        if (!empty($signs['subcalibrado'])) {
            $processed['under_inflated'] = [
                'title' => 'Subcalibrado (Pressão Baixa)',
                'signs' => $signs['subcalibrado'],
                'severity_class' => 'border-red-500',
                'icon_class' => 'alert-triangle'
            ];
        }

        if (!empty($signs['sobrecalibrado'])) {
            $processed['over_inflated'] = [
                'title' => 'Sobrecalibrado (Pressão Alta)',
                'signs' => $signs['sobrecalibrado'],
                'severity_class' => 'border-red-500',
                'icon_class' => 'alert-triangle'
            ];
        }

        return $processed;
    }

    /**
     * Processa informações do veículo
     */
    private function processVehicleInfo(): array
    {
        $vehicleInfo = $this->article->extracted_entities ?? [];

        return [
            'full_name' => $this->getVehicleFullName(),
            'make' => $vehicleInfo['marca'] ?? '',
            'model' => $vehicleInfo['modelo'] ?? '',
            'year' => $vehicleInfo['ano'] ?? '',
            'category' => $vehicleInfo['categoria'] ?? '',
            'engine' => $vehicleInfo['motorizacao'] ?? '',
            'version' => $vehicleInfo['versao'] ?? '',
            'fuel' => $vehicleInfo['combustivel'] ?? '',
            'image_url' => $this->getVehicleImageUrl(),
            'is_motorcycle' => true,
            'is_electric' => $this->isElectricVehicle(),
            'is_premium' => $this->isPremiumVehicle()
        ];
    }

    /**
     * Obtém nome completo do veículo
     */
    private function getVehicleFullName(): string
    {
        $vehicleInfo = $this->article->extracted_entities ?? [];

        $make = $vehicleInfo['marca'] ?? '';
        $model = $vehicleInfo['modelo'] ?? '';
        $year = $vehicleInfo['ano'] ?? '';
        $version = $vehicleInfo['versao'] ?? '';

        $fullName = trim("{$make} {$model}");
        if ($version && $version !== $model) {
            $fullName .= " {$version}";
        }
        if ($year) {
            $fullName .= " {$year}";
        }

        return $fullName;
    }

    /**
     * Obtém tópicos relacionados para motocicletas
     */
    private function getRelatedTopics(): array
    {
        $vehicleInfo = $this->article->extracted_entities ?? [];
        $make = strtolower($vehicleInfo['marca'] ?? '');
        $model = strtolower($vehicleInfo['modelo'] ?? '');

        return [
            [
                'title' => "Pneus Recomendados para {$vehicleInfo['marca']} {$vehicleInfo['modelo']}",
                'description' => 'Descubra os melhores pneus para sua moto',
                'url' => "/info/pneus-recomendados/{$make}-{$model}-{$vehicleInfo['ano']}/"
            ],
            [
                'title' => "Ajustes de Suspensão para {$vehicleInfo['modelo']}",
                'description' => 'Otimize a suspensão da sua motocicleta',
                'url' => "/info/manutencao/suspensao-{$make}-{$model}/"
            ],
            [
                'title' => "Como Aumentar a Vida Útil dos Pneus",
                'description' => 'Dicas para maximizar a durabilidade',
                'url' => "/info/manutencao/pneus-vida-util/"
            ],
            [
                'title' => "Alinhamento e Balanceamento de Rodas",
                'description' => 'Manutenção essencial para motocicletas',
                'url' => "/info/manutencao/rodas-alinhamento/"
            ]
        ];
    }

    /**
     * Verifica se é veículo elétrico
     */
    private function isElectricVehicle(): bool
    {
        $fuel = $this->article->extracted_entities['combustivel'] ?? '';
        return in_array(strtolower($fuel), ['elétrico', 'electric', 'eletrico']);
    }

    /**
     * Verifica se é veículo premium
     */
    private function isPremiumVehicle(): bool
    {
        $model = strtolower($this->article->extracted_entities['modelo'] ?? '');
        $category = strtolower($this->article->extracted_entities['categoria'] ?? '');
        
        return str_contains($model, 'premium') || 
               str_contains($category, 'premium') ||
               str_contains($model, 'touring') ||
               str_contains($category, 'sport');
    }

    /**
     * Obtém URL da imagem do veículo
     */
    private function getVehicleImageUrl(): string
    {
        $vehicleInfo = $this->article->extracted_entities ?? [];
        $makeSlug = strtolower($vehicleInfo['marca'] ?? '');
        $modelSlug = strtolower(str_replace(' ', '-', $vehicleInfo['modelo'] ?? ''));
        $year = $vehicleInfo['ano'] ?? '';

        return "https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/motorcycles/{$makeSlug}-{$modelSlug}-{$year}.jpg";
    }

    /**
     * Obtém classe CSS para condição da tabela de pressões
     */
    private function getConditionCssClass(string $condition): string
    {
        $condition = strtolower($condition);

        if (str_contains($condition, 'solo') || str_contains($condition, 'normal')) {
            return 'bg-white';
        }

        if (str_contains($condition, 'passageiro') || str_contains($condition, 'garupa')) {
            return 'bg-gray-50';
        }

        if (str_contains($condition, 'esportiva') || str_contains($condition, 'sport')) {
            return 'bg-white';
        }

        if (str_contains($condition, 'carga') || str_contains($condition, 'bagagem')) {
            return 'bg-gray-50';
        }

        return 'bg-white';
    }

    /**
     * Obtém classe de ícone para categoria de uso
     */
    private function getUsageIconClass(string $category): string
    {
        $category = strtolower($category);

        if (str_contains($category, 'urbano')) {
            return 'building';
        }

        if (str_contains($category, 'estrada') || str_contains($category, 'rodovia')) {
            return 'info';
        }

        if (str_contains($category, 'esportiv')) {
            return 'zap';
        }

        if (str_contains($category, 'carga') || str_contains($category, 'bagagem')) {
            return 'luggage';
        }

        return 'motorcycle';
    }

    /**
     * Obtém classe de ícone para passos do procedimento
     */
    private function getStepIconClass(int $stepNumber): string
    {
        $icons = [
            1 => 'settings',
            2 => 'search',
            3 => 'target',
            4 => 'target',
            5 => 'check-circle'
        ];

        return $icons[$stepNumber] ?? 'circle';
    }

    /**
     * Obtém classe de ícone para pneus alternativos
     */
    private function getAlternativeTireIconClass(string $category): string
    {
        $category = strtolower($category);

        if (str_contains($category, 'esportiv')) {
            return 'zap';
        }

        if (str_contains($category, 'touring') || str_contains($category, 'viagem')) {
            return 'navigation';
        }

        if (str_contains($category, 'urbano')) {
            return 'building';
        }

        return 'wheel';
    }

    /**
     * Obtém classe de importância para equipamentos
     */
    private function getImportanceClass(string $importance): string
    {
        $importanceMap = [
            'essencial' => 'text-red-600 font-bold',
            'muito útil' => 'text-orange-600 font-medium',
            'recomendado' => 'text-green-600',
            'opcional' => 'text-gray-600'
        ];

        return $importanceMap[strtolower($importance)] ?? 'text-gray-600';
    }

    /**
     * Obtém classe de ícone para cuidados especiais
     */
    private function getCareIconClass(string $category): string
    {
        $category = strtolower($category);

        if (str_contains($category, 'diferença') || str_contains($category, 'pneu')) {
            return 'info';
        }

        if (str_contains($category, 'especiais') || str_contains($category, 'condições')) {
            return 'alert-circle';
        }

        if (str_contains($category, 'sinais') || str_contains($category, 'atenção')) {
            return 'eye';
        }

        return 'help-circle';
    }

    /**
     * Processa dados SEO específicos para motocicletas
     */
    private function processSeoData(): array
    {
        $vehicleFullName = $this->getVehicleFullName();
        $vehicleInfo = $this->article->extracted_entities ?? [];
        $seoData = $this->article->seo_data ?? [];

        return [
            'title' => $seoData['page_title'] ?? "Calibragem de Pneus {$vehicleFullName} - Guia Completo | Mercado Veículos",
            'meta_description' => $seoData['meta_description'] ?? "Guia completo sobre calibragem de pneus para {$vehicleFullName}. Pressões ideais, procedimento passo a passo e cuidados especiais para motocicletas.",
            'keywords' => $seoData['secondary_keywords'] ?? [],
            'focus_keyword' => $seoData['primary_keyword'] ?? "calibragem pneu {$vehicleInfo['marca']} {$vehicleInfo['modelo']} {$vehicleInfo['ano']}",
            'canonical_url' => $this->getCanonicalUrl(),
            'h1' => $seoData['h1'] ?? "Calibragem do Pneu da {$vehicleFullName}",
            'h2_tags' => $seoData['h2_tags'] ?? [],
            'og_title' => "Calibragem de Pneus {$vehicleFullName} - Guia Completo",
            'og_description' => "Guia completo sobre calibragem de pneus para {$vehicleFullName}. Pressões ideais, procedimento e cuidados especiais.",
            'og_image' => "https://mercadoveiculos.com/images/{$vehicleInfo['marca']}-{$vehicleInfo['modelo']}-{$vehicleInfo['ano']}.jpg",
            'og_type' => 'article',
            'twitter_card' => 'summary_large_image'
        ];
    }

    /**
     * Constrói dados estruturados Schema.org
     */
    private function buildStructuredData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'];
        $vehicleFullName = $vehicleInfo['full_name'];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'TechArticle',
            'name' => "Calibragem de Pneus da {$vehicleFullName}",
            'description' => "Guia completo sobre calibragem de pneus da {$vehicleFullName}, incluindo pressões recomendadas, procedimento passo a passo e cuidados especiais para motocicletas.",
            'vehicleEngine' => $vehicleFullName,
            'category' => 'Manutenção de Motocicletas',
            'image' => [
                '@type' => 'ImageObject',
                'url' => $vehicleInfo['image_url'] ?? $this->getDefaultMotorcycleImage(),
                'width' => 1200,
                'height' => 630
            ],
            'author' => [
                '@type' => 'Organization',
                'name' => 'Mercado Veículos',
                'url' => 'https://mercadoveiculos.com.br'
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Mercado Veículos',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => 'https://mercadoveiculos.s3.amazonaws.com/statics/logos/logo-mercadoveiculos.png'
                ]
            ],
            'datePublished' => $this->article->created_at?->toISOString(),
            'dateModified' => $this->article->updated_at?->toISOString()
        ];
    }

    /**
     * Obtém URL canônica do artigo
     */
    private function getCanonicalUrl(): string
    {
        return $this->article->canonical_url ?? "https://mercadoveiculos.com/info/calibragem/{$this->article->slug}.html";
    }

    /**
     * Obtém imagem padrão para motocicletas
     */
    private function getDefaultMotorcycleImage(): string
    {
        return 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/calibragem-moto.jpg';
    }

    /**
     * Processa breadcrumbs para navegação
     */
    public function getBreadcrumbs(): array
    {
        return [
            [
                'name' => 'Início',
                'url' => route('home'),
                'position' => 1
            ],
            [
                'name' => 'Informações',
                'url' => route('info.category.index'),
                'position' => 2
            ],
            [
                'name' => Str::title($this->article->category_name ?? 'Calibragem de Pneus'),
                'url' => route('info.category.show', $this->article->category_slug ?? 'calibragem-pneus'),
                'position' => 3
            ],
            [
                'name' => $this->article->title,
                'url' => route('info.article.show', $this->article->slug), // URL para evitar erro
                'position' => 4
            ],
        ];
    }

    /**
     * Verifica se propriedade existe
     */
    public function __isset(string $property): bool
    {
        return isset($this->processedData[$property]);
    }

    /**
     * Obter todos os dados processados
     */
    public function toArray(): array
    {
        return $this->processedData;
    }
}