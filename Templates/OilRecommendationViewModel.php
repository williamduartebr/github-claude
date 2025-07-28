<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Src\AutoInfoCenter\ViewModels\Templates\TemplateViewModel;
use Illuminate\Support\Str;

class OilRecommendationViewModel extends TemplateViewModel
{
    /**
     * Nome do template a ser utilizado
     */
    protected string $templateName = 'oil_recommendation';

    /**
     * Processa dados específicos para o template de recomendação de óleo
     */
    protected function processTemplateSpecificData(): void
    {
        // Extrai dados específicos do conteúdo do artigo
        $content = $this->article->content;

        // Introdução
        $this->processedData['introduction'] = $content['introducao'] ?? '';

        // Recomendações do fabricante
        $this->processedData['manufacturer_recommendation'] = $content['recomendacoes_fabricante'] ?? null;

        // Alternativa premium
        $this->processedData['premium_alternative'] = $content['alternativa_premium'] ?? null;

        // Opção econômica
        $this->processedData['economic_option'] = $content['opcao_economica'] ?? null;

        // Especificações técnicas
        $this->processedData['specifications'] = $content['especificacoes'] ?? null;

        // Benefícios do óleo correto
        $this->processedData['benefits'] = $content['beneficios'] ?? [];

        // Condições de uso
        $this->processedData['usage_conditions'] = $content['condicoes_uso'] ?? null;

        // Procedimento de troca
        $this->processedData['change_procedure'] = $content['procedimento'] ?? [];

        // Nota ambiental
        $this->processedData['environmental_note'] = $content['nota_ambiental'] ?? '';

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
            $imageDefault = "https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/oleo-{$vehicleType}.png";
        } else {
            $imageDefault = "https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/oil_recommendation.png";
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
                'name' => 'Óleo recomendado para ' . $vehicleInfo['marca'] . ' ' . $vehicleInfo['modelo'],
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

            // Adiciona informações sobre recomendação de óleo
            if (!empty($content['recomendacoes_fabricante'])) {
                $structuredData['mainEntity'] = [
                    '@type' => 'Product',
                    'name' => 'Óleo Recomendado para ' . $vehicleInfo['marca'] . ' ' . $vehicleInfo['modelo'],
                    'category' => 'Automotive Oil',
                    'description' => 'Recomendação oficial de óleo para este veículo'
                ];

                // Adiciona especificação se existir
                if (isset($content['recomendacoes_fabricante']['especificacao'])) {
                    $structuredData['mainEntity']['additionalProperty'] = [
                        '@type' => 'PropertyValue',
                        'name' => 'Oil Specification',
                        'value' => $content['recomendacoes_fabricante']['especificacao']
                    ];
                }
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
