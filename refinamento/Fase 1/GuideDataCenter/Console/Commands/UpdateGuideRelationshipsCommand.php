<?php

namespace Src\GuideDataCenter\Console\Commands;

use Illuminate\Console\Command;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Services\GuideRelationshipService;
use Src\GuideDataCenter\Domain\Services\GuideClusterService;

/**
 * UpdateGuideRelationshipsCommand
 * 
 * Command para atualizar relacionamentos entre guias
 * Pode ser executado manualmente ou via Schedule
 * 
 * Uso:
 * php artisan guide:update-relationships
 * php artisan guide:update-relationships --limit=100
 * php artisan guide:update-relationships --force
 * 
 * @package Src\GuideDataCenter\Console\Commands
 */
class UpdateGuideRelationshipsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guide:update-relationships
                            {--limit=50 : Número de guias a processar por execução}
                            {--force : Força atualização mesmo se já tiver relacionamentos}
                            {--clear-cache : Limpa todo o cache antes de processar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza relacionamentos entre guias (links internos)';

    /**
     * @var GuideRelationshipService
     */
    protected GuideRelationshipService $relationshipService;

    /**
     * @var GuideClusterService
     */
    protected GuideClusterService $clusterService;

    /**
     * Constructor
     */
    public function __construct(
        GuideRelationshipService $relationshipService,
        GuideClusterService $clusterService
    ) {
        parent::__construct();
        $this->relationshipService = $relationshipService;
        $this->clusterService = $clusterService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Iniciando atualização de relacionamentos...');
        $this->newLine();

        // Limpa cache se solicitado
        if ($this->option('clear-cache')) {
            $this->info('🗑️  Limpando cache...');
            $this->relationshipService->clearAllCache();
            $this->info('✅ Cache limpo com sucesso');
            $this->newLine();
        }

        $limit = (int) $this->option('limit');
        $force = $this->option('force');

        // Busca guias que precisam de atualização
        $guides = $this->getGuidesToUpdate($limit, $force);

        if ($guides->isEmpty()) {
            $this->info('✅ Todos os guias já possuem relacionamentos adequados!');
            return Command::SUCCESS;
        }

        $this->info("📊 {$guides->count()} guias serão processados");
        $this->newLine();

        $bar = $this->output->createProgressBar($guides->count());
        $bar->setFormat('verbose');

        $successCount = 0;
        $errorCount = 0;
        $skippedCount = 0;

        foreach ($guides as $guide) {
            try {
                $result = $this->updateGuideRelationships($guide);

                if ($result['updated']) {
                    $successCount++;
                    $bar->setMessage("✓ {$guide->full_title}");
                } else {
                    $skippedCount++;
                    $bar->setMessage("⊘ Pulado: {$guide->full_title}");
                }

            } catch (\Exception $e) {
                $errorCount++;
                $bar->setMessage("✗ Erro: {$guide->full_title}");
                $this->error("\n   Erro: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Relatório final
        $this->displaySummary($successCount, $skippedCount, $errorCount, $guides->count());

        return Command::SUCCESS;
    }

    /**
     * Busca guias que precisam de atualização
     *
     * @param int $limit
     * @param bool $force
     * @return \Illuminate\Support\Collection
     */
    protected function getGuidesToUpdate(int $limit, bool $force)
    {
        $query = Guide::query();

        if (!$force) {
            // Busca apenas guias com poucos ou nenhum relacionamento
            $query->where(function ($q) {
                $q->whereNull('links_internal')
                  ->orWhereRaw('JSON_LENGTH(links_internal) < ?', [5]);
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Atualiza relacionamentos de um guia específico
     *
     * @param Guide $guide
     * @return array
     */
    protected function updateGuideRelationships(Guide $guide): array
    {
        // Busca relacionamentos
        $related = $this->relationshipService->getRelatedGuides($guide, 8);
        $essential = $this->relationshipService->getEssentialContents($guide, 8);
        $sameCategory = $this->relationshipService->getSameCategoryGuides($guide, 6);

        // Monta array de links internos
        $internalLinks = [];

        // Adiciona guias relacionados
        foreach ($related as $relatedGuide) {
            $internalLinks[] = [
                'type' => 'related',
                'title' => $relatedGuide->full_title,
                'url' => $relatedGuide->url,
                'category' => $relatedGuide->category->name ?? null,
            ];
        }

        // Adiciona conteúdos essenciais
        foreach ($essential as $essentialGuide) {
            $internalLinks[] = [
                'type' => 'essential',
                'title' => $essentialGuide->full_title,
                'url' => $essentialGuide->url,
                'year' => $essentialGuide->year_start,
            ];
        }

        // Adiciona mesma categoria
        foreach ($sameCategory as $categoryGuide) {
            $internalLinks[] = [
                'type' => 'same_category',
                'title' => $categoryGuide->full_title,
                'url' => $categoryGuide->url,
            ];
        }

        // Atualiza o guia
        $guide->links_internal = $internalLinks;
        $guide->save();

        // Atualiza clusters se necessário
        $this->clusterService->updateGuideClusters($guide->_id);

        // Limpa cache específico deste guia
        $this->relationshipService->clearCache($guide->_id);

        return [
            'updated' => true,
            'total_links' => count($internalLinks),
            'related' => $related->count(),
            'essential' => $essential->count(),
            'same_category' => $sameCategory->count(),
        ];
    }

    /**
     * Exibe resumo da execução
     *
     * @param int $successCount
     * @param int $skippedCount
     * @param int $errorCount
     * @param int $total
     * @return void
     */
    protected function displaySummary(
        int $successCount,
        int $skippedCount,
        int $errorCount,
        int $total
    ): void {
        $this->info(str_repeat('=', 60));
        $this->info('📊 RESUMO DA EXECUÇÃO');
        $this->info(str_repeat('=', 60));
        $this->table(
            ['Métrica', 'Quantidade', 'Percentual'],
            [
                ['✅ Atualizados', $successCount, number_format(($successCount / $total) * 100, 1) . '%'],
                ['⊘ Pulados', $skippedCount, number_format(($skippedCount / $total) * 100, 1) . '%'],
                ['❌ Erros', $errorCount, number_format(($errorCount / $total) * 100, 1) . '%'],
                ['📈 Total', $total, '100%'],
            ]
        );
        $this->info(str_repeat('=', 60));

        if ($errorCount > 0) {
            $this->warn("⚠️  Atenção: {$errorCount} guias apresentaram erros");
        }

        if ($successCount > 0) {
            $this->info("✨ {$successCount} guias atualizados com sucesso!");
        }
    }
}