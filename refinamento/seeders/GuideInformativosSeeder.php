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
 * Seeder Informativos: Problemas Comuns, Recalls, Comparações
 * CORRIGIDO - busca IDs do MySQL
 */
class GuideInformativosSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedProblemasComuns();
        $this->seedRecalls();
        $this->seedComparacoes();
    }

    private function seedProblemasComuns(): void
    {
        $category = GuideCategory::where('slug', 'problemas-comuns')->first();
        if (!$category) return;

        $this->createGuide($category, 'toyota', 'corolla', 'gli', 2025, [
            ['type' => BlockType::HERO->value, 'order' => 1, 'data' => ['title' => 'Problemas Comuns - Toyota Corolla GLi 2025', 'description' => 'Problemas relatados por proprietários.', 'badges' => [['text' => 'Baseado em Relatos', 'color' => 'yellow']]]],
            ['type' => BlockType::TABLE->value, 'order' => 2, 'data' => ['heading' => 'Problemas por Km', 'headers' => ['Km', 'Problema', 'Solução'], 'rows' => [['0-30k', 'Ruído elétrico', 'Revisão'], ['60k+', 'Bomba combustível', 'Troca']]]],
            ['type' => BlockType::LIST->value, 'order' => 3, 'data' => ['heading' => 'Mais Relatados', 'items' => ['Condensação nos faróis', 'Ruído na direção', 'Borracha ressecando']]],
        ]);
    }

    private function seedRecalls(): void
    {
        $category = GuideCategory::where('slug', 'recalls')->first();
        if (!$category) return;

        $this->createGuide($category, 'toyota', 'corolla', 'gli', 2025, [
            ['type' => BlockType::HERO->value, 'order' => 1, 'data' => ['title' => 'Recalls - Toyota Corolla GLi 2025', 'description' => 'Campanhas oficiais de recall.', 'badges' => [['text' => 'Atualizado', 'color' => 'green']]]],
            ['type' => BlockType::TABLE->value, 'order' => 2, 'data' => ['heading' => 'Recalls Oficiais', 'headers' => ['Ano', 'Código', 'Motivo', 'Status'], 'rows' => [['2024', 'R-2024-001', 'Airbag', 'Ativo'], ['2023', 'R-2023-045', 'Cinto', 'Concluído']]]],
            ['type' => BlockType::LIST->value, 'order' => 3, 'data' => ['heading' => 'Como Verificar', 'items' => ['Site da Toyota com chassi', 'SAC: 0800-703-0202', 'Concessionária']]],
        ]);
    }

    private function seedComparacoes(): void
    {
        $category = GuideCategory::where('slug', 'comparacoes')->first();
        if (!$category) return;

        $this->createGuide($category, 'toyota', 'corolla', 'gli', 2025, [
            ['type' => BlockType::HERO->value, 'order' => 1, 'data' => ['title' => 'Comparativo - Corolla GLi vs XEi vs Altis 2025', 'description' => 'Comparação entre versões.', 'badges' => [['text' => 'Análise Completa', 'color' => 'blue']]]],
            ['type' => BlockType::TABLE->value, 'order' => 2, 'data' => ['heading' => 'Especificações', 'headers' => ['Item', 'GLi', 'XEi', 'Altis'], 'rows' => [['Motor', '2.0 Flex', '2.0 Flex', '2.0 Hybrid'], ['Preço', 'R$ 135k', 'R$ 145k', 'R$ 165k']]]],
            ['type' => BlockType::LIST->value, 'order' => 3, 'data' => ['heading' => 'Diferenças GLi', 'items' => ['Melhor custo-benefício', 'Bancos em tecido', 'Rodas 16"']]],
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
                'url' => "/guias/{$category->slug}/{$make->slug}/{$model->slug}/{$year}/{$versionSlug}",
                'is_active' => true,
                'content_blocks' => $blocks,
            ]
        );

        $this->command->info("✅ Guia de {$category->slug} criado!");
    }
}
