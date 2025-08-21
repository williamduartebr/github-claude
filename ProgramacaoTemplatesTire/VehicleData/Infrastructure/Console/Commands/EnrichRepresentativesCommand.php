<?php

namespace Src\VehicleData\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\VehicleData\Domain\Entities\VehicleEnrichmentGroup;
use Src\VehicleData\Infrastructure\Services\ClaudeSonnetVehicleDataService;

/**
 * Command para enriquecer representantes via API Claude
 * 
 * VERSÃO CORRIGIDA - Compatible com MongoDB
 */
class EnrichRepresentativesCommand extends Command
{
    protected $signature = 'vehicle-data:enrich-representatives
                           {--batch-size=10 : Número de grupos por lote}
                           {--priority= : Processar apenas uma prioridade (high/medium/low)}
                           {--make= : Processar apenas uma marca específica}
                           {--dry-run : Simular enrichment sem chamar API}
                           {--force : Reprocessar grupos já enriquecidos}
                           {--limit= : Limite total de grupos para processar}';

    protected $description = 'Enriquecer representantes de grupos via Claude API';

    protected ClaudeSonnetVehicleDataService $claudeService;
    protected int $processedCount = 0;
    protected int $successCount = 0;
    protected int $errorCount = 0;
    protected int $skippedCount = 0;
    protected array $errors = [];

    public function __construct(ClaudeSonnetVehicleDataService $claudeService)
    {
        parent::__construct();
        $this->claudeService = $claudeService;
    }

