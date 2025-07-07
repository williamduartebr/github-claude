<?php

namespace Src\ContentGeneration\TireSchedule\Infrastructure\Services\MicroServices;

use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;

/**
 * 🔍 Micro-Service: Validação e Análise de Dados de Pneus
 * 
 * Responsabilidade única: Validar se artigo precisa de correção
 * Performance: Rápido, sem calls externos
 */
class TireDataValidationService
{
    /**
     * 🎯 Validar se artigo precisa de correção de pressões
     */
    public function needsPressureCorrection(TempArticle $article): array
    {
        $vehicleData = $article->vehicle_data ?? [];
        $pressures = $vehicleData['pressures'] ?? [];
        
        $issues = [];
        $needsUpdate = false;

        // ✅ Validação 1: Pressões zeradas inválidas
        if (($pressures['loaded_front'] ?? 0) == 0 || ($pressures['loaded_rear'] ?? 0) == 0) {
            $issues[] = 'Pressões carregadas zeradas - tecnicamente impossível';
            $needsUpdate = true;
        }

        // ✅ Validação 2: Pressões irreais (muito baixas/altas)
        $emptyFront = $pressures['empty_front'] ?? 0;
        $emptyRear = $pressures['empty_rear'] ?? 0;
        
        if ($emptyFront < 20 || $emptyFront > 50) {
            $issues[] = "Pressão dianteira vazia suspeita: {$emptyFront} PSI";
            $needsUpdate = true;
        }
        
        if ($emptyRear < 20 || $emptyRear > 50) {
            $issues[] = "Pressão traseira vazia suspeita: {$emptyRear} PSI";
            $needsUpdate = true;
        }

        // ✅ Validação 3: Display de pressão inconsistente
        $pressureDisplay = $vehicleData['pressure_display'] ?? '';
        if (strpos($pressureDisplay, '0/0') !== false) {
            $issues[] = 'Display de pressão mostra 0/0 PSI';
            $needsUpdate = true;
        }

        // ✅ Validação 4: Diferença exagerada entre vazio e carregado
        $loadedFront = $pressures['loaded_front'] ?? 0;
        $loadedRear = $pressures['loaded_rear'] ?? 0;
        
        if ($loadedFront > 0 && abs($loadedFront - $emptyFront) > 8) {
            $issues[] = "Diferença exagerada pressão dianteira: {$emptyFront} -> {$loadedFront}";
            $needsUpdate = true;
        }

        return [
            'needs_correction' => $needsUpdate,
            'issues_found' => $issues,
            'current_pressures' => $pressures,
            'priority' => $this->calculatePriority($issues)
        ];
    }

    /**
     * 🎯 Validar se artigo precisa de correção de título/SEO
     */
    public function needsTitleCorrection(TempArticle $article): array
    {
        $seoData = $article->seo_data ?? [];
        $vehicleData = $article->vehicle_data ?? [];
        $content = $article->content ?? [];
        
        $issues = [];
        $needsUpdate = false;

        $vehicleName = $vehicleData['vehicle_name'] ?? 'N/A';
        $vehicleYear = $vehicleData['vehicle_year'] ?? 'N/A';

        // ✅ Validação 1: Título com placeholders
        $pageTitle = $seoData['page_title'] ?? '';
        if (strpos($pageTitle, 'N/A N/A N/A') !== false) {
            $issues[] = 'Título contém placeholders N/A N/A N/A';
            $needsUpdate = true;
        }

        // ✅ Validação 2: Título sem ano do veículo
        if ($vehicleYear !== 'N/A' && strpos($pageTitle, (string)$vehicleYear) === false) {
            $issues[] = "Título não contém ano do veículo: {$vehicleYear}";
            $needsUpdate = true;
        }

        // ✅ Validação 3: Meta description com problemas
        $metaDescription = $seoData['meta_description'] ?? '';
        if (strpos($metaDescription, 'N/A N/A N/A') !== false) {
            $issues[] = 'Meta description contém placeholders';
            $needsUpdate = true;
        }

        // ✅ Validação 4: FAQs com placeholders
        $faqs = $content['perguntas_frequentes'] ?? [];
        $brokenFaqs = 0;
        
        foreach ($faqs as $faq) {
            if (isset($faq['pergunta']) && strpos($faq['pergunta'], 'N/A N/A N/A') !== false) {
                $brokenFaqs++;
            }
        }
        
        if ($brokenFaqs > 0) {
            $issues[] = "{$brokenFaqs} FAQs contêm placeholders N/A";
            $needsUpdate = true;
        }

        return [
            'needs_correction' => $needsUpdate,
            'issues_found' => $issues,
            'broken_faqs_count' => $brokenFaqs,
            'priority' => $this->calculatePriority($issues)
        ];
    }

