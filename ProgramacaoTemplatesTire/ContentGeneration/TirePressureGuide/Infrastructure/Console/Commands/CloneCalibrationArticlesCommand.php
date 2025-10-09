<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * ✅ CloneCalibrationArticlesCommand - COMANDO PARA CLONAR ARTIGOS CALIBRATION
 * 
 * FUNCIONALIDADES:
 * - Clona artigos com template_type="calibration" 
 * - Altera template_used para tire_calibration_car/tire_calibration_motorcycle
 * - Atualiza slug baseado no wordpress_url
 * - Cria título "Calibragem do Pneu do [Marca] [Modelo] [Ano]"
 * - Flag anti-duplicação (cloned_from_calibration=true)
 * - Dry-run para preview
 * - Processamento em lotes
 */
class CloneCalibrationArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tire-pressure-guide:clone-calibration-articles 
                           {--dry-run : Preview changes without executing}
                           {--limit=50 : Number of articles to process per batch}
                           {--make= : Filter by specific make}
                           {--year= : Filter by specific year}
                           {--force : Force clone even if already cloned}
                           {--skip-validation : Skip data validation}';

    /**
     * The console command description.
     */
    protected $description = 'Clone calibration articles with new template_used values and updated titles/slugs';

    /**
     * Mapeamento de template_used antigo para novo
     */
    private const TEMPLATE_MAPPING = [
        'tire_pressure_guide_car' => 'tire_calibration_car',
        'tire_pressure_guide_motorcycle' => 'tire_calibration_motorcycle',
        
        // ✅ NOVOS MAPEAMENTOS PARA ViewModels
        'TirePressureGuideCarViewModel' => 'tire_calibration_car',
        'TirePressureGuideMotorcycleViewModel' => 'tire_calibration_motorcycle',
        
        // Fallbacks para casos antigos
        'ideal_tire_pressure_car' => 'tire_calibration_car',
        'ideal_tire_pressure_motorcycle' => 'tire_calibration_motorcycle',
    ];

    /**
     * Estatísticas da execução
     */
    private array $stats = [
        'total_found' => 0,
        'already_cloned' => 0,
        'successfully_cloned' => 0,
        'errors' => 0,
        'skipped' => 0,
        'car_articles' => 0,
        'motorcycle_articles' => 0,
        'processing_time' => 0,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        $isDryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $force = $this->option('force');

        $this->info("🚀 INICIANDO CLONAGEM DE ARTIGOS CALIBRATION");
        $this->info("Modo: " . ($isDryRun ? '🔍 DRY-RUN (preview)' : '⚡ EXECUÇÃO REAL'));
        $this->info("Limite por lote: {$limit}");
        
        try {
            // 1. Buscar artigos calibration para clonar
            $articlesToClone = $this->getArticlesToClone($force);
            
            if ($articlesToClone->isEmpty()) {
                $this->warn("❌ Nenhum artigo encontrado para clonagem.");
                $this->displayNoResultsReasons($force);
                return 0;
            }

            $this->stats['total_found'] = $articlesToClone->count();
            $this->info("📊 Total de artigos encontrados: {$this->stats['total_found']}");

            // 2. Aplicar filtros se especificados
            $filteredArticles = $this->applyFilters($articlesToClone);
            
            if ($filteredArticles->count() !== $articlesToClone->count()) {
                $this->info("🔧 Filtros aplicados. Artigos selecionados: {$filteredArticles->count()}");
            }

            // 3. Preview no dry-run
            if ($isDryRun) {
                $this->previewChanges($filteredArticles->take($limit));
                return 0;
            }

            // 4. Processar em lotes
            $this->processInBatches($filteredArticles, $limit);

            // 5. Relatório final
            $this->stats['processing_time'] = round(microtime(true) - $startTime, 2);
            $this->displayFinalReport();

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erro durante execução: " . $e->getMessage());
            Log::error("CloneCalibrationArticlesCommand failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'stats' => $this->stats
            ]);
            return 1;
        }
    }

    /**
     * Buscar artigos calibration para clonar
     */
    private function getArticlesToClone(bool $force): \Illuminate\Database\Eloquent\Collection
    {
        $query = TirePressureArticle::where('template_type', 'calibration');

        // Se não for force, excluir já clonados
        if (!$force) {
            // Verificar se já existe clone (campo cloned_from_calibration OU novo template_used)
            $query->where(function($q) {
                $q->whereNull('cloned_from_calibration')
                  ->orWhere('cloned_from_calibration', '!=', true);
            });
        }

        return $query->orderBy('make')
                    ->orderBy('model') 
                    ->orderBy('year')
                    ->get();
    }

    /**
     * Aplicar filtros especificados
     */
    private function applyFilters(\Illuminate\Database\Eloquent\Collection $articles): \Illuminate\Database\Eloquent\Collection
    {
        if ($make = $this->option('make')) {
            $articles = $articles->filter(fn($article) => 
                strtolower($article->make) === strtolower($make)
            );
        }

        if ($year = $this->option('year')) {
            $articles = $articles->filter(fn($article) => 
                $article->year == $year
            );
        }

        return $articles;
    }

    /**
     * Preview das mudanças (dry-run)
     */
    private function previewChanges(\Illuminate\Database\Eloquent\Collection $articles): void
    {
        $this->info("\n🔍 PREVIEW DAS MUDANÇAS (PRIMEIROS {$articles->count()} ARTIGOS):");
        $this->info(str_repeat('=', 80));

        foreach ($articles as $index => $article) {
            $articleNumber = $index + 1;
            $this->info("\n📄 ARTIGO #{$articleNumber}:");
            $this->line("   Original ID: {$article->id}");
            $this->line("   Veículo: {$article->make} {$article->model} {$article->year}");
            
            // Template usado atual vs novo
            $oldTemplate = $article->template_used;
            $newTemplate = $this->getNewTemplateUsed($oldTemplate);
            $this->line("   Template usado: {$oldTemplate} → {$newTemplate}");
            
            // Título atual vs novo
            $oldTitle = $article->title;
            $newTitle = $this->generateNewTitle($article);
            $this->line("   Título: {$oldTitle}");
            $this->line("           → {$newTitle}");
            
            // Slug atual vs novo  
            $oldSlug = $article->slug;
            $newSlug = $this->generateNewSlug($article);
            $this->line("   Slug: {$oldSlug}");
            $this->line("         → {$newSlug}");

            if ($index >= 4) { // Mostrar só 5 exemplos
                $remaining = $articles->count() - 5;
                if ($remaining > 0) {
                    $this->info("\n... e mais {$remaining} artigos");
                }
                break;
            }
        }

        $this->info("\n✅ Para executar as mudanças, execute sem --dry-run");
    }

    /**
     * Processar artigos em lotes
     */
    private function processInBatches(\Illuminate\Database\Eloquent\Collection $articles, int $batchSize): void
    {
        $chunks = $articles->chunk($batchSize);
        $totalChunks = $chunks->count();

        $this->info("📦 Processando em {$totalChunks} lotes de {$batchSize} artigos cada:");

        foreach ($chunks as $chunkIndex => $chunk) {
            $this->info("\n🔄 Processando lote " . ($chunkIndex + 1) . "/{$totalChunks}...");
            
            $this->processChunk($chunk);
            
            // Progress bar
            $processed = ($chunkIndex + 1) * $batchSize;
            $total = $articles->count();
            $percentage = round(($processed / $total) * 100, 1);
            $this->line("   ✅ Progresso: {$processed}/{$total} ({$percentage}%)");
        }
    }

    /**
     * Processar um chunk de artigos
     */
    private function processChunk(\Illuminate\Database\Eloquent\Collection $chunk): void
    {
        foreach ($chunk as $originalArticle) {
            try {
                $this->cloneArticle($originalArticle);
                $this->stats['successfully_cloned']++;
                
                // Incrementar contadores por tipo
                if ($this->isMotorcycle($originalArticle)) {
                    $this->stats['motorcycle_articles']++;
                } else {
                    $this->stats['car_articles']++;
                }

            } catch (\Exception $e) {
                $this->stats['errors']++;
                $this->error("❌ Erro ao clonar {$originalArticle->make} {$originalArticle->model} {$originalArticle->year}: " . $e->getMessage());
                
                Log::error("Failed to clone calibration article", [
                    'article_id' => $originalArticle->id,
                    'vehicle' => "{$originalArticle->make} {$originalArticle->model} {$originalArticle->year}",
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Clonar um artigo individual
     */
    private function cloneArticle(TirePressureArticle $originalArticle): TirePressureArticle
    {
        // Verificar se já foi clonado
        if ($originalArticle->cloned_from_calibration === true && !$this->option('force')) {
            $this->stats['already_cloned']++;
            $this->line("⚠️  Já clonado: {$originalArticle->make} {$originalArticle->model} {$originalArticle->year}");
            return $originalArticle;
        }

        // Criar novo artigo baseado no original
        $clonedData = $originalArticle->toArray();
        
        // Remover ID para criar novo registro
        unset($clonedData['id']);
        
        // ✅ MUDANÇAS SOLICITADAS:
        
        // 1. Alterar template_used
        $clonedData['template_used'] = $this->getNewTemplateUsed($originalArticle->template_used);
        
        // 2. Alterar slug baseado no wordpress_url
        $clonedData['slug'] = $this->generateNewSlug($originalArticle);

         // Alterar template_type
        $clonedData['template_type'] = 'tire_calibration';
        
        // 3. Alterar título para padrão "Calibragem do Pneu do [Marca] [Modelo] [Ano]"
        $clonedData['title'] = $this->generateNewTitle($originalArticle);
        
        // 4. Atualizar wordpress_slug para coincidir com slug
        $clonedData['wordpress_slug'] = $clonedData['slug'];
        
        // 5. Marcar como clonado e referenciar original
        $clonedData['cloned_from_calibration'] = true;
        $clonedData['original_calibration_article_id'] = (string) $originalArticle->id;
        $clonedData['clone_created_at'] = Carbon::now();
        
        // 6. Atualizar timestamps
        $clonedData['created_at'] = Carbon::now();
        $clonedData['updated_at'] = Carbon::now();
        
        // 7. Atualizar meta description
        $clonedData['meta_description'] = $this->generateNewMetaDescription($originalArticle);

        // 8. Atualizar URL do WordPress
        $clonedData['wordpress_url'] = $clonedData['slug'];
        
        // Criar novo artigo
        $clonedArticle = TirePressureArticle::create($clonedData);
        
        // Marcar artigo original como "pai" do clone
        $originalArticle->update([
            'cloned_from_calibration' => true,
            'clone_article_id' => (string) $clonedArticle->id,
            'clone_created_at' => Carbon::now()
        ]);

        $this->line("✅ Clonado: {$originalArticle->make} {$originalArticle->model} {$originalArticle->year}");
        
        return $clonedArticle;
    }

    /**
     * Obter novo template_used baseado no mapeamento
     */
    private function getNewTemplateUsed(string $currentTemplate): string
    {
        return self::TEMPLATE_MAPPING[$currentTemplate] ?? $currentTemplate;
    }

    /**
     * Gerar novo slug baseado no wordpress_url
     */
    private function generateNewSlug(TirePressureArticle $article): string
    {
        // Extrair slug do wordpress_url se existir
        if (!empty($article->wordpress_url)) {
            // Se wordpress_url já contém um slug válido, usar como base
            $baseSlug = trim(str_replace(['http://', 'https://', 'www.'], '', $article->wordpress_url), '/');
            
            // Se parece com um slug (sem domínio), usar como base
            if (!str_contains($baseSlug, '.') && !str_contains($baseSlug, '/')) {
                return $baseSlug;
            }
        }
        
        // Caso contrário, gerar slug baseado no padrão novo
        $make = Str::slug($article->make);
        $model = Str::slug($article->model);
        $year = $article->year;
        
        return "calibragem-pneu-{$make}-{$model}-{$year}";
    }

    /**
     * Gerar novo título no padrão solicitado
     */
    private function generateNewTitle(TirePressureArticle $article): string
    {
        $make = $article->make;
        $model = $article->model;
        $year = $article->year;
        
        return "Calibragem do Pneu do {$make} {$model} {$year}";
    }

    /**
     * Gerar nova meta description
     */
    private function generateNewMetaDescription(TirePressureArticle $article): string
    {
        $make = $article->make;
        $model = $article->model;
        $year = $article->year;
        
        return "Guia completo para calibragem do pneu do {$make} {$model} {$year}. Pressões corretas, passo a passo e dicas de manutenção.";
    }

    /**
     * Verificar se é motocicleta
     */
    private function isMotorcycle(TirePressureArticle $article): bool
    {
        $vehicleData = $article->vehicle_data ?? [];
        return ($vehicleData['is_motorcycle'] ?? false) === true ||
               ($vehicleData['vehicle_type'] ?? '') === 'motorcycle' ||
               str_contains($article->template_used, 'motorcycle');
    }

    /**
     * Exibir razões para nenhum resultado
     */
    private function displayNoResultsReasons(bool $force): void
    {
        $this->info("\n🔍 POSSÍVEIS RAZÕES:");
        
        if (!$force) {
            $alreadyCloned = TirePressureArticle::where('template_type', 'calibration')
                                               ->where('cloned_from_calibration', true)
                                               ->count();
            
            if ($alreadyCloned > 0) {
                $this->line("• {$alreadyCloned} artigos já foram clonados anteriormente");
                $this->line("• Use --force para clonar novamente");
            }
        }
        
        $totalCalibration = TirePressureArticle::where('template_type', 'calibration')->count();
        $this->line("• Total de artigos calibration na base: {$totalCalibration}");
        
        if ($this->option('make') || $this->option('year')) {
            $this->line("• Filtros aplicados podem estar muito restritivos");
        }
    }

    /**
     * Exibir relatório final
     */
    private function displayFinalReport(): void
    {
        $this->info("\n" . str_repeat('=', 80));
        $this->info("📊 RELATÓRIO FINAL DA CLONAGEM");
        $this->info(str_repeat('=', 80));
        
        $this->info("✅ Estatísticas gerais:");
        $this->line("   • Total encontrados: {$this->stats['total_found']}");
        $this->line("   • Clonados com sucesso: {$this->stats['successfully_cloned']}");
        $this->line("   • Já clonados anteriormente: {$this->stats['already_cloned']}");
        $this->line("   • Erros: {$this->stats['errors']}");
        $this->line("   • Tempo de processamento: {$this->stats['processing_time']}s");
        
        $this->info("\n🚗 Por tipo de veículo:");
        $this->line("   • Carros: {$this->stats['car_articles']}");
        $this->line("   • Motocicletas: {$this->stats['motorcycle_articles']}");
        
        if ($this->stats['errors'] > 0) {
            $this->warn("\n⚠️  Houve {$this->stats['errors']} erros. Verifique os logs para detalhes.");
        }
        
        $successRate = $this->stats['total_found'] > 0 
            ? round(($this->stats['successfully_cloned'] / $this->stats['total_found']) * 100, 1)
            : 0;
        
        $this->info("\n🎯 Taxa de sucesso: {$successRate}%");
        
        if ($this->stats['successfully_cloned'] > 0) {
            $this->info("\n✅ PRÓXIMOS PASSOS RECOMENDADOS:");
            $this->line("1. Verificar artigos clonados:");
            $this->line("   php artisan tire-pressure-guide:validate-cloned-articles");
            $this->line("2. Executar refinamento Claude:");
            $this->line("   php artisan tire-pressure-guide:refine-sections --filter-cloned");
            $this->line("3. Publicar artigos:");
            $this->line("   php artisan tire-pressure-guide:publish --filter-cloned --limit=10");
        }
    }
}