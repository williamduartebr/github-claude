<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;

/**
 * PublishTireCalibrationDirectCommand
 * 
 * Comando direto para testes locais: TireCalibration → Article
 * Pula o intermediário TempArticle para agilizar desenvolvimento e testes.
 * 
 * CRITÉRIOS DE FILTRO:
 * - claude_refinement_version = "v4_completed"
 * - version = "v2"
 * - enrichment_phase = "claude_3b_completed"
 * 
 * FLUXO DIRETO:
 * TireCalibration → Article (sem passar por TempArticle)
 * 
 * @author Claude Sonnet 4
 * @version 1.0
 */
class PublishTireCalibrationDirectCommand extends Command
{
    /**
     * Nome e assinatura do comando.
     */
    protected $signature = 'tire-calibration:publish-direct
                           {--limit=10 : Número máximo de artigos a processar}
                           {--dry-run : Simular execução sem persistir dados}
                           {--force : Republicar artigos mesmo se slug já existir}
                           {--debug : Exibir informações detalhadas}
                           {--vehicle= : Filtrar por veículo específico (ex: "audi a3")}
                           {--humanize-dates : Humanizar datas após publicação}
                           {--days=7 : Dias para distribuir artigos (para testes)}';

    /**
     * Descrição do comando.
     */
    protected $description = 'Publicação direta de TireCalibration para Article (comando para testes locais)';

    /**
     * Execute o comando.
     */
    public function handle(): int
    {
        $this->displayHeader();

        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $debug = $this->option('debug');
        $vehicleFilter = $this->option('vehicle');

        try {
            // Buscar artigos elegíveis
            $eligibleArticles = $this->findEligibleArticles($limit, $force, $vehicleFilter);

            if ($eligibleArticles->isEmpty()) {
                $this->warn('Nenhum artigo de calibragem encontrado para publicação direta.');
                return Command::SUCCESS;
            }

            $this->displayFoundArticles($eligibleArticles, $dryRun);

            // Confirmar execução se não for dry-run
            if (!$dryRun && !$this->confirmExecution()) {
                $this->info('Operação cancelada pelo usuário.');
                return Command::SUCCESS;
            }

            // Processar artigos
            $results = $this->processArticlesDirect($eligibleArticles, $dryRun, $debug);

            // Exibir resultados
            $this->displayResults($results);

            // Pós-processamento
            if (!$dryRun && $results['published'] > 0) {
                $this->runPostProcessing();
            }

            return $results['errors'] === 0 ? Command::SUCCESS : Command::FAILURE;

        } catch (\Exception $e) {
            $this->error("Erro fatal: {$e->getMessage()}");
            Log::error('PublishTireCalibrationDirectCommand failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Exibe cabeçalho do comando
     */
    private function displayHeader(): void
    {
        $this->info('PUBLICAÇÃO DIRETA - TESTE LOCAL');
        $this->info('TireCalibration → Article');
        $this->info(now()->format('d/m/Y H:i:s'));
        $this->newLine();
        $this->warn('⚠️  Comando para desenvolvimento/testes - pula TempArticle');
        $this->newLine();
    }

    /**
     * Busca artigos elegíveis para publicação direta
     */
    private function findEligibleArticles(int $limit, bool $force, ?string $vehicleFilter)
    {
        $this->info('Buscando artigos de calibragem finalizados...');

        $query = TireCalibration::where([
            'claude_refinement_version' => 'v4_completed',
            'version' => 'v2',
            'enrichment_phase' => 'claude_3b_completed'
        ])
        ->whereNotNull('article_refined')
        ->whereNotNull('claude_enhancements');

        // Filtro por veículo específico
        if ($vehicleFilter) {
            $vehicleParts = explode(' ', strtolower($vehicleFilter));
            if (count($vehicleParts) >= 2) {
                $make = ucfirst($vehicleParts[0]);
                $model = ucfirst($vehicleParts[1]);
                
                $query->where('vehicle_make', $make)
                      ->where('vehicle_model', 'like', "%{$model}%");
            }
        }

        // Se não forçar, excluir já publicados diretamente
        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('direct_published_at')
                  ->orWhere('direct_publish_status', '!=', 'published');
            });
        }

        $query->orderBy('claude_completed_at', 'desc')
              ->limit($limit);

        return $query->get();
    }

