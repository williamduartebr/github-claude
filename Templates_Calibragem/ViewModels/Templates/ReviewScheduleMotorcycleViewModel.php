<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Src\AutoInfoCenter\ViewModels\Templates\TemplateViewModel;
use Illuminate\Support\Str;

class ReviewScheduleMotorcycleViewModel extends TemplateViewModel
{
    /**
     * Nome do template a ser utilizado
     */
    protected string $templateName = 'review_schedule_motorcycle';

    /**
     * Processa dados específicos do template de cronograma de revisões para motocicletas
     */
    protected function processTemplateSpecificData(): void
    {
        // Extrai dados específicos do conteúdo do artigo
        $content = $this->article->content;

        // Introdução
        $this->processedData['introduction'] = $content['introducao'] ?? '';

        // Visão geral das revisões (tabela resumo)
        $this->processedData['overview_schedule'] = $this->processOverviewSchedule($content['visao_geral_revisoes'] ?? []);

        // Cronograma detalhado por revisão
        $this->processedData['detailed_schedule'] = $this->processDetailedSchedule($content['cronograma_detalhado'] ?? []);

        // Manutenção preventiva entre revisões
        $this->processedData['preventive_maintenance'] = $this->processPreventiveMaintenance($content['manutencao_preventiva'] ?? []);

        // Peças que exigem atenção especial (específico para motos)
        $this->processedData['critical_parts'] = $this->processCriticalParts($content['pecas_atencao'] ?? []);

        // Especificações técnicas
        $this->processedData['technical_specs'] = $content['especificacoes_tecnicas'] ?? [];

        // Garantia e recomendações
        $this->processedData['warranty_info'] = $this->processWarrantyInfo($content['garantia_recomendacoes'] ?? []);

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
     * Processa dados estruturados para SEO específicos do cronograma de revisões de motocicletas
     */
    private function processStructuredDataForSEO(): void
    {
        $vehicleInfo = $this->article->vehicle_info ?? [];
        $content = $this->article->content;

        $imageDefault = "https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/revisoes-motocicleta.png";

        // Constrói informações estruturadas para manutenção de motocicletas
        $this->processedData['structured_data'] = [
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => $this->article->title,
            'description' => $content['introducao'] ?? '',
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
            'image' => [
                '@type' => 'ImageObject',
                'url' => $imageDefault,
                'width' => 1200,
                'height' => 630
            ],
            'totalTime' => 'PT1H30M', // Tempo estimado para revisão de moto (menor que carro)
            'estimatedCost' => [
                '@type' => 'MonetaryAmount',
                'currency' => 'BRL',
                'value' => $this->extractAverageCost($content['visao_geral_revisoes'] ?? [])
            ],
            'step' => $this->buildMaintenanceSteps($content['cronograma_detalhado'] ?? []),
            'about' => [
                '@type' => 'Motorcycle',
                'brand' => $vehicleInfo['make'] ?? '',
                'model' => $vehicleInfo['model'] ?? '',
                'modelDate' => $vehicleInfo['year'] ?? '',
                'vehicleEngine' => [
                    '@type' => 'EngineSpecification',
                    'engineDisplacement' => $vehicleInfo['engine'] ?? ''
                ]
            ]
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

    /**
     * Processa dados da visão geral das revisões
     */
    private function processOverviewSchedule(array $overview): array
    {
        return array_map(function ($item) {
            return [
                'revisao' => $item['revisao'] ?? '',
                'intervalo' => $item['intervalo'] ?? '',
                'principais_servicos' => $item['principais_servicos'] ?? '',
                'estimativa_custo' => $item['estimativa_custo'] ?? '',
            ];
        }, $overview);
    }

    /**
     * Processa cronograma detalhado
     */
    private function processDetailedSchedule(array $schedule): array
    {
        return array_map(function ($item) {
            return [
                'numero_revisao' => $item['numero_revisao'] ?? '',
                'intervalo' => $item['intervalo'] ?? '',
                'km' => $this->formatKilometers($item['km'] ?? ''),
                'servicos_principais' => $item['servicos_principais'] ?? [],
                'verificacoes_complementares' => $item['verificacoes_complementares'] ?? [],
                'estimativa_custo' => $item['estimativa_custo'] ?? '',
                'observacoes' => $item['observacoes'] ?? '',
            ];
        }, $schedule);
    }

    /**
     * Processa manutenção preventiva específica para motocicletas
     */
    private function processPreventiveMaintenance(array $maintenance): array
    {
        return [
            'verificacoes_mensais' => $maintenance['verificacoes_mensais'] ?? [],
            'verificacoes_trimestrais' => $maintenance['verificacoes_trimestrais'] ?? [],
            'verificacoes_anuais' => $maintenance['verificacoes_anuais'] ?? [],
        ];
    }

    /**
     * Processa peças críticas específicas para motocicletas
     */
    private function processCriticalParts(array $parts): array
    {
        return array_map(function ($part) {
            return [
                'componente' => $part['componente'] ?? '',
                'intervalo_recomendado' => $part['intervalo_recomendado'] ?? '',
                'observacao' => $part['observacao'] ?? '',
            ];
        }, $parts);
    }

    /**
     * Processa informações de garantia
     */
    private function processWarrantyInfo(array $warranty): array
    {
        return [
            'prazo_garantia' => $warranty['prazo_garantia'] ?? '',
            'garantia_anticorrosao' => $warranty['garantia_anticorrosao'] ?? '',
            'garantia_itens_desgaste' => $warranty['garantia_itens_desgaste'] ?? '',
            'observacoes_importantes' => $warranty['observacoes_importantes'] ?? '',
            'dicas_vida_util' => $warranty['dicas_vida_util'] ?? [],
        ];
    }

    /**
     * Extrai custo médio das revisões para dados estruturados
     */
    private function extractAverageCost(array $overview): string
    {
        if (empty($overview)) {
            return '300'; // Menor que carros
        }

        $costs = [];
        foreach ($overview as $item) {
            $costString = $item['estimativa_custo'] ?? '';
            // Extrai números do formato "R$ 390 - R$ 450"
            preg_match_all('/\d+/', $costString, $matches);
            if (!empty($matches[0])) {
                $costs[] = (int) $matches[0][0]; // Pega o primeiro valor
            }
        }

        return empty($costs) ? '300' : (string) intval(array_sum($costs) / count($costs));
    }

    /**
     * Constrói passos estruturados para dados de SEO
     */
    private function buildMaintenanceSteps(array $schedule): array
    {
        return array_map(function ($item, $index) {
            return [
                '@type' => 'HowToStep',
                'position' => $index + 1,
                'name' => "{$item['numero_revisao']}ª Revisão - {$item['intervalo']}",
                'text' => $item['observacoes'] ?? "Revisão programada conforme especificação do fabricante",
                'supply' => array_merge(
                    $item['servicos_principais'] ?? [],
                    $item['verificacoes_complementares'] ?? []
                ),
                'tool' => [
                    'Ferramentas especializadas para motocicletas',
                    'Equipamentos de diagnóstico',
                    'Peças originais ou homologadas',
                    'Suporte para elevação da moto'
                ]
            ];
        }, $schedule, array_keys($schedule));
    }

    /**
     * Formata quilometragem para exibição compacta
     */
    private function formatKilometers(string $km): string
    {
        // Remove pontos e converte para número
        $number = (int) str_replace('.', '', $km);

        if ($number >= 1000) {
            return ($number / 1000) . 'k';
        }

        return (string) $number;
    }

    /**
     * Retorna o nome completo do veículo
     */
    private function getVehicleFullName(): string
    {
        $vehicleInfo = $this->article->extracted_entities ?? [];
        return sprintf(
            '%s %s %s',
            $vehicleInfo['marca'] ?? '',
            $vehicleInfo['modelo'] ?? '',
            $vehicleInfo['ano'] ?? ''
        );
    }
}
