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
            $imageDefault = "https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/oleo-{$vehicleType}.png";
        } else {
            $imageDefault = "https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/oil_recommendation.png";
        }

        // Estrutura base do schema - SEMPRE Article no root
        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'Article', // SEMPRE Article, nunca Product
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
            'keywords' => implode(', ', $this->article->tags ?? ['óleo motor', 'recomendação óleo', 'manutenção'])
        ];

        // VALIDAÇÃO: Só adiciona dados do veículo se marca E modelo existirem
        if (!empty($vehicleInfo['marca']) && !empty($vehicleInfo['modelo'])) {
            // NÃO adiciona dados do veículo no about para evitar erro de Product
            // Apenas adiciona informações básicas como keywords
            $vehicleKeywords = [
                $vehicleInfo['marca'] ?? '',
                $vehicleInfo['modelo'] ?? '',
                $vehicleInfo['categoria'] ?? '',
                'óleo motor',
                'recomendação'
            ];

            // Adiciona keywords do veículo às keywords existentes
            $allKeywords = array_merge(
                $this->article->tags ?? ['óleo motor', 'recomendação óleo', 'manutenção'],
                array_filter($vehicleKeywords)
            );

            $structuredData['keywords'] = implode(', ', array_unique($allKeywords));

            // Se houver recomendação, menciona apenas como texto
            if (!empty($content['recomendacoes_fabricante'])) {
                $structuredData['description'] = $structuredData['description'] .
                    ' Recomendação: ' . ($content['recomendacoes_fabricante']['nome_oleo'] ?? '') .
                    ' ' . ($content['recomendacoes_fabricante']['viscosidade'] ?? '');
            }
        }

        $this->processedData['structured_data'] = $structuredData;

        // URLs canônica e alternativas
        $this->processedData['canonical_url'] = route('info.article.show', $this->article->slug);

        // Breadcrumbs - adiciona URL ao último item para evitar erro
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
                'name' => Str::title($this->article->category_name ?? 'Óleo e Lubrificantes'),
                'url' => route('info.category.show', $this->article->category_slug ?? 'oleo-lubrificantes'),
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
