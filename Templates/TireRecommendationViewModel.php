<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Src\AutoInfoCenter\ViewModels\Templates\TemplateViewModel;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Illuminate\Support\Str;

class TireRecommendationViewModel extends TemplateViewModel
{
    /**
     * Nome do template a ser utilizado
     */
    protected string $templateName = 'tire_recommendation';

    /**
     * Processa dados específicos para o template de recomendação de pneus
     */
    protected function processTemplateSpecificData(): void
    {
        // Extrai dados específicos do conteúdo do artigo
        $content = $this->article->content;

        // Introdução
        $this->processedData['introduction'] = $content['introducao'] ?? '';

        // Especificações oficiais
        $this->processedData['official_specs'] = $content['especificacoes_oficiais'] ?? null;

        // Melhores pneus dianteiros
        $this->processedData['front_tires'] = $content['pneus_dianteiros'] ?? [];

        // Melhores pneus traseiros
        $this->processedData['rear_tires'] = $content['pneus_traseiros'] ?? [];

        // Comparativo por tipo de uso
        $this->processedData['usage_comparison'] = $content['comparativo_uso'] ?? [];

        // Guia de desgaste e substituição
        $this->processedData['wear_guide'] = $content['guia_desgaste'] ?? null;

        // Dicas de manutenção
        $this->processedData['maintenance_tips'] = $content['dicas_manutencao'] ?? [];

        // Perguntas frequentes
        $this->processedData['faq'] = $content['perguntas_frequentes'] ?? [];

        // Considerações finais
        $this->processedData['final_considerations'] = $content['consideracoes_finais'] ?? '';

        // Info do veículo específica
        $this->processedData['vehicle_full_name'] = $this->getVehicleFullName();

        // Dados estruturados para SEO
        $this->processStructuredDataForSEO();
    }

