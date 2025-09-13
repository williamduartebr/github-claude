<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;

/**
 * PublishTempTireCalibrationArticlesCommand
 * 
 * Transfere artigos de calibragem de pneus finalizados (TireCalibration) 
 * para a coleção temporária (TempArticle) antes da publicação final.
 * 
 * CRITÉRIOS DE FILTRO:
 * - claude_refinement_version = "v4_completed"
 * - version = "v2"
 * - enrichment_phase = "claude_3b_completed"
 * 
 * FLUXO:
 * TireCalibration → TempArticle → Article (comando separado)
 * 
 * @author Claude Sonnet 4
 * @version 1.0
 */
class PublishTempTireCalibrationArticlesCommand extends Command
{
    /**
     * Nome e assinatura do comando.
     */
    protected $signature = 'tire-calibration:publish-to-temp
                           {--limit=50 : Número máximo de artigos a processar}
                           {--dry-run : Simular execução sem persistir dados}
                           {--force : Reprocessar artigos já transferidos}
                           {--debug : Exibir informações detalhadas}';

    /**
     * Descrição do comando.
     */
    protected $description = 'Transfere artigos de calibragem finalizados para TempArticle aguardando publicação final';

    /**
     * Execute o comando.
     */
    public function handle(): int
    {
        $this->displayHeader();

        // Obter configurações
        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $debug = $this->option('debug');

        try {
            // Buscar artigos elegíveis
            $eligibleArticles = $this->findEligibleArticles($limit, $force);

            if ($eligibleArticles->isEmpty()) {
                $this->warn('❌ Nenhum artigo elegível encontrado.');
                return Command::SUCCESS;
            }

            $this->info("📋 Encontrados {$eligibleArticles->count()} artigos para transferência.");

            if ($dryRun) {
                $this->warn('🔍 MODO SIMULAÇÃO - Nenhuma alteração será persistida');
            }

            // Processar artigos
            $results = $this->processArticles($eligibleArticles, $dryRun, $debug);

            // Exibir resultados
            $this->displayResults($results);

            return $results['errors'] === 0 ? Command::SUCCESS : Command::FAILURE;
        } catch (\Exception $e) {
            $this->error("❌ Erro fatal: {$e->getMessage()}");
            Log::error('PublishTempTireCalibrationArticlesCommand failed', [
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
        $this->info('🔧 PUBLICAÇÃO TEMPORÁRIA - ARTIGOS DE CALIBRAGEM');
        $this->info('📅 ' . now()->format('d/m/Y H:i:s'));
        $this->info('📂 TireCalibration → TempArticle');
        $this->newLine();
    }

    /**
     * Busca artigos elegíveis para transferência
     */
    private function findEligibleArticles(int $limit, bool $force): \Illuminate\Database\Eloquent\Collection
    {
        $this->info('🔍 Buscando artigos elegíveis...');

        $query = TireCalibration::where([
            'claude_refinement_version' => 'v4_completed',
            'version' => 'v2',
            'enrichment_phase' => 'claude_3b_completed'
        ])
            ->whereNotNull('article_refined')
            ->whereNotNull('claude_enhancements');

        // Se não forçar, excluir já transferidos
        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('temp_article_published_at')
                    ->orWhere('temp_article_status', '!=', 'published');
            });
        }

        $query->orderBy('claude_completed_at', 'desc')
            ->limit($limit);

        $articles = $query->get();

        $this->line("   ✅ Query executada com sucesso");
        $this->line("   📊 Critérios: v4_completed, v2, claude_3b_completed");
        $this->line("   🔢 Limit: {$limit} | Force: " . ($force ? 'SIM' : 'NÃO'));

        return $articles;
    }

