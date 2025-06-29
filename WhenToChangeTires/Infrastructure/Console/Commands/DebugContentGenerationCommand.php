<?php

namespace Src\ContentGeneration\WhenToChangeTires\Infrastructure\Console\Commands;


use App\ContentGeneration\WhenToChangeTires\Infrastructure\Services\VehicleDataProcessorService;
use App\ContentGeneration\WhenToChangeTires\Infrastructure\Services\TemplateBasedContentService;
use App\ContentGeneration\WhenToChangeTires\Domain\ValueObjects\VehicleData;
use Illuminate\Console\Command;

class DebugContentGenerationCommand extends Command
{
    protected $signature = 'when-to-change-tires:debug-content {--vehicle-make=Honda} {--vehicle-model=Civic} {--vehicle-year=2022}';
    protected $description = 'Debug da geração de conteúdo para um veículo específico';

    public function __construct(
        protected VehicleDataProcessorService $vehicleProcessor,
        protected TemplateBasedContentService $contentService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🔍 Debug da Geração de Conteúdo');

        try {
            // 1. Carregar veículos e encontrar o específico
            $vehicles = $this->vehicleProcessor->importFromCsv('todos_veiculos.csv');
            
            $make = $this->option('vehicle-make');
            $model = $this->option('vehicle-model');
            $year = (int) $this->option('vehicle-year');
            
            $vehicle = $vehicles->first(function (VehicleData $v) use ($make, $model, $year) {
                return strtolower($v->make) === strtolower($make) 
                    && strtolower($v->model) === strtolower($model)
                    && $v->year === $year;
            });

            if (!$vehicle) {
                $this->error("❌ Veículo não encontrado: {$make} {$model} {$year}");
                return 1;
            }

            $this->info("✅ Veículo encontrado: {$vehicle->getVehicleIdentifier()}");
            $this->line("");

            // 2. Mostrar dados do veículo
            $this->info("📋 DADOS DO VEÍCULO:");
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['Make', $vehicle->make],
                    ['Model', $vehicle->model],
                    ['Year', $vehicle->year],
                    ['Tire Size', $vehicle->tireSize],
                    ['Pressure Front', $vehicle->pressureEmptyFront . ' PSI'],
                    ['Pressure Rear', $vehicle->pressureEmptyRear . ' PSI'],
                    ['Category', $vehicle->category],
                    ['Vehicle Type', $vehicle->getVehicleType()],
                    ['Recommended Oil', $vehicle->recommendedOil ?? 'N/A']
                ]
            );

            // 3. Gerar conteúdo
            $this->info("🎨 Gerando conteúdo...");
            $content = $this->contentService->generateTireChangeArticle($vehicle);

            // 4. Mostrar detalhes de validação
            $this->info("✅ Conteúdo gerado! Validando...");
            $validationDetails = $content->getValidationDetails();
            
            $this->table(
                ['Validação', 'Status'],
                [
                    ['Has Title', $validationDetails['has_title'] ? '✅' : '❌'],
                    ['Has Slug', $validationDetails['has_slug'] ? '✅' : '❌'],
                    ['Has Content', $validationDetails['has_content'] ? '✅' : '❌'],
                    ['Has Introdução', $validationDetails['has_introducao'] ? '✅' : '❌'],
                    ['Has Meta Description', $validationDetails['has_meta_description'] ? '✅' : '❌'],
                    ['Has Vehicle Info', $validationDetails['has_vehicle_info'] ? '✅' : '❌'],
                    ['Word Count', $validationDetails['word_count']],
                    ['IS VALID', $validationDetails['is_valid'] ? '✅ SIM' : '❌ NÃO']
                ]
            );

            // 5. Mostrar seções de conteúdo
            $this->info("📝 SEÇÕES DE CONTEÚDO:");
            foreach ($validationDetails['content_sections'] as $section) {
                $this->line("  • {$section}");
            }

            // 6. Mostrar título e slug
            $this->line("");
            $this->info("📋 METADADOS:");
            $this->line("Título: " . $content->title);
            $this->line("Slug: " . $content->slug);
            $this->line("Template: " . $content->template);

            // 7. Se inválido, mostrar mais detalhes
            if (!$content->isValid()) {
                $this->line("");
                $this->error("❌ CONTEÚDO INVÁLIDO - DETALHES:");
                
                // Verificar o que está faltando
                if (empty($content->title)) {
                    $this->line("  • Título vazio");
                }
                if (empty($content->slug)) {
                    $this->line("  • Slug vazio");
                }
                if (empty($content->content)) {
                    $this->line("  • Conteúdo vazio");
                }
                if (!isset($content->content['introducao']) || empty($content->content['introducao'])) {
                    $this->line("  • Introdução ausente ou vazia");
                }
                if (empty($content->seoData['meta_description'])) {
                    $this->line("  • Meta description vazia");
                }
                if (empty($content->vehicleInfo['make']) || empty($content->vehicleInfo['model'])) {
                    $this->line("  • Informações do veículo incompletas");
                }
            } else {
                $this->line("");
                $this->info("✅ CONTEÚDO VÁLIDO!");
                
                // Mostrar preview da introdução
                if (isset($content->content['introducao'])) {
                    $intro = $content->content['introducao'];
                    $preview = strlen($intro) > 200 ? substr($intro, 0, 200) . '...' : $intro;
                    $this->line("");
                    $this->info("📖 PREVIEW DA INTRODUÇÃO:");
                    $this->line($preview);
                }
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erro durante debug: " . $e->getMessage());
            $this->line("Stack trace:");
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
}