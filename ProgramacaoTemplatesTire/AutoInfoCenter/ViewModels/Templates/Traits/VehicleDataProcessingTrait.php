<?php

namespace Src\AutoInfoCenter\ViewModels\Templates\Traits;


use Illuminate\Support\Str;

trait VehicleDataProcessingTrait
{
    /**
     * Processa informações básicas do veículo
     */
    private function processVehicleInfo(): array
    {
        $vehicleInfo = $this->article->extracted_entities ?? [];

        return [
            'full_name' => $this->getVehicleFullName(),
            'make' => $vehicleInfo['marca'] ?? '',
            'model' => $vehicleInfo['modelo'] ?? '',
            'year' => $vehicleInfo['ano'] ?? '',
            'category' => $vehicleInfo['categoria'] ?? '',
            'engine' => $vehicleInfo['motorizacao'] ?? '',
            'version' => $vehicleInfo['versao'] ?? '',
            'fuel' => $vehicleInfo['combustivel'] ?? '',
            'image_url' => $this->getVehicleImageUrl(),
            'slug' => $this->generateSlug($vehicleInfo),
            'is_premium' => $this->isPremiumVehicle(),
            'segment' => $this->getVehicleSegment()
        ];
    }

    /**
     * Obtém nome completo do veículo
     */
    private function getVehicleFullName(): string
    {
        $vehicleInfo = $this->article->extracted_entities ?? [];

        if (empty($vehicleInfo['marca']) || empty($vehicleInfo['modelo'])) {
            return '';
        }

        $make = $vehicleInfo['marca'] ?? '';
        $model = $vehicleInfo['modelo'] ?? '';
        $year = $vehicleInfo['ano'] ?? '';

        return trim("{$make} {$model} {$year}");
    }

    /**
     * Obtém URL da imagem do veículo
     */
    private function getVehicleImageUrl(): string
    {
        $vehicleInfo = $this->article->extracted_entities ?? [];
        $makeSlug = strtolower($vehicleInfo['marca'] ?? '');
        $modelSlug = strtolower(str_replace(' ', '-', $vehicleInfo['modelo'] ?? ''));
        $year = $vehicleInfo['ano'] ?? '';

        $vehicleType = $this->getVehicleTypeForImage();
        $basePath = "https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/{$vehicleType}";

        return "{$basePath}/{$makeSlug}-{$modelSlug}-{$year}.jpg";
    }

    /**
     * Gera slug baseado nos dados do veículo
     */
    private function generateSlug(array $vehicleInfo): string
    {
        $make = strtolower($vehicleInfo['marca'] ?? '');
        $model = strtolower(str_replace(' ', '-', $vehicleInfo['modelo'] ?? ''));

        return "{$make}-{$model}";
    }

    /**
     * Obtém URL canônica do artigo
     */
    private function getCanonicalUrl(): string
    {
        return $this->article->canonical_url ?? route('info.article.show', $this->article->slug);
    }

    /**
     * Processa breadcrumbs para navegação
     */
    private function getBreadcrumbs(): array
    {
        return [
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
                'name' => Str::title($this->article->category_name ?? 'Informações'),
                'url' => route('info.category.show', $this->article->category_slug ?? 'informacoes'),
                'position' => 3
            ],
            [
                'name' => $this->article->title,
                'url' => route('info.article.show', $this->article->slug),
                'position' => 4
            ],
        ];
    }

    // Métodos abstratos que devem ser implementados nas ViewModels específicas
    abstract protected function getVehicleTypeForImage(): string;
    abstract protected function isPremiumVehicle(): bool;
    abstract protected function getVehicleSegment(): string;
}