<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\VehicleDataProcessorService;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\InitialArticleGeneratorService;

/**
 * Debug específico para veículos que falharam
 */
class DebugFailedVehiclesCommand extends Command
{
    protected $signature = 'tire-pressure:debug-failed 
                           {--vehicle= : Filtrar por veículo específico (ex: "Fiat Mobi 2021")}
                           {--template= : Template específico para debug}
                           {--detailed : Logs detalhados}';

    protected $description = 'Debug específico para veículos que falharam na geração';

    public function handle(): int
    {
        $this->info("🔍 DEBUG - VEÍCULOS COM FALHA");
        $this->info("=====================================");

        $targetVehicle = $this->option('vehicle');
        $templateType = $this->option('template') ?? 'ideal';
        $detailed = $this->option('detailed');

        try {
            // 1. Processar CSV para encontrar os veículos específicos
            $processor = app(VehicleDataProcessorService::class);
            $generator = app(InitialArticleGeneratorService::class);

            $this->info("📂 Processando CSV...");
            $allVehicles = $processor->processVehicleData('data/todos_veiculos.csv', []);

            // 2. Filtrar veículos que sabemos que falharam
            $failedVehicles = $this->getKnownFailedVehicles($allVehicles, $targetVehicle);

            if ($failedVehicles->isEmpty()) {
                $this->warn("Nenhum veículo encontrado para debug");
                return self::FAILURE;
            }

            $this->info("🎯 Veículos para debug: " . $failedVehicles->count());

            // 3. Debug cada veículo individualmente
            foreach ($failedVehicles as $vehicle) {
                $this->debugSingleVehicle($vehicle, $templateType, $generator, $detailed);
                $this->line(""); // Separador
            }

            $this->info("✅ Debug concluído!");
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Erro durante debug: " . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Obter veículos que sabemos que falharam
     */
    protected function getKnownFailedVehicles($allVehicles, ?string $targetVehicle)
    {
        $knownFailed = ['Fiat Mobi 2021', 'BYD Dolphin 2024'];

        return $allVehicles->filter(function ($vehicle) use ($knownFailed, $targetVehicle) {
            $vehicleId = trim($vehicle['make'] . ' ' . $vehicle['model'] . ' ' . $vehicle['year']);

            if ($targetVehicle) {
                return $vehicleId === $targetVehicle;
            }

            return in_array($vehicleId, $knownFailed);
        });
    }

    /**
     * Debug de um veículo específico
     */
    protected function debugSingleVehicle(array $vehicle, string $template, $generator, bool $detailed): void
    {
        $vehicleId = trim($vehicle['make'] . ' ' . $vehicle['model'] . ' ' . $vehicle['year']);

        $this->info("🚗 DEBUGANDO: {$vehicleId} - Template: {$template}");
        $this->line("─────────────────────────────────────────────");

        // 1. Mostrar dados do veículo
        $this->showVehicleData($vehicle, $detailed);

        // 2. Tentar gerar artigo com logs detalhados
        $this->info("🔧 Tentando gerar artigo...");

        try {


            $batchId = 'debug_' . time();
            $article = $generator->generateArticle($vehicle, $batchId, $template);

            if ($article) {
                $this->info("✅ SUCESSO! Artigo gerado:");
                $this->line("   ID: " . $article->_id);
                $this->line("   Slug: " . $article->slug);
                $this->line("   Content Score: " . $article->content_score);
                $this->line("   Template: " . $article->template_type);
            } else {
                $this->error("❌ FALHA! Artigo retornou null");
                $this->analyzeFailure($vehicle, $template);
            }
        } catch (\Exception $e) {
            $this->error("❌ EXCEÇÃO: " . $e->getMessage());
            $this->line("   Trace: " . $e->getFile() . ':' . $e->getLine());

            if ($detailed) {
                $this->line("   Stack trace completo:");
                $this->line($e->getTraceAsString());
            }
        }
    }

    /**
     * Mostrar dados do veículo
     */
    protected function showVehicleData(array $vehicle, bool $detailed): void
    {
        $this->info("📋 Dados do veículo:");
        $this->table(['Campo', 'Valor'], [
            ['Make', $vehicle['make'] ?? 'N/A'],
            ['Model', $vehicle['model'] ?? 'N/A'],
            ['Year', $vehicle['year'] ?? 'N/A'],
            ['Tire Size', $vehicle['tire_size'] ?? 'N/A'],
            ['Category', $vehicle['main_category'] ?? 'N/A'],
            ['Is Motorcycle', $vehicle['is_motorcycle'] ? 'Sim' : 'Não'],
            ['Vehicle Type', $vehicle['vehicle_type'] ?? 'N/A'],
            ['Pressure Front (Light)', $vehicle['pressure_light_front'] ?? 'N/A'],
            ['Pressure Rear (Light)', $vehicle['pressure_light_rear'] ?? 'N/A'],
            ['Pressure Front (Empty)', $vehicle['pressure_empty_front'] ?? 'N/A'],
            ['Pressure Rear (Empty)', $vehicle['pressure_empty_rear'] ?? 'N/A']
        ]);

        if ($detailed) {
            $this->info("🔍 Dados completos (detailed):");
            $this->line(json_encode($vehicle, JSON_PRETTY_PRINT));
        }
    }

    /**
     * Analisar falha específica
     */
    protected function analyzeFailure(array $vehicle, string $template): void
    {
        $this->warn("🔍 ANÁLISE DA FALHA:");

        // Verificar campos obrigatórios
        $requiredFields = ['make', 'model', 'year'];
        $missingRequired = [];

        foreach ($requiredFields as $field) {
            if (empty($vehicle[$field])) {
                $missingRequired[] = $field;
            }
        }

        if (!empty($missingRequired)) {
            $this->error("   ❌ Campos obrigatórios ausentes: " . implode(', ', $missingRequired));
        } else {
            $this->info("   ✅ Campos obrigatórios OK");
        }

        // Verificar caracteres especiais ou problemas de encoding
        foreach (['make', 'model'] as $field) {
            $value = $vehicle[$field] ?? '';
            if (preg_match('/[^\w\s\-]/', $value)) {
                $this->warn("   ⚠️ Campo {$field} contém caracteres especiais: '{$value}'");
            }
        }

        // Verificar se há conflito de slug
        $make = \Illuminate\Support\Str::slug($vehicle['make']);
        $model = \Illuminate\Support\Str::slug($vehicle['model']);
        $year = $vehicle['year'];

        if ($template === 'ideal') {
            $expectedSlug = "pressao-pneus-{$make}-{$model}-{$year}";
        } else {
            $expectedSlug = "como-calibrar-pneus-{$make}-{$model}-{$year}";
        }

        $this->line("   📝 Slug esperado: {$expectedSlug}");

        // Verificar se já existe artigo com este slug
        try {
            $existing = \Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle::where('slug', $expectedSlug)->first();
            if ($existing) {
                $this->warn("   ⚠️ JÁ EXISTE artigo com este slug!");
                $this->line("      ID existente: " . $existing->_id);
                $this->line("      Template existente: " . $existing->template_type);
            } else {
                $this->info("   ✅ Slug disponível");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Erro ao verificar slug existente: " . $e->getMessage());
        }

        // Possíveis soluções
        $this->info("💡 Possíveis soluções:");
        $this->line("   1. Verificar encoding do arquivo CSV");
        $this->line("   2. Limpar caracteres especiais dos nomes");
        $this->line("   3. Verificar duplicação de slugs");
        $this->line("   4. Analisar logs específicos deste veículo");
    }

    /**
     * Comando de recuperação para estes veículos específicos
     */
    protected function generateRecoveryCommand($failedVehicles): string
    {
        $vehicleIds = $failedVehicles->map(function ($vehicle) {
            return $vehicle['make'] . ' ' . $vehicle['model'] . ' ' . $vehicle['year'];
        })->implode('","');

        return 'php artisan tire-pressure:retry-specific --vehicles="' . $vehicleIds . '"';
    }
}
