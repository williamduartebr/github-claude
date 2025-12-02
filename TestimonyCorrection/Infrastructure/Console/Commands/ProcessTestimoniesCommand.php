<?php

namespace Src\TestimonyCorrection\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Src\TestimonyCorrection\Application\Services\TestimonyCorrectionService;

class ProcessTestimoniesCommand extends Command
{
    protected $signature = 'testimony:process {--limit=1}';
    protected $description = 'Processa depoimentos draft via IA';

    public function handle(TestimonyCorrectionService $service)
    {

        // Só executa em produção e staging
        // if (app()->environment(['local', 'testing'])) {
        //     return;
        // }

        $limit = (int) $this->option('limit');
        $done = $service->processDrafts($limit);
        $this->info("Processados: {$done}");
        return 0;
    }
}
