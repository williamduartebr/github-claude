<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Carbon\Carbon;

/**
 * CorrectGenericVersionsTempArticleCommandV1 v1.0
 * 
 * NOVA ABORDAGEM - QUERY DIRETA NOS CAMPOS DEDICADOS
 * 
 * Busca por registros usando:
 * - needs_version_correction = true
 * - version_correction_priority = 'high'|'medium'|'low'
 * - version_corrected_at IS NULL (não corrigidos)
 * 
 * @author Claude Sonnet 4
 * @version 1.0 - Query simplificada e assertiva
 */
class CorrectGenericVersionsTempArticleCommandV1 extends Command
{
    protected $signature = 'temp-article:correct-generic-versions
                            {--limit=1 : Número máximo de registros}
                            {--batch-size=5 : Tamanho do batch}
                            {--dry-run : Simulação sem modificar dados}
                            {--priority=high : Prioridade (high|medium|low|all)}
                            {--force-reprocess : Reprocessar já corrigidos}
                            {--delay=2 : Delay entre requests (segundos)}
                            {--debug : Debug detalhado}';

    protected $description = 'Corrigir versões genéricas usando campos dedicados e Claude API';

    private const CLAUDE_API_URL = 'https://api.anthropic.com/v1/messages';

    // private const MODEL = 'claude-3-opus-20240229'; // Top
    // private const MODEL = 'claude-3-7-sonnet-20250219'; // Intermediaria
    private const MODEL = 'claude-3-5-sonnet-20240620'; // Padrao
    private const MAX_TOKENS = 2000;
    private const TEMPERATURE = 0.1;

    private string $apiKey;
    private int $timeout;
    private int $maxRetries;

    private int $totalProcessed = 0;
    private int $successfulCorrections = 0;
    private int $failedCorrections = 0;
    private array $errorSummary = [];
    private array $correctionExamples = [];

    public function __construct()
    {
        parent::__construct();
        $this->apiKey = config('services.anthropic.api_key', '');
        $this->timeout = config('services.anthropic.timeout', 90);
        $this->maxRetries = config('services.anthropic.max_retries', 3);
    }

    public function handle(): int
    {
        $this->displayHeader();

        if (empty($this->apiKey)) {
            $this->error('Claude API Key não configurada!');
            $this->line('Configure em config/services.php ou env ANTHROPIC_API_KEY');
            return self::FAILURE;
        }

        try {
            $config = $this->getConfiguration();
            $this->displayConfiguration($config);

            if (!$this->testApiConnectivity()) {
                return self::FAILURE;
            }

            if ($config['debug']) {
                $this->debugQuery($config);
            }

            $tempArticles = $this->getTempArticlesForCorrection($config);

            if ($tempArticles->isEmpty()) {
                $this->info('Nenhum TempArticle encontrado para correção.');
                return self::SUCCESS;
            }

            $this->info("Iniciando correção de {$tempArticles->count()} TempArticles...");
            $this->newLine();

            $batches = $tempArticles->chunk($config['batch_size']);

            foreach ($batches as $batchIndex => $batch) {
                $this->info("Batch " . ($batchIndex + 1) . "/" . $batches->count());

                foreach ($batch as $tempArticle) {
                    $this->processTempArticleCorrection($tempArticle, $config);

                    if ($config['delay'] > 0) {
                        sleep($config['delay']);
                    }
                }
                $this->newLine();
            }

            $this->displayResults($config);
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Erro durante correção: " . $e->getMessage());
            Log::error('CorrectGenericVersions failed', [
                'error' => $e->getMessage()
            ]);
            return self::FAILURE;
        }
    }

    private function getConfiguration(): array
    {
        return [
            'limit' => (int) $this->option('limit'),
            'batch_size' => (int) $this->option('batch-size'),
            'dry_run' => $this->option('dry-run'),
            'priority' => $this->option('priority'),
            'force_reprocess' => $this->option('force-reprocess'),
            'delay' => (int) $this->option('delay'),
            'debug' => $this->option('debug')
        ];
    }

