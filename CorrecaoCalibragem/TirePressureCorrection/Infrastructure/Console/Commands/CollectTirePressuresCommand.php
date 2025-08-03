<?php

namespace Src\TirePressureCorrection\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\TirePressureCorrection\Domain\Entities\TirePressureCorrection;
use Src\TirePressureCorrection\Infrastructure\Services\ClaudeSonnetService;

/**
 * Command para coletar pressões de pneus via Claude API
 * Agrupa veículos similares para economizar chamadas
 */
class CollectTirePressuresCommand extends Command
{
    protected $signature = 'articles:collect-tire-pressures 
                           {--limit=50 : Número máximo de artigos para processar}
                           {--groups=10 : Número máximo de grupos de veículos}
                           {--dry-run : Simular execução sem chamar API}
                           {--force : Forçar coleta mesmo se já foi processado}';
    
    protected $description = 'Coletar pressões corretas de pneus via Claude API (agrupa veículos similares)';
    
    protected ClaudeSonnetService $claudeService;
    
    public function __construct(ClaudeSonnetService $claudeService)
    {
        parent::__construct();
        $this->claudeService = $claudeService;
    }
    
    public function handle(): int
    {
        $this->info('=== COLETA DE PRESSÕES DE PNEUS VIA CLAUDE API ===');
        $this->newLine();
        
        $limit = (int) $this->option('limit');
        $maxGroups = (int) $this->option('groups');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        
        if ($dryRun) {
            $this->warn('🔍 MODO DRY-RUN: Não fará chamadas reais à API');
            $this->newLine();
        }
        
        // Buscar e agrupar artigos
        $groups = $this->getVehicleGroups($limit, $force);
        
        if ($groups->isEmpty()) {
            $this->info('✅ Nenhum artigo encontrado para processar');
            return Command::SUCCESS;
        }
        
        // Limitar número de grupos
        if ($groups->count() > $maxGroups) {
            $groups = $groups->take($maxGroups);
            $this->warn("⚠️  Limitando a {$maxGroups} grupos de veículos");
        }
        
        $this->info("📊 Grupos de veículos encontrados: {$groups->count()}");
        $this->newLine();
        
        // Mostrar prévia
        $this->showGroupsPreview($groups);
        
        // Processar grupos
        $results = $this->processGroups($groups, $dryRun);
        
        // Exibir resultados
        $this->showResults($results);
        
        return Command::SUCCESS;
    }
    
    /**
     * Buscar e agrupar artigos por veículo similar
     */
    protected function getVehicleGroups(int $limit, bool $force): \Illuminate\Support\Collection
    {
        // Buscar artigos sem filtrar por marca/modelo na query
        // Faremos a validação depois, pois a query aninhada não funciona
        $query = Article::where('template', 'when_to_change_tires')
            ->whereNotNull('extracted_entities')
            ->orderBy('updated_at', 'desc')
            ->limit($limit * 2); // Pegar mais para compensar possíveis filtros
        
        // Se não forçar, excluir já processados
        if (!$force) {
            $processedArticles = TirePressureCorrection::where('created_at', '>=', now()->subDays(7))
                ->where('status', '!=', TirePressureCorrection::STATUS_FAILED)
                ->pluck('article_id');
            
            if ($processedArticles->isNotEmpty()) {
                $query->whereNotIn('_id', $processedArticles);
            }
        }
        
        $articles = $query->get();
        
        // Filtrar manualmente artigos sem marca/modelo
        $validArticles = $articles->filter(function ($article) {
            $marca = data_get($article, 'extracted_entities.marca');
            $modelo = data_get($article, 'extracted_entities.modelo');
            return !empty($marca) && !empty($modelo);
        })->take($limit);
        
        // Agrupar por marca + modelo + medida_pneu
        return $validArticles->groupBy(function ($article) {
            $entities = data_get($article, 'extracted_entities', []);
            
            $key = sprintf(
                '%s|%s|%s',
                strtolower(data_get($entities, 'marca', '')),
                strtolower(data_get($entities, 'modelo', '')),
                data_get($entities, 'medida_pneu', '')
            );
            
            return $key;
        })->filter(function ($group) {
            // Remover grupos vazios ou com dados incompletos
            $firstArticle = $group->first();
            $entities = data_get($firstArticle, 'extracted_entities', []);
            
            return !empty(data_get($entities, 'marca')) && 
                   !empty(data_get($entities, 'modelo'));
        });
         
        // Agrupar por marca + modelo + medida_pneu
        return $articles->groupBy(function ($article) {
            $entities = data_get($article, 'extracted_entities', []);
            
            $key = sprintf(
                '%s|%s|%s',
                strtolower(data_get($entities, 'marca', '')),
                strtolower(data_get($entities, 'modelo', '')),
                data_get($entities, 'medida_pneu', '')
            );
            
            return $key;
        })->filter(function ($group) {
            // Remover grupos vazios ou com dados incompletos
            $firstArticle = $group->first();
            $entities = data_get($firstArticle, 'extracted_entities', []);
            
            return !empty(data_get($entities, 'marca')) && 
                   !empty(data_get($entities, 'modelo'));
        });
    }
    
