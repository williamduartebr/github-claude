<?php

namespace Src\ContentGeneration\ReviewSchedule\Console;

use Illuminate\Console\Command;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\Eloquent\ReviewScheduleArticle;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\ContentTemplates\CarMaintenanceTemplate;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\ContentTemplates\MotorcycleMaintenanceTemplate;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\ContentTemplates\ElectricVehicleMaintenanceTemplate;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\ContentTemplates\HybridVehicleMaintenanceTemplate;

class FixDetailedSchedule extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'review-schedule:fix-detailed-schedule 
                            {--limit=100 : Limit number of articles to fix}
                            {--dry-run : Show what would be fixed without saving}
                            {--vehicle-type= : Filter by vehicle type}
                            {--force : Fix even if schedule exists but incomplete}';

    /**
     * The console command description.
     */
    protected $description = 'Fix missing cronograma_detalhado fields using templates';

    private array $fixedArticles = [];
    private array $statistics = [
        'total_processed' => 0,
        'total_fixed' => 0,
        'already_complete' => 0,
        'fix_errors' => 0,
        'fixes_by_type' => []
    ];

    public function handle()
    {
        $limit = (int)$this->option('limit');
        $dryRun = $this->option('dry-run');
        $vehicleType = $this->option('vehicle-type');
        $force = $this->option('force');

        $this->info($dryRun ? '🔍 SIMULAÇÃO de correção do cronograma_detalhado...' : '🔧 Corrigindo cronograma_detalhado...');

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

        try {
            $fixedContent = $this->fixScheduleContent($content, $vehicleInfo, $vehicleType);
            
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

    private function fixScheduleContent(array $content, array $vehicleInfo, string $vehicleType): array
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
            // Gerar cronograma usando o template (mantém estrutura original)
            $newSchedule = $template->generateDetailedSchedule($vehicleData);
            
            // Verificar se o schedule foi gerado corretamente
            if (empty($newSchedule)) {
                throw new \Exception("Template retornou cronograma vazio");
            }
            
            // Verificar se tem a estrutura esperada
            if (!isset($newSchedule[0])) {
                throw new \Exception("Cronograma sem revisões");
            }
            
            // Atualizar conteúdo preservando estrutura do template
            $content['cronograma_detalhado'] = $newSchedule;
            
            return $content;
        } catch (\Exception $e) {
            // Se falhar, criar cronograma básico com estrutura correta
            $content['cronograma_detalhado'] = $this->createBasicScheduleCorrectFormat($vehicleData, $vehicleType);
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

    private function createBasicScheduleCorrectFormat(array $vehicleData, string $vehicleType): array
    {
        $schedule = [];
        $intervals = $this->getBasicIntervals($vehicleType);
        
        foreach ($intervals as $index => $interval) {
            $revisionNumber = $index + 1;
            $schedule[] = [
                'numero_revisao' => $revisionNumber,
                'intervalo' => $interval['intervalo'],
                'km' => $interval['km'],
                'servicos_principais' => $this->getBasicServices($revisionNumber, $vehicleType),
                'verificacoes_complementares' => $this->getBasicChecks($revisionNumber, $vehicleType),
                'estimativa_custo' => $this->getBasicCost($revisionNumber),
                'observacoes' => $this->getBasicObservations($revisionNumber)
            ];
        }
        
        return $schedule;
    }

    private function getBasicIntervals(string $vehicleType): array
    {
        if (in_array($vehicleType, ['motorcycle', 'moto'])) {
            return [
                ['intervalo' => '1.000 km ou 6 meses', 'km' => '1.000'],
                ['intervalo' => '5.000 km ou 12 meses', 'km' => '5.000'],
                ['intervalo' => '10.000 km ou 18 meses', 'km' => '10.000'],
                ['intervalo' => '15.000 km ou 24 meses', 'km' => '15.000'],
                ['intervalo' => '20.000 km ou 30 meses', 'km' => '20.000'],
                ['intervalo' => '25.000 km ou 36 meses', 'km' => '25.000']
            ];
        }
        
        return [
            ['intervalo' => '10.000 km ou 12 meses', 'km' => '10.000'],
            ['intervalo' => '20.000 km ou 24 meses', 'km' => '20.000'],
            ['intervalo' => '30.000 km ou 36 meses', 'km' => '30.000'],
            ['intervalo' => '40.000 km ou 48 meses', 'km' => '40.000'],
            ['intervalo' => '50.000 km ou 60 meses', 'km' => '50.000'],
            ['intervalo' => '60.000 km ou 72 meses', 'km' => '60.000']
        ];
    }

    private function getBasicCost(int $revision): string
    {
        $costs = [
            1 => 'R$ 280 - R$ 350',
            2 => 'R$ 320 - R$ 420',
            3 => 'R$ 380 - R$ 520',
            4 => 'R$ 450 - R$ 650',
            5 => 'R$ 520 - R$ 750',
            6 => 'R$ 600 - R$ 900'
        ];
        
        return $costs[$revision] ?? 'R$ 350 - R$ 500';
    }

    private function getBasicServices(int $revision, string $vehicleType): array
    {
        if (in_array($vehicleType, ['motorcycle', 'moto'])) {
            $services = [
                1 => [
                    'Verificação inicial completa da motocicleta',
                    'Troca do óleo lubrificante e filtro',
                    'Ajuste da corrente de transmissão',
                    'Regulagem de válvulas se necessário'
                ],
                2 => [
                    'Substituição do óleo e filtro do motor',
                    'Inspeção do sistema de freios',
                    'Lubrificação da corrente',
                    'Verificação do sistema elétrico'
                ],
                3 => [
                    'Troca de óleo e filtro',
                    'Limpeza do filtro de ar',
                    'Regulagem de válvulas',
                    'Verificação da embreagem'
                ]
            ];
        } else {
            $services = [
                1 => [
                    'Verificação minuciosa do sistema de freios',
                    'Substituição do filtro de ar-condicionado',
                    'Diagnóstico básico dos sistemas elétricos',
                    'Inspeção detalhada dos pneumáticos'
                ],
                2 => [
                    'Troca de óleo e filtro do motor',
                    'Substituição dos filtros de ar e combustível',
                    'Verificação do sistema de arrefecimento',
                    'Inspeção dos freios e pastilhas'
                ],
                3 => [
                    'Substituição do óleo lubrificante',
                    'Limpeza do sistema de injeção',
                    'Verificação da embreagem',
                    'Análise do sistema elétrico completo'
                ]
            ];
        }
        
        return $services[$revision] ?? $services[1];
    }

    private function getBasicChecks(int $revision, string $vehicleType): array
    {
        if (in_array($vehicleType, ['motorcycle', 'moto'])) {
            return [
                'Verificação da calibragem dos pneus',
                'Teste do sistema de iluminação',
                'Inspeção da bateria e terminais',
                'Verificação dos espelhos retrovisores'
            ];
        }
        
        return [
            'Verificação da pressão dos pneus',
            'Teste da bateria e sistema de carga',
            'Inspeção do sistema de iluminação',
            'Verificação dos níveis de fluidos'
        ];
    }

    private function getBasicObservations(int $revision): string
    {
        $observations = [
            1 => 'Primeira revisão focada em adaptação do veículo',
            2 => 'Revisão de acompanhamento dos sistemas principais',
            3 => 'Manutenção preventiva intermediária',
            4 => 'Revisão ampla com atenção aos desgastes',
            5 => 'Verificação completa dos sistemas críticos',
            6 => 'Revisão extensiva com foco na longevidade'
        ];
        
        return $observations[$revision] ?? 'Revisão de manutenção preventiva';
    }

    private function recordFixPreview($article, array $originalContent, array $fixedContent, array $vehicleInfo): void
    {
        $originalSchedule = $originalContent['cronograma_detalhado'] ?? [];
        $newSchedule = $fixedContent['cronograma_detalhado'] ?? [];
        
        $this->fixedArticles[] = [
            'id' => $article->_id ?? $article->id,
            'title' => $article->title,
            'vehicle' => [
                'marca' => $vehicleInfo['marca'] ?? 'N/A',
                'modelo' => $vehicleInfo['modelo'] ?? 'N/A',
                'ano' => $vehicleInfo['ano'] ?? 'N/A'
            ],
            'original_revisions' => count($originalSchedule),
            'new_revisions' => count($newSchedule),
            'preview' => [
                'first_revision' => $newSchedule[0] ?? null,
                'has_required_fields' => $this->hasRequiredFields($newSchedule[0] ?? [])
            ]
        ];
    }

    private function hasRequiredFields(array $revision): array
    {
        return [
            'numero_revisao' => isset($revision['numero_revisao']),
            'intervalo' => isset($revision['intervalo']),
            'km' => isset($revision['km']),
            'estimativa_custo' => isset($revision['estimativa_custo']),
            'servicos_principais' => isset($revision['servicos_principais']),
            'verificacoes_complementares' => isset($revision['verificacoes_complementares'])
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
                ['Já Completos', $this->statistics['already_complete']],
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
            $this->line('   php artisan review-schedule:fix-detailed-schedule --limit=' . $this->option('limit'));
        } else {
            $this->info("✅ Correção concluída! {$this->statistics['total_fixed']} artigos foram atualizados.");
            $this->line('💡 Verifique os resultados com:');
            $this->line('   php artisan review-schedule:analyze-detailed-schedule --limit=' . $this->statistics['total_fixed']);
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
            $this->line("Revisões: {$fix['original_revisions']} → {$fix['new_revisions']}");
            
            if ($fix['preview']['first_revision']) {
                $fields = $fix['preview']['has_required_fields'];
                $status = [];
                foreach ($fields as $field => $exists) {
                    $status[] = $field . ': ' . ($exists ? '✅' : '❌');
                }
                $this->line("Campos: " . implode(', ', $status));
            }
            
            $this->line(str_repeat('-', 50));
        }
    }
}