<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;

/**
 * InvestigateGenericVersions - VERSÃO LIMPA v3.0
 * 
 * SISTEMA SIMPLIFICADO:
 * - Detecta apenas palavras genéricas: comfort, style, premium, base, entry, standard
 * - Remove completamente verificação de especificações duplicadas
 * - Usa apenas um campo: has_generic_versions (true/false)
 * - Sistema mais simples e confiável
 */

/**
 * Comandos
 * php artisan temp-article:investigate-generic-versions --reset
 * php artisan temp-article:investigate-generic-versions --flag-for-correction --limit=1000 --force-all
 */
class InvestigateGenericVersionPatternsTempArticleCommand extends Command
{
    protected $signature = 'temp-article:investigate-generic-versions
                            {--limit=500 : Número máximo de registros}
                            {--dry-run : Executar sem modificar dados}
                            {--flag-for-correction : Marcar registros para correção}
                            {--force-all : Processar todos os registros}
                            {--debug : Debug detalhado}
                            {--reset : Resetar todos os flags}';

    protected $description = 'Detecção limpa: apenas palavras genéricas comfort/style/premium/base/entry/standard';

    // Apenas palavras genéricas - nada mais
    private const GENERIC_WORDS = ['comfort', 'style', 'premium', 'base', 'entry', 'standard'];

    private int $totalAnalyzed = 0;
    private int $genericFound = 0;
    private int $flaggedForCorrection = 0;
    private int $resetCount = 0;

    public function handle(): int
    {
        if ($this->option('reset')) {
            return $this->handleReset();
        }

        $this->info('DETECÇÃO SIMPLES DE VERSÕES GENÉRICAS v3.0');
        $this->info('Detecta apenas: ' . implode(', ', self::GENERIC_WORDS));
        $this->newLine();

        $config = $this->getConfig();
        $this->displayConfig($config);

        $tempArticles = $this->getTempArticles($config);

        if ($tempArticles->isEmpty()) {
            $this->info('Nenhum TempArticle encontrado.');
            return self::SUCCESS;
        }

        $this->info("Analisando {$tempArticles->count()} TempArticles...");
        $this->newLine();

        foreach ($tempArticles as $tempArticle) {
            $this->analyzeArticle($tempArticle, $config);
        }

        $this->displayResults($config);
        return self::SUCCESS;
    }

    /**
     * Análise simples - apenas verificar palavras genéricas
     */
    private function analyzeArticle($tempArticle, array $config): void
    {
        $this->totalAnalyzed++;

        $vehicleKey = $this->getVehicleKey($tempArticle);
        $hasGeneric = false;
        $genericVersions = [];

        if ($config['debug']) {
            $this->line("Analisando: {$vehicleKey}");
        }

        $content = $tempArticle->content ?? [];

        // Verificar especificações por versão
        if (isset($content['especificacoes_por_versao'])) {
            foreach ($content['especificacoes_por_versao'] as $spec) {
                $versao = $spec['versao'] ?? '';
                if ($this->isGeneric($versao, $config)) {
                    $hasGeneric = true;
                    $genericVersions[] = $versao;
                }
            }
        }

        // Verificar tabela de carga
        if (isset($content['tabela_carga_completa']['condicoes'])) {
            foreach ($content['tabela_carga_completa']['condicoes'] as $condicao) {
                $versao = $condicao['versao'] ?? '';
                if ($this->isGeneric($versao, $config) && !in_array($versao, $genericVersions)) {
                    $hasGeneric = true;
                    $genericVersions[] = $versao;
                }
            }
        }

        if ($hasGeneric) {
            $this->genericFound++;

            if ($config['debug']) {
                $this->line("  GENÉRICAS: " . implode(', ', $genericVersions));
            }

            if ($config['flag_for_correction'] && !$config['dry_run']) {
                $this->flagForCorrection($tempArticle);
            }
        } else {
            $tempArticle->update([
                'has_specific_versions' => true,
            ]);

            if ($config['debug']) {
                $this->line("  OK - Todas versões específicas");
            }
        }
    }

