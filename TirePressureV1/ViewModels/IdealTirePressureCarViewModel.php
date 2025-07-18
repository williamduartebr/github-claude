<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Src\AutoInfoCenter\ViewModels\Templates\TemplateViewModel;
use Illuminate\Support\Str;

class IdealTirePressureCarViewModel extends TemplateViewModel
{
    /**
     * Nome do template a ser utilizado
     */
    protected string $templateName = 'ideal_tire_pressure_car';

    /**
     * Processa dados específicos do template de pressão ideal para carros
     */
    protected function processTemplateSpecificData(): void
    {
        $content = $this->article->content;

        // Introdução
        $this->processedData['introduction'] = $content['introducao'] ?? '';

        // Especificações dos pneus
        $this->processedData['tire_specifications'] = $this->processTireSpecifications($content['especificacoes_pneus'] ?? []);

        // Tabela principal de pressões
        $this->processedData['pressure_table'] = $this->processPressureTable($content['tabela_pressoes'] ?? []);

        // Tabela de conversão de unidades
        $this->processedData['unit_conversion'] = $this->processUnitConversion($content['conversao_unidades'] ?? []);

        // Localização da etiqueta
        $this->processedData['label_location'] = $this->processLabelLocation($content['localizacao_etiqueta'] ?? []);

        // Benefícios da calibragem correta
        $this->processedData['calibration_benefits'] = $this->processCalibrationBenefits($content['beneficios_calibragem'] ?? []);

        // Dicas de manutenção
        $this->processedData['maintenance_tips'] = $this->processMaintenanceTips($content['dicas_manutencao'] ?? []);

        // Alertas importantes
        $this->processedData['important_alerts'] = $this->processImportantAlerts($content['alertas_importantes'] ?? []);

        // Perguntas frequentes
        $this->processedData['faq'] = $content['perguntas_frequentes'] ?? [];

        // Considerações finais
        $this->processedData['final_considerations'] = $content['consideracoes_finais'] ?? '';

        // Informações do veículo formatadas
        $this->processedData['vehicle_info'] = $this->processVehicleInfo();

        // Dados estruturados para SEO
        $this->processedData['structured_data'] = $this->buildStructuredData();
        $this->processedData['seo_data'] = $this->processSeoData();
    }

    /**
     * Processa especificações dos pneus
     */
    private function processTireSpecifications(array $specs): array
    {
        if (empty($specs)) {
            return [];
        }

        return [
            'original_size' => $specs['medida_original'] ?? '',
            'optional_size' => $specs['medida_opcional'] ?? '',
            'load_index' => $specs['indice_carga'] ?? '',
            'speed_rating' => $specs['indice_velocidade'] ?? '',
            'construction_type' => $specs['tipo_construcao'] ?? '',
            'original_brands' => $specs['marca_original'] ?? '',
            'display_size' => $this->getDisplayTireSize($specs)
        ];
    }

    /**
     * Processa tabela de pressões
     */
    private function processPressureTable(array $table): array
    {
        $processed = [
            'versions' => [],
            'usage_conditions' => []
        ];

        // Processa versões do veículo
        if (!empty($table['versoes']) && is_array($table['versoes'])) {
            foreach ($table['versoes'] as $version) {
                if (!empty($version['nome_versao'])) {
                    $processed['versions'][] = [
                        'name' => $version['nome_versao'],
                        'engine' => $version['motor'] ?? '',
                        'tire_size' => $version['medida_pneu'] ?? '',
                        'front_normal' => $version['pressao_dianteira_normal'] ?? '',
                        'rear_normal' => $version['pressao_traseira_normal'] ?? '',
                        'front_loaded' => $version['pressao_dianteira_carregado'] ?? '',
                        'rear_loaded' => $version['pressao_traseira_carregado'] ?? '',
                        'observation' => $version['observacao'] ?? '',
                        'version_class' => $this->getVersionCssClass($version['nome_versao'])
                    ];
                }
            }
        }

        // Processa condições de uso
        if (!empty($table['condicoes_uso']) && is_array($table['condicoes_uso'])) {
            foreach ($table['condicoes_uso'] as $condition) {
                if (!empty($condition['situacao'])) {
                    $processed['usage_conditions'][] = [
                        'situation' => $condition['situacao'],
                        'occupants' => $condition['ocupantes'] ?? '',
                        'luggage' => $condition['bagagem'] ?? '',
                        'front_adjustment' => $condition['ajuste_dianteira'] ?? '',
                        'rear_adjustment' => $condition['ajuste_traseira'] ?? '',
                        'benefits' => $condition['beneficios'] ?? '',
                        'situation_class' => $this->getSituationCssClass($condition['situacao']),
                        'icon_class' => $this->getSituationIconClass($condition['situacao'])
                    ];
                }
            }
        }

        return $processed;
    }

