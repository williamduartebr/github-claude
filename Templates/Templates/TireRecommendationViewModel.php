<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

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

        // Dados estruturados para SEO
        $this->processStructuredDataForSEO();
    }

    /**
     * Processa metadados para melhorar o SEO da página
     */
    private function processStructuredDataForSEO(): void
    {
        $vehicleInfo = $this->article->vehicle_info;

        // Constrói informações estruturadas para o veículo
        $this->processedData['structured_data'] = [
            '@context' => 'https://schema.org',
            '@type' => 'TechArticle',
            'headline' => $this->article->title,
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
            'description' => $this->article->seo_data['meta_description'] ?? '',
            'vehicleEngine' => $vehicleInfo['make'] . ' ' . $vehicleInfo['model'] . ' ' . ($vehicleInfo['engine'] ?? '') . ' ' . $vehicleInfo['year'],
            'about' => [
                '@type' => 'Thing',
                'name' => 'Pneus recomendados para ' . $vehicleInfo['make'] . ' ' . $vehicleInfo['model'],
            ],
        ];

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
                'name' => Str::title($this->article->category_name),
                'url' => route('info.category.show', $this->article->category_slug),
                'position' => 3
            ],
            [
                'name' => $this->article->title,
                'url' => null,
                'position' => 4
            ],
        ];
    }
}