    /**
     * Verificar se versão é genérica - MÉTODO PRINCIPAL
     */
    private function isGeneric(string $versao, array $config): bool
    {
        $versaoLower = strtolower(trim($versao));

        if ($config['debug']) {
            $this->line("    Verificando: '{$versao}'");
        }

        foreach (self::GENERIC_WORDS as $word) {
            if (str_contains($versaoLower, $word)) {
                if ($config['debug']) {
                    $this->line("    ❌ GENÉRICA: contém '{$word}'");
                }
                return true;
            }
        }

        if ($config['debug']) {
            $this->line("    ✅ OK: específica");
        }

        return false;
    }

    /**
     * Marcar para correção - SISTEMA SIMPLIFICADO
     */
    private function flagForCorrection($tempArticle): void
    {
        try {
            $tempArticle->update([
                'has_generic_versions' => true,
                'flagged_at' => now()
            ]);

            $this->flaggedForCorrection++;
        } catch (\Exception $e) {
            Log::error('Erro ao marcar TempArticle', [
                'id' => $tempArticle->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Reset de todos os flags
     */
    private function handleReset(): int
    {
        $this->warn('RESET: Limpando todos os flags de versões genéricas');

        if (!$this->confirm('Tem certeza? Isso vai limpar todos os flags.')) {
            $this->info('Reset cancelado.');
            return self::SUCCESS;
        }

        $this->resetCount = TempArticle::whereNull('has_generic_versions')
            ->update([
                'has_generic_versions' => null,
                'flagged_at' => null,
                'needs_version_correction' => null,
                'version_correction_priority' => null,
                'version_issues_detected' => null,
                'correction_flagged_at' => null,
                'has_specific_versions' => null,                
            ]);

        $this->info("Reset concluído: {$this->resetCount} registros limpos.");
        return self::SUCCESS;
    }

    /**
     * Obter TempArticles
     */
    private function getTempArticles(array $config)
    {
        $query = TempArticle::where('status', 'draft');

        if (!$config['force_all']) {
            // Apenas os que não foram flagged ainda
            $query->whereNull('has_generic_versions');
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($config['limit'])
            ->get();
    }

    /**
     * Configuração
     */
    private function getConfig(): array
    {
        return [
            'limit' => (int) $this->option('limit'),
            'dry_run' => $this->option('dry-run'),
            'flag_for_correction' => $this->option('flag-for-correction'),
            'force_all' => $this->option('force-all'),
            'debug' => $this->option('debug')
        ];
    }

    /**
     * Exibir configuração
     */
    private function displayConfig(array $config): void
    {
        $this->info('CONFIGURAÇÃO:');
        $this->line("   Limite: {$config['limit']}");
        $this->line("   Dry run: " . ($config['dry_run'] ? 'SIM' : 'NÃO'));
        $this->line("   Marcar para correção: " . ($config['flag_for_correction'] ? 'SIM' : 'NÃO'));
        $this->line("   Forçar todos: " . ($config['force_all'] ? 'SIM' : 'NÃO'));
        $this->line("   Debug: " . ($config['debug'] ? 'SIM' : 'NÃO'));
        $this->newLine();
    }

    /**
     * Exibir resultados
     */
    private function displayResults(array $config): void
    {
        $this->info('RESULTADOS:');
        $this->newLine();

        $this->line("Total analisado: {$this->totalAnalyzed}");
        $this->line("Com versões genéricas: {$this->genericFound}");

        if ($config['flag_for_correction'] && !$config['dry_run']) {
            $this->line("Marcados para correção: {$this->flaggedForCorrection}");
        }

        $percentage = $this->totalAnalyzed > 0
            ? round(($this->genericFound / $this->totalAnalyzed) * 100, 1)
            : 0;

        $this->line("Percentual com problemas: {$percentage}%");
        $this->newLine();

        if ($this->genericFound > 0) {
            $this->warn("Encontradas {$this->genericFound} TempArticles com versões genéricas.");
            $this->line("Use o CorrectGenericVersionsCommand para corrigir.");
        } else {
            $this->info("Todos os artigos analisados têm versões específicas.");
        }
    }

    /**
     * Obter chave do veículo
     */
    private function getVehicleKey($tempArticle): string
    {
        $entities = $tempArticle->extracted_entities ?? [];
        $marca = $entities['marca'] ?? 'Unknown';
        $modelo = $entities['modelo'] ?? 'Unknown';
        return "{$marca} {$modelo}";
    }
}