    /**
     * Processa metadados para melhorar o SEO da página
     * MÉTODO CORRIGIDO: Usa extracted_entities, valida dados e corrige erros
     */
    private function processStructuredDataForSEO(): void
    {
        // CORREÇÃO: Usar extracted_entities ao invés de vehicle_info
        $vehicleInfo = $this->article->extracted_entities ?? [];
        $content = $this->article->content;

        // Imagem padrão baseada no tipo de veículo
        $vehicleType = $vehicleInfo['tipo_veiculo'] ?? '';
        if (!empty($vehicleType)) {
            $imageDefault = "https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/pneus-{$vehicleType}.png";
        } else {
            $imageDefault = "https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/tire_recommendation.png";
        }

        // Estrutura base do schema
        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $this->article->title,
            'name' => $this->article->title,
            'description' => $content['introducao'] ?? ($this->article->seo_data['meta_description'] ?? ''),
            'datePublished' => $this->article->created_at->utc()->toAtomString(),
            'dateModified' => $this->article->updated_at->utc()->toAtomString(),
            'author' => [
                '@type' => 'Person',
                'name' => $this->article->author['name'] ?? 'Equipe Editorial',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Mercado Veículos',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/logos/default_share_image.jpg',
                ],
            ],
            'image' => [
                '@type' => 'ImageObject',
                'url' => $imageDefault,
                'width' => 1200,
                'height' => 630
            ],
        ];

        // VALIDAÇÃO: Só adiciona dados do veículo se marca E modelo existirem
        if (!empty($vehicleInfo['marca']) && !empty($vehicleInfo['modelo'])) {
            // Determina o tipo baseado no tipo_veiculo
            $vehicleSchemaType = ($vehicleInfo['tipo_veiculo'] ?? '') === 'motocicleta' ? 'Motorcycle' : 'Vehicle';

            $structuredData['about'] = [
                '@type' => $vehicleSchemaType,
                'brand' => $vehicleInfo['marca'], // marca → brand
                'model' => $vehicleInfo['modelo'], // modelo → model
                'name' => 'Pneus recomendados para ' . $vehicleInfo['marca'] . ' ' . $vehicleInfo['modelo'],
            ];

            // Adiciona ano se existir
            if (!empty($vehicleInfo['ano'])) {
                $structuredData['about']['modelDate'] = (string) $vehicleInfo['ano']; // ano → modelDate
            }

            // Adiciona motorização se existir e não estiver vazia
            if (!empty($vehicleInfo['motorizacao'])) {
                $structuredData['about']['vehicleEngine'] = [
                    '@type' => 'EngineSpecification',
                    'engineDisplacement' => $vehicleInfo['motorizacao']
                ];
            }

            // Propriedades adicionais
            $additionalProperties = [];

            // Adiciona versão se existir e não for "Todas"
            if (!empty($vehicleInfo['versao']) && $vehicleInfo['versao'] !== 'Todas') {
                $additionalProperties[] = [
                    '@type' => 'PropertyValue',
                    'name' => 'Version',
                    'value' => $vehicleInfo['versao']
                ];
            }

            // Adiciona combustível se existir
            if (!empty($vehicleInfo['combustivel'])) {
                $additionalProperties[] = [
                    '@type' => 'PropertyValue',
                    'name' => 'Fuel Type',
                    'value' => ucfirst($vehicleInfo['combustivel'])
                ];
            }

            // Adiciona categoria se existir
            if (!empty($vehicleInfo['categoria'])) {
                $additionalProperties[] = [
                    '@type' => 'PropertyValue',
                    'name' => 'Category',
                    'value' => ucfirst($vehicleInfo['categoria'])
                ];
            }

            // Só adiciona additionalProperty se houver propriedades
            if (!empty($additionalProperties)) {
                $structuredData['about']['additionalProperty'] = $additionalProperties;
            }

            // Adiciona informações sobre recomendações de pneus
            $structuredData['mainEntity'] = [
                '@type' => 'ItemList',
                'name' => 'Pneus Recomendados para ' . $vehicleInfo['marca'] . ' ' . $vehicleInfo['modelo'],
                'description' => 'Lista de pneus recomendados com análise detalhada por categoria de uso'
            ];

            // Adiciona itens da lista se existirem recomendações
            $tireItems = [];

            // Pneus dianteiros
            if (!empty($content['pneus_dianteiros'])) {
                foreach ($content['pneus_dianteiros'] as $index => $tire) {
                    if (!empty($tire['marca']) && !empty($tire['modelo'])) {
                        $tireItems[] = [
                            '@type' => 'Product',
                            'position' => count($tireItems) + 1,
                            'name' => $tire['marca'] . ' ' . $tire['modelo'],
                            'category' => 'Pneu Dianteiro',
                            'description' => $tire['descricao'] ?? '',
                            'brand' => [
                                '@type' => 'Brand',
                                'name' => $tire['marca']
                            ]
                        ];
                    }
                }
            }

            // Pneus traseiros
            if (!empty($content['pneus_traseiros'])) {
                foreach ($content['pneus_traseiros'] as $index => $tire) {
                    if (!empty($tire['marca']) && !empty($tire['modelo'])) {
                        $tireItems[] = [
                            '@type' => 'Product',
                            'position' => count($tireItems) + 1,
                            'name' => $tire['marca'] . ' ' . $tire['modelo'],
                            'category' => 'Pneu Traseiro',
                            'description' => $tire['descricao'] ?? '',
                            'brand' => [
                                '@type' => 'Brand',
                                'name' => $tire['marca']
                            ]
                        ];
                    }
                }
            }

            if (!empty($tireItems)) {
                $structuredData['mainEntity']['itemListElement'] = $tireItems;
                $structuredData['mainEntity']['numberOfItems'] = count($tireItems);
            }
        }

        $this->processedData['structured_data'] = $structuredData;

        // URLs canônica e alternativas
        $this->processedData['canonical_url'] = route('info.article.show', $this->article->slug);

        // Breadcrumbs
        $this->processedData['breadcrumbs'] = [
            [
                'name' => 'Início',
                'url' => url('/'),
                'position' => 1
            ],
            [
                'name' => 'Informações',
                'url' => route('info.category.index'),
                'position' => 2
            ],
            [
                'name' => Str::title($this->article->category_name ?? ''),
                'url' => route('info.category.show', $this->article->category_slug ?? ''),
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
     * Retorna o nome completo do veículo
     */
    private function getVehicleFullName(): string
    {
        $vehicleInfo = $this->article->extracted_entities ?? [];

        // Validação básica - retorna vazio se não tiver marca ou modelo
        if (empty($vehicleInfo['marca']) || empty($vehicleInfo['modelo'])) {
            return '';
        }

        return sprintf(
            '%s %s %s',
            $vehicleInfo['marca'] ?? '',
            $vehicleInfo['modelo'] ?? '',
            $vehicleInfo['ano'] ?? ''
        );
    }
}