    /**
     * Mostrar prévia dos grupos
     */
    protected function showGroupsPreview(\Illuminate\Support\Collection $groups): void
    {
        $this->info('📋 Grupos de veículos a serem processados:');
        $this->newLine();
        
        $headers = ['#', 'Veículo', 'Medida Pneu', 'Artigos', 'Anos'];
        $rows = [];
        
        foreach ($groups->take(10) as $key => $articles) {
            $firstArticle = $articles->first();
            $entities = data_get($firstArticle, 'extracted_entities', []);
            
            // Coletar todos os anos do grupo
            $years = $articles->map(function ($article) {
                return data_get($article, 'extracted_entities.ano', 'N/A');
            })->unique()->sort()->values();
            
            $rows[] = [
                count($rows) + 1,
                sprintf('%s %s', 
                    data_get($entities, 'marca', 'N/A'),
                    data_get($entities, 'modelo', 'N/A')
                ),
                data_get($entities, 'medida_pneu', 'N/A'),
                $articles->count(),
                $years->implode(', ')
            ];
        }
        
        $this->table($headers, $rows);
        
        if ($groups->count() > 10) {
            $this->info("... e mais " . ($groups->count() - 10) . " grupos");
        }
        
        $totalArticles = $groups->sum(fn($group) => $group->count());
        $this->info("📊 Total de artigos: {$totalArticles}");
        $this->newLine();
    }
    
