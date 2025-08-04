<?php

namespace Src\TirePressureCorrection\Infrastructure\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\VehicleData\Domain\Entities\VehicleData;
use Src\TirePressureCorrection\Domain\Entities\TirePressureCorrection;

/**
 * Command para monitorar progresso das atualizações de SEO e FAQ
 */
class SeoFaqProgressCommand extends Command
{
    protected $signature = 'articles:seo-faq-progress
                           {--reset : Resetar todos os trackings de SEO/FAQ}
                           {--detailed : Mostrar estatísticas detalhadas}';

    protected $description = 'Monitorar progresso das atualizações de SEO e FAQ';

    const CORRECTION_TYPE_SEO_FAQ = 'seo_faq_update';

    public function handle(): int
    {
        $this->info('📊 PROGRESSO DAS ATUALIZAÇÕES SEO/FAQ');
        $this->newLine();

        $reset = $this->option('reset');
        $detailed = $this->option('detailed');

        if ($reset) {
            return $this->handleReset();
        }

        // Estatísticas gerais
        $this->showGeneralStats();

        // Estatísticas detalhadas se solicitado
        if ($detailed) {
            $this->showDetailedStats();
        }

        // Próximos passos
        $this->showNextSteps();

        return Command::SUCCESS;
    }

    /**
     * Resetar trackings
     */
    protected function handleReset(): int
    {
        if (!$this->confirm('⚠️  Tem certeza que deseja resetar TODOS os trackings de SEO/FAQ?')) {
            $this->info('Operação cancelada.');
            return Command::SUCCESS;
        }

        $deleted = TirePressureCorrection::where('correction_type', self::CORRECTION_TYPE_SEO_FAQ)->delete();
        
        $this->info("🗑️  {$deleted} registros de tracking SEO/FAQ foram removidos.");
        $this->warn('Todos os artigos serão reprocessados na próxima execução.');

        return Command::SUCCESS;
    }

    /**
     * Mostrar estatísticas gerais
     */
    protected function showGeneralStats(): void
    {
        // Total de artigos elegíveis
        $totalArticles = Article::where('template', 'when_to_change_tires')
            ->whereNotNull('extracted_entities')
            ->count();

        // Artigos com dados válidos
        $validArticles = $this->countValidArticles();

        // Trackings de SEO/FAQ
        $seoFaqTrackings = TirePressureCorrection::where('correction_type', self::CORRECTION_TYPE_SEO_FAQ)->count();

        // Por status
        $statusStats = TirePressureCorrection::where('correction_type', self::CORRECTION_TYPE_SEO_FAQ)
            ->get()
            ->groupBy('status')
            ->map->count()
            ->toArray();

        $this->info('🎯 ESTATÍSTICAS GERAIS:');
        $this->newLine();

        $this->table([
            'Métrica', 'Quantidade', 'Percentual'
        ], [
            ['Total de artigos (template)', $totalArticles, '100%'],
            ['Artigos com dados válidos', $validArticles, $this->percentage($validArticles, $totalArticles)],
            ['Com tracking SEO/FAQ', $seoFaqTrackings, $this->percentage($seoFaqTrackings, $validArticles)],
            ['Pendentes de processamento', $validArticles - $seoFaqTrackings, $this->percentage($validArticles - $seoFaqTrackings, $validArticles)],
        ]);

        $this->newLine();

        if ($seoFaqTrackings > 0) {
            $this->info('📋 POR STATUS:');
            
            $statusRows = [];
            foreach ($statusStats as $status => $count) {
                $statusRows[] = [
                    $this->getStatusEmoji($status) . ' ' . ucfirst($status),
                    $count,
                    $this->percentage($count, $seoFaqTrackings)
                ];
            }

            $this->table(['Status', 'Quantidade', 'Percentual'], $statusRows);
        }

        $this->newLine();
    }

    /**
     * Mostrar estatísticas detalhadas
     */
    protected function showDetailedStats(): void
    {
        $this->info('📊 ESTATÍSTICAS DETALHADAS:');
        $this->newLine();

        // Por categoria de veículo
        $this->showStatsByCategory();

        // Por marca
        $this->showStatsByMake();

        // Histórico por dias
        $this->showDailyProgress();

        // Erros mais comuns
        $this->showCommonErrors();
    }

    /**
     * Estatísticas por categoria
     */
    protected function showStatsByCategory(): void
    {
        $this->line('🏷️  Por categoria de veículo:');

        $categoryStats = collect();
        
        // Buscar dados do VehicleData para categorização
        VehicleData::select('main_category')
            ->get()
            ->groupBy('main_category')
            ->each(function ($vehicles, $category) use (&$categoryStats) {
                $count = $vehicles->count();
                $categoryStats->put($category, $count);
            });

        $categoryStats->sortDesc()->take(10)->each(function ($count, $category) {
            $this->line("   • {$category}: {$count} veículos");
        });

        $this->newLine();
    }

    /**
     * Estatísticas por marca
     */
    protected function showStatsByMake(): void
    {
        $this->line('🏭 Top 10 marcas:');

        $makeStats = TirePressureCorrection::where('correction_type', self::CORRECTION_TYPE_SEO_FAQ)
            ->get()
            ->map(function ($correction) {
                // Extrair marca do vehicle_name
                $parts = explode(' ', $correction->vehicle_name);
                return $parts[0] ?? 'Unknown';
            })
            ->countBy()
            ->sortDesc()
            ->take(10);

        $makeStats->each(function ($count, $make) {
            $this->line("   • {$make}: {$count} artigos");
        });

        $this->newLine();
    }

