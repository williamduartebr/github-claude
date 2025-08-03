<?php

namespace Src\VehicleData\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\VehicleData\Domain\Entities\VehicleData;

/**
 * Command para validar dados de veículos
 * 
 * Executa validações de qualidade, consistência e integridade
 * nos dados armazenados na collection vehicle_data
 */
class ValidateVehicleDataCommand extends Command
{
    protected $signature = 'vehicle-data:validate
                           {--fix : Corrigir problemas automaticamente quando possível}
                           {--make= : Validar apenas uma marca específica}
                           {--category= : Validar apenas uma categoria específica}
                           {--score-threshold=6.0 : Score mínimo de qualidade aceitável}
                           {--detailed : Exibir relatório detalhado}';

    protected $description = 'Validar qualidade e consistência dos dados de veículos';

    protected array $validationResults = [];
    protected int $validatedCount = 0;
    protected int $issuesFound = 0;
    protected int $issuesFixed = 0;

    /**
     * Executar validação
     */
    public function handle(): ?int
    {
        $this->info('🔍 Iniciando validação de dados de veículos...');

        $fix = $this->option('fix');
        $make = $this->option('make');
        $category = $this->option('category');
        $scoreThreshold = (float) $this->option('score-threshold');
        $detailed = $this->option('detailed');

        try {
            // Executar validações
            $this->runBasicValidations($make, $category, $fix);
            $this->runQualityScoreValidation($scoreThreshold, $fix);
            $this->runConsistencyValidations($fix);
            $this->runIntegrityValidations($fix);

            // Exibir resultados
            $this->displayValidationResults($detailed);

            return $this->issuesFound > 0 ? 1 : 0;

        } catch (\Exception $e) {
            $this->error("❌ ERRO: " . $e->getMessage());
            Log::error('ValidateVehicleDataCommand failed', [
                'error' => $e->getMessage()
            ]);
            return 1;
        }
    }

    /**
     * Executar validações básicas
     */
    protected function runBasicValidations(?string $make, ?string $category, bool $fix): void
    {
        $this->info("\n📋 Executando validações básicas...");

        $query = VehicleData::query();
        
        if ($make) {
            $query->byMake($make);
        }
        
        if ($category) {
            $query->byCategory($category);
        }

        $vehicles = $query->get();
        $bar = $this->output->createProgressBar($vehicles->count());

        foreach ($vehicles as $vehicle) {
            $this->validateBasicFields($vehicle, $fix);
            $this->validatePressureRanges($vehicle, $fix);
            $this->validateYearRange($vehicle, $fix);
            $this->validateCategoryConsistency($vehicle, $fix);
            
            $this->validatedCount++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }
}