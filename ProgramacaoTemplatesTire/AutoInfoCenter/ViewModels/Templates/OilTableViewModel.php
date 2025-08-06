<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Src\AutoInfoCenter\ViewModels\Templates\TemplateViewModel;
use Illuminate\Support\Str;

class OilTableViewModel extends TemplateViewModel
{
    /**
     * Nome do template a ser utilizado
     */
    protected string $templateName = 'oil_table';

    /**
     * Processa dados específicos para o template de tabela de óleo
     */
    protected function processTemplateSpecificData(): void
    {
        // Extrai dados específicos do conteúdo do artigo
        $content = $this->article->content;

        // Introdução
        $this->processedData['introduction'] = $content['introducao'] ?? '';

        // Tabela principal de óleos por geração/período
        $this->processedData['oil_table'] = $content['tabela_oleo'] ?? [];

        // Especificações detalhadas por tipo de óleo
        $this->processedData['oil_specifications'] = $content['especificacoes_oleo'] ?? [];

        // Filtros de óleo recomendados
        $this->processedData['oil_filters'] = $content['filtros_oleo'] ?? [];

        // Intervalos de troca por condição de uso
        $this->processedData['maintenance_intervals'] = $content['intervalos_troca'] ?? [];

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
     * MÉTODO CORRIGIDO: Usa extracted_entities, valida dados e segue padrão Article
     */
    private function processStructuredDataForSEO(): void
    {
        // CORREÇÃO: Usar extracted_entities ao invés de vehicle_info
        $vehicleInfo = $this->article->extracted_entities ?? [];
        $content = $this->article->content;

        // Imagem padrão baseada no tipo de veículo
        $vehicleType = $vehicleInfo['tipo_veiculo'] ?? '';
        if (!empty($vehicleType)) {
            $imageDefault = "https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/tabela-oleo-{$vehicleType}.png";
        } else {
            $imageDefault = "https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/oil_table.png";
        }

        // Estrutura base do schema - SEMPRE Article no root
        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'Article', // SEMPRE Article, nunca Product/HowTo/Dataset no root
            'headline' => $this->article->title,
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
                    'width' => 600,
                    'height' => 60
                ],
            ],
            'image' => [
                '@type' => 'ImageObject',
                'url' => $imageDefault,
                'width' => 1200,
                'height' => 630
            ],
            'articleSection' => 'Óleo e Lubrificantes',
            'keywords' => implode(', ', $this->article->tags ?? ['óleo motor', 'tabela óleo', 'manutenção'])
        ];

        // VALIDAÇÃO: Só adiciona dados do veículo se marca E modelo existirem
        if (!empty($vehicleInfo['marca']) && !empty($vehicleInfo['modelo'])) {
            // NÃO adiciona dados do veículo no about para evitar erro de Product
            // Apenas adiciona informações como keywords e description
            $vehicleKeywords = [
                $vehicleInfo['marca'] ?? '',
                $vehicleInfo['modelo'] ?? '',
                $vehicleInfo['categoria'] ?? '',
                'tabela óleo',
                'especificações'
            ];
            
            // Adiciona keywords do veículo às keywords existentes
            $allKeywords = array_merge(
                $this->article->tags ?? ['óleo motor', 'tabela óleo', 'manutenção'],
                array_filter($vehicleKeywords)
            );
            
            $structuredData['keywords'] = implode(', ', array_unique($allKeywords));
            
            // Enriquece a descrição com informações do veículo
            if (!empty($vehicleInfo['anos_fabricacao'])) {
                $structuredData['description'] = $structuredData['description'] . 
                    ' Referência para ' . $vehicleInfo['marca'] . ' ' . 
                    $vehicleInfo['modelo'] . ' ' . $vehicleInfo['anos_fabricacao'];
            }
            
            // Adiciona informações sobre a tabela como mentions (não mainEntity)
            if (!empty($content['tabela_oleo'])) {
                $structuredData['mentions'] = [
                    '@type' => 'Dataset',
                    'name' => 'Tabela de Especificações de Óleo',
                    'description' => 'Dados técnicos sobre óleo motor por geração e motorização',
                    'about' => [
                        '@type' => 'Thing',
                        'name' => 'Especificações de Óleo Automotivo'
                    ]
                ];
            }
        }

        $this->processedData['structured_data'] = $structuredData;

        // URLs canônica e alternativas
        $this->processedData['canonical_url'] = route('info.article.show', $this->article->slug);

        // Breadcrumbs - mantém a URL do último item para evitar erro de schema
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
                'name' => Str::title($this->article->category_name ?? 'Óleo e Lubrificantes'),
                'url' => route('info.category.show', $this->article->category_slug ?? 'oleo-lubrificantes'),
                'position' => 3
            ],
            [
                'name' => $this->article->title,
                'url' => route('info.article.show', $this->article->slug), // Adiciona URL para evitar erro
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

        // Para este caso específico, usar anos_fabricacao ao invés de ano
        $years = $vehicleInfo['anos_fabricacao'] ?? '';
        
        return sprintf(
            '%s %s %s',
            $vehicleInfo['marca'] ?? '',
            $vehicleInfo['modelo'] ?? '',
            $years
        );
    }
}