    /**
     * Progresso diário
     */
    protected function showDailyProgress(): void
    {
        $this->line('📅 Progresso dos últimos 7 dias:');

        $dailyStats = TirePressureCorrection::where('correction_type', self::CORRECTION_TYPE_SEO_FAQ)
            ->where('created_at', '>=', now()->subDays(7))
            ->get()
            ->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            })
            ->map->count()
            ->sortKeys();

        if ($dailyStats->isEmpty()) {
            $this->line('   • Nenhum processamento nos últimos 7 dias');
        } else {
            $dailyStats->each(function ($count, $date) {
                $formattedDate = \Carbon\Carbon::parse($date)->format('d/m');
                $this->line("   • {$formattedDate}: {$count} artigos");
            });
        }

        $this->newLine();
    }

    /**
     * Erros mais comuns
     */
    protected function showCommonErrors(): void
    {
        $this->line('❌ Erros mais comuns:');

        $errorStats = TirePressureCorrection::where('correction_type', self::CORRECTION_TYPE_SEO_FAQ)
            ->where('status', TirePressureCorrection::STATUS_FAILED)
            ->whereNotNull('error_message')
            ->get()
            ->groupBy('error_message')
            ->map->count()
            ->sortDesc()
            ->take(5);

        if ($errorStats->isEmpty()) {
            $this->line('   • Nenhum erro registrado 🎉');
        } else {
            $errorStats->each(function ($count, $error) {
                $shortError = Str::limit($error, 60);
                $this->line("   • {$shortError}: {$count}x");
            });
        }

        $this->newLine();
    }

    /**
     * Próximos passos
     */
    protected function showNextSteps(): void
    {
        $validArticles = $this->countValidArticles();
        $processedArticles = TirePressureCorrection::where('correction_type', self::CORRECTION_TYPE_SEO_FAQ)
            ->whereIn('status', [
                TirePressureCorrection::STATUS_COMPLETED,
                TirePressureCorrection::STATUS_NO_CHANGES
            ])
            ->count();

        $remaining = $validArticles - $processedArticles;

        $this->info('🚀 PRÓXIMOS PASSOS:');
        $this->newLine();

        if ($remaining > 0) {
            $this->line("📝 Artigos restantes para processar: <fg=yellow>{$remaining}</>");
            $this->newLine();

            $this->line('Comandos recomendados para continuar:');
            $this->line('');
            
            if ($remaining > 200) {
                $this->line('   # Para lotes grandes (processamento em background)');
                $this->line('   php artisan articles:update-seo-faq-from-vehicle-data --limit=100 --batch-size=25 > seo_faq.log 2>&1 &');
                $this->newLine();
            }

            $this->line('   # Para lotes médios');
            $this->line('   php artisan articles:update-seo-faq-from-vehicle-data --limit=50');
            $this->newLine();

            $this->line('   # Para teste com poucos artigos');
            $this->line('   php artisan articles:update-seo-faq-from-vehicle-data --limit=10 --dry-run');
            $this->newLine();

            // Estimativa de tempo
            $estimatedMinutes = ceil($remaining / 50) * 2; // ~2 minutos por lote de 50
            $this->line("⏱️  Tempo estimado para processar todos: ~{$estimatedMinutes} minutos");

        } else {
            $this->info('✅ Todos os artigos elegíveis foram processados!');
            $this->newLine();
            
            $failedCount = TirePressureCorrection::where('correction_type', self::CORRECTION_TYPE_SEO_FAQ)
                ->where('status', TirePressureCorrection::STATUS_FAILED)
                ->count();

            if ($failedCount > 0) {
                $this->line('   Comandos para lidar com falhas:');
                $this->line('   # Reprocessar apenas os que falharam');
                $this->line('   php artisan articles:update-seo-faq-from-vehicle-data --force --limit=100');
                $this->newLine();
            }

            $this->line('   Comandos de manutenção:');
            $this->line('   # Verificar qualidade geral');
            $this->line('   php artisan articles:diagnostic-tire-pressure-status');
            $this->line('   ');
            $this->line('   # Limpar registros antigos (opcional)');
            $this->line('   php artisan articles:seo-faq-progress --reset');
        }
    }

    /**
     * Contar artigos válidos
     */
    protected function countValidArticles(): int
    {
        $count = 0;
        
        Article::where('template', 'when_to_change_tires')
            ->whereNotNull('extracted_entities')
            ->chunk(100, function ($articles) use (&$count) {
                foreach ($articles as $article) {
                    $marca = data_get($article, 'extracted_entities.marca');
                    $modelo = data_get($article, 'extracted_entities.modelo');
                    $ano = data_get($article, 'extracted_entities.ano');
                    
                    if (!empty($marca) && !empty($modelo) && !empty($ano)) {
                        $count++;
                    }
                }
            });
        
        return $count;
    }

    /**
     * Calcular percentual
     */
    protected function percentage(int $part, int $total): string
    {
        if ($total === 0) {
            return '0%';
        }

        return round(($part / $total) * 100, 1) . '%';
    }

    /**
     * Emoji por status
     */
    protected function getStatusEmoji(string $status): string
    {
        return match($status) {
            TirePressureCorrection::STATUS_COMPLETED => '✅',
            TirePressureCorrection::STATUS_NO_CHANGES => '➡️',
            TirePressureCorrection::STATUS_FAILED => '❌',
            TirePressureCorrection::STATUS_PROCESSING => '⏳',
            TirePressureCorrection::STATUS_PENDING => '📋',
            default => '❓'
        };
    }
}