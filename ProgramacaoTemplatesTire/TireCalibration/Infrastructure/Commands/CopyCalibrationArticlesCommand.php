<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;

use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;

class CopyCalibrationArticlesCommand extends Command
{
    protected $signature = 'tire-calibration:copy-calibration {--dry-run}';
    protected $description = 'Copia artigos com template_type calibration para collection tire_pressures';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('🔍 Executando em modo DRY RUN');
        }

        // Buscar artigos calibration
        $articles = TirePressureArticle::where('template_type', 'calibration')->get();

        $this->info("📊 Total de artigos calibration encontrados: {$articles->count()}");

        if ($articles->isEmpty()) {
            $this->warn('⚠️ Nenhum artigo encontrado com template_type = calibration');
            return 0;
        }

        $copied = 0;

        foreach ($articles as $article) {
            if (!$isDryRun) {
                TireCalibration::create([
                    'wordpress_url' => $article->wordpress_url,
                    'blog_modified_time' => $article->blog_modified_time,
                    'blog_published_time' => $article->blog_published_time,
                ]);
                $copied++;
            } else {
                $this->line("📝 [DRY RUN] Copiaria artigo ID: {$article->_id}");
            }
        }

        if (!$isDryRun) {
            $this->info("✅ {$copied} artigos copiados com sucesso!");
            Log::info("TireCalibration: {$copied} artigos calibration copiados");
        } else {
            $this->info("✅ DRY RUN concluído - {$articles->count()} artigos seriam copiados");
        }

        return 0;
    }
}
