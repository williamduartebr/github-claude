<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

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

        // Dados estruturados para SEO
        $this->processProcessedDataForSEO();
    }

    /**
     * Processa metadados para melhorar o SEO da página
     */
    private function processProcessedDataForSEO(): void
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
            'vehicleEngine' => $vehicleInfo['make'] . ' ' . $vehicleInfo['model'] . ' ' . $vehicleInfo['engine'] . ' ' . $vehicleInfo['year'],
            'about' => [
                '@type' => 'Thing',
                'name' => 'Óleo recomendado para ' . $vehicleInfo['make'] . ' ' . $vehicleInfo['model'],
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
                'name' => clean_title($this->article->title),
                'url' => null,
                'position' => 4
            ],
        ];
    }
}
