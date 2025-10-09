<?php

namespace Src\ContentGeneration\ReviewSchedule\Console;

use Illuminate\Console\Command;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\Eloquent\ReviewScheduleArticle;

class DebugOverviewIssues extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'review-schedule:debug-overview 
                            {--limit=20 : Limit number of problematic articles to examine}
                            {--show-content : Show actual content structure}';

    /**
     * The console command description.
     */
    protected $description = 'Debug specific overview issues to understand validation differences';

    public function handle()
    {
        $limit = (int)$this->option('limit');
        $showContent = $this->option('show-content');

        $this->info('🔍 Debug dos problemas de visao_geral_revisoes...');

        $articles = ReviewScheduleArticle::limit(1000)->get(); // Buscar mais para encontrar problemas
        
        $this->info("📊 Analisando {$articles->count()} artigos...");
        
        $problematicArticles = [];
        $validationResults = [];

        foreach ($articles as $article) {
            $content = $this->getContentArray($article);
            
            if (!$content) {
                continue;
            }

            // Aplicar ambas as validações
            $quickCheckResult = $this->quickCheckValidation($content);
            $fixCommandResult = $this->fixCommandValidation($content);

            // Se há divergência
            if ($quickCheckResult !== $fixCommandResult) {
                $problematicArticles[] = [
                    'id' => $article->_id ?? $article->id,
                    'title' => substr($article->title, 0, 60) . '...',
                    'vehicle' => $this->getVehicleInfo($content),
                    'quick_check' => $quickCheckResult,
                    'fix_command' => $fixCommandResult,
                    'overview_type' => gettype($content['visao_geral_revisoes'] ?? null),
                    'overview_sample' => $this->getOverviewSample($content, $showContent)
                ];

                if (count($problematicArticles) >= $limit) {
                    break;
                }
            }

            // Coletar estatísticas
            $key = $quickCheckResult . '_vs_' . $fixCommandResult;
            $validationResults[$key] = ($validationResults[$key] ?? 0) + 1;
        }

        $this->displayDebugResults($problematicArticles, $validationResults, $showContent);
    }

    private function getContentArray($article): ?array
    {
        $content = $article->content;
        
        if (is_array($content)) {
            return $content;
        }
        
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : null;
        }
        
        return null;
    }

    private function quickCheckValidation(array $content): string
    {
        // Lógica do QuickContentCheck
        if (!isset($content['visao_geral_revisoes'])) {
            return 'missing';
        }

        $overview = $content['visao_geral_revisoes'];
        
        if (empty($overview)) {
            return 'missing';
        }

        // Se é string, verificar se tem tamanho mínimo
        if (is_string($overview)) {
            return strlen(trim($overview)) >= 100 ? 'ok' : 'invalid';
        }

        // Se é array, verificar estrutura básica
        if (is_array($overview)) {
            if (count($overview) < 3) {
                return 'invalid';
            }
            
            $firstItem = $overview[0] ?? null;
            if (!is_array($firstItem) || 
                !isset($firstItem['revisao']) || 
                !isset($firstItem['intervalo'])) {
                return 'invalid';
            }
            
            return 'ok';
        }

        return 'invalid';
    }

    private function fixCommandValidation(array $content): string
    {
        // Lógica do FixOverviewSection->needsOverviewFix()
        if (!isset($content['visao_geral_revisoes'])) {
            return 'needs_fix';
        }

        $overview = $content['visao_geral_revisoes'];
        
        // Se é null ou vazio
        if (empty($overview)) {
            return 'needs_fix';
        }

        // Com force=true (que não foi usado), verificar estrutura
        if (is_string($overview)) {
            return strlen(trim($overview)) < 100 ? 'needs_fix' : 'valid';
        }
        
        if (is_array($overview)) {
            // Verificar se é array tabular válido
            if (empty($overview)) {
                return 'needs_fix';
            }
            
            $firstItem = $overview[0] ?? null;
            if (!is_array($firstItem)) {
                return 'needs_fix';
            }
            
            // Verificar campos obrigatórios
            $requiredFields = ['revisao', 'intervalo', 'principais_servicos', 'estimativa_custo'];
            foreach ($requiredFields as $field) {
                if (!isset($firstItem[$field])) {
                    return 'needs_fix';
                }
            }
            
            return 'valid';
        }

        return 'needs_fix';
    }

    private function getVehicleInfo(array $content): string
    {
        $vehicleInfo = $content['extracted_entities'] ?? [];
        $marca = $vehicleInfo['marca'] ?? 'N/A';
        $modelo = $vehicleInfo['modelo'] ?? 'N/A';
        $ano = $vehicleInfo['ano'] ?? 'N/A';
        
        return "$marca $modelo $ano";
    }

    private function getOverviewSample(array $content, bool $showFull): string
    {
        $overview = $content['visao_geral_revisoes'] ?? null;
        
        if ($overview === null) {
            return 'NULL';
        }
        
        if (is_string($overview)) {
            $sample = substr($overview, 0, 100);
            return $showFull ? $overview : $sample . (strlen($overview) > 100 ? '...' : '');
        }
        
        if (is_array($overview)) {
            if (empty($overview)) {
                return 'Array vazio []';
            }
            
            $firstItem = $overview[0] ?? null;
            if ($showFull) {
                return json_encode($overview, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
            
            if (is_array($firstItem)) {
                $fields = array_keys($firstItem);
                return "Array[" . count($overview) . "] com campos: " . implode(', ', $fields);
            }
            
            return "Array[" . count($overview) . "] com elementos: " . gettype($firstItem);
        }
        
        return gettype($overview);
    }

    private function displayDebugResults(array $problematicArticles, array $validationResults, bool $showContent): void
    {
        $this->newLine();
        $this->info('🔍 DIVERGÊNCIAS ENCONTRADAS:');
        
        if (empty($problematicArticles)) {
            $this->info('✅ Não foram encontradas divergências entre as validações!');
            $this->line('Isso significa que os critérios estão alinhados.');
            return;
        }

        // Mostrar estatísticas de divergência
        $this->table(
            ['Quick Check', 'Fix Command', 'Quantidade'],
            array_map(function($key, $count) {
                [$quick, $fix] = explode('_vs_', $key);
                return [$quick, $fix, $count];
            }, array_keys($validationResults), $validationResults)
        );

        $this->newLine();
        $this->info("📋 EXEMPLOS DE ARTIGOS COM DIVERGÊNCIA (primeiros " . count($problematicArticles) . "):");

        foreach ($problematicArticles as $article) {
            $this->newLine();
            $this->line("ID: {$article['id']}");
            $this->line("Veículo: {$article['vehicle']}");
            $this->line("Quick Check: {$article['quick_check']} | Fix Command: {$article['fix_command']}");
            $this->line("Tipo: {$article['overview_type']}");
            
            if ($showContent) {
                $this->line("Conteúdo:");
                $this->line($article['overview_sample']);
            } else {
                $this->line("Estrutura: {$article['overview_sample']}");
            }
            
            $this->line(str_repeat('-', 60));
        }

        $this->newLine();
        $this->info('💡 POSSÍVEIS CAUSAS DA DIVERGÊNCIA:');
        $this->line('1. Critérios diferentes para validação');
        $this->line('2. Campos obrigatórios diferentes');
        $this->line('3. Tratamento diferente de arrays vs strings');
        $this->line('4. Lógica de validação não sincronizada');

        $this->newLine();
        $this->info('🔧 PRÓXIMOS PASSOS:');
        $this->line('1. Unificar critérios de validação');
        $this->line('2. Executar fix-overview com --force para casos edge');
        $this->line('3. Re-executar verificação');
    }
}