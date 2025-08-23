<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Services;

/**
 * ClonedArticleValidationService - Validação de artigos clonados
 */
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;

class ClonedArticleValidationService
{
    /**
     * Validar artigo clonado
     */
    public function validateClonedArticle(TirePressureArticle $article): array
    {
        $issues = [];

        // Validar template_used
        $issues = array_merge($issues, $this->validateTemplateUsed($article));

        // Validar título
        $issues = array_merge($issues, $this->validateTitle($article));

        // Validar slug
        $issues = array_merge($issues, $this->validateSlug($article));

        // Validar referência original
        $issues = array_merge($issues, $this->validateOriginalReference($article));

        return $issues;
    }

    /**
     * Validar template_used
     */
    private function validateTemplateUsed(TirePressureArticle $article): array
    {
        $issues = [];
        $expectedTemplates = ['tire_calibration_car', 'tire_calibration_motorcycle'];
        
        if (!in_array($article->template_used, $expectedTemplates)) {
            $issues[] = [
                'type' => 'invalid_template_used',
                'severity' => 'critical',
                'message' => "Template usado inválido: {$article->template_used}",
                'expected' => $expectedTemplates
            ];
        }

        return $issues;
    }

    /**
     * Validar título
     */
    private function validateTitle(TirePressureArticle $article): array
    {
        $issues = [];
        $expectedTitle = "Calibragem do Pneu do {$article->make} {$article->model} {$article->year}";
        
        if ($article->title !== $expectedTitle) {
            $issues[] = [
                'type' => 'incorrect_title',
                'severity' => 'warning',
                'message' => "Título não segue padrão esperado",
                'current' => $article->title,
                'expected' => $expectedTitle
            ];
        }

        return $issues;
    }

    /**
     * Validar slug
     */
    private function validateSlug(TirePressureArticle $article): array
    {
        $issues = [];
        
        if (!str_starts_with($article->slug, 'calibragem-pneu-')) {
            $issues[] = [
                'type' => 'incorrect_slug_pattern',
                'severity' => 'warning',
                'message' => "Slug não segue padrão esperado: calibragem-pneu-[marca]-[modelo]-[ano]",
                'current' => $article->slug
            ];
        }

        // Verificar caracteres válidos
        if (!preg_match('/^[a-z0-9\-]+$/', $article->slug)) {
            $issues[] = [
                'type' => 'invalid_slug_characters',
                'severity' => 'critical',
                'message' => "Slug contém caracteres inválidos",
                'current' => $article->slug
            ];
        }

        return $issues;
    }

    /**
     * Validar referência ao artigo original
     */
    private function validateOriginalReference(TirePressureArticle $article): array
    {
        $issues = [];
        
        if (empty($article->original_calibration_article_id)) {
            $issues[] = [
                'type' => 'missing_original_reference',
                'severity' => 'critical',
                'message' => "Referência ao artigo original ausente"
            ];
        } else {
            // Verificar se artigo original existe
            $originalExists = TirePressureArticle::where('_id', $article->original_calibration_article_id)->exists();
            if (!$originalExists) {
                $issues[] = [
                    'type' => 'invalid_original_reference',
                    'severity' => 'critical',
                    'message' => "Artigo original não encontrado",
                    'original_id' => $article->original_calibration_article_id
                ];
            }
        }

        return $issues;
    }
}