    public function handle(): ?int
    {

        // Só executa em produção e staging
        if (app()->environment(['local', 'testing'])) {
            return null;
        }

        $this->info('🤖 INICIANDO ENRICHMENT VIA CLAUDE API');
        $this->newLine();

        $batchSize = (int) $this->option('batch-size');
        $priority = $this->option('priority');
        $make = $this->option('make');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        if ($dryRun) {
            $this->warn('🔍 MODO DRY-RUN: Nenhuma API será chamada');
            $this->newLine();
        }

        try {
            // Verificar se Claude API está configurada
            if (!$dryRun && !$this->claudeService->canMakeRequest()) {
                $waitTime = $this->claudeService->timeUntilNextRequest();
                $this->warn("⏳ Rate limit ativo. Aguarde {$waitTime} segundos.");

                if (!$this->confirm('Continuar mesmo assim?')) {
                    return Command::SUCCESS;
                }
            }

            // Exibir estatísticas iniciais
            $this->displayInitialStats($priority, $make, $force);

            // Buscar grupos para processar
            $groups = $this->getGroupsToProcess($batchSize, $priority, $make, $force, $limit);

            if ($groups->isEmpty()) {
                $this->info('✅ Nenhum grupo encontrado para processar');
                return Command::SUCCESS;
            }

            // Processar grupos
            $this->processGroups($groups, $dryRun);

            // Exibir resultados
            $this->displayResults();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ ERRO: ' . $e->getMessage());
            Log::error('EnrichRepresentativesCommand: Erro fatal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Exibir estatísticas iniciais
     */
    protected function displayInitialStats(?string $priority, ?string $make, bool $force): void
    {
        $stats = VehicleEnrichmentGroup::getProcessingStats();

        $this->info('📊 ESTATÍSTICAS ATUAIS:');
        $this->line("   Total de grupos: {$stats['total_groups']}");
        $this->line("   Pendentes: {$stats['pending_enrichment']}");
        $this->line("   Já enriquecidos: {$stats['enriched']}");
        $this->line("   Falhas: {$stats['failed']}");

        if ($priority) {
            $priorityCount = VehicleEnrichmentGroup::byPriority($priority)->count();
            $this->line("   Filtro prioridade '{$priority}': {$priorityCount}");
        }

        if ($make) {
            $makeCount = VehicleEnrichmentGroup::byMake($make)->count();
            $this->line("   Filtro marca '{$make}': {$makeCount}");
        }

        if ($force) {
            $this->warn("   MODO FORCE: Reprocessará grupos já enriquecidos");
        }

        $this->newLine();
    }

    /**
     * Buscar grupos para processar - CORRIGIDO para MongoDB
     */
    protected function getGroupsToProcess(
        int $batchSize,
        ?string $priority,
        ?string $make,
        bool $force,
        ?int $limit
    ): \Illuminate\Support\Collection {

        if ($force) {
            // Se force, pegar todos os grupos
            $query = VehicleEnrichmentGroup::query();
        } else {
            // Apenas pendentes
            $query = VehicleEnrichmentGroup::pendingEnrichment();
        }

        // Aplicar filtros
        if ($priority) {
            $query->byPriority($priority);
        }

        if ($make) {
            $query->byMake($make);
        }

        // CORREÇÃO: Ordenação compatível com MongoDB
        $groups = $query->get();

        // Ordenar na memória usando Collection
        $groups = $groups->sortBy([
            // Primeiro por prioridade (high = 1, medium = 2, low = 3)
            function ($group) {
                return match ($group->priority) {
                    'high' => 1,
                    'medium' => 2,
                    'low' => 3,
                    default => 4
                };
            },
            // Depois por data de criação (mais antigos primeiro)
            ['created_at', 'asc']
        ]);

        // Aplicar limite se especificado
        if ($limit) {
            $groups = $groups->take($limit);
        } else {
            $groups = $groups->take($batchSize * 10); // Máximo 10 lotes por execução
        }

        return $groups;
    }

    /**
     * Processar grupos
     */
    protected function processGroups(\Illuminate\Support\Collection $groups, bool $dryRun): void
    {
        $this->info("🔄 Processando {$groups->count()} grupos...");
        $this->newLine();

        $progressBar = $this->output->createProgressBar($groups->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');

        foreach ($groups as $group) {
            $repInfo = $group->getRepresentativeInfo();
            $vehicleName = $repInfo['full_name'];
            $progressBar->setMessage("Processando: {$vehicleName}");

            $this->processGroup($group, $dryRun);
            $progressBar->advance();

            // Rate limiting (2 minutos entre chamadas)
            if (!$dryRun && $this->successCount > 0) {
                sleep(2); // 2 segundos mínimo entre requests
            }
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    /**
     * Processar um grupo individual
     */
    protected function processGroup(VehicleEnrichmentGroup $group, bool $dryRun): void
    {
        try {
            $this->processedCount++;

            // Verificar se pode tentar novamente
            if (!$group->canRetryEnrichment()) {
                $this->skippedCount++;
                Log::info('Grupo ignorado - máximo de tentativas atingido', [
                    'group_id' => $group->id,
                    'generation_key' => $group->generation_key,
                    'attempts' => $group->enrichment_attempts
                ]);
                return;
            }

            $repInfo = $group->getRepresentativeInfo();

            if ($dryRun) {
                $this->successCount++;
                Log::info('DRY-RUN: Grupo seria enriquecido', [
                    'group_id' => $group->id,
                    'vehicle' => $repInfo['full_name'],
                    'priority' => $group->priority
                ]);
                return;
            }

            // Marcar como processando
            $group->markAsEnriching();

            // Construir prompt para Claude
            $prompt = $this->buildClaudePrompt($repInfo, $group);

            // Chamar Claude API
            $response = $this->claudeService->generateContent($prompt, [
                'max_tokens' => 2000,
                'temperature' => 0.1,
                'timeout' => 45
            ]);

            // Parsear resposta
            $enrichedData = $this->parseClaudeResponse($response, $repInfo);

            if (!$enrichedData) {
                throw new \Exception('Resposta inválida do Claude API');
            }

            // Salvar dados enriquecidos
            $group->markAsEnriched($enrichedData);
            $this->successCount++;

            Log::info('Grupo enriquecido com sucesso', [
                'group_id' => $group->id,
                'vehicle' => $repInfo['full_name'],
                'fields_enriched' => count($enrichedData)
            ]);
        } catch (\Exception $e) {
            $this->errorCount++;
            $group->markEnrichmentAsFailed($e->getMessage());

            $this->errors[] = [
                'group_id' => $group->id,
                'vehicle' => $repInfo['full_name'] ?? 'Unknown',
                'error' => $e->getMessage()
            ];

            Log::error('Erro ao enriquecer grupo', [
                'group_id' => $group->id,
                'generation_key' => $group->generation_key,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Construir prompt para Claude
     */
    protected function buildClaudePrompt(array $repInfo, VehicleEnrichmentGroup $group): string
    {
        $make = $repInfo['make'];
        $model = $repInfo['model'];
        $year = $repInfo['year'];
        $category = $repInfo['category'];

        $isMotorcycle = str_contains($category, 'motorcycle');
        $isElectric = str_contains($category, 'electric');

        $prompt = "Você é um especialista em dados técnicos automotivos. Preciso dos dados técnicos OFICIAIS para:

VEÍCULO: {$make} {$model} {$year}
CATEGORIA: {$category}
TIPO: " . ($isMotorcycle ? "Motocicleta" : "Automóvel") . "

Forneça APENAS os dados técnicos PRECISOS em formato JSON válido:

{
  \"engine_data\": {
    \"engine_type\": \"ex: 1.6 16V Flex\",
    \"displacement\": \"ex: 1598cc\",
    \"horsepower\": \"ex: 120 cv\",
    \"torque\": \"ex: 15.8 kgfm\",
    \"fuel_type\": \"Flex/Gasolina/Diesel/Elétrico\"
  },
  \"transmission_data\": {
    \"type\": \"Manual/Automático/CVT\",
    \"gears\": \"5/6/Contínua\"
  },
  \"fuel_data\": {
    \"consumption_city\": \"ex: 11.8 km/l\",
    \"consumption_highway\": \"ex: 14.2 km/l\",
    \"fuel_tank_capacity\": \"ex: 50L\"
  },
  \"dimensions\": {
    \"length\": \"ex: 4540mm\",
    \"width\": \"ex: 1800mm\",
    \"height\": \"ex: 1610mm\",
    \"wheelbase\": \"ex: 2640mm\",
    \"weight\": \"ex: 1450kg\"
  },
  \"technical_specs\": {
    \"max_load\": \"ex: 500kg\",
    \"suspension_front\": \"ex: McPherson\",
    \"suspension_rear\": \"ex: Eixo de torção\",
    \"brakes_front\": \"ex: Disco ventilado\",
    \"brakes_rear\": \"ex: Tambor/Disco\"
  },
  \"market_data\": {
    \"price_range\": \"ex: R$ 85.000 - 120.000\",
    \"launch_year\": \"{$year}\",
    \"main_competitors\": [\"Modelo1\", \"Modelo2\", \"Modelo3\"]
  }
}

IMPORTANTE:
- Use APENAS dados do manual oficial do fabricante
- Valores PRECISOS, não estimativas
- Para motocicletas: adapte campos (ex: sem fuel_tank_capacity se for muito pequeno)
- Para elétricos: adapte fuel_data (autonomia ao invés de consumo)
- Retorne APENAS o JSON, sem texto adicional";

        return $prompt;
    }

    /**
     * Parsear resposta do Claude
     */
    protected function parseClaudeResponse(string $response, array $repInfo): ?array
    {
        // Limpar resposta (remover markdown, etc)
        $cleanResponse = trim($response);
        $cleanResponse = preg_replace('/```json\s*/', '', $cleanResponse);
        $cleanResponse = preg_replace('/```\s*$/', '', $cleanResponse);
        $cleanResponse = trim($cleanResponse);

        // Tentar extrair JSON
        if (preg_match('/\{.*\}/s', $cleanResponse, $matches)) {
            $jsonString = $matches[0];
        } else {
            $jsonString = $cleanResponse;
        }

        $data = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON inválido do Claude', [
                'vehicle' => $repInfo['full_name'],
                'json_error' => json_last_error_msg(),
                'response' => substr($response, 0, 500)
            ]);
            return null;
        }

        // Validar estrutura mínima
        $requiredSections = ['engine_data', 'transmission_data', 'dimensions'];
        foreach ($requiredSections as $section) {
            if (!isset($data[$section]) || !is_array($data[$section])) {
                Log::error('Seção obrigatória faltando', [
                    'vehicle' => $repInfo['full_name'],
                    'missing_section' => $section
                ]);
                return null;
            }
        }

        return $data;
    }

    /**
     * Exibir resultados
     */
    protected function displayResults(): void
    {
        $this->info('=== RESULTADO DO ENRICHMENT ===');
        $this->newLine();

        $this->line("📄 <fg=cyan>Grupos processados:</> {$this->processedCount}");
        $this->line("✅ <fg=green>Enriquecidos com sucesso:</> {$this->successCount}");
        $this->line("⏭️  <fg=yellow>Ignorados:</> {$this->skippedCount}");
        $this->line("❌ <fg=red>Erros:</> {$this->errorCount}");

        if (!empty($this->errors)) {
            $this->newLine();
            $this->warn('Primeiros erros encontrados:');
            foreach (array_slice($this->errors, 0, 5) as $error) {
                $this->line("  • {$error['vehicle']}: {$error['error']}");
            }

            if (count($this->errors) > 5) {
                $this->line("  ... e mais " . (count($this->errors) - 5) . " erros");
            }
        }

        // Estatísticas atualizadas
        $this->newLine();
        $stats = VehicleEnrichmentGroup::getProcessingStats();
        $this->info('📊 ESTATÍSTICAS ATUALIZADAS:');
        $this->line("   Pendentes: {$stats['pending_enrichment']}");
        $this->line("   Enriquecidos: {$stats['enriched']}");
        $this->line("   Prontos para propagação: {$stats['pending_propagation']}");
        $this->line("   Taxa de conclusão: {$stats['completion_rate']}%");

        $this->newLine();

        if ($stats['pending_propagation'] > 0) {
            $this->info('📋 PRÓXIMO PASSO:');
            $this->line('   php artisan vehicle-data:propagate-from-representatives');
        }

        Log::info('EnrichRepresentativesCommand: Execução concluída', [
            'processed' => $this->processedCount,
            'success' => $this->successCount,
            'errors' => $this->errorCount,
            'skipped' => $this->skippedCount
        ]);
    }
}