    /**
     * Processar grupos de veículos
     */
    protected function processGroups(\Illuminate\Support\Collection $groups, bool $dryRun): array
    {
        $results = [
            'groups_processed' => 0,
            'articles_processed' => 0,
            'api_calls' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        $progressBar = $this->output->createProgressBar($groups->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
        
        foreach ($groups as $key => $articles) {
            $firstArticle = $articles->first();
            $entities = data_get($firstArticle, 'extracted_entities', []);
            
            $vehicle = sprintf('%s %s', 
                data_get($entities, 'marca', 'N/A'),
                data_get($entities, 'modelo', 'N/A')
            );
            
            $progressBar->setMessage("Processando: {$vehicle}");
            
            try {
                if ($dryRun) {
                    // Simular processamento
                    $this->createMockCorrections($articles);
                } else {
                    // Processar grupo real
                    $this->processVehicleGroup($articles);
                    $results['api_calls']++;
                }
                
                $results['groups_processed']++;
                $results['articles_processed'] += $articles->count();
                
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'vehicle' => $vehicle,
                    'articles' => $articles->count(),
                    'error' => $e->getMessage()
                ];
                
                Log::error('CollectTirePressuresCommand: Erro ao processar grupo', [
                    'vehicle' => $vehicle,
                    'error' => $e->getMessage()
                ]);
            }
            
            $progressBar->advance();
            
            // Respeitar rate limit entre grupos
            if (!$dryRun && $progressBar->getProgress() < $groups->count()) {
                $progressBar->setMessage("Aguardando rate limit (2 min)...");
                sleep(120);
            }
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        return $results;
    }
    
    /**
     * Processar um grupo de veículos similares
     */
    protected function processVehicleGroup(\Illuminate\Support\Collection $articles): void
    {
        $firstArticle = $articles->first();
        $vehicleData = $this->extractVehicleData($firstArticle);
        
        // Obter pressões do Claude (uma vez para todo o grupo)
        $correctedPressures = $this->getPressuresFromClaude($vehicleData);
        
        if (!$correctedPressures) {
            throw new \Exception('Falha ao obter pressões do Claude');
        }
        
        // Criar correção para cada artigo do grupo
        foreach ($articles as $article) {
            $correction = TirePressureCorrection::createForArticle($article, 
                TirePressureCorrection::CORRECTION_TYPE_CLAUDE_API
            );
            
            // Salvar resposta do Claude
            $correction->claude_response = $correctedPressures;
            $correction->corrected_pressures = $correctedPressures;
            $correction->status = TirePressureCorrection::STATUS_PENDING;
            $correction->save();
            
            $this->line("  ✓ {$article->slug} - Correção salva");
        }
    }
    
    /**
     * Criar correções simuladas (dry-run)
     */
    protected function createMockCorrections(\Illuminate\Support\Collection $articles): void
    {
        foreach ($articles as $article) {
            $this->line("  [DRY-RUN] {$article->slug} - Simulando coleta");
        }
    }
    
    /**
     * Extrair dados do veículo
     */
    protected function extractVehicleData($article): array
    {
        $entities = data_get($article, 'extracted_entities', []);
        
        return [
            'marca' => data_get($entities, 'marca', ''),
            'modelo' => data_get($entities, 'modelo', ''),
            'ano' => data_get($entities, 'ano', ''),
            'tipo_veiculo' => data_get($entities, 'tipo_veiculo', ''),
            'categoria' => data_get($entities, 'categoria', ''),
            'medida_pneu' => data_get($entities, 'medida_pneu', '')
        ];
    }
    
    /**
     * Obter pressões do Claude
     */
    protected function getPressuresFromClaude(array $vehicleData): ?array
    {
        $isMotorcycle = $vehicleData['tipo_veiculo'] === 'motorcycle' || 
                       str_contains(strtolower($vehicleData['categoria'] ?? ''), 'motorcycle');
        
        $prompt = "Você é um especialista em pressão de pneus. Preciso das pressões corretas para:

VEÍCULO: {$vehicleData['marca']} {$vehicleData['modelo']}
TIPO: " . ($isMotorcycle ? "Motocicleta" : "Carro") . "
MEDIDA DO PNEU: {$vehicleData['medida_pneu']}

IMPORTANTE:
- Use as pressões recomendadas pelo fabricante (não importa o ano)
- Para motocicletas: geralmente 22-42 PSI
- Para carros: geralmente 28-36 PSI
- Sempre números inteiros

Responda APENAS com este JSON:
{
    \"empty_front\": 0,
    \"empty_rear\": 0,
    \"loaded_front\": 0,
    \"loaded_rear\": 0
}";

        try {
            $response = $this->claudeService->generateContent($prompt, [
                'max_tokens' => 200,
                'temperature' => 0.1
            ]);
            
            // Extrair JSON da resposta
            if (preg_match('/\{[^}]+\}/', $response, $matches)) {
                $data = json_decode($matches[0], true);
                
                if (json_last_error() === JSON_ERROR_NONE && $this->validatePressures($data)) {
                    return $data;
                }
            }
            
            Log::error('CollectTirePressuresCommand: Resposta inválida do Claude', [
                'response' => $response
            ]);
            
        } catch (\Exception $e) {
            Log::error('CollectTirePressuresCommand: Erro ao chamar Claude', [
                'error' => $e->getMessage()
            ]);
        }
        
        return null;
    }
    
    /**
     * Validar pressões
     */
    protected function validatePressures($data): bool
    {
        if (!is_array($data)) {
            return false;
        }
        
        $required = ['empty_front', 'empty_rear', 'loaded_front', 'loaded_rear'];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || !is_numeric($data[$field])) {
                return false;
            }
            
            if ($data[$field] < 10 || $data[$field] > 100) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Exibir resultados
     */
    protected function showResults(array $results): void
    {
        $this->info('=== RESULTADO DA COLETA ===');
        $this->newLine();
        
        $this->line("✅ Grupos processados: <fg=green>{$results['groups_processed']}</>");
        $this->line("📄 Artigos processados: <fg=green>{$results['articles_processed']}</>");
        $this->line("🔌 Chamadas à API: <fg=cyan>{$results['api_calls']}</>");
        $this->line("❌ Falhas: <fg=red>{$results['failed']}</>");
        
        if ($results['api_calls'] > 0) {
            $economy = $results['articles_processed'] - $results['api_calls'];
            $this->line("💰 Economia de chamadas: <fg=yellow>{$economy}</>");
        }
        
        if (!empty($results['errors'])) {
            $this->newLine();
            $this->error('Erros encontrados:');
            foreach ($results['errors'] as $error) {
                $this->line("  - {$error['vehicle']} ({$error['articles']} artigos): {$error['error']}");
            }
        }
        
        $this->newLine();
        
        // Estatísticas de correções pendentes
        $stats = TirePressureCorrection::getStats();
        $this->info('📊 Correções pendentes para aplicação: ' . $stats['pending']);
    }
}