<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Illuminate\Support\Str;
use Src\AutoInfoCenter\ViewModels\Templates\TemplateViewModel;

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

        $this->processedData['introduction'] = $content['introducao'] ?? '';
        $this->processedData['tire_specifications_by_version'] = $this->processTireSpecificationsByVersion($content['especificacoes_por_versao'] ?? []);
        $this->processedData['label_location'] = $this->processLabelLocation($content['localizacao_etiqueta'] ?? []);
        $this->processedData['special_conditions'] = $this->processSpecialConditions($content['condicoes_especiais'] ?? []);
        $this->processedData['unit_conversion'] = $this->processUnitConversion($content['conversao_unidades'] ?? []);
        $this->processedData['care_recommendations'] = $this->processCareRecommendations($content['cuidados_recomendacoes'] ?? []);
        $this->processedData['pressure_impact'] = $this->processPressureImpact($content['impacto_pressao'] ?? []);
        $this->processedData['faq'] = $content['perguntas_frequentes'] ?? [];
        $this->processedData['final_considerations'] = $content['consideracoes_finais'] ?? '';
        $this->processedData['related_topics'] = $this->getRelatedTopics();
        
        // Dados auxiliares
        $this->processedData['vehicle_info'] = $this->processVehicleInfo();
        $this->processedData['structured_data'] = $this->buildStructuredData();
        $this->processedData['seo_data'] = $this->processSeoData();
        $this->processedData['breadcrumbs'] = $this->getBreadcrumbs();
        $this->processedData['canonical_url'] = $this->getCanonicalUrl();
    }

    /**
     * Processa especificações dos pneus por versão
     */
    private function processTireSpecificationsByVersion(array $specs): array
    {
        if (empty($specs)) {
            return [];
        }

        $processed = [];

        foreach ($specs as $spec) {
            if (!empty($spec['versao'])) {
                $processed[] = [
                    'version' => $spec['versao'],
                    'tire_size' => $spec['medida_pneus'] ?? '',
                    'load_speed_index' => $spec['indice_carga_velocidade'] ?? '',
                    'front_normal' => $spec['pressao_dianteiro_normal'] ?? '',
                    'rear_normal' => $spec['pressao_traseiro_normal'] ?? '',
                    'front_loaded' => $spec['pressao_dianteiro_carregado'] ?? '',
                    'rear_loaded' => $spec['pressao_traseiro_carregado'] ?? '',
                    'css_class' => $this->getVersionCssClass($spec['versao'])
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
            'description' => $location['descricao'] ?? '',
            'alternative_locations' => $location['locais_alternativos'] ?? [],
            'note' => $location['observacao'] ?? '',
            'visual_guide' => $this->generateVisualGuide($location)
        ];
    }

    /**
     * Processa condições especiais de uso
     */
    private function processSpecialConditions(array $conditions): array
    {
        if (empty($conditions)) {
            return [];
        }

        $processed = [];

        foreach ($conditions as $condition) {
            if (!empty($condition['condicao'])) {
                $processed[] = [
                    'condition' => $condition['condicao'],
                    'recommended_adjustment' => $condition['ajuste_recomendado'] ?? '',
                    'application' => $condition['aplicacao'] ?? '',
                    'justification' => $condition['justificativa'] ?? '',
                    'icon_class' => $this->getConditionIconClass($condition['condicao'])
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa tabela de conversão de unidades
     */
    private function processUnitConversion(array $conversion): array
    {
        if (empty($conversion)) {
            return [];
        }

        $processed = [
            'conversion_table' => [],
            'formulas' => [],
            'note' => $conversion['observacao'] ?? ''
        ];

        // Processa tabela de conversão
        if (!empty($conversion['tabela_conversao']) && is_array($conversion['tabela_conversao'])) {
            foreach ($conversion['tabela_conversao'] as $row) {
                $processed['conversion_table'][] = [
                    'psi' => $row['psi'] ?? '',
                    'kgf_cm2' => $row['kgf_cm2'] ?? '',
                    'bar' => $row['bar'] ?? ''
                ];
            }
        }

        // Processa fórmulas
        if (!empty($conversion['formulas'])) {
            $processed['formulas'] = [
                'psi_para_kgf' => $conversion['formulas']['psi_para_kgf'] ?? '',
                'kgf_para_psi' => $conversion['formulas']['kgf_para_psi'] ?? '',
                'psi_para_bar' => $conversion['formulas']['psi_para_bar'] ?? ''
            ];
        }

        return $processed;
    }

    /**
     * Processa cuidados e recomendações
     */
    private function processCareRecommendations(array $recommendations): array
    {
        if (empty($recommendations)) {
            return [];
        }

        $processed = [];

        foreach ($recommendations as $rec) {
            if (!empty($rec['categoria'])) {
                $processed[] = [
                    'category' => $rec['categoria'],
                    'description' => $rec['descricao'] ?? '',
                    'icon_class' => $this->getCareIconClass($rec['categoria'])
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa impacto da pressão no desempenho
     */
    private function processPressureImpact(array $impact): array
    {
        if (empty($impact)) {
            return [];
        }

        $processed = [];

        foreach ($impact as $key => $impactData) {
            if (!empty($impactData['titulo'])) {
                $processed[] = [
                    'type' => $key,
                    'title' => $impactData['titulo'],
                    'items' => $impactData['problemas'] ?? $impactData['beneficios'] ?? [],
                    'color' => $this->getImpactColor($key),
                    'icon_class' => $this->getImpactIconClass($key),
                    'css_class' => $this->getImpactCssClass($key)
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
            'slug' => $this->generateSlug($vehicleInfo),
            'is_premium' => $this->isPremiumVehicle(),
            'segment' => $this->getVehicleSegment()
        ];
    }

    /**
     * Obtém classe CSS para versão do veículo
     */
    private function getVersionCssClass(string $version): string
    {
        return ($this->getVersionIndex($version) % 2 === 0) ? 'bg-white' : 'bg-gray-50';
    }

    /**
     * Obtém índice da versão para CSS alternado
     */
    private function getVersionIndex(string $version): int
    {
        static $versionIndex = 0;
        return $versionIndex++;
    }

    /**
     * Obtém classe de ícone para condição especial
     */
    private function getConditionIconClass(string $condition): string
    {
        $condition = strtolower($condition);

        if (str_contains($condition, 'rodovia') || str_contains($condition, 'viagem')) {
            return 'trending-up';
        }

        if (str_contains($condition, 'carga') || str_contains($condition, 'máxima')) {
            return 'package';
        }

        if (str_contains($condition, 'econômica') || str_contains($condition, 'economia')) {
            return 'dollar-sign';
        }

        return 'info';
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
                'Olhe na coluna da porta',
                'Procure por etiqueta branca com tabela de pressões'
            ]
        ];
    }

    /**
     * Obtém classe de ícone para cuidados
     */
    private function getCareIconClass(string $category): string
    {
        $category = strtolower($category);

        if (str_contains($category, 'verificação') || str_contains($category, 'mensal')) {
            return 'clock';
        }

        if (str_contains($category, 'frios') || str_contains($category, 'temperatura')) {
            return 'thermometer';
        }

        if (str_contains($category, 'calibradores') || str_contains($category, 'equipamento')) {
            return 'tool';
        }

        if (str_contains($category, 'calor') || str_contains($category, 'sol')) {
            return 'sun';
        }

        if (str_contains($category, 'chuva') || str_contains($category, 'poeira')) {
            return 'cloud-rain';
        }

        if (str_contains($category, 'rodízio')) {
            return 'rotate-cw';
        }

        return 'info';
    }

    /**
     * Obtém cor para tipo de impacto
     */
    private function getImpactColor(string $type): string
    {
        $colorMap = [
            'subcalibrado' => 'red',
            'ideal' => 'green',
            'sobrecalibrado' => 'amber'
        ];

        return $colorMap[$type] ?? 'gray';
    }

    /**
     * Obtém classe de ícone para impacto
     */
    private function getImpactIconClass(string $type): string
    {
        $iconMap = [
            'subcalibrado' => 'minus',
            'ideal' => 'check',
            'sobrecalibrado' => 'alert-triangle'
        ];

        return $iconMap[$type] ?? 'info';
    }

    /**
     * Obtém classe CSS para impacto
     */
    private function getImpactCssClass(string $type): string
    {
        $color = $this->getImpactColor($type);
        return "from-{$color}-100 to-{$color}-200";
    }

    /**
     * Obtém tópicos relacionados
     */
    private function getRelatedTopics(): array
    {
        $vehicleInfo = $this->article->extracted_entities ?? [];
        $make = strtolower($vehicleInfo['marca'] ?? '');
        $model = strtolower($vehicleInfo['modelo'] ?? '');
        $year = $vehicleInfo['ano'] ?? '';

        return [
            [
                'title' => "Melhores Pneus para {$vehicleInfo['marca']} {$vehicleInfo['modelo']} {$year}",
                'description' => 'Descubra os pneus ideais para seu veículo',
                'url' => "/info/pneus-recomendados/{$make}-{$model}-{$year}/"
            ],
            [
                'title' => "Guia de Rodízio de Pneus do {$vehicleInfo['marca']} {$vehicleInfo['modelo']}",
                'description' => 'Como fazer o rodízio correto dos pneus',
                'url' => "/info/rodizio-pneus/{$make}-{$model}-{$year}/"
            ],
            [
                'title' => "Consumo Real do {$vehicleInfo['marca']} {$vehicleInfo['modelo']} {$year}",
                'description' => 'Dados reais de consumo de combustível',
                'url' => "/info/consumo/{$make}-{$model}-{$year}/"
            ],
            [
                'title' => "Cronograma de Revisões do {$vehicleInfo['marca']} {$vehicleInfo['modelo']}",
                'description' => 'Plano de manutenção preventiva',
                'url' => "/info/revisoes/{$make}-{$model}-{$year}/"
            ]
        ];
    }

    /**
     * Obtém nome completo do veículo
     */
    private function getVehicleFullName(): string
    {
        $vehicleInfo = $this->article->extracted_entities ?? [];

        if (empty($vehicleInfo['marca']) || empty($vehicleInfo['modelo'])) {
            return '';
        }

        $make = $vehicleInfo['marca'] ?? '';
        $model = $vehicleInfo['modelo'] ?? '';
        $year = $vehicleInfo['ano'] ?? '';

        return trim("{$make} {$model} {$year}");
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

        return "https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/vehicles/{$makeSlug}-{$modelSlug}-{$year}.jpg";
    }

    /**
     * Gera slug baseado nos dados do veículo
     */
    private function generateSlug(array $vehicleInfo): string
    {
        $make = strtolower($vehicleInfo['marca'] ?? '');
        $model = strtolower(str_replace(' ', '-', $vehicleInfo['modelo'] ?? ''));

        return "{$make}-{$model}";
    }

    /**
     * Verifica se é veículo premium
     */
    private function isPremiumVehicle(): bool
    {
        $make = strtolower($this->article->extracted_entities['marca'] ?? '');
        $premiumBrands = ['audi', 'bmw', 'mercedes', 'lexus', 'volvo', 'porsche'];

        return in_array($make, $premiumBrands);
    }

    /**
     * Obtém segmento do veículo
     */
    private function getVehicleSegment(): string
    {
        $category = strtolower($this->article->extracted_entities['categoria'] ?? '');

        $segmentMap = [
            'hatches' => 'Hatchback Compacto',
            'sedan' => 'Sedan Médio',
            'suv' => 'SUV',
            'pickup' => 'Picape',
            'coupe' => 'Cupê'
        ];

        return $segmentMap[$category] ?? 'Automóvel';
    }

    /**
     * Processa dados SEO específicos
     */
    private function processSeoData(): array
    {
        $vehicleFullName = $this->getVehicleFullName();
        $vehicleInfo = $this->article->extracted_entities ?? [];
        $seoData = $this->article->seo_data ?? [];

        return [
            'title' => $seoData['page_title'] ?? "Pressão Ideal para Pneus do {$vehicleFullName} – Tabela Completa",
            'meta_description' => $seoData['meta_description'] ?? "Tabela completa de pressão dos pneus do {$vehicleFullName}. Valores oficiais em PSI, conversões e dicas de calibragem para o Brasil.",
            'keywords' => $seoData['secondary_keywords'] ?? [],
            'focus_keyword' => $seoData['primary_keyword'] ?? "pressão ideal pneus {$vehicleInfo['marca']} {$vehicleInfo['modelo']} {$vehicleInfo['ano']}",
            'canonical_url' => $this->getCanonicalUrl(),
            'h1' => $seoData['h1'] ?? "Pressão Ideal para Pneus do {$vehicleFullName} – Tabela Completa",
            'h2_tags' => $seoData['h2_tags'] ?? [],
            'og_title' => "Pressão Ideal para Pneus do {$vehicleFullName} – Tabela Oficial",
            'og_description' => "Tabela completa com pressões oficiais em PSI para {$vehicleFullName}. Conversões, dicas e localização da etiqueta.",
            'og_image' => $this->processedData['vehicle_info']['image_url'] ?? '',
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
        $vehicleData = $this->article->extracted_entities ?? [];

        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
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
            'about' => [
                '@type' => 'Thing',
                'name' => 'Calibragem de Pneus',
                'description' => 'Pressões ideais para pneus automotivos'
            ]
        ];

        if (!empty($vehicleData['marca']) && !empty($vehicleData['modelo'])) {
            $structuredData['mainEntity'] = [
                '@type' => 'Car',
                'name' => 'Pressão ideal para ' . $vehicleData['marca'] . ' ' . $vehicleData['modelo'],
                'brand' => $vehicleData['marca'],
                'model' => $vehicleData['modelo']
            ];

            if (!empty($vehicleData['ano'])) {
                $structuredData['mainEntity']['modelDate'] = (string) $vehicleData['ano'];
            }
        }

        return $structuredData;
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
                'url' => route('info.article.show', $this->article->slug),
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