    /**
     * Processa tabela de conversão de unidades
     */
    private function processUnitConversion(array $conversion): array
    {
        $processed = [
            'conversion_table' => [],
            'observation' => $conversion['observacao'] ?? ''
        ];

        if (!empty($conversion['tabela_conversao']) && is_array($conversion['tabela_conversao'])) {
            foreach ($conversion['tabela_conversao'] as $row) {
                $processed['conversion_table'][] = [
                    'psi' => $row['psi'] ?? '',
                    'kgf_cm2' => $row['kgf_cm2'] ?? '',
                    'bar' => $row['bar'] ?? '',
                    'is_common' => $this->isCommonPressure($row['psi'] ?? '')
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa localização da etiqueta
     */
    private function processLabelLocation(array $location): array
    {
        if (empty($location)) {
            return [];
        }

        return [
            'main_location' => $location['local_principal'] ?? '',
            'alternative_locations' => $location['locais_alternativos'] ?? [],
            'label_information' => $location['informacoes_etiqueta'] ?? [],
            'important_tip' => $location['dica_importante'] ?? '',
            'visual_guide' => $this->generateVisualGuide($location)
        ];
    }

    /**
     * Processa benefícios da calibragem
     */
    private function processCalibrationBenefits(array $benefits): array
    {
        if (empty($benefits)) {
            return [];
        }

        $processed = [];

        foreach ($benefits as $key => $benefit) {
            if (!empty($benefit['titulo'])) {
                $processed[] = [
                    'category' => $key,
                    'title' => $benefit['titulo'],
                    'description' => $benefit['descricao'] ?? '',
                    'financial_impact' => $benefit['impacto_financeiro'] ?? '',
                    'estimated_savings' => $benefit['economia_estimada'] ?? '',
                    'aspects' => $benefit['aspectos'] ?? [],
                    'characteristics' => $benefit['caracteristicas'] ?? [],
                    'icon_class' => $this->getBenefitIconClass($key),
                    'color_class' => $this->getBenefitColorClass($key)
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa dicas de manutenção
     */
    private function processMaintenanceTips(array $tips): array
    {
        if (empty($tips) || !is_array($tips)) {
            return [];
        }

        $processed = [];

        foreach ($tips as $tip) {
            if (!empty($tip['categoria']) && !empty($tip['itens'])) {
                $processed[] = [
                    'category' => $tip['categoria'],
                    'items' => $tip['itens'],
                    'frequency' => $tip['frequencia'] ?? '',
                    'priority' => $this->getTipPriority($tip['categoria']),
                    'icon_class' => $this->getTipIconClass($tip['categoria']),
                    'color_class' => $this->getTipColorClass($tip['categoria'])
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa alertas importantes
     */
    private function processImportantAlerts(array $alerts): array
    {
        if (empty($alerts) || !is_array($alerts)) {
            return [];
        }

        $processed = [];

        foreach ($alerts as $alert) {
            if (!empty($alert['titulo'])) {
                $processed[] = [
                    'type' => $alert['tipo'] ?? 'info',
                    'title' => $alert['titulo'],
                    'description' => $alert['descricao'] ?? '',
                    'consequence' => $alert['consequencia'] ?? '',
                    'severity_class' => $this->getAlertSeverityClass($alert['tipo'] ?? 'info'),
                    'icon_class' => $this->getAlertIconClass($alert['tipo'] ?? 'info'),
                    'border_class' => $this->getAlertBorderClass($alert['tipo'] ?? 'info')
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa informações do veículo
     */
    private function processVehicleInfo(): array
    {
        $vehicleInfo = $this->article->vehicle_info ?? [];
        
        return [
            'full_name' => $this->getVehicleFullName(),
            'make' => $vehicleInfo['make'] ?? '',
            'model' => $vehicleInfo['model'] ?? '',
            'year' => $vehicleInfo['year'] ?? '',
            'category' => $vehicleInfo['category'] ?? '',
            'image_url' => $this->getVehicleImageUrl(),
            'slug' => $vehicleInfo['make_model_slug'] ?? '',
            'is_premium' => $this->isPremiumVehicle(),
            'segment' => $this->getVehicleSegment()
        ];
    }

    /**
     * Obtém tamanho de pneu para exibição
     */
    private function getDisplayTireSize(array $specs): string
    {
        $original = $specs['medida_original'] ?? '';
        $optional = $specs['medida_opcional'] ?? '';
        
        if (!empty($optional)) {
            return "{$original} / {$optional}";
        }
        
        return $original;
    }

    /**
     * Obtém classe CSS para versão do veículo
     */
    private function getVersionCssClass(string $version): string
    {
        $version = strtolower($version);
        
        if (str_contains($version, 'gts') || str_contains($version, 'sport')) {
            return 'version-sport';
        }
        
        if (str_contains($version, 'tsi') || str_contains($version, 'turbo')) {
            return 'version-turbo';
        }
        
        return 'version-standard';
    }

    /**
     * Obtém classe CSS para situação de uso
     */
    private function getSituationCssClass(string $situation): string
    {
        $situation = strtolower($situation);
        
        if (str_contains($situation, 'urbano') || str_contains($situation, 'normal')) {
            return 'situation-urban';
        }
        
        if (str_contains($situation, 'família') || str_contains($situation, 'completa')) {
            return 'situation-family';
        }
        
        if (str_contains($situation, 'viagem') || str_contains($situation, 'carga')) {
            return 'situation-travel';
        }
        
        if (str_contains($situation, 'rodovia') || str_contains($situation, 'velocidade')) {
            return 'situation-highway';
        }
        
        return 'situation-default';
    }

    /**
     * Obtém classe de ícone para situação
     */
    private function getSituationIconClass(string $situation): string
    {
        $situation = strtolower($situation);
        
        if (str_contains($situation, 'urbano')) return 'home';
        if (str_contains($situation, 'família')) return 'users';
        if (str_contains($situation, 'viagem')) return 'map';
        if (str_contains($situation, 'rodovia')) return 'trending-up';
        
        return 'circle';
    }

    /**
     * Verifica se é pressão comum
     */
    private function isCommonPressure(string $psi): bool
    {
        $commonPressures = ['30', '32', '34'];
        return in_array($psi, $commonPressures);
    }

    /**
     * Gera guia visual para localização
     */
    private function generateVisualGuide(array $location): array
    {
        return [
            'main_step' => [
                'title' => 'Localização Principal',
                'description' => $location['local_principal'] ?? '',
                'icon' => 'map-pin'
            ],
            'verification_steps' => [
                'Abra a porta do motorista',
                'Olhe na parte inferior da soleira',
                'Procure por etiqueta branca com tabela'
            ]
        ];
    }

    /**
     * Obtém classe de ícone para benefício
     */
    private function getBenefitIconClass(string $benefit): string
    {
        $iconMap = [
            'economia' => 'dollar-sign',
            'seguranca' => 'shield',
            'durabilidade' => 'clock',
            'performance' => 'zap'
        ];
        
        return $iconMap[$benefit] ?? 'check-circle';
    }

    /**
     * Obtém classe de cor para benefício
     */
    private function getBenefitColorClass(string $benefit): string
    {
        $colorMap = [
            'economia' => 'green',
            'seguranca' => 'blue',
            'durabilidade' => 'purple',
            'performance' => 'orange'
        ];
        
        return $colorMap[$benefit] ?? 'gray';
    }

    /**
     * Obtém prioridade da dica
     */
    private function getTipPriority(string $category): string
    {
        $category = strtolower($category);
        
        if (str_contains($category, 'regular') || str_contains($category, 'verificação')) {
            return 'alta';
        }
        
        if (str_contains($category, 'ideais') || str_contains($category, 'condições')) {
            return 'média';
        }
        
        return 'normal';
    }

    /**
     * Obtém classe de ícone para dica
     */
    private function getTipIconClass(string $category): string
    {
        $category = strtolower($category);
        
        if (str_contains($category, 'verificação')) return 'search';
        if (str_contains($category, 'condições')) return 'sun';
        if (str_contains($category, 'cuidados')) return 'heart';
        
        return 'info';
    }

    /**
     * Obtém classe de cor para dica
     */
    private function getTipColorClass(string $category): string
    {
        $category = strtolower($category);
        
        if (str_contains($category, 'verificação')) return 'blue';
        if (str_contains($category, 'condições')) return 'yellow';
        if (str_contains($category, 'cuidados')) return 'green';
        
        return 'gray';
    }

    /**
     * Obtém classe de severidade para alerta
     */
    private function getAlertSeverityClass(string $type): string
    {
        $severityMap = [
            'crítico' => 'alert-critical',
            'importante' => 'alert-important',
            'atenção' => 'alert-warning',
            'info' => 'alert-info'
        ];
        
        return $severityMap[strtolower($type)] ?? 'alert-info';
    }

    /**
     * Obtém classe de ícone para alerta
     */
    private function getAlertIconClass(string $type): string
    {
        $iconMap = [
            'crítico' => 'alert-triangle',
            'importante' => 'alert-circle',
            'atenção' => 'info',
            'info' => 'help-circle'
        ];
        
        return $iconMap[strtolower($type)] ?? 'help-circle';
    }

    /**
     * Obtém classe de borda para alerta
     */
    private function getAlertBorderClass(string $type): string
    {
        $borderMap = [
            'crítico' => 'border-red-500',
            'importante' => 'border-orange-500',
            'atenção' => 'border-yellow-500',
            'info' => 'border-blue-500'
        ];
        
        return $borderMap[strtolower($type)] ?? 'border-blue-500';
    }

    /**
     * Obtém nome completo do veículo
     */
    private function getVehicleFullName(): string
    {
        $vehicleInfo = $this->article->vehicle_info ?? [];
        
        $make = $vehicleInfo['make'] ?? '';
        $model = $vehicleInfo['model'] ?? '';
        $year = $vehicleInfo['year'] ?? '';
        
        return trim("{$make} {$model} {$year}");
    }

    /**
     * Obtém URL da imagem do veículo
     */
    private function getVehicleImageUrl(): string
    {
        $vehicleInfo = $this->article->vehicle_info ?? [];
        $makeSlug = $vehicleInfo['make_slug'] ?? strtolower($vehicleInfo['make'] ?? '');
        $modelSlug = $vehicleInfo['model_slug'] ?? strtolower($vehicleInfo['model'] ?? '');
        $year = $vehicleInfo['year'] ?? '';
        
        return "https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/vehicles/{$makeSlug}-{$modelSlug}-{$year}.jpg";
    }

    /**
     * Verifica se é veículo premium
     */
    private function isPremiumVehicle(): bool
    {
        $make = strtolower($this->article->vehicle_info['make'] ?? '');
        $premiumBrands = ['audi', 'bmw', 'mercedes', 'lexus', 'volvo', 'porsche'];
        
        return in_array($make, $premiumBrands);
    }

    /**
     * Obtém segmento do veículo
     */
    private function getVehicleSegment(): string
    {
        $category = strtolower($this->article->vehicle_info['category'] ?? '');
        
        $segmentMap = [
            'hatch' => 'Hatchback Compacto',
            'sedan' => 'Sedan Médio',
            'suv' => 'SUV',
            'pickup' => 'Picape',
            'coupe' => 'Cupê'
        ];
        
        return $segmentMap[$category] ?? 'Automóvel';
    }

    /**
     * Processa dados SEO específicos para carros
     */
    private function processSeoData(): array
    {
        $vehicleFullName = $this->getVehicleFullName();
        $mainPressure = $this->getMainPressureDisplay();
        
        return [
            'title' => $this->article->title ?? "Pressão Ideal para Pneus do {$vehicleFullName} – Tabela Completa",
            'meta_description' => $this->article->meta_description ?? "Tabela completa de pressão dos pneus do {$vehicleFullName}. Valores oficiais em PSI: {$mainPressure}. Guia prático com conversões e dicas de calibragem.",
            'keywords' => $this->article->seo_keywords ?? [],
            'focus_keyword' => "pressão ideal pneus {$this->article->vehicle_info['make']} {$this->article->vehicle_info['model']} {$this->article->vehicle_info['year']}",
            'canonical_url' => $this->getCanonicalUrl(),
            'og_title' => "Pressão Ideal para Pneus do {$vehicleFullName} – Tabela Oficial",
            'og_description' => "Tabela completa com pressões oficiais em PSI para {$vehicleFullName}. Conversões, dicas e localização da etiqueta.",
            'og_image' => $this->processedData['vehicle_info']['image_url'],
            'og_type' => 'article',
            'twitter_card' => 'summary_large_image'
        ];
    }

    /**
     * Obtém pressão principal para exibição
     */
    private function getMainPressureDisplay(): string
    {
        $versions = $this->processedData['pressure_table']['versions'] ?? [];
        
        if (!empty($versions[0])) {
            $front = $versions[0]['front_normal'] ?? '';
            $rear = $versions[0]['rear_normal'] ?? '';
            return "{$front} (dianteira) / {$rear} (traseira)";
        }
        
        return '';
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
            'name' => "Pressão Ideal para Pneus do {$vehicleFullName}",
            'description' => "Tabela completa de pressões ideais para os pneus do {$vehicleFullName}, incluindo todas as versões e condições de uso.",
            'image' => [
                '@type' => 'ImageObject',
                'url' => $vehicleInfo['image_url'] ?? $this->getDefaultCarImage(),
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
            'dateModified' => $this->article->updated_at?->toISOString(),
            'mainEntity' => [
                '@type' => 'Car',
                'brand' => $vehicleInfo['make'],
                'model' => $vehicleInfo['model'],
                'modelDate' => $vehicleInfo['year']
            ],
            'about' => [
                '@type' => 'Thing',
                'name' => 'Calibragem de Pneus',
                'description' => 'Pressões ideais para pneus automotivos'
            ]
        ];
    }

    /**
     * Obtém URL canônica do artigo
     */
    private function getCanonicalUrl(): string
    {
        return $this->article->canonical_url ?? route('info.article.show', $this->article->slug);
    }

    /**
     * Obtém imagem padrão para carros
     */
    private function getDefaultCarImage(): string
    {
        return 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/pressao-ideal-carro.jpg';
    }

    /**
     * Processa breadcrumbs para navegação
     */
    public function getBreadcrumbs(): array
    {
        return [
            [
                'name' => 'Home',
                'url' => route('home'),
                'position' => 1
            ],
            [
                'name' => 'Info Center',
                'url' => route('info.home'),
                'position' => 2
            ],
            [
                'name' => $this->article->category_name ?? 'Calibragem de Pneus',
                'url' => route('info.category.show', $this->article->category_slug ?? 'calibragem-pneus'),
                'position' => 3
            ],
            [
                'name' => $this->article->title,
                'url' => null,
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