    /**
     * 🎯 Calcular prioridade baseada nos problemas encontrados
     */
    private function calculatePriority(array $issues): string
    {
        $criticalKeywords = ['impossível', 'suspeita', 'exagerada'];
        $mediumKeywords = ['placeholders', 'não contém'];
        
        foreach ($issues as $issue) {
            foreach ($criticalKeywords as $keyword) {
                if (strpos(strtolower($issue), $keyword) !== false) {
                    return 'high';
                }
            }
        }
        
        foreach ($issues as $issue) {
            foreach ($mediumKeywords as $keyword) {
                if (strpos(strtolower($issue), $keyword) !== false) {
                    return 'medium';
                }
            }
        }
        
        return 'low';
    }

    /**
     * 🎯 Gerar pressões realistas baseadas nos dados atuais
     */
    public function generateRealisticPressures(array $currentPressures, bool $isMotorcycle = false): array
    {
        $emptyFront = $currentPressures['empty_front'] ?? 32;
        $emptyRear = $currentPressures['empty_rear'] ?? 32;
        
        // ✅ Para motos, diferença menor entre vazio e carregado
        $frontIncrease = $isMotorcycle ? rand(2, 3) : rand(3, 5);
        $rearIncrease = $isMotorcycle ? rand(2, 4) : rand(3, 5);
        
        $loadedFront = $emptyFront + $frontIncrease;
        $loadedRear = $emptyRear + $rearIncrease;
        
        return [
            'empty_front' => $emptyFront,
            'empty_rear' => $emptyRear,
            'loaded_front' => $loadedFront,
            'loaded_rear' => $loadedRear,
            'max_front' => $loadedFront + 2,
            'max_rear' => $loadedRear + 2,
            'spare' => $isMotorcycle ? 0 : 35,
            'pressure_display' => "{$emptyFront}/{$emptyRear} PSI",
            'pressure_loaded_display' => "{$loadedFront}/{$loadedRear} PSI"
        ];
    }

    /**
     * 🎯 Validar integridade completa do artigo
     */
    public function validateArticleIntegrity(TempArticle $article): array
    {
        $pressureValidation = $this->needsPressureCorrection($article);
        $titleValidation = $this->needsTitleCorrection($article);
        
        $allIssues = array_merge(
            $pressureValidation['issues_found'],
            $titleValidation['issues_found']
        );
        
        $overallPriority = 'low';
        if ($pressureValidation['priority'] === 'high' || $titleValidation['priority'] === 'high') {
            $overallPriority = 'high';
        } elseif ($pressureValidation['priority'] === 'medium' || $titleValidation['priority'] === 'medium') {
            $overallPriority = 'medium';
        }
        
        return [
            'needs_any_correction' => $pressureValidation['needs_correction'] || $titleValidation['needs_correction'],
            'needs_pressure_correction' => $pressureValidation['needs_correction'],
            'needs_title_correction' => $titleValidation['needs_correction'],
            'total_issues' => count($allIssues),
            'all_issues' => $allIssues,
            'overall_priority' => $overallPriority,
            'pressure_details' => $pressureValidation,
            'title_details' => $titleValidation,
            'validated_at' => now()->toISOString()
        ];
    }

    /**
     * 🎯 Análise rápida em lote para múltiplos artigos
     */
    public function validateBatch(array $articles, int $limit = 100): array
    {
        $results = [
            'analyzed' => 0,
            'needs_pressure_correction' => 0,
            'needs_title_correction' => 0,
            'high_priority' => 0,
            'medium_priority' => 0,
            'low_priority' => 0,
            'articles_details' => []
        ];
        
        $processed = 0;
        foreach ($articles as $article) {
            if ($processed >= $limit) break;
            
            $validation = $this->validateArticleIntegrity($article);
            
            $results['analyzed']++;
            if ($validation['needs_pressure_correction']) {
                $results['needs_pressure_correction']++;
            }
            if ($validation['needs_title_correction']) {
                $results['needs_title_correction']++;
            }
            
            switch ($validation['overall_priority']) {
                case 'high':
                    $results['high_priority']++;
                    break;
                case 'medium':
                    $results['medium_priority']++;
                    break;
                default:
                    $results['low_priority']++;
            }
            
            if ($validation['needs_any_correction']) {
                $results['articles_details'][] = [
                    'slug' => $article->slug,
                    'vehicle_name' => $article->vehicle_data['vehicle_name'] ?? 'N/A',
                    'priority' => $validation['overall_priority'],
                    'issues_count' => $validation['total_issues'],
                    'needs_pressure' => $validation['needs_pressure_correction'],
                    'needs_title' => $validation['needs_title_correction']
                ];
            }
            
            $processed++;
        }
        
        $results['correction_rate'] = $results['analyzed'] > 0 ? 
            round((($results['needs_pressure_correction'] + $results['needs_title_correction']) / $results['analyzed']) * 100, 2) : 0;
            
        return $results;
    }
}