    /**
     * Exibe artigos encontrados
     */
    private function displayFoundArticles($articles, bool $dryRun): void
    {
        $this->info("Encontrados {$articles->count()} artigos para publicação:");
        
        foreach ($articles as $index => $article) {
            $vehicle = "{$article->vehicle_make} {$article->vehicle_model}";
            $slug = $this->generateFinalSlug($article);
            
            $this->line("   " . ($index + 1) . ". {$vehicle} → {$slug}");
        }

        $this->newLine();
        
        if ($dryRun) {
            $this->warn('MODO SIMULAÇÃO - Nenhuma alteração será persistida');
        }
    }

    /**
     * Confirma execução com o usuário
     */
    private function confirmExecution(): bool
    {
        return $this->confirm('Confirma a publicação direta destes artigos?');
    }

    /**
     * Processa artigos diretamente para Article
     */
    private function processArticlesDirect($articles, bool $dryRun, bool $debug): array
    {
        $bar = $this->output->createProgressBar($articles->count());
        $bar->setFormat('[%bar%] %current%/%max% %message%');
        $bar->start();

        $results = [
            'published' => 0,
            'skipped' => 0,
            'errors' => 0,
            'details' => []
        ];

        foreach ($articles as $article) {
            $vehicle = "{$article->vehicle_make} {$article->vehicle_model}";
            $bar->setMessage("Publicando: {$vehicle}");

            try {
                $result = $this->publishDirectly($article, $dryRun, $debug);
                
                if ($result['published']) {
                    $results['published']++;
                } else {
                    $results['skipped']++;
                }

                $results['details'][] = $result;

            } catch (\Exception $e) {
                $results['errors']++;
                $results['details'][] = [
                    'published' => false,
                    'slug' => $this->generateFinalSlug($article),
                    'vehicle' => $vehicle,
                    'error' => $e->getMessage()
                ];

                if ($debug) {
                    $this->newLine();
                    $this->error("❌ Erro ao publicar {$vehicle}: {$e->getMessage()}");
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        return $results;
    }

    /**
     * Publica artigo diretamente
     */
    private function publishDirectly($tireArticle, bool $dryRun, bool $debug): array
    {
        $finalSlug = $this->generateFinalSlug($tireArticle);
        $vehicle = "{$tireArticle->vehicle_make} {$tireArticle->vehicle_model}";

        // Verificar se slug já existe
        if (!$this->option('force')) {
            $exists = Article::where('slug', $finalSlug)->exists();
            if ($exists) {
                return [
                    'published' => false,
                    'slug' => $finalSlug,
                    'vehicle' => $vehicle,
                    'reason' => 'Slug já existe'
                ];
            }
        }

        if ($debug) {
            $this->newLine();
            $this->line("🔄 Publicando diretamente:");
            $this->line("   🚗 Veículo: {$vehicle}");
            $this->line("   📝 Slug: {$finalSlug}");
            $this->line("   📅 Processado: {$tireArticle->claude_completed_at}");
        }

        if ($dryRun) {
            return [
                'published' => true,
                'slug' => $finalSlug,
                'vehicle' => $vehicle,
                'reason' => 'Simulação bem-sucedida'
            ];
        }

        // Construir dados do artigo
        $articleData = $this->buildDirectArticleData($tireArticle, $finalSlug);

        // Criar ou atualizar artigo
        if ($this->option('force') && Article::where('slug', $finalSlug)->exists()) {
            Article::where('slug', $finalSlug)->delete();
        }

        Article::create($articleData);

        // Atualizar TireCalibration para tracking
        $tireArticle->update([
            'direct_published_at' => now(),
            'direct_publish_status' => 'published',
            'direct_article_slug' => $finalSlug
        ]);

        Log::info('TireCalibration published directly to Article', [
            'tire_calibration_id' => $tireArticle->_id,
            'article_slug' => $finalSlug,
            'vehicle' => $vehicle
        ]);

        return [
            'published' => true,
            'slug' => $finalSlug,
            'vehicle' => $vehicle,
            'title' => $articleData['title']
        ];
    }

    /**
     * Gera slug final para Article
     */
    private function generateFinalSlug($tireArticle): string
    {
        $make = Str::slug($tireArticle->vehicle_make);
        $model = Str::slug($tireArticle->vehicle_model);
        
        return "calibragem-pneu-{$make}-{$model}";
    }

    /**
     * Constrói dados completos do artigo para publicação direta
     */
    private function buildDirectArticleData($tireArticle, string $finalSlug): array
    {
        $articleRefined = $tireArticle->article_refined ?? $tireArticle->generated_article;
        $vehicle = "{$tireArticle->vehicle_make} {$tireArticle->vehicle_model}";

        return [
            'title' => $articleRefined['title'] ?? "Calibragem de Pneus {$vehicle} - Guia Completo",
            'slug' => $finalSlug,
            'template' => $articleRefined['template'] ?? 'tire_calibration',
            'category_id' => $articleRefined['category_id'] ?? 1,
            'category_name' => $articleRefined['category_name'] ?? 'Calibragem de Pneus',
            'category_slug' => $articleRefined['category_slug'] ?? 'calibragem-pneus',
            'content' => $articleRefined['content'] ?? [],
            'extracted_entities' => $this->buildEntities($tireArticle),
            'seo_data' => $this->buildSeoData($tireArticle, $vehicle),
            'metadata' => $this->buildMetadata($tireArticle),
            'tags' => $this->extractTags($tireArticle),
            'related_topics' => $this->extractRelatedTopics($tireArticle),
            'status' => 'published',
            'original_post_id' => null,
            'created_at' => $this->generatePublishDate(),
            'updated_at' => now(),
            'published_at' => $this->generatePublishDate(),
            'vehicle_info' => $this->buildVehicleInfo($tireArticle),
            'filter_data' => $this->buildFilterData($tireArticle),
            'author' => $this->assignAuthor($tireArticle),
            'source_collection' => 'tire_calibrations',
            'source_document_id' => $tireArticle->_id,
            'humanized_at' => null,
            'direct_publish' => true, // Flag indicando publicação direta
            'direct_published_at' => now()
        ];
    }

    /**
     * Constrói entidades do veículo
     */
    private function buildEntities($tireArticle): array
    {
        return [
            'marca' => $tireArticle->vehicle_make,
            'modelo' => $tireArticle->vehicle_model,
            'categoria' => $this->mapCategory($tireArticle->main_category),
            'tipo_veiculo' => $this->mapVehicleType($tireArticle->vehicle_features['vehicle_type'] ?? 'car'),
            'ano' => $this->extractYear($tireArticle),
            'combustivel' => $this->mapFuelType($tireArticle),
            'pneus' => $tireArticle->pressure_specifications['tire_size'] ?? null
        ];
    }

    /**
     * Constrói dados de SEO
     */
    private function buildSeoData($tireArticle, string $vehicle): array
    {
        $lowerVehicle = strtolower($vehicle);
        $finalSlug = $this->generateFinalSlug($tireArticle);
        
        return [
            'page_title' => "Calibragem de Pneus {$vehicle} - Guia Completo",
            'meta_description' => "Descubra a pressão ideal dos pneus do {$vehicle}. Guia completo com procedimentos, especificações técnicas e dicas de segurança.",
            'h1' => "Calibragem de Pneus {$vehicle}",
            'primary_keyword' => "calibragem pneu {$lowerVehicle}",
            'secondary_keywords' => [
                "pressão pneu {$tireArticle->vehicle_make}",
                "calibrar pneu {$tireArticle->vehicle_model}",
                "procedimento calibragem {$lowerVehicle}",
                "especificação pneu {$lowerVehicle}"
            ],
            'url_slug' =>  $finalSlug,
            'canonical_url' => "https://mercadoveiculos.com.br/info/".  $finalSlug,
            'og_title' => "Calibragem de Pneus {$vehicle} - Guia Completo",
            'og_description' => "Procedimento completo de calibragem dos pneus do {$vehicle}. Pressões específicas e dicas especializadas."
        ];
    }

    /**
     * Constrói metadados
     */
    private function buildMetadata($tireArticle): array
    {
        return [
            'word_count' => 1500,
            'reading_time' => 8,
            'article_tone' => 'técnico-informativo',
            'published_date' => now()->format('Y-m-d'),
            'direct_publish_command' => true,
            'processing_pipeline' => 'TireCalibration->Article (direct)',
            'source_quality_score' => $tireArticle->data_completeness_score ?? 8,
            'claude_version' => $tireArticle->claude_refinement_version,
            'processed_at' => $tireArticle->claude_completed_at,
            'vehicle_specs' => [
                'make' => $tireArticle->vehicle_make,
                'model' => $tireArticle->vehicle_model,
                'tire_size' => $tireArticle->pressure_specifications['tire_size'] ?? null,
                'is_premium' => $tireArticle->vehicle_features['is_premium'] ?? false,
                'has_tpms' => $tireArticle->vehicle_features['has_tpms'] ?? false,
                'is_electric' => $tireArticle->vehicle_features['is_electric'] ?? false
            ]
        ];
    }

    /**
     * Extrai tags relevantes
     */
    private function extractTags($tireArticle): array
    {
        $tags = [
            // Tags do veículo
            $tireArticle->vehicle_make,
            $tireArticle->vehicle_model,
            "{$tireArticle->vehicle_make} {$tireArticle->vehicle_model}",
            
            // Tags técnicas
            'calibragem de pneus',
            'pressão dos pneus',
            'manutenção automotiva',
            'segurança veicular',
            
            // Tags por categoria
            $this->mapCategory($tireArticle->main_category),
            $this->mapVehicleType($tireArticle->vehicle_features['vehicle_type'] ?? 'car')
        ];

        // Tags específicas por tipo
        if ($tireArticle->vehicle_features['has_tpms'] ?? false) {
            $tags[] = 'TPMS';
            $tags[] = 'monitoramento pressão';
        }

        if ($tireArticle->vehicle_features['is_premium'] ?? false) {
            $tags[] = 'veículo premium';
        }

        if ($tireArticle->vehicle_features['is_electric'] ?? false) {
            $tags[] = 'carro elétrico';
            $tags[] = 'eficiência energética';
        }

        return array_unique($tags);
    }

    /**
     * Extrai tópicos relacionados
     */
    private function extractRelatedTopics($tireArticle): array
    {
        $vehicle = "{$tireArticle->vehicle_make} {$tireArticle->vehicle_model}";
        
        return [
            [
                'title' => "Óleo Recomendado para {$vehicle}",
                'slug' => Str::slug("oleo-recomendado-{$vehicle}"),
                'icon' => 'oil-can'
            ],
            [
                'title' => "Filtro de Ar {$vehicle}",
                'slug' => Str::slug("filtro-ar-{$vehicle}"),
                'icon' => 'air-filter'
            ],
            [
                'title' => "Consumo de Combustível {$vehicle}",
                'slug' => Str::slug("consumo-combustivel-{$vehicle}"),
                'icon' => 'fuel-pump'
            ],
            [
                'title' => "Manual do Proprietário {$vehicle}",
                'slug' => Str::slug("manual-{$vehicle}"),
                'icon' => 'book'
            ]
        ];
    }

    /**
     * Constrói dados do veículo
     */
    private function buildVehicleInfo($tireArticle): array
    {
        return $tireArticle->generated_article["vehicle_data"];
    }

    /**
     * Constrói dados de filtro
     */
    private function buildFilterData($tireArticle): array
    {
        return [
            'marca' => $tireArticle->vehicle_make,
            'modelo' => $tireArticle->vehicle_model,
            'categoria' => $this->mapCategory($tireArticle->main_category),
            'tipo_veiculo' => $this->mapVehicleType($tireArticle->vehicle_features['vehicle_type'] ?? 'car'),
            'marca_slug' => Str::slug($tireArticle->vehicle_make),
            'modelo_slug' => Str::slug($tireArticle->vehicle_model),
            'marca_modelo_slug' => Str::slug("{$tireArticle->vehicle_make} {$tireArticle->vehicle_model}")
        ];
    }

    /**
     * Atribui autor
     */
    private function assignAuthor($tireArticle): array
    {
        $authors = [
            ['name' => 'Carlos Santos', 'bio' => 'Especialista em manutenção automotiva'],
            ['name' => 'Ana Oliveira', 'bio' => 'Engenheira mecânica especializada em pneus'],
            ['name' => 'Ricardo Lima', 'bio' => 'Técnico automotivo com 15 anos de experiência'],
            ['name' => 'Marina Costa', 'bio' => 'Especialista em segurança veicular']
        ];

        $index = crc32($tireArticle->vehicle_make . $tireArticle->vehicle_model) % count($authors);
        return $authors[$index];
    }

    /**
     * Gera data de publicação para testes
     */
    private function generatePublishDate(): Carbon
    {
        // Para testes, usar data recente (últimos 7 dias)
        return now()->subDays(rand(0, 7));
    }

    // Métodos de mapeamento
    private function mapCategory(string $category): string
    {
        return match($category) {
            'sedan' => 'Sedan',
            'hatch' => 'Hatchback',
            'suv' => 'SUV',
            'pickup' => 'Picape',
            'car_electric' => 'Carro Elétrico',
            default => 'Automotivo'
        };
    }

    private function mapVehicleType(string $type): string
    {
        return match($type) {
            'car' => 'Carro',
            'suv' => 'SUV',
            'pickup' => 'Picape',
            'motorcycle' => 'Moto',
            default => 'Carro'
        };
    }

    private function mapFuelType($tireArticle): string
    {
        if ($tireArticle->vehicle_features['is_electric'] ?? false) return 'Elétrico';
        if ($tireArticle->vehicle_features['is_hybrid'] ?? false) return 'Híbrido';
        return 'Flex';
    }

    private function extractYear($tireArticle): string
    {
        $fullName = $tireArticle->vehicle_basic_data['full_name'] ?? '';
        if (preg_match('/(\d{4})/', $fullName, $matches)) {
            return $matches[1];
        }
        return date('Y');
    }

    /**
     * Executa pós-processamento
     */
    private function runPostProcessing(): void
    {
        if ($this->option('humanize-dates')) {
            $this->info('Humanizando datas dos artigos publicados...');
            
            $this->call('articles:humanize-dates', [
                '--days' => $this->option('days'),
                '--direct-published-only' => true
            ]);
        }
    }

    /**
     * Exibe resultados
     */
    private function displayResults(array $results): void
    {
        $this->info('RESULTADOS DA PUBLICAÇÃO DIRETA:');
        $this->table(
            ['Métrica', 'Quantidade'],
            [
                ['Publicados', $results['published']],
                ['Ignorados', $results['skipped']],
                ['Erros', $results['errors']]
            ]
        );

        if ($results['published'] > 0) {
            $this->newLine();
            $this->info('✅ Artigos publicados com sucesso:');
            foreach ($results['details'] as $detail) {
                if ($detail['published']) {
                    $this->line("   • {$detail['vehicle']} → {$detail['slug']}");
                }
            }
        }

        if ($results['errors'] > 0) {
            $this->newLine();
            $this->warn('❌ Erros encontrados:');
            foreach ($results['details'] as $detail) {
                if (!$detail['published'] && isset($detail['error'])) {
                    $this->line("   • {$detail['vehicle']}: {$detail['error']}");
                }
            }
        }

        if ($results['published'] > 0) {
            $this->newLine();
            $this->info('🚀 Artigos disponíveis para visualização no sistema!');
        }
    }
}