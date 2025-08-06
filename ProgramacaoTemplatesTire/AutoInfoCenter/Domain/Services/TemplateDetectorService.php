<?php

namespace Src\AutoInfoCenter\Domain\Services;

use Src\AutoInfoCenter\Domain\Eloquent\Article;

class TemplateDetectorService
{

    /**
     * Mapeamento de tipos de template para identificadores internos
     */
    private const TEMPLATE_MAPPINGS = [
        'oil_recommendation' => 'oil_recommendation',
        'tire_recommendation' => 'tire_recommendation',
        'oil_table' => 'oil_table',
        'review_schedule_car' => 'review_schedule_car',
        'review_schedule_motorcycle' => 'review_schedule_motorcycle',
        'review_schedule_hybrid' => 'review_schedule_hybrid',
        'review_schedule_electric' => 'review_schedule_electric', 
        'when_to_change_tires' =>'when_to_change_tires',        
            'tire_pressure_guide_car' => 'tire_pressure_guide_car',
            'tire_pressure_guide_motorcycle' => 'tire_pressure_guide_motorcycle',
            'ideal_tire_pressure_car' => 'ideal_tire_pressure_car',
            'ideal_tire_pressure_motorcycle' => 'ideal_tire_pressure_motorcycle',// Novo           

        'tire_pressure' => 'tire_pressure',
        'air_filter' => 'air_filter',
        'maintenance_schedule' => 'maintenance_schedule',
        'battery_guide' => 'battery_guide',
        'fluid_guide' => 'fluid_guide',
        'fuel_consumption' => 'fuel_consumption',
        'parts_recommendation' => 'parts_recommendation',
        'repair_guide' => 'repair_guide',
        'troubleshooting' => 'troubleshooting',
        // Adicione mais mapeamentos conforme necessário
    ];

    /**
     * Template padrão caso não seja possível detectar
     */
    private const DEFAULT_TEMPLATE = 'generic_article';

    /**
     * Detecta o tipo de template com base no campo template do artigo
     *
     * @param Article $article
     * @return string
     */
    public function detectTemplate(Article $article): string
    {
        // Verifica especificamente o campo template
        $template = $article->template ?? null;

        // Se o template existe e está no mapeamento, retorna o valor mapeado
        if ($template && isset(self::TEMPLATE_MAPPINGS[$template])) {
            return self::TEMPLATE_MAPPINGS[$template];
        }

        // Caso contrário, retorna o template genérico
        return self::DEFAULT_TEMPLATE;
    }
}
