<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;

/**
 * ArticleCloningService - Lógica de clonagem de artigos
 */
class ArticleCloningService
{
    /**
     * Mapeamento de templates
     */
    private const TEMPLATE_MAPPING = [
        'tire_pressure_guide_car' => 'tire_calibration_car',
        'tire_pressure_guide_motorcycle' => 'tire_calibration_motorcycle',
    ];

    /**
     * Clonar artigo calibration
     */
    public function cloneCalibrationArticle(TirePressureArticle $originalArticle, bool $force = false): TirePressureArticle
    {
        // Verificar se já foi clonado
        if ($originalArticle->cloned_from_calibration === true && !$force) {
            throw new \Exception("Artigo já foi clonado anteriormente. Use force=true para forçar.");
        }

        // Preparar dados do clone
        $clonedData = $this->prepareClonedData($originalArticle);

        // Criar novo artigo
        $clonedArticle = TirePressureArticle::create($clonedData);

        // Marcar artigo original
        $this->markOriginalAsCloned($originalArticle, $clonedArticle);

        Log::info("Artigo clonado com sucesso", [
            'original_id' => $originalArticle->_id,
            'cloned_id' => $clonedArticle->_id,
            'vehicle' => "{$originalArticle->make} {$originalArticle->model} {$originalArticle->year}"
        ]);

        return $clonedArticle;
    }

    /**
     * Preparar dados para o clone
     */
    private function prepareClonedData(TirePressureArticle $originalArticle): array
    {
        $clonedData = $originalArticle->toArray();
        
        // Remover ID para criar novo registro
        unset($clonedData['_id']);
        
        // Aplicar transformações
        $clonedData['template_used'] = $this->getNewTemplateUsed($originalArticle->template_used);
        $clonedData['slug'] = $this->generateNewSlug($originalArticle);
        $clonedData['title'] = $this->generateNewTitle($originalArticle);
        $clonedData['wordpress_slug'] = $clonedData['slug'];
        $clonedData['wordpress_url'] = $clonedData['slug'];
        $clonedData['meta_description'] = $this->generateNewMetaDescription($originalArticle);
        
        // Marcar como clonado
        $clonedData['cloned_from_calibration'] = true;
        $clonedData['original_calibration_article_id'] = (string) $originalArticle->_id;
        $clonedData['clone_created_at'] = Carbon::now();
        
        // Atualizar timestamps
        $clonedData['created_at'] = Carbon::now();
        $clonedData['updated_at'] = Carbon::now();
        
        return $clonedData;
    }

    /**
     * Marcar artigo original como clonado
     */
    private function markOriginalAsCloned(TirePressureArticle $originalArticle, TirePressureArticle $clonedArticle): void
    {
        $originalArticle->update([
            'cloned_from_calibration' => true,
            'clone_article_id' => (string) $clonedArticle->_id,
            'clone_created_at' => Carbon::now()
        ]);
    }

    /**
     * Obter novo template_used
     */
    private function getNewTemplateUsed(string $currentTemplate): string
    {
        return self::TEMPLATE_MAPPING[$currentTemplate] ?? $currentTemplate;
    }

    /**
     * Gerar novo slug
     */
    private function generateNewSlug(TirePressureArticle $article): string
    {
        $make = Str::slug($article->make);
        $model = Str::slug($article->model);
        $year = $article->year;
        
        return "calibragem-pneu-{$make}-{$model}-{$year}";
    }

    /**
     * Gerar novo título
     */
    private function generateNewTitle(TirePressureArticle $article): string
    {
        return "Calibragem do Pneu do {$article->make} {$article->model} {$article->year}";
    }

    /**
     * Gerar nova meta description
     */
    private function generateNewMetaDescription(TirePressureArticle $article): string
    {
        return "Guia completo para calibragem do pneu do {$article->make} {$article->model} {$article->year}. Pressões corretas, passo a passo e dicas de manutenção.";
    }
}