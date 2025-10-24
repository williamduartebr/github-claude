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
     * MÉTODO CORRIGIDO: Remove completamente referências a Vehicle e Product para evitar erro
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

        // Estrutura base do schema - SEMPRE Article no root
        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'Article', // SEMPRE Article, nunca Product/HowTo/Dataset
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
            'articleSection' => 'Pneus e Rodas',
            'keywords' => 'pneus recomendados, recomendação pneus, melhores pneus'
        ];

        // VALIDAÇÃO: Só adiciona dados do veículo se marca E modelo existirem
        if (!empty($vehicleInfo['marca']) && !empty($vehicleInfo['modelo'])) {
            // NÃO adiciona dados do veículo no about para evitar erro de Product
            // Apenas enriquece keywords e description
            $vehicleKeywords = [
                $vehicleInfo['marca'] ?? '',
                $vehicleInfo['modelo'] ?? '',
                $vehicleInfo['ano'] ?? '',
                'pneus recomendados',
                'melhores pneus'
            ];
            
            // Adiciona keywords do veículo às keywords existentes
            $allKeywords = array_merge(
                $this->article->tags ?? ['pneus recomendados', 'recomendação pneus', 'melhores pneus'],
                array_filter($vehicleKeywords)
            );
            
            $structuredData['keywords'] = implode(', ', array_unique($allKeywords));
            
            // Enriquece a descrição com informações do veículo
            $vehicleName = trim($vehicleInfo['marca'] . ' ' . $vehicleInfo['modelo'] . ' ' . ($vehicleInfo['ano'] ?? ''));
            if (!empty($vehicleName)) {
                $structuredData['description'] = $structuredData['description'] . 
                    ' Recomendações específicas para ' . $vehicleName . '.';
            }

            // NÃO adiciona mainEntity com ItemList/Product - isso causa o erro
            // Apenas menciona que o artigo trata sobre recomendações
            if (!empty($content['pneus_dianteiros']) || !empty($content['pneus_traseiros'])) {
                $structuredData['mentions'] = [
                    '@type' => 'Thing',
                    'name' => 'Recomendações de Pneus',
                    'description' => 'Lista de pneus recomendados com análise detalhada'
                ];
            }
        }

        $this->processedData['structured_data'] = $structuredData;

        // URLs canônica e alternativas
        $this->processedData['canonical_url'] = route('info.article.show', $this->article->slug);

        // Breadcrumbs com URL no último item para evitar erro
        $this->processedData['breadcrumbs'] = [
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
                'name' => Str::title($this->article->category_name ?? 'Pneus e Rodas'),
                'url' => route('info.category.show', $this->article->category_slug ?? 'pneus-rodas'),
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
