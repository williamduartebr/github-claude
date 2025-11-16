<?php

namespace Src\GenericArticleGenerator\Commands;

use Illuminate\Console\Command;
use Src\GenericArticleGenerator\Infrastructure\Eloquent\GenerationTempArticle;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

/**
 * USO:
 * php artisan generation:seed-oil-articles --file=oil_technical_tables_v2.json
 */

class SeedOilTableArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generation:seed-oil-table-articles 
                          {--file= : Arquivo JSON específico (ex: oil_technical_tables_v2.json)}
                          {--dry-run : Simular sem inserir no banco}
                          {--preview : Mostrar preview dos títulos sem processar}
                          {--force : Forçar inserção mesmo que já existam}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Popular collection generation_temp_articles com títulos sobre óleo automotivo';

    /**
     * Caminho base dos arquivos JSON
     */
    private const JSON_PATH = 'src/GenericArticleGenerator/Data/ArticleTitles';

    /**
     * Contadores para estatísticas
     */
    private int $lastInsertedCount = 0;
    private int $lastSkippedCount = 0;
    private int $lastErrorCount = 0;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->displayHeader();

        // Verificar se o diretório existe
        if (!File::isDirectory(base_path(self::JSON_PATH))) {
            $this->error('❌ Diretório não encontrado: ' . self::JSON_PATH);
            $this->line('   Crie o diretório primeiro com os arquivos JSON.');
            return self::FAILURE;
        }

        // Modo preview
        if ($this->option('preview')) {
            return $this->previewTitles();
        }

        // Processar arquivo específico
        if ($file = $this->option('file')) {
            return $this->processFile($file);
        }

        // Processar todos os arquivos
        return $this->processAllFiles();
    }

    /**
     * Exibe cabeçalho bonito
     */
    private function displayHeader(): void
    {
        $this->newLine();
        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║       🛢️  SEED DE ARTIGOS SOBRE ÓLEO AUTOMOTIVO          ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->newLine();
    }

    /**
     * Preview dos títulos sem processar
     */
    private function previewTitles(): int
    {
        $files = File::glob(base_path(self::JSON_PATH . '/*.json'));

        if (empty($files)) {
            $this->warn('⚠️  Nenhum arquivo JSON encontrado.');
            return self::SUCCESS;
        }

        $this->info('📚 Arquivos encontrados: ' . count($files));
        $this->newLine();

        $totalTitles = 0;

        foreach ($files as $file) {
            $data = json_decode(File::get($file), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('❌ Erro ao ler: ' . basename($file));
                continue;
            }

            $filename = basename($file);
            $count = $this->countTitles($data);
            $totalTitles += $count;

            $this->info("📄 {$filename}");
            $this->line("   ├─ Tema: <fg=cyan>{$data['theme']}</>");
            $this->line("   ├─ Categoria: {$data['category_slug']} (ID: {$data['category_id']})");
            $this->line("   ├─ Total de títulos: <fg=yellow>{$count}</>");
            $this->line("   └─ Subcategorias:");

            foreach ($data['subcategories'] as $subSlug => $subData) {
                $subCount = count($subData['titles']);
                $priority = $subData['priority'];
                $priorityColor = $this->getPriorityColor($priority);

                $this->line("      ├─ {$subSlug}: <fg=white>{$subCount}</> títulos " .
                           "(<fg={$priorityColor}>{$priority}</>)");
            }

            $this->newLine();
        }

        $this->info("📊 Total geral: <fg=green>{$totalTitles}</> títulos");
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Processar arquivo específico
     */
    private function processFile(string $filename): int
    {
        // Adicionar .json se não tiver
        if (!str_ends_with($filename, '.json')) {
            $filename .= '.json';
        }

        $path = base_path(self::JSON_PATH . '/' . $filename);

        if (!File::exists($path)) {
            $this->error("❌ Arquivo não encontrado: {$filename}");
            $this->line("   Caminho: {$path}");
            return self::FAILURE;
        }

        $data = json_decode(File::get($path), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('❌ Erro ao decodificar JSON: ' . json_last_error_msg());
            return self::FAILURE;
        }

        $this->validateJsonStructure($data, $filename);

        $result = $this->seedFromData($data, $filename);

        if ($result === self::SUCCESS) {
            $this->displaySingleFileSummary($filename);
        }

        return $result;
    }

    /**
     * Processar todos os arquivos
     */
    private function processAllFiles(): int
    {
        $files = File::glob(base_path(self::JSON_PATH . '/*.json'));

        if (empty($files)) {
            $this->warn('⚠️  Nenhum arquivo JSON encontrado em: ' . self::JSON_PATH);
            $this->line('   Crie arquivos JSON com títulos para popular.');
            return self::SUCCESS;
        }

        $this->info('📁 Encontrados ' . count($files) . ' arquivo(s)');
        $this->newLine();

        $totalInserted = 0;
        $totalSkipped = 0;
        $totalErrors = 0;

        foreach ($files as $file) {
            $data = json_decode(File::get($file), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('❌ Erro ao processar: ' . basename($file));
                continue;
            }

            $filename = basename($file);

            $result = $this->seedFromData($data, $filename);

            if ($result === self::SUCCESS) {
                $totalInserted += $this->lastInsertedCount;
                $totalSkipped += $this->lastSkippedCount;
                $totalErrors += $this->lastErrorCount;
            }
        }

        $this->displayFullSummary($totalInserted, $totalSkipped, $totalErrors);

        return self::SUCCESS;
    }

    /**
     * Seed a partir dos dados do JSON
     */
    private function seedFromData(array $data, string $source): int
    {
        $totalTitles = $this->countTitles($data);

        $this->info("📝 Processando: <fg=cyan>{$source}</>");
        $this->line("   ├─ Tema: {$data['theme']}");
        $this->line("   ├─ Total de títulos: {$totalTitles}");

        if ($this->option('dry-run')) {
            $this->warn('   └─ 🔍 Modo DRY-RUN ativo (simulação)');
        }

        $this->newLine();

        $bar = $this->output->createProgressBar($totalTitles);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | %message%');

        $inserted = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($data['subcategories'] as $subSlug => $subData) {
            foreach ($subData['titles'] as $title) {

                $slug = Str::slug($title);

                $bar->setMessage(Str::limit($title, 50));

                // Verificar se já existe
                $exists = GenerationTempArticle::where('slug', $slug)->exists();

                if ($exists && !$this->option('force')) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                if (!$this->option('dry-run')) {
                    try {
                        // Usar updateOrCreate para evitar duplicatas
                        GenerationTempArticle::updateOrCreate(
                            ['slug' => $slug],
                            [
                                'title' => $title,
                                'maintenance_category_id' => $data['category_id'],
                                'maintenance_subcategory_id' => $subData['subcategory_id'],
                                'theme' => $data['theme'],
                                'generation_status' => 'pending',
                                'generation_priority' => $subData['priority'],
                                'generation_model' => null,
                                'retry_count' => 0,
                                'generation_cost' => 0,
                                'metadata' => [
                                    'source' => 'seed_command',
                                    'file' => $source,
                                    'theme' => $data['theme'],
                                    'subcategory_slug' => $subSlug,
                                    'estimated_tokens' => $this->estimateTokens($title),
                                    'seeded_at' => now()->toISOString(),
                                ]
                            ]
                        );

                        $inserted++;

                    } catch (\Exception $e) {
                        $errors++;
                        $this->newLine();
                        $this->error("   ❌ Erro ao inserir: {$title}");
                        $this->line("      Motivo: " . $e->getMessage());
                    }
                } else {
                    // Dry-run conta como inserido
                    $inserted++;
                }

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->lastInsertedCount = $inserted;
        $this->lastSkippedCount = $skipped;
        $this->lastErrorCount = $errors;

        $this->displayFileResults($source, $inserted, $skipped, $errors, $totalTitles);

        return self::SUCCESS;
    }

    /**
     * Exibir resultados de um arquivo
     */
    private function displayFileResults(
        string $file,
        int $inserted,
        int $skipped,
        int $errors,
        int $total
    ): void {
        $this->info("✅ Arquivo processado: <fg=cyan>{$file}</>");

        if ($this->option('dry-run')) {
            $this->line("   ├─ Simulados: <fg=green>{$inserted}</>");
        } else {
            $this->line("   ├─ Inseridos: <fg=green>{$inserted}</>");
        }

        $this->line("   ├─ Ignorados (já existem): <fg=yellow>{$skipped}</>");

        if ($errors > 0) {
            $this->line("   ├─ Erros: <fg=red>{$errors}</>");
        }

        $this->line("   └─ Total processado: <fg=cyan>{$total}</>");
        $this->newLine();
    }

    /**
     * Exibir resumo de um único arquivo
     */
    private function displaySingleFileSummary(string $filename): void
    {
        $this->newLine();
        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║                     📊 RESULTADO                           ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $mode = $this->option('dry-run') ? 'Simulados' : 'Inseridos';

        $this->line("   Arquivo: <fg=cyan>{$filename}</>");
        $this->line("   {$mode}: <fg=green>{$this->lastInsertedCount}</> artigos");
        $this->line("   Ignorados: <fg=yellow>{$this->lastSkippedCount}</> artigos");

        if ($this->lastErrorCount > 0) {
            $this->line("   Erros: <fg=red>{$this->lastErrorCount}</> artigos");
        }

        $total = $this->lastInsertedCount + $this->lastSkippedCount;
        $this->line("   Total: <fg=white>{$total}</> artigos");

        $this->newLine();

        if (!$this->option('dry-run') && $this->lastInsertedCount > 0) {
            $this->info('✅ Artigos prontos para geração!');
            $this->line('   Execute: <fg=cyan>php artisan generation:generate-standard</>');
        }

        $this->newLine();
    }

    /**
     * Exibir resumo completo de todos os arquivos
     */
    private function displayFullSummary(int $totalInserted, int $totalSkipped, int $totalErrors): void
    {
        $this->newLine();
        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║                  📊 RESUMO FINAL                           ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $mode = $this->option('dry-run') ? 'Simulados' : 'Inseridos';

        $this->line("   {$mode}: <fg=green>{$totalInserted}</> artigos");
        $this->line("   Ignorados: <fg=yellow>{$totalSkipped}</> artigos");

        if ($totalErrors > 0) {
            $this->line("   Erros: <fg=red>{$totalErrors}</> artigos");
        }

        $grandTotal = $totalInserted + $totalSkipped;
        $this->line("   Grand Total: <fg=cyan>{$grandTotal}</> artigos");

        $this->newLine();

        // Estatísticas por prioridade
        if (!$this->option('dry-run') && $totalInserted > 0) {
            $this->displayPriorityStats();
        }

        if (!$this->option('dry-run') && $totalInserted > 0) {
            $this->info('✅ Artigos prontos para geração!');
            $this->newLine();
            $this->line('   Comandos disponíveis:');
            $this->line('   ├─ <fg=cyan>php artisan generation:generate-standard</>');
            $this->line('   ├─ <fg=cyan>php artisan generation:generate-intermediate --only-failed-standard</>');
            $this->line('   └─ <fg=cyan>php artisan generation:stats</> (ver estatísticas)');
        }

        $this->newLine();
    }

    /**
     * Exibir estatísticas por prioridade
     */
    private function displayPriorityStats(): void
    {
        $stats = GenerationTempArticle::where('generation_status', 'pending')
            ->where('metadata->source', 'seed_command')
            ->select('generation_priority', DB::raw('count(*) as total'))
            ->groupBy('generation_priority')
            ->get();

        if ($stats->isNotEmpty()) {
            $this->line('   Distribuição por prioridade:');

            foreach ($stats as $stat) {
                $priority = $stat->generation_priority;
                $total = $stat->total;
                $color = $this->getPriorityColor($priority);

                $this->line("   ├─ <fg={$color}>{$priority}</> : {$total} artigos");
            }

            $this->newLine();
        }
    }

    /**
     * Validar estrutura do JSON
     */
    private function validateJsonStructure(array $data, string $filename): void
    {
        $required = ['category_id', 'category_slug', 'theme', 'subcategories'];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                $this->warn("⚠️  Campo obrigatório ausente em {$filename}: {$field}");
            }
        }
    }

    /**
     * Contar total de títulos no JSON
     */
    private function countTitles(array $data): int
    {
        $count = 0;

        foreach ($data['subcategories'] as $sub) {
            $count += count($sub['titles'] ?? []);
        }

        return $count;
    }

    /**
     * Estimar tokens baseado no título
     */
    private function estimateTokens(string $title): int
    {
        // Palavras-chave que indicam conteúdo técnico mais extenso
        $technicalKeywords = [
            'viscosidade', 'especificação', 'ATF', 'sintético',
            'API', 'ACEA', 'dexron', 'mercon', 'CVT',
            'quilometragem', 'consumo', 'retífica', 'GL-4', 'GL-5'
        ];

        // Palavras-chave que indicam urgência/problema (conteúdo mais prático)
        $urgencyKeywords = [
            'urgente', 'acidente', 'emergência', 'problema',
            'barulho', 'vazamento', 'queimando', 'consumindo',
            'nunca troquei', 'morreu', 'estragar'
        ];

        // Palavras-chave de comparação (conteúdo médio)
        $comparisonKeywords = [
            'vs', 'comparação', 'diferença', 'qual melhor',
            'teste', 'experiência', 'funcionou'
        ];

        $titleLower = mb_strtolower($title);

        // Artigos técnicos tendem a ser mais longos
        foreach ($technicalKeywords as $keyword) {
            if (stripos($titleLower, $keyword) !== false) {
                return 4200;
            }
        }

        // Artigos de urgência são mais diretos e práticos
        foreach ($urgencyKeywords as $keyword) {
            if (stripos($titleLower, $keyword) !== false) {
                return 3200;
            }
        }

        // Artigos de comparação são médios
        foreach ($comparisonKeywords as $keyword) {
            if (stripos($titleLower, $keyword) !== false) {
                return 3600;
            }
        }

        // Padrão para artigos informativos gerais
        return 3700;
    }

    /**
     * Obter cor para prioridade
     */
    private function getPriorityColor(string $priority): string
    {
        return match(strtolower($priority)) {
            'high' => 'red',
            'medium' => 'yellow',
            'low' => 'green',
            default => 'white',
        };
    }
}