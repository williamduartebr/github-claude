<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;
use Illuminate\Support\Facades\Log;

class FixTemplateUsedCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tire-pressure-guide:fix-template-used 
                           {--dry-run : Preview changes without executing}
                           {--limit=100 : Number of articles to process per batch}
                           {--make= : Filter by specific make}';

    /**
     * The console command description.
     */
    protected $description = 'Fix template_used field in TirePressureArticles to use correct template identifiers';

    /**
     * Mapeamento de template_type + vehicle_type para templates corretos
     */
    private const TEMPLATE_MAPPING = [
        // CARROS
        'ideal_car' => 'ideal_tire_pressure_car',
        'calibration_car' => 'tire_pressure_guide_car',

        // MOTOS  
        'ideal_motorcycle' => 'ideal_tire_pressure_motorcycle',
        'calibration_motorcycle' => 'tire_pressure_guide_motorcycle',

        // FALLBACKS (casos antigos)
        'ideal_unknown' => 'ideal_tire_pressure_car',
        'calibration_unknown' => 'tire_pressure_guide_car',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Iniciando correção do campo template_used...');

        $dryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $makeFilter = $this->option('make');

        if ($dryRun) {
            $this->warn('⚠️  MODO DRY-RUN - Nenhuma alteração será salva');
        }

        // Estatísticas
        $stats = [
            'total_processed' => 0,
            'cars_updated' => 0,
            'motorcycles_updated' => 0,
            'vehicle_type_fixed' => 0,
            'errors' => 0,
            'by_template' => []
        ];

        try {
            // Buscar artigos em lotes
            $query = TirePressureArticle::query();

            if ($makeFilter) {
                $query->where('vehicle_data.make', $makeFilter);
                $this->info("📝 Filtro aplicado: make = {$makeFilter}");
            }

            $totalArticles = $query->count();
            $this->info("📊 Total de artigos encontrados: {$totalArticles}");

            // Processar em lotes
            $offset = 0;
            $progressBar = $this->output->createProgressBar($totalArticles);
            $progressBar->start();

            while ($offset < $totalArticles) {
                $articles = $query->offset($offset)->limit($limit)->get();

                if ($articles->isEmpty()) {
                    break;
                }

                foreach ($articles as $article) {
                    $result = $this->processArticle($article, $dryRun);

                    $stats['total_processed']++;

                    if ($result['updated']) {
                        if ($result['vehicle_type'] === 'car') {
                            $stats['cars_updated']++;
                        } else {
                            $stats['motorcycles_updated']++;
                        }

                        if ($result['vehicle_type_fixed']) {
                            $stats['vehicle_type_fixed']++;
                        }

                        $templateKey = $result['template_type'] . '_' . $result['vehicle_type'];
                        $stats['by_template'][$templateKey] = ($stats['by_template'][$templateKey] ?? 0) + 1;
                    }

                    if ($result['error']) {
                        $stats['errors']++;
                    }

                    $progressBar->advance();
                }

                $offset += $limit;

                // Dar uma pausa para não sobrecarregar
                if (!$dryRun) {
                    usleep(100000); // 0.1 segundo
                }
            }

            $progressBar->finish();
            $this->newLine(2);

            // Mostrar relatório final
            $this->displayFinalReport($stats, $dryRun);
        } catch (\Exception $e) {
            $this->error("❌ Erro durante execução: " . $e->getMessage());
            Log::error("Erro no FixTemplateUsedCommand", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }

    /**
     * Processar um artigo individual
     */
    private function processArticle(TirePressureArticle $article, bool $dryRun): array
    {
        $result = [
            'updated' => false,
            'vehicle_type' => 'unknown',
            'template_type' => 'unknown',
            'vehicle_type_fixed' => false,
            'error' => false,
            'old_template_used' => $article->template_used ?? 'null',
            'new_template_used' => null
        ];

        try {
            $vehicleData = $article->vehicle_data ?? [];
            $templateType = $article->template_type ?? 'ideal';

            // 1. Corrigir vehicle_type se necessário
            $vehicleType = $this->determineCorrectVehicleType($vehicleData);

            if (($vehicleData['vehicle_type'] ?? '') !== $vehicleType) {
                $vehicleData['vehicle_type'] = $vehicleType;
                $result['vehicle_type_fixed'] = true;
            }

            $result['vehicle_type'] = $vehicleType;
            $result['template_type'] = $templateType;

            // 2. Determinar template_used correto
            $correctTemplateUsed = $this->getCorrectTemplateUsed($templateType, $vehicleType);
            $result['new_template_used'] = $correctTemplateUsed;

            // 3. Verificar se precisa atualizar
            $currentTemplateUsed = $article->template_used ?? '';

            if ($currentTemplateUsed !== $correctTemplateUsed || $result['vehicle_type_fixed']) {

                if (!$dryRun) {
                    // Atualizar o artigo
                    $updateData = [
                        'template_used' => $correctTemplateUsed
                    ];

                    if ($result['vehicle_type_fixed']) {
                        $updateData['vehicle_data'] = $vehicleData;
                    }

                    $article->update($updateData);
                }

                $result['updated'] = true;
            }
        } catch (\Exception $e) {
            $result['error'] = true;
            Log::warning("Erro ao processar artigo {$article->_id}", [
                'error' => $e->getMessage(),
                'article_id' => $article->_id
            ]);
        }

        return $result;
    }

    /**
     * Determinar o vehicle_type correto
     */
    private function determineCorrectVehicleType(array $vehicleData): string
    {
        // Método 1: Campo is_motorcycle
        if (isset($vehicleData['is_motorcycle']) && $vehicleData['is_motorcycle'] === true) {
            return 'motorcycle';
        }

        // Método 2: vehicle_type já é 'motorcycle'
        if (($vehicleData['vehicle_type'] ?? '') === 'motorcycle') {
            return 'motorcycle';
        }

        // Método 3: main_category começa com 'motorcycle_'
        $mainCategory = $vehicleData['main_category'] ?? '';
        if (str_starts_with($mainCategory, 'motorcycle_')) {
            return 'motorcycle';
        }

        // Método 4: category_normalized contém 'Motocicleta'
        $categoryNormalized = $vehicleData['category_normalized'] ?? '';
        if (
            str_contains($categoryNormalized, 'Motocicleta') ||
            str_contains($categoryNormalized, 'Scooter') ||
            $categoryNormalized === 'Motorcycle'
        ) {
            return 'motorcycle';
        }

        // Todos os outros são carros
        return 'car';
    }

    /**
     * Obter template_used correto baseado no template_type e vehicle_type
     */
    private function getCorrectTemplateUsed(string $templateType, string $vehicleType): string
    {
        $key = $templateType . '_' . $vehicleType;

        return self::TEMPLATE_MAPPING[$key] ?? self::TEMPLATE_MAPPING[$templateType . '_unknown'];
    }

    /**
     * Exibir relatório final
     */
    private function displayFinalReport(array $stats, bool $dryRun): void
    {
        $this->info('📊 RELATÓRIO FINAL:');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total processados', number_format($stats['total_processed'])],
                ['Carros atualizados', number_format($stats['cars_updated'])],
                ['Motos atualizadas', number_format($stats['motorcycles_updated'])],
                ['Vehicle_type corrigidos', number_format($stats['vehicle_type_fixed'])],
                ['Erros', number_format($stats['errors'])]
            ]
        );

        if (!empty($stats['by_template'])) {
            $this->newLine();
            $this->info('📋 Por tipo de template:');

            $templateRows = [];
            foreach ($stats['by_template'] as $template => $count) {
                $templateRows[] = [$template, number_format($count)];
            }

            $this->table(['Template + Tipo', 'Quantidade'], $templateRows);
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('⚠️  MODO DRY-RUN: Para aplicar as alterações, execute sem --dry-run');
        } else {
            $this->newLine();
            $this->info('✅ Correções aplicadas com sucesso!');
        }

        // Sugestões de próximos passos
        $this->newLine();
        $this->info('🎯 PRÓXIMOS PASSOS RECOMENDADOS:');
        $this->line('1. Verificar se os templates estão corretos:');
        $this->line('   php artisan tinker');
        $this->line('   TirePressureArticle::pluck("template_used")->unique()->values()');
        $this->line('');
        $this->line('2. Iniciar refinamento das seções (Fase 2):');
        $this->line('   php artisan tire-pressure-guide:refine-sections');
    }
}