    private function testApiConnectivity(): bool
    {
        $this->info('Testando Claude API...');

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01'
                ])
                ->post(self::CLAUDE_API_URL, [
                    'model' => self::MODEL,
                    'max_tokens' => 50,
                    'messages' => [
                        ['role' => 'user', 'content' => 'Teste - responda: OK']
                    ]
                ]);

            if ($response->successful()) {
                $this->line('   Claude API conectada');
                return true;
            } else {
                $this->error('   Falha: HTTP ' . $response->status());
                return false;
            }
        } catch (\Exception $e) {
            $this->error('   Erro: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * NOVA QUERY ASSERTIVA - Campos dedicados
     */
    private function getTempArticlesForCorrection(array $config)
    {
        $query = TempArticle::where('needs_version_correction', true);

        // Filtrar por prioridade
        if ($config['priority'] !== 'all') {
            $query->where('version_correction_priority', $config['priority']);
        }

        // Excluir já processados
        if (!$config['force_reprocess']) {
            $query->whereNull('version_corrected_at');
        }

        return $query->orderBy('correction_flagged_at', 'desc')
            ->limit($config['limit'])
            ->get();
    }

    /**
     * Debug da nova query
     */
    private function debugQuery(array $config): void
    {
        $this->info('DEBUG DA QUERY:');

        $total = TempArticle::count();
        $this->line("   Total TempArticles: {$total}");

        $needsCorrection = TempArticle::where('needs_version_correction', true)->count();
        $this->line("   Needs correction: {$needsCorrection}");

        if ($config['priority'] !== 'all') {
            $withPriority = TempArticle::where('needs_version_correction', true)
                ->where('version_correction_priority', $config['priority'])
                ->count();
            $this->line("   Priority {$config['priority']}: {$withPriority}");
        }

        if (!$config['force_reprocess']) {
            $notCorrected = TempArticle::where('needs_version_correction', true)
                ->whereNull('version_corrected_at')
                ->count();
            $this->line("   Não corrigidos: {$notCorrected}");
        }

        // Amostras
        $samples = TempArticle::where('needs_version_correction', true)
            ->limit(3)
            ->get(['id', 'title', 'needs_version_correction', 'version_correction_priority', 'version_corrected_at']);

        $this->line("   Amostras:");
        foreach ($samples as $sample) {
            $corrected = $sample->version_corrected_at ? 'CORRIGIDO' : 'PENDENTE';
            $priority = $sample->version_correction_priority ?? 'N/A';
            $this->line("     {$sample->id}: {$priority} | {$corrected}");
        }

        $this->newLine();
    }

    private function processTempArticleCorrection(TempArticle $tempArticle, array $config): void
    {
        $this->totalProcessed++;

        try {
            $vehicleInfo = $this->extractVehicleInfo($tempArticle);
            $this->line("Processando: {$vehicleInfo['display_name']} (ID: {$tempArticle->id})");

            if ($config['debug']) {
                $issuesCount = count($tempArticle->version_issues_detected ?? []);
                $this->line("   Issues: {$issuesCount}");
            }

            $content = $tempArticle->content ?? [];
            $corrections = $this->generateCorrectionsViaClaudeApi($vehicleInfo, $content, $config);

            if (empty($corrections)) {
                throw new \Exception('Claude API retornou correções vazias');
            }

            $this->validateCorrections($corrections, $vehicleInfo);

            if (!$config['dry_run']) {
                $this->applyCorrections($tempArticle, $corrections);
                $this->markAsCorrected($tempArticle);
            }

            $this->successfulCorrections++;
            $this->line("   Corrigido com sucesso");

            if (count($this->correctionExamples) < 3) {
                $this->correctionExamples[] = [
                    'vehicle' => $vehicleInfo['display_name'],
                    'temp_article_id' => $tempArticle->id,
                    'corrections_applied' => array_keys($corrections)
                ];
            }
        } catch (\Exception $e) {
            $this->failedCorrections++;
            $this->line("   Falha: " . $e->getMessage());

            $errorCategory = $this->categorizeError($e->getMessage());
            if (!isset($this->errorSummary[$errorCategory])) {
                $this->errorSummary[$errorCategory] = 0;
            }
            $this->errorSummary[$errorCategory]++;

            Log::error('Falha na correção de versão genérica', [
                'temp_article_id' => $tempArticle->id,
                'vehicle' => $vehicleInfo['display_name'] ?? 'Unknown',
                'error' => $e->getMessage(),
                'error_category' => $errorCategory
            ]);
        }
    }

    private function extractVehicleInfo(TempArticle $tempArticle): array
    {
        $entities = $tempArticle->extracted_entities ?? [];
        $vehicleInfo = $tempArticle->vehicle_info ?? [];

        return [
            'marca' => $entities['marca'] ?? $vehicleInfo['make'] ?? 'Unknown',
            'modelo' => $entities['modelo'] ?? $vehicleInfo['model'] ?? 'Unknown',
            'ano' => $entities['ano'] ?? date('Y'),
            'categoria' => $entities['categoria'] ?? $vehicleInfo['main_category'] ?? 'Unknown',
            'display_name' => ($entities['marca'] ?? 'Unknown') . ' ' . ($entities['modelo'] ?? 'Unknown'),
            'tire_size' => $vehicleInfo['tire_size'] ?? $entities['pneus'] ?? 'Unknown'
        ];
    }

    private function generateCorrectionsViaClaudeApi(array $vehicleInfo, array $content, array $config): array
    {
        $prompt = $this->buildCorrectionPrompt($vehicleInfo, $content);

        $retryCount = 0;
        while ($retryCount < $this->maxRetries) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'x-api-key' => $this->apiKey,
                        'anthropic-version' => '2023-06-01'
                    ])
                    ->post(self::CLAUDE_API_URL, [
                        'model' => self::MODEL,
                        'max_tokens' => self::MAX_TOKENS,
                        'temperature' => self::TEMPERATURE,
                        'messages' => [
                            ['role' => 'user', 'content' => $prompt]
                        ]
                    ]);

                if ($response->successful()) {
                    $responseData = $response->json();
                    $text = $responseData['content'][0]['text'] ?? '';
                    return $this->parseCorrectionsFromResponse($text);
                } else {
                    throw new \Exception('Claude API Error: HTTP ' . $response->status());
                }
            } catch (\Exception $e) {
                $retryCount++;
                if ($retryCount >= $this->maxRetries) {
                    throw $e;
                }
                sleep(pow(2, $retryCount));
            }
        }

        throw new \Exception('Máximo de tentativas excedido');
    }

    private function buildCorrectionPrompt(array $vehicleInfo, array $content): string
    {
        $currentVersions = $this->extractCurrentVersions($content);

        return "Você é especialista em especificações automotivas. Corrija versões genéricas.

VEÍCULO: {$vehicleInfo['marca']} {$vehicleInfo['modelo']} {$vehicleInfo['ano']}
CATEGORIA: {$vehicleInfo['categoria']}
PNEUS: {$vehicleInfo['tire_size']}

VERSÕES ATUAIS (GENÉRICAS):
" . implode(', ', $currentVersions) . "

TAREFA:
1. Substitua por versões REAIS do {$vehicleInfo['marca']} {$vehicleInfo['modelo']}
2. Mantenha especificações técnicas coerentes
3. Use apenas versões do mercado brasileiro

REGRAS:
- NÃO use Comfort, Style, Premium
- USE versões específicas reais
- Mantenha 3 versões diferentes
- Preserve pressões dos pneus

RESPONDA APENAS JSON:
```json
{
  \"especificacoes_por_versao\": [
    {
      \"versao\": \"Nome versão real\",
      \"medida_pneus\": \"255/70 R16\",
      \"indice_carga_velocidade\": \"112S\",
      \"pressao_dianteiro_normal\": 35,
      \"pressao_traseiro_normal\": 35,
      \"pressao_dianteiro_carregado\": 40,
      \"pressao_traseiro_carregado\": 45
    }
  ],
  \"tabela_carga_completa\": {
    \"condicoes\": [
      {
        \"versao\": \"Nome versão real\",
        \"ocupantes\": \"4-5 pessoas\",
        \"bagagem\": \"Carga máxima\",
        \"pressao_dianteira\": \"40 PSI\",
        \"pressao_traseira\": \"45 PSI\",
        \"observacao\": \"Observação específica\"
      }
    ]
  }
}
```";
    }

    private function extractCurrentVersions(array $content): array
    {
        $versions = [];

        if (isset($content['especificacoes_por_versao'])) {
            foreach ($content['especificacoes_por_versao'] as $spec) {
                if (isset($spec['versao'])) {
                    $versions[] = $spec['versao'];
                }
            }
        }

        if (isset($content['tabela_carga_completa']['condicoes'])) {
            foreach ($content['tabela_carga_completa']['condicoes'] as $condicao) {
                if (isset($condicao['versao']) && !in_array($condicao['versao'], $versions)) {
                    $versions[] = $condicao['versao'];
                }
            }
        }

        return array_unique($versions);
    }

    private function parseCorrectionsFromResponse(string $text): array
    {
        if (preg_match('/```json\s*(.*?)\s*```/s', $text, $matches)) {
            $jsonString = $matches[1];
            $json = json_decode($jsonString, true);

            if ($json && json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }

        $json = json_decode($text, true);
        if ($json && json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }

        throw new \Exception('Resposta Claude API não contém JSON válido');
    }

    private function validateCorrections(array $corrections, array $vehicleInfo): void
    {
        if (!isset($corrections['especificacoes_por_versao']) || !isset($corrections['tabela_carga_completa'])) {
            throw new \Exception('Estrutura de correções incompleta');
        }

        $specs = $corrections['especificacoes_por_versao'];
        if (!is_array($specs) || count($specs) < 2) {
            throw new \Exception('Especificações insuficientes');
        }

        foreach ($specs as $spec) {
            $versao = $spec['versao'] ?? '';
            if ($this->isGenericVersion($versao)) {
                throw new \Exception("Versão genérica presente: {$versao}");
            }
        }

        foreach ($specs as $spec) {
            $required = ['versao', 'medida_pneus', 'pressao_dianteiro_normal', 'pressao_traseiro_normal'];
            foreach ($required as $field) {
                if (!isset($spec[$field]) || empty($spec[$field])) {
                    throw new \Exception("Campo obrigatório ausente: {$field}");
                }
            }
        }
    }

    private function isGenericVersion(string $versionName): bool
    {
        $patterns = ['comfort', 'style', 'premium', 'base', 'entry', 'standard'];

        foreach ($patterns as $pattern) {
            if (stripos($versionName, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    private function applyCorrections(TempArticle $tempArticle, array $corrections): void
    {
        $content = $tempArticle->content ?? [];

        if (isset($corrections['especificacoes_por_versao'])) {
            $content['especificacoes_por_versao'] = $corrections['especificacoes_por_versao'];
        }

        if (isset($corrections['tabela_carga_completa'])) {
            $content['tabela_carga_completa'] = array_merge(
                $content['tabela_carga_completa'] ?? [],
                $corrections['tabela_carga_completa']
            );
        }

        $tempArticle->update(['content' => $content]);
    }

    /**
     * MARCAR COMO CORRIGIDO - Usando campos dedicados
     */
    private function markAsCorrected(TempArticle $tempArticle): void
    {
        $tempArticle->update([
            'needs_version_correction' => false,
            'version_corrected_at' => now(),
            'corrected_by' => 'claude_api_v1.0'
        ]);
    }

    private function categorizeError(string $errorMessage): string
    {
        if (strpos($errorMessage, 'timeout') !== false) {
            return 'api_timeout';
        } elseif (strpos($errorMessage, 'rate') !== false) {
            return 'api_rate_limit';
        } elseif (strpos($errorMessage, 'JSON') !== false) {
            return 'json_parse_error';
        } elseif (strpos($errorMessage, 'validation') !== false) {
            return 'validation_error';
        } elseif (strpos($errorMessage, 'genérica') !== false) {
            return 'generic_version_persist';
        } else {
            return 'other_errors';
        }
    }

    private function displayHeader(): void
    {
        $this->info('CORREÇÃO DE VERSÕES GENÉRICAS v1.0');
        $this->info('Claude API + Campos dedicados');
        $this->info(now()->format('d/m/Y H:i:s'));
        $this->newLine();
    }

    private function displayConfiguration(array $config): void
    {
        $this->line('CONFIGURAÇÃO:');
        $this->line('   Limite: ' . $config['limit']);
        $this->line('   Batch: ' . $config['batch_size']);
        $this->line('   Prioridade: ' . $config['priority']);
        $this->line('   Modo: ' . ($config['dry_run'] ? 'SIMULAÇÃO' : 'EXECUÇÃO'));
        $this->line('   Reprocessar: ' . ($config['force_reprocess'] ? 'SIM' : 'NÃO'));
        $this->line('   Delay: ' . $config['delay'] . 's');
        $this->newLine();

        $this->info('MODELO CLAUDE:');
        $this->line('   Model: ' . self::MODEL);
        $this->line('   Max Tokens: ' . self::MAX_TOKENS);
        $this->line('   Temperature: ' . self::TEMPERATURE);
        $this->newLine();
    }

    private function displayResults(array $config): void
    {
        $this->newLine();
        $this->info('RESULTADOS:');
        $this->line("   Total processado: {$this->totalProcessed}");
        $this->line("   Sucessos: {$this->successfulCorrections}");
        $this->line("   Falhas: {$this->failedCorrections}");

        $successRate = $this->totalProcessed > 0 ?
            round(($this->successfulCorrections / $this->totalProcessed) * 100, 1) : 0;

        $this->line("   Taxa de sucesso: {$successRate}%");
        $this->newLine();

        if (!empty($this->errorSummary)) {
            $this->displayErrorSummary();
        }

        if (!empty($this->correctionExamples)) {
            $this->displayCorrectionExamples();
        }

        $this->displayRecommendations($successRate);
    }

    private function displayErrorSummary(): void
    {
        $this->info('RESUMO DE ERROS:');

        foreach ($this->errorSummary as $category => $count) {
            $this->line("   {$category}: {$count}");
        }
        $this->newLine();
    }

    private function displayCorrectionExamples(): void
    {
        $this->info('EXEMPLOS DE CORREÇÕES:');

        foreach ($this->correctionExamples as $example) {
            $this->line("   {$example['vehicle']} (ID: {$example['temp_article_id']})");
            $this->line("      Correções: " . implode(', ', $example['corrections_applied']));
        }
        $this->newLine();
    }

    private function displayRecommendations(float $successRate): void
    {
        $this->line('RECOMENDAÇÕES:');

        if ($successRate >= 90) {
            $this->line('   Excelente! Sistema funcionando perfeitamente.');
        } elseif ($successRate >= 70) {
            $this->line('   Boa taxa. Verificar erros para otimizações.');
        } elseif ($successRate >= 50) {
            $this->line('   Taxa moderada. Revisar prompts.');
        } else {
            $this->line('   Taxa baixa! Revisar sistema.');
        }

        $this->newLine();
        $this->line('PRÓXIMOS PASSOS:');
        $this->line('   1. Verificar melhorias nos registros corrigidos');
        $this->line('   2. Monitorar novos TempArticles');

        if ($this->failedCorrections > 0) {
            $this->line('   3. Investigar registros com falha');
            $this->line('   4. Considerar reprocessamento');
        }
    }
}