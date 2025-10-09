<?php

namespace Src\ContentGeneration\ReviewSchedule\Console;

use Illuminate\Console\Command;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\Eloquent\ReviewScheduleArticle;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\ContentTemplates\CarMaintenanceTemplate;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\ContentTemplates\MotorcycleMaintenanceTemplate;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\ContentTemplates\ElectricVehicleMaintenanceTemplate;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\ContentTemplates\HybridVehicleMaintenanceTemplate;

class FixOverviewSection extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'review-schedule:fix-overview 
                            {--limit=100 : Limit number of articles to fix}
                            {--dry-run : Show what would be fixed without saving}
                            {--vehicle-type= : Filter by vehicle type}
                            {--force : Fix even if overview exists but is invalid}';

    /**
     * The console command description.
     */
    protected $description = 'Fix missing or invalid visao_geral_revisoes using templates';

    private array $fixedArticles = [];
    private array $statistics = [
        'total_processed' => 0,
        'total_fixed' => 0,
        'already_valid' => 0,
        'fix_errors' => 0,
        'fixes_by_type' => []
    ];

    public function handle()
    {
        $limit = (int)$this->option('limit');
        $dryRun = $this->option('dry-run');
        $vehicleType = $this->option('vehicle-type');
        $force = $this->option('force');

        $this->info($dryRun ? '🔍 SIMULAÇÃO de correção da visao_geral_revisoes...' : '🔧 Corrigindo visao_geral_revisoes...');

        $query = ReviewScheduleArticle::limit($limit);

        if ($vehicleType) {
            $this->info("🔍 Filtrando por tipo: {$vehicleType}");
        }

        $articles = $query->get();
        $this->info("📊 Processando {$articles->count()} artigos...");

        $progressBar = $this->output->createProgressBar($articles->count());
        $progressBar->start();

        foreach ($articles as $article) {
            $this->processArticle($article, $dryRun, $vehicleType, $force);
            $this->statistics['total_processed']++;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->displayResults($dryRun);
    }

    private function processArticle($article, bool $dryRun, ?string $vehicleTypeFilter, bool $force): void
    {
        $content = $this->getContentArray($article);

        if (!$content) {
            return;
        }

        $vehicleInfo = $content['extracted_entities'] ?? [];
        $vehicleType = strtolower($vehicleInfo['tipo_veiculo'] ?? 'car');

        // Aplicar filtro se especificado
        if ($vehicleTypeFilter && !$this->matchesVehicleType($vehicleType, $vehicleTypeFilter)) {
            return;
        }

        // Verificar se precisa de correção
        if (!$this->needsOverviewFix($content, $force)) {
            $this->statistics['already_valid']++;
            return;
        }

        try {
            $fixedContent = $this->fixOverviewContent($content, $vehicleInfo, $vehicleType);

            if ($dryRun) {
                $this->recordFixPreview($article, $content, $fixedContent, $vehicleInfo);
            } else {
                $this->saveFixedArticle($article, $fixedContent);
            }

            $this->statistics['total_fixed']++;
            $this->recordFixByType($vehicleType);
        } catch (\Exception $e) {
            $this->statistics['fix_errors']++;
            $this->warn("Erro ao corrigir artigo {$article->_id}: {$e->getMessage()}");
        }
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

    private function matchesVehicleType(string $vehicleType, string $filter): bool
    {
        return strpos($vehicleType, strtolower($filter)) !== false;
    }

    private function needsOverviewFix(array $content, bool $force): bool
    {
        if (!isset($content['visao_geral_revisoes'])) {
            return true;
        }

        $overview = $content['visao_geral_revisoes'];

        // Se é null ou vazio
        if (empty($overview)) {
            return true;
        }

        // Verificar se estrutura está válida (aplicar mesma lógica do comando de análise)
        if (is_string($overview)) {
            return strlen(trim($overview)) < 100; // String muito curta
        }

        if (is_array($overview)) {
            // CORREÇÃO: Verificar se tem menos de 3 elementos (problema identificado)
            if (count($overview) < 3) {
                return true;
            }

            // Verificar se é array tabular válido
            if (empty($overview)) {
                return true;
            }

            $firstItem = $overview[0] ?? null;
            if (!is_array($firstItem)) {
                return true;
            }

            // Verificar campos obrigatórios básicos (mesmos critérios do análise)
            $requiredFields = ['revisao', 'intervalo'];
            foreach ($requiredFields as $field) {
                if (!isset($firstItem[$field])) {
                    return true;
                }
            }
        }

        return false;
    }

    private function fixOverviewContent(array $content, array $vehicleInfo, string $vehicleType): array
    {
        // Obter template apropriado
        $template = $this->getTemplateForVehicleType($vehicleType);

        // Preparar dados do veículo
        $vehicleData = [
            'make' => $vehicleInfo['marca'] ?? 'Veículo',
            'model' => $vehicleInfo['modelo'] ?? 'Genérico',
            'year' => $vehicleInfo['ano'] ?? date('Y'),
            'engine' => $vehicleInfo['motor'] ?? '1.0',
            'vehicle_type' => $vehicleType,
            'fuel_type' => $this->extractFuelType($vehicleInfo)
        ];

        try {
            // Gerar nova visão geral usando o template
            $newOverview = $template->generateOverviewTable($vehicleData);

            // Verificar se foi gerada corretamente
            if (empty($newOverview)) {
                throw new \Exception("Template retornou overview vazia");
            }

            // Atualizar conteúdo
            $content['visao_geral_revisoes'] = $newOverview;

            return $content;
        } catch (\Exception $e) {
            // Se falhar, criar overview básica
            $content['visao_geral_revisoes'] = $this->createBasicOverview($vehicleData, $vehicleType);
            return $content;
        }
    }

    private function getTemplateForVehicleType(string $vehicleType): object
    {
        switch ($vehicleType) {
            case 'motorcycle':
            case 'moto':
                return new MotorcycleMaintenanceTemplate();

            case 'electric':
            case 'eletrico':
                return new ElectricVehicleMaintenanceTemplate();

            case 'hybrid':
            case 'hibrido':
                return new HybridVehicleMaintenanceTemplate();

            default:
                return new CarMaintenanceTemplate();
        }
    }

    private function extractFuelType(array $vehicleInfo): string
    {
        $fuelType = strtolower($vehicleInfo['combustivel'] ?? 'flex');

        $fuelMap = [
            'gasolina' => 'gasoline',
            'etanol' => 'ethanol',
            'diesel' => 'diesel',
            'flex' => 'flex',
            'eletrico' => 'electric',
            'hibrido' => 'hybrid'
        ];

        return $fuelMap[$fuelType] ?? 'flex';
    }

    private function createBasicOverview(array $vehicleData, string $vehicleType): array
    {
        // Criar overview tabular básica
        if (in_array($vehicleType, ['motorcycle', 'moto'])) {
            return [
                [
                    'revisao' => '1ª Revisão',
                    'intervalo' => '1.000 km ou 6 meses',
                    'principais_servicos' => 'Óleo, filtro, ajustes iniciais',
                    'estimativa_custo' => 'R$ 150 - R$ 220'
                ],
                [
                    'revisao' => '2ª Revisão',
                    'intervalo' => '5.000 km ou 12 meses',
                    'principais_servicos' => 'Óleo, freios, corrente',
                    'estimativa_custo' => 'R$ 180 - R$ 280'
                ],
                [
                    'revisao' => '3ª Revisão',
                    'intervalo' => '10.000 km ou 18 meses',
                    'principais_servicos' => 'Óleo, válvulas, filtros',
                    'estimativa_custo' => 'R$ 220 - R$ 350'
                ],
                [
                    'revisao' => '4ª Revisão',
                    'intervalo' => '15.000 km ou 24 meses',
                    'principais_servicos' => 'Óleo, velas, fluido de freio',
                    'estimativa_custo' => 'R$ 280 - R$ 420'
                ],
                [
                    'revisao' => '5ª Revisão',
                    'intervalo' => '20.000 km ou 30 meses',
                    'principais_servicos' => 'Revisão ampla, velas, sincronização',
                    'estimativa_custo' => 'R$ 350 - R$ 500'
                ],
                [
                    'revisao' => '6ª Revisão',
                    'intervalo' => '25.000 km ou 36 meses',
                    'principais_servicos' => 'Óleo, transmissão, suspensão',
                    'estimativa_custo' => 'R$ 400 - R$ 600'
                ]
            ];
        }

        // Overview para carros, elétricos e híbridos
        return [
            [
                'revisao' => '1ª Revisão',
                'intervalo' => '10.000 km ou 12 meses',
                'principais_servicos' => 'Óleo, filtros, verificações básicas',
                'estimativa_custo' => 'R$ 280 - R$ 350'
            ],
            [
                'revisao' => '2ª Revisão',
                'intervalo' => '20.000 km ou 24 meses',
                'principais_servicos' => 'Óleo, filtros de ar e combustível',
                'estimativa_custo' => 'R$ 320 - R$ 420'
            ],
            [
                'revisao' => '3ª Revisão',
                'intervalo' => '30.000 km ou 36 meses',
                'principais_servicos' => 'Óleo, limpeza de injetores, embreagem',
                'estimativa_custo' => 'R$ 380 - R$ 520'
            ],
            [
                'revisao' => '4ª Revisão',
                'intervalo' => '40.000 km ou 48 meses',
                'principais_servicos' => 'Óleo, correias, transmissão',
                'estimativa_custo' => 'R$ 450 - R$ 650'
            ],
            [
                'revisao' => '5ª Revisão',
                'intervalo' => '50.000 km ou 60 meses',
                'principais_servicos' => 'Óleo, arrefecimento, direção, suspensão',
                'estimativa_custo' => 'R$ 520 - R$ 750'
            ],
            [
                'revisao' => '6ª Revisão',
                'intervalo' => '60.000 km ou 72 meses',
                'principais_servicos' => 'Revisão ampla, correia dentada, fluidos',
                'estimativa_custo' => 'R$ 600 - R$ 900'
            ]
        ];
    }

    private function recordFixPreview($article, array $originalContent, array $fixedContent, array $vehicleInfo): void
    {
        $originalOverview = $originalContent['visao_geral_revisoes'] ?? null;
        $newOverview = $fixedContent['visao_geral_revisoes'] ?? null;

        $this->fixedArticles[] = [
            'id' => $article->_id ?? $article->id,
            'title' => $article->title,
            'vehicle' => [
                'marca' => $vehicleInfo['marca'] ?? 'N/A',
                'modelo' => $vehicleInfo['modelo'] ?? 'N/A',
                'ano' => $vehicleInfo['ano'] ?? 'N/A'
            ],
            'original_type' => gettype($originalOverview),
            'new_type' => gettype($newOverview),
            'new_count' => is_array($newOverview) ? count($newOverview) : strlen($newOverview ?? ''),
            'preview' => [
                'first_row' => is_array($newOverview) ? ($newOverview[0] ?? null) : substr($newOverview ?? '', 0, 100)
            ]
        ];
    }

    private function saveFixedArticle($article, array $fixedContent): void
    {
        try {
            $article->content = $fixedContent;
            $article->updated_at = now();
            $article->save();
        } catch (\Exception $e) {
            throw new \Exception("Erro ao salvar artigo: {$e->getMessage()}");
        }
    }

    private function recordFixByType(string $vehicleType): void
    {
        if (!isset($this->statistics['fixes_by_type'][$vehicleType])) {
            $this->statistics['fixes_by_type'][$vehicleType] = 0;
        }
        $this->statistics['fixes_by_type'][$vehicleType]++;
    }

    private function displayResults(bool $dryRun): void
    {
        $this->info($dryRun ? '📋 RESULTADO DA SIMULAÇÃO:' : '✅ RESULTADO DA CORREÇÃO:');

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total Processado', $this->statistics['total_processed']],
                ['Corrigidos', $this->statistics['total_fixed']],
                ['Já Válidos', $this->statistics['already_valid']],
                ['Erros', $this->statistics['fix_errors']]
            ]
        );

        if (!empty($this->statistics['fixes_by_type'])) {
            $this->newLine();
            $this->info('🔧 CORREÇÕES POR TIPO:');
            $typeTable = [];
            foreach ($this->statistics['fixes_by_type'] as $type => $count) {
                $typeTable[] = [$type, $count];
            }
            $this->table(['Tipo de Veículo', 'Artigos Corrigidos'], $typeTable);
        }

        // Mostrar exemplos de correções
        if ($dryRun && !empty($this->fixedArticles)) {
            $this->displayFixPreview();
        }

        $this->newLine();
        if ($dryRun) {
            $this->info('💡 Para aplicar as correções, execute sem --dry-run:');
            $this->line('   php artisan review-schedule:fix-overview --limit=' . $this->option('limit'));
        } else {
            $this->info("✅ Correção concluída! {$this->statistics['total_fixed']} artigos foram atualizados.");
            $this->line('💡 Verifique os resultados com:');
            $this->line('   php artisan review-schedule:analyze-overview --limit=' . $this->statistics['total_fixed']);
        }
    }

    private function displayFixPreview(): void
    {
        $this->newLine();
        $this->info('📝 PREVIEW DAS CORREÇÕES (primeiros 5):');

        foreach (array_slice($this->fixedArticles, 0, 5) as $fix) {
            $this->newLine();
            $this->line("ID: {$fix['id']}");
            $this->line("Veículo: {$fix['vehicle']['marca']} {$fix['vehicle']['modelo']} {$fix['vehicle']['ano']}");
            $this->line("Tipo Original: {$fix['original_type']} → Novo: {$fix['new_type']}");

            if ($fix['new_type'] === 'array') {
                $this->line("Linhas na tabela: {$fix['new_count']}");
                if ($fix['preview']['first_row']) {
                    $firstRow = $fix['preview']['first_row'];
                    $this->line("Primeira linha: {$firstRow['revisao']} - {$firstRow['intervalo']}");
                }
            } else {
                $this->line("Tamanho do texto: {$fix['new_count']} caracteres");
                $this->line("Preview: {$fix['preview']['first_row']}...");
            }

            $this->line(str_repeat('-', 50));
        }
    }
}
