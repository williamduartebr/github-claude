<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

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
            'vehicleEngine' => ($vehicleInfo['make'] ?? '') . ' ' . ($vehicleInfo['model'] ?? '') . ' ' . ($vehicleInfo['engine'] ?? '') . ' ' . ($vehicleInfo['year'] ?? ''),
            'about' => [
                '@type' => 'Thing',
                'name' => 'Tabela de óleo para ' . ($vehicleInfo['make'] ?? '') . ' ' . ($vehicleInfo['model'] ?? ''),
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
}