    /**
     * Processa os artigos encontrados
     */
    private function processArticles($articles, bool $dryRun, bool $debug): array
    {
        $bar = $this->output->createProgressBar($articles->count());
        $bar->setFormat('[%bar%] %current%/%max% %message%');
        $bar->start();

        $results = [
            'processed' => 0,
            'skipped' => 0,
            'errors' => 0,
            'details' => []
        ];

        foreach ($articles as $article) {
            $bar->setMessage("Processando: {$article->vehicle_make} {$article->vehicle_model}");

            try {
                $result = $this->transferArticle($article, $dryRun, $debug);

                if ($result['success']) {
                    $results['processed']++;
                } else {
                    $results['skipped']++;
                }

                $results['details'][] = $result;
            } catch (\Exception $e) {
                $results['errors']++;
                $results['details'][] = [
                    'success' => false,
                    'slug' => $article->wordpress_url ?? 'unknown',
                    'error' => $e->getMessage()
                ];

                if ($debug) {
                    $this->newLine();
                    $this->error("❌ Erro no artigo {$article->_id}: {$e->getMessage()}");
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        return $results;
    }

    /**
     * Transfere um artigo individual
     */
    private function transferArticle($tireArticle, bool $dryRun, bool $debug): array
    {
        // Gerar slug para TempArticle
        $slug = $this->generateTempSlug($tireArticle);

        // Verificar se já existe
        if (!$dryRun) {
            $exists = TempArticle::where('slug', $slug)->exists();
            if ($exists) {
                return [
                    'success' => false,
                    'slug' => $slug,
                    'reason' => 'Slug já existe em TempArticle'
                ];
            }
        }

        // Extrair dados do artigo refinado
        $articleData = $this->extractArticleData($tireArticle);

        if ($debug) {
            $this->newLine();
            $this->line("🔄 Transferindo: {$slug}");
            $this->line("   📝 Título: {$articleData['title']}");
            $this->line("   🚗 Veículo: {$tireArticle->vehicle_make} {$tireArticle->vehicle_model}");
        }

        if (!$dryRun) {
            // Criar registro em TempArticle
            TempArticle::create($articleData);

            // Atualizar TireCalibration
            $tireArticle->update([
                'temp_article_published_at' => now(),
                'temp_article_status' => 'published',
                'temp_article_slug' => $slug
            ]);

            Log::info('TireCalibration article transferred to TempArticle', [
                'tire_calibration_id' => $tireArticle->_id,
                'temp_article_slug' => $slug,
                'vehicle' => "{$tireArticle->vehicle_make} {$tireArticle->vehicle_model}"
            ]);
        }

        return [
            'success' => true,
            'slug' => $slug,
            'title' => $articleData['title']
        ];
    }

    /**
     * Gera slug para o TempArticle
     */
    private function generateTempSlug($tireArticle): string
    {
        $make = Str::slug($tireArticle->vehicle_make);
        $model = Str::slug($tireArticle->vehicle_model);

        return "calibragem-pneu-{$make}-{$model}";
    }

    /**
     * Extrai dados do artigo refinado para TempArticle
     */
    private function extractArticleData($tireArticle): array
    {
        $articleRefined = $tireArticle->article_refined;
        $slug = $this->generateTempSlug($tireArticle);

        return [
            'title' => $articleRefined['title'] ?? "Calibragem de Pneus {$tireArticle->vehicle_make} {$tireArticle->vehicle_model}",
            'slug' => $slug,
            'new_slug' => str_replace('temp-', '', $slug), // Para futura publicação
            'template' => $articleRefined['template'] ?? 'tire_calibration',
            'category_id' => $articleRefined['category_id'] ?? 1,
            'category_name' => $articleRefined['category_name'] ?? 'Calibragem de Pneus',
            'category_slug' => $articleRefined['category_slug'] ?? 'calibragem-pneus',
            'content' => $articleRefined['content'] ?? [],
            'extracted_entities' => $articleRefined['extracted_entities'] ?? $this->buildEntities($tireArticle),
            'seo_data' => $articleRefined['seo_data'] ?? $this->buildSeoData($tireArticle),
            'metadata' => $this->buildMetadata($tireArticle),
            'status' => 'draft',
            'original_post_id' => null, // Artigos novos, não importados
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
            'vehicle_info' => $this->buildVehicleInfo($tireArticle),
            'filter_data' => $this->buildFilterData($tireArticle),
            'source_collection' => 'tire_calibrations',
            'source_document_id' => $tireArticle->_id,
        ];
    }

    /**
     * Constrói entidades extraídas
     */
    private function buildEntities($tireArticle): array
    {
        return [
            'marca' => $tireArticle->vehicle_make,
            'modelo' => $tireArticle->vehicle_model,
            'categoria' => $tireArticle->main_category ?? 'automotivo',
            'tipo_veiculo' => $this->mapVehicleType($tireArticle->vehicle_features['vehicle_type'] ?? 'car'),
            'ano' => $this->extractYear($tireArticle),
            'combustivel' => $this->mapFuelType($tireArticle)
        ];
    }

    /**
     * Constrói dados de SEO
     */
    private function buildSeoData($tireArticle): array
    {
        $vehicle = "{$tireArticle->vehicle_make} {$tireArticle->vehicle_model}";

        return [
            'page_title' => "Calibragem de Pneus {$vehicle} - Guia Completo",
            'meta_description' => "Guia completo para calibragem dos pneus do {$vehicle}. Pressões ideais, procedimentos e dicas especializadas.",
            'primary_keyword' => "calibragem pneu " . strtolower($vehicle),
            'secondary_keywords' => [
                "pressão pneu {$tireArticle->vehicle_make}",
                "calibrar pneu {$tireArticle->vehicle_model}",
                "procedimento calibragem {$tireArticle->vehicle_make}"
            ],
            'h1' => "Calibragem de Pneus {$vehicle}",
            'url_slug' => $this->generateTempSlug($tireArticle)
        ];
    }

    /**
     * Constrói metadados
     */
    private function buildMetadata($tireArticle): array
    {
        return [
            'word_count' => 1200,
            'reading_time' => 6,
            'article_tone' => 'técnico-informativo',
            'published_date' => now()->format('Y-m-d'),
            'source_data_quality' => $tireArticle->data_completeness_score ?? 8,
            'claude_refinement_version' => $tireArticle->claude_refinement_version,
            'processing_completed_at' => $tireArticle->claude_completed_at,
            'vehicle_specifications' => [
                'make' => $tireArticle->vehicle_make,
                'model' => $tireArticle->vehicle_model,
                'tire_size' => $tireArticle->pressure_specifications['tire_size'] ?? null,
                'is_premium' => $tireArticle->vehicle_features['is_premium'] ?? false,
                'has_tpms' => $tireArticle->vehicle_features['has_tpms'] ?? false
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
            'categoria' => $tireArticle->main_category,
            'tipo_veiculo' => $this->mapVehicleType($tireArticle->vehicle_features['vehicle_type'] ?? 'car'),
            'marca_slug' => \Illuminate\Support\Str::slug($tireArticle->vehicle_make),
            'modelo_slug' => \Illuminate\Support\Str::slug($tireArticle->vehicle_model)
        ];
    }

    /**
     * Mapeia tipo de veículo
     */
    private function mapVehicleType(string $type): string
    {
        return match ($type) {
            'car' => 'Carro',
            'suv' => 'SUV',
            'pickup' => 'Picape',
            'motorcycle' => 'Moto',
            default => 'Carro'
        };
    }

    /**
     * Mapeia tipo de combustível
     */
    private function mapFuelType($tireArticle): string
    {
        if ($tireArticle->vehicle_features['is_electric'] ?? false) {
            return 'Elétrico';
        }
        if ($tireArticle->vehicle_features['is_hybrid'] ?? false) {
            return 'Híbrido';
        }
        return 'Flex';
    }

    /**
     * Extrai ano do veículo
     */
    private function extractYear($tireArticle): string
    {
        // Tentar extrair do nome completo ou usar ano atual
        $fullName = $tireArticle->vehicle_basic_data['full_name'] ?? '';
        if (preg_match('/(\d{4})/', $fullName, $matches)) {
            return $matches[1];
        }
        return date('Y');
    }

    /**
     * Exibe resultados finais
     */
    private function displayResults(array $results): void
    {
        $this->info('📊 RESULTADOS:');
        $this->line("   ✅ Processados: {$results['processed']}");
        $this->line("   ⏭️  Ignorados: {$results['skipped']}");
        $this->line("   ❌ Erros: {$results['errors']}");

        if ($results['errors'] > 0) {
            $this->newLine();
            $this->warn('⚠️  ERROS ENCONTRADOS:');
            foreach ($results['details'] as $detail) {
                if (!$detail['success'] && isset($detail['error'])) {
                    $this->line("   • {$detail['slug']}: {$detail['error']}");
                }
            }
        }

        if ($results['processed'] > 0) {
            $this->newLine();
            $this->info('🚀 Próximo passo: php artisan tire-calibration:publish-articles');
        }
    }
}
