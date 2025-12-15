<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleModel;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleVersion;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;
use Src\GuideDataCenter\Domain\Enums\BlockType;

/**
 * Seeder Técnicos: Câmbio, Arrefecimento, Suspensão
 * CORRIGIDO - busca IDs do MySQL
 */
class GuideTecnicosSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCambio();
        $this->seedArrefecimento();
        $this->seedSuspensao();
    }

    private function seedCambio(): void
    {
        $category = GuideCategory::where('slug', 'cambio')->first();
        if (!$category) return;

        $this->createGuide($category, 'toyota', 'corolla', 'gli', 2025, [
            ['type' => BlockType::HERO->value, 'order' => 1, 'data' => ['title' => 'Câmbio do Toyota Corolla GLi 2025', 'description' => 'Transmissão CVT.', 'badges' => [['text' => 'CVT', 'color' => 'blue']]]],
            ['type' => BlockType::SPECS_GRID->value, 'order' => 2, 'data' => ['heading' => 'Especificações', 'specs' => [['label' => 'Tipo', 'value' => 'CVT'], ['label' => 'Fluido', 'value' => 'CVT Fluid FE']]]],
        ]);
    }

    private function seedArrefecimento(): void
    {
        $category = GuideCategory::where('slug', 'arrefecimento')->first();
        if (!$category) return;

        $this->createGuide($category, 'toyota', 'corolla', 'gli', 2025, [
            ['type' => BlockType::HERO->value, 'order' => 1, 'data' => ['title' => 'Arrefecimento do Toyota Corolla GLi 2025', 'description' => 'Sistema de arrefecimento.', 'badges' => [['text' => 'Selado', 'color' => 'green']]]],
            ['type' => BlockType::SPECS_GRID->value, 'order' => 2, 'data' => ['heading' => 'Sistema', 'specs' => [['label' => 'Capacidade', 'value' => '6.5 litros'], ['label' => 'Tipo', 'value' => 'Long Life']]]],
        ]);
    }

    private function seedSuspensao(): void
    {
        $category = GuideCategory::where('slug', 'suspensao')->first();
        if (!$category) return;

        $this->createGuide($category, 'toyota', 'corolla', 'gli', 2025, [
            ['type' => BlockType::HERO->value, 'order' => 1, 'data' => ['title' => 'Suspensão do Toyota Corolla GLi 2025', 'description' => 'Sistema de suspensão.', 'badges' => [['text' => 'MacPherson', 'color' => 'blue']]]],
            ['type' => BlockType::SPECS_GRID->value, 'order' => 2, 'data' => ['heading' => 'Sistema', 'specs' => [['label' => 'Dianteira', 'value' => 'MacPherson'], ['label' => 'Traseira', 'value' => 'Eixo de torção']]]],
        ]);
    }

    private function createGuide($category, $makeSlug, $modelSlug, $versionSlug, $year, $blocks)
    {
        $make = VehicleMake::where('slug', $makeSlug)->first();
        if (!$make) return;

        $model = VehicleModel::where('slug', $modelSlug)->where('make_id', $make->id)->first();
        if (!$model) return;

        $version = VehicleVersion::where('model_id', $model->id)->where('year', $year)->first();

        Guide::updateOrCreate(
            ['slug' => "{$make->slug}-{$model->slug}-{$versionSlug}-{$year}-{$category->slug}"],
            [
                'vehicle_make_id' => $make->id,
                'vehicle_model_id' => $model->id,
                'vehicle_version_id' => $version?->id,
                'make' => $make->name,
                'make_slug' => $make->slug,
                'model' => $model->name,
                'model_slug' => $model->slug,
                'version' => $version?->name,
                'version_slug' => $versionSlug,
                'year_start' => $year,
                'guide_category_id' => $category->_id,
                'category' => $category->name,
                'category_slug' => $category->slug,
                'template' => 'vehicle_guide',
                'full_title' => "{$category->name} — {$make->name} {$model->name} {$year}",
                'url' => "/guias/{$category->slug}/{$make->slug}/{$model->slug}/{$year}/{$version->slug}",
                'is_active' => true,
                'content_blocks' => $blocks,
            ]
        );

        $this->command->info("✅ Guia de {$category->slug} criado!");
    }
}
