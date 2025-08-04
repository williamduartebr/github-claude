<?php

namespace Src\TirePressureCorrection\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\TirePressureCorrection\Domain\Entities\TirePressureCorrection;
use Src\VehicleData\Domain\Entities\VehicleData;

/**
 * Command para atualizar SEO e FAQ dos artigos usando dados do VehicleData
 * 
 * Com sistema de tracking via TirePressureCorrection para evitar reprocessamento
 */
class UpdateSeoAndFaqFromVehicleDataCommand extends Command
{
    protected $signature = 'articles:update-seo-faq-from-vehicle-data
                           {--limit=100 : Número máximo de artigos para processar}
                           {--dry-run : Simular execução sem salvar}
                           {--force : Forçar atualização mesmo se já processado}
                           {--only-seo : Atualizar apenas dados SEO}
                           {--only-faq : Atualizar apenas perguntas frequentes}
                           {--min-quality-score=6.0 : Score mínimo de qualidade dos dados}
                           {--batch-size=50 : Tamanho do lote para processamento}';

    protected $description = 'Atualizar SEO e FAQ dos artigos usando dados validados do VehicleData';

    // Novo tipo de correção para SEO/FAQ
    const CORRECTION_TYPE_SEO_FAQ = 'seo_faq_update';

    protected int $processedCount = 0;
    protected int $seoUpdatedCount = 0;
    protected int $faqUpdatedCount = 0;
    protected int $skippedCount = 0;
    protected int $errorCount = 0;
    protected array $errors = [];

    // Templates de FAQ por categoria
    protected array $faqTemplates = [
        'motorcycle' => [
            [
                'pergunta' => 'Posso usar medida diferente no {marca} {modelo} {ano}?',
                'resposta' => 'Não é recomendado. Use sempre a medida original {medida_pneu} para manter as características de segurança, economia e desempenho especificadas pelo fabricante.',
                'priority' => 1
            ],
            [
                'pergunta' => 'Com que frequência devo verificar a pressão no {marca} {modelo} {ano}?',
                'resposta' => 'Em motocicletas, verifique semanalmente ou antes de cada saída. Use as pressões recomendadas: {pressao_display}.',
                'priority' => 1
            ],
            [
                'pergunta' => 'É seguro trocar apenas um pneu no {marca} {modelo} {ano}?',
                'resposta' => 'Em motocicletas, é altamente recomendado trocar sempre em pares ou individualmente conforme desgaste. Mantenha sempre pneus da mesma marca e modelo para garantir comportamento uniforme.',
                'priority' => 2
            ],
            [
                'pergunta' => 'Posso usar pneus de carro na motocicleta {marca} {modelo} {ano}?',
                'resposta' => 'Jamais! Motocicletas exigem pneus específicos com construção, compostos e desenhos adequados às características de duas rodas.',
                'priority' => 1
            ],
            [
                'pergunta' => 'Como evitar o formato quadrado no pneu traseiro do {marca} {modelo} {ano}?',
                'resposta' => 'Varie o estilo de pilotagem, evite apenas uso urbano, faça curvas ocasionalmente e mantenha a pressão correta. O formato quadrado é comum em uso urbano excessivo.',
                'priority' => 3
            ]
        ],
        'car' => [
            [
                'pergunta' => 'Posso usar medida diferente no {marca} {modelo} {ano}?',
                'resposta' => 'Não é recomendado. Use sempre a medida original {medida_pneu} para manter as características de segurança, economia e desempenho especificadas pelo fabricante.',
                'priority' => 1
            ],
            [
                'pergunta' => 'Com que frequência devo verificar a pressão no {marca} {modelo} {ano}?',
                'resposta' => 'Verifique mensalmente e antes de viagens longas. Use as pressões recomendadas: {pressao_display} (vazio) e {pressao_carregado_display} (com carga).',
                'priority' => 1
            ],
            [
                'pergunta' => 'É seguro trocar apenas um pneu no {marca} {modelo} {ano}?',
                'resposta' => 'O ideal é trocar em pares (eixo dianteiro ou traseiro). Se trocar apenas um, use pneu idêntico e coloque no lado direito para melhor estabilidade.',
                'priority' => 2
            ],
            [
                'pergunta' => 'Preciso fazer rodízio de pneus no {marca} {modelo} {ano}?',
                'resposta' => 'Sim, faça rodízio a cada 10.000 km seguindo o padrão cruzado (dianteiro esquerdo → traseiro direito) para desgaste uniforme e maior durabilidade.',
                'priority' => 2
            ],
            [
                'pergunta' => 'Como identificar desgaste irregular no {marca} {modelo} {ano}?',
                'resposta' => 'Verifique se há desgaste apenas nas bordas (pressão baixa), no centro (pressão alta) ou de um lado (desalinhamento). Desgaste irregular indica problema que deve ser corrigido.',
                'priority' => 3
            ]
        ],
        'suv' => [
            [
                'pergunta' => 'SUVs como o {marca} {modelo} {ano} precisam de pressão diferente?',
                'resposta' => 'Sim, SUVs geralmente requerem pressões mais altas devido ao peso e centro de gravidade. Use sempre as pressões recomendadas: {pressao_display}.',
                'priority' => 1
            ],
            [
                'pergunta' => 'Posso usar pneus de passeio no {marca} {modelo} {ano}?',
                'resposta' => 'Depende da especificação original. Verifique se o pneu de passeio suporta o peso e torque do seu SUV. O ideal é usar pneus específicos para SUV.',
                'priority' => 2
            ]
        ],
        'pickup' => [
            [
                'pergunta' => 'Pickups como o {marca} {modelo} {ano} precisam de pressão diferente vazio e carregado?',
                'resposta' => 'Sim, a diferença é maior em pickups devido à variação de carga. Use {pressao_display} vazio e {pressao_carregado_display} carregado.',
                'priority' => 1
            ],
            [
                'pergunta' => 'Posso usar pneus de carga no {marca} {modelo} {ano} sem carga?',
                'resposta' => 'Sim, mas pneus de carga podem comprometer o conforto no uso sem carga. Para uso urbano frequente, considere pneus híbridos.',
                'priority' => 3
            ]
        ],
        'electric' => [
            [
                'pergunta' => 'Pneus de veículos elétricos como o {marca} {modelo} {ano} são especiais?',
                'resposta' => 'Veículos elétricos podem usar pneus específicos com menor resistência ao rolamento para maximizar autonomia, mas pneus convencionais também funcionam adequadamente.',
                'priority' => 1
            ],
            [
                'pergunta' => 'A frequência de troca é diferente no {marca} {modelo} {ano} por ser elétrico?',
                'resposta' => 'O torque instantâneo dos motores elétricos pode acelerar o desgaste dos pneus. Verifique mais frequentemente e considere pneus de maior durabilidade se necessário.',
                'priority' => 2
            ],
            [
                'pergunta' => 'Como o sistema de regeneração afeta os pneus do {marca} {modelo} {ano}?',
                'resposta' => 'A frenagem regenerativa reduz o uso dos freios convencionais, mas pode causar desgaste diferenciado. Monitore o desgaste e faça rodízio mais frequentemente.',
                'priority' => 3
            ]
        ],
        'hybrid' => [
            [
                'pergunta' => 'Híbridos como o {marca} {modelo} {ano} têm desgaste diferente de pneus?',
                'resposta' => 'Veículos híbridos podem ter padrão de desgaste diferente devido às transições entre motor elétrico e combustão. Monitore regularmente o desgaste dos pneus.',
                'priority' => 1
            ],
            [
                'pergunta' => 'Preciso de pneus específicos para o {marca} {modelo} {ano} híbrido?',
                'resposta' => 'Não obrigatoriamente, mas pneus de baixa resistência ao rolamento podem melhorar a eficiência do sistema híbrido. Consulte as especificações do fabricante.',
                'priority' => 2
            ],
            [
                'pergunta' => 'A regeneração de energia no {marca} {modelo} {ano} afeta os pneus?',
                'resposta' => 'A frenagem regenerativa dos híbridos é mais suave que a dos elétricos puros, mas ainda pode influenciar o padrão de desgaste. Faça rodízio conforme recomendado.',
                'priority' => 3
            ]
        ]
    ];

    public function handle(): int
    {
        $this->info('🚀 ATUALIZANDO SEO E FAQ VIA VEHICLE DATA (COM TRACKING)');
        $this->newLine();

        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $onlySeo = $this->option('only-seo');
        $onlyFaq = $this->option('only-faq');
        $minQualityScore = (float) $this->option('min-quality-score');
        $batchSize = (int) $this->option('batch-size');

        if ($dryRun) {
            $this->warn('🔍 MODO DRY-RUN: Nenhuma alteração será salva');
            $this->newLine();
        }

        try {
            // Verificar status inicial do tracking
            $this->showTrackingStats();

            // Buscar artigos elegíveis (considerando tracking)
            $articles = $this->getEligibleArticles($limit, $force);

            if ($articles->isEmpty()) {
                $this->info('✅ Nenhum artigo encontrado para processar');
                return Command::SUCCESS;
            }

            $this->info("📊 Artigos elegíveis: {$articles->count()}");
            $this->newLine();

            // Processar em lotes
            $this->processInBatches($articles, $batchSize, $dryRun, $minQualityScore, $onlySeo, $onlyFaq);

            // Exibir resultados
            $this->showResults();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ ERRO: ' . $e->getMessage());
            Log::error('UpdateSeoAndFaqFromVehicleDataCommand: Erro fatal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Mostrar estatísticas do tracking
     */
    protected function showTrackingStats(): void
    {
        $this->info('📊 STATUS DO TRACKING (SEO/FAQ):');

        // Contar por tipo de correção
        $seoFaqCorrections = TirePressureCorrection::where('correction_type', self::CORRECTION_TYPE_SEO_FAQ)->count();
        $recentSeoFaq = TirePressureCorrection::where('correction_type', self::CORRECTION_TYPE_SEO_FAQ)
            ->where('created_at', '>=', now()->subDays(1))
            ->count();

        $this->line("   🎯 Total de correções SEO/FAQ: {$seoFaqCorrections}");
        $this->line("   🕐 Processados nas últimas 24h: {$recentSeoFaq}");
        $this->newLine();
    }

    /**
     * Buscar artigos elegíveis (considerando tracking)
     */
    protected function getEligibleArticles(int $limit, bool $force): \Illuminate\Support\Collection
    {
        $articles = collect();

        if (!$force) {
            // Buscar artigos NÃO processados recentemente para SEO/FAQ
            $recentlyProcessed = TirePressureCorrection::where('correction_type', self::CORRECTION_TYPE_SEO_FAQ)
                ->where('created_at', '>=', now()->subDays(7)) // 7 dias para SEO/FAQ
                ->whereIn('status', [
                    TirePressureCorrection::STATUS_COMPLETED,
                    TirePressureCorrection::STATUS_NO_CHANGES
                ])
                ->pluck('article_id');

            $this->line("🔍 Artigos já processados (SEO/FAQ): {$recentlyProcessed->count()}");
        } else {
            $recentlyProcessed = collect();
            $this->warn('⚠️  MODO FORCE: Reprocessando todos os artigos');
        }

        // Query base
        $query = Article::where('template', 'when_to_change_tires')
            ->whereNotNull('extracted_entities');

        if ($recentlyProcessed->isNotEmpty()) {
            $query->whereNotIn('_id', $recentlyProcessed);
        }

        // Processar em chunks para validar dados
        $query->orderBy('updated_at', 'desc')
            ->chunk(200, function ($chunk) use (&$articles, $limit) {
                foreach ($chunk as $article) {
                    if ($articles->count() >= $limit) {
                        return false; // Parar chunk
                    }

                    $marca = data_get($article, 'extracted_entities.marca');
                    $modelo = data_get($article, 'extracted_entities.modelo');
                    $ano = data_get($article, 'extracted_entities.ano');

                    if (!empty($marca) && !empty($modelo) && !empty($ano)) {
                        $articles->push($article);
                    }
                }
            });

        return $articles->take($limit);
    }

    /**
     * Processar artigos em lotes
     */
    protected function processInBatches(
        \Illuminate\Support\Collection $articles,
        int $batchSize,
        bool $dryRun,
        float $minQualityScore,
        bool $onlySeo,
        bool $onlyFaq
    ): void {
        $batches = $articles->chunk($batchSize);
        $totalBatches = $batches->count();

        $this->info("📦 Processando em {$totalBatches} lotes de {$batchSize} artigos");
        $this->newLine();

        foreach ($batches as $batchIndex => $batch) {
            $this->line("📦 Lote " . ($batchIndex + 1) . "/{$totalBatches}");
            
            $progressBar = $this->output->createProgressBar($batch->count());
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');

            foreach ($batch as $article) {
                $vehicleName = $this->getVehicleName($article);
                $progressBar->setMessage("Processando: {$vehicleName}");

                try {
                    $result = $this->processArticle($article, $dryRun, $minQualityScore, $onlySeo, $onlyFaq);

                    switch ($result['status']) {
                        case 'updated':
                            if ($result['seo_updated'] ?? false) $this->seoUpdatedCount++;
                            if ($result['faq_updated'] ?? false) $this->faqUpdatedCount++;
                            break;
                        case 'skipped':
                            $this->skippedCount++;
                            break;
                        case 'error':
                            $this->errorCount++;
                            $this->errors[] = $result['error'];
                            break;
                    }

                    $this->processedCount++;
                } catch (\Exception $e) {
                    $this->errorCount++;
                    $this->errors[] = [
                        'vehicle' => $vehicleName,
                        'error' => $e->getMessage()
                    ];

                    Log::error('UpdateSeoAndFaqFromVehicleDataCommand: Erro ao processar artigo', [
                        'article_id' => $article->_id,
                        'vehicle' => $vehicleName,
                        'error' => $e->getMessage()
                    ]);
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine();

            // Breve pausa entre lotes para evitar sobrecarga
            if ($batchIndex < $totalBatches - 1) {
                $this->line('⏳ Pausa entre lotes (2 segundos)...');
                sleep(2);
            }
        }
    }

    /**
     * Processar um artigo individual
     */
    protected function processArticle(
        Article $article,
        bool $dryRun,
        float $minQualityScore,
        bool $onlySeo,
        bool $onlyFaq
    ): array {
        $extractedEntities = data_get($article, 'extracted_entities', []);
        $marca = data_get($extractedEntities, 'marca');
        $modelo = data_get($extractedEntities, 'modelo');
        $ano = (int) data_get($extractedEntities, 'ano');

        // Criar ou buscar registro de tracking
        $correction = $this->getOrCreateTrackingRecord($article);

        try {
            // Marcar como processando
            if (!$dryRun) {
                $correction->markAsProcessing();
            }

            // ✅ NOVO: Priorizar dados do próprio Article
            $articleVehicleData = data_get($article, 'content.vehicle_data', []);
            
            if (!empty($articleVehicleData)) {
                // Usar dados do Article (mais confiável)
                $vehicleDataSource = 'article';
                $vehicleInfo = $this->createVehicleInfoFromArticle($articleVehicleData, $extractedEntities);
            } else {
                // Fallback: Buscar no VehicleData
                $vehicleDataFromDb = $this->findVehicleData($marca, $modelo, $ano);
                
                if (!$vehicleDataFromDb) {
                    if (!$dryRun) {
                        $correction->markAsFailed('Dados do veículo não encontrados');
                    }
                    return [
                        'status' => 'skipped',
                        'reason' => 'Dados do veículo não encontrados'
                    ];
                }

                // Verificar qualidade dos dados do VehicleData
                if ($vehicleDataFromDb->data_quality_score < $minQualityScore) {
                    if (!$dryRun) {
                        $correction->markAsFailed("Qualidade dos dados insuficiente: {$vehicleDataFromDb->data_quality_score}");
                    }
                    return [
                        'status' => 'skipped',
                        'reason' => "Qualidade dos dados insuficiente: {$vehicleDataFromDb->data_quality_score}"
                    ];
                }

                $vehicleDataSource = 'database';
                $vehicleInfo = $vehicleDataFromDb;
            }

            if ($dryRun) {
                return [
                    'status' => 'updated',
                    'seo_updated' => !$onlyFaq,
                    'faq_updated' => !$onlySeo,
                    'reason' => "DRY-RUN: SEO e FAQ seriam atualizados (fonte: {$vehicleDataSource})"
                ];
            }

            $fieldsUpdated = [];
            $seoUpdated = false;
            $faqUpdated = false;

            // Atualizar SEO
            if (!$onlyFaq) {
                $seoUpdated = $this->updateSeoData($article, $vehicleInfo);
                if ($seoUpdated) {
                    $fieldsUpdated[] = 'seo_data';
                }
            }

            // Atualizar FAQ
            if (!$onlySeo) {
                $faqUpdated = $this->updateFrequentQuestions($article, $vehicleInfo);
                if ($faqUpdated) {
                    $fieldsUpdated[] = 'perguntas_frequentes';
                }
            }

            // Salvar se houver alterações
            if ($seoUpdated || $faqUpdated) {
                $article->save();
                
                // Marcar correção como concluída
                $correction->markAsCompleted(
                    [
                        'seo_updated' => $seoUpdated,
                        'faq_updated' => $faqUpdated,
                        'data_source' => $vehicleDataSource,
                        'vehicle_info' => $vehicleInfo instanceof VehicleData ? [
                            'id' => $vehicleInfo->_id,
                            'quality_score' => $vehicleInfo->data_quality_score
                        ] : 'article_data'
                    ],
                    $fieldsUpdated
                );
            } else {
                // Marcar como sem alterações
                $correction->markAsNoChanges('SEO e FAQ já estavam atualizados');
            }

            return [
                'status' => 'updated',
                'seo_updated' => $seoUpdated,
                'faq_updated' => $faqUpdated,
                'data_source' => $vehicleDataSource
            ];

        } catch (\Exception $e) {
            if (!$dryRun) {
                $correction->markAsFailed($e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Criar estrutura de dados do veículo a partir do Article
     */
    protected function createVehicleInfoFromArticle(array $articleVehicleData, array $extractedEntities): object
    {
        // Criar objeto compatível com VehicleData
        $vehicleInfo = (object) [
            'make' => $articleVehicleData['vehicle_brand'] ?? $extractedEntities['marca'] ?? '',
            'model' => $articleVehicleData['vehicle_model'] ?? $extractedEntities['modelo'] ?? '',
            'year' => $articleVehicleData['vehicle_year'] ?? $extractedEntities['ano'] ?? 0,
            'tire_size' => $articleVehicleData['tire_size'] ?? $extractedEntities['medida_pneu'] ?? '',
            'main_category' => $articleVehicleData['vehicle_category'] ?? $extractedEntities['categoria'] ?? 'car',
            'is_motorcycle' => $articleVehicleData['is_motorcycle'] ?? false,
            'is_electric' => $articleVehicleData['is_electric'] ?? false,
            'is_hybrid' => $articleVehicleData['is_hybrid'] ?? false,
            'is_premium' => $articleVehicleData['is_premium'] ?? false,
            'has_tpms' => $articleVehicleData['has_tpms'] ?? false,
            'pressure_specifications' => $this->extractPressureSpecsFromArticle($articleVehicleData),
            'data_quality_score' => 9.0 // Article sempre tem qualidade alta
        ];

        Log::info('UpdateSeoAndFaqFromVehicleDataCommand: Usando dados do Article', [
            'vehicle' => "{$vehicleInfo->make} {$vehicleInfo->model} {$vehicleInfo->year}",
            'is_motorcycle' => $vehicleInfo->is_motorcycle,
            'is_electric' => $vehicleInfo->is_electric,
            'is_hybrid' => $vehicleInfo->is_hybrid,
            'main_category' => $vehicleInfo->main_category,
            'source' => 'article_vehicle_data'
        ]);

        return $vehicleInfo;
    }

    /**
     * Extrair especificações de pressão do Article
     */
    protected function extractPressureSpecsFromArticle(array $articleVehicleData): array
    {
        $pressures = $articleVehicleData['pressures'] ?? [];
        
        return [
            'pressure_empty_front' => $pressures['empty_front'] ?? null,
            'pressure_empty_rear' => $pressures['empty_rear'] ?? null,
            'pressure_light_front' => $pressures['loaded_front'] ?? $pressures['light_front'] ?? null,
            'pressure_light_rear' => $pressures['loaded_rear'] ?? $pressures['light_rear'] ?? null,
            'pressure_max_front' => $pressures['max_front'] ?? $pressures['loaded_front'] ?? null,
            'pressure_max_rear' => $pressures['max_rear'] ?? $pressures['loaded_rear'] ?? null,
            'pressure_spare' => $pressures['spare'] ?? null,
            'pressure_display' => $articleVehicleData['pressure_display'] ?? null,
            'loaded_pressure_display' => $articleVehicleData['pressure_loaded_display'] ?? null,
        ];
    }

    /**
     * Obter ou criar registro de tracking
     */
    protected function getOrCreateTrackingRecord(Article $article): TirePressureCorrection
    {
        // Buscar registro existente para SEO/FAQ
        $existing = TirePressureCorrection::where('article_id', $article->_id)
            ->where('correction_type', self::CORRECTION_TYPE_SEO_FAQ)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($existing) {
            return $existing;
        }

        // Criar novo registro
        return TirePressureCorrection::createForArticle($article, self::CORRECTION_TYPE_SEO_FAQ);
    }

    /**
     * Buscar dados do veículo (reutilizar lógica)
     */
    protected function findVehicleData(string $marca, string $modelo, int $ano): ?VehicleData
    {
        // Busca exata primeiro
        $vehicle = VehicleData::findVehicle($marca, $modelo, $ano);
        if ($vehicle) {
            return $vehicle;
        }

        // Buscar anos próximos
        $allYears = VehicleData::findAllYears($marca, $modelo);
        
        if ($allYears->isEmpty()) {
            return null;
        }

        // Encontrar o ano mais próximo
        $bestMatch = null;
        $smallestDiff = PHP_INT_MAX;

        foreach ($allYears as $vehicleOption) {
            $yearDiff = abs($vehicleOption->year - $ano);
            if ($yearDiff <= 3 && $yearDiff < $smallestDiff) {
                $bestMatch = $vehicleOption;
                $smallestDiff = $yearDiff;
            }
        }

        return $bestMatch;
    }

    /**
     * Atualizar dados SEO
     */
    protected function updateSeoData(Article $article, $vehicleInfo): bool
    {
        $seoData = $article->seo_data ?? [];
        $updated = false;

        // Extrair dados do veículo (compatível com VehicleData e objeto do Article)
        $marca = $vehicleInfo->make ?? '';
        $modelo = $vehicleInfo->model ?? '';
        $ano = $vehicleInfo->year ?? 0;
        $categoria = $this->getCategoryName($vehicleInfo->main_category ?? 'car');
        
        // Obter pressões formatadas
        $pressureSpecs = $vehicleInfo->pressure_specifications ?? [];
        $pressaoVazio = $this->formatPressure(
            $pressureSpecs['pressure_empty_front'] ?? $pressureSpecs['pressure_light_front'] ?? 0,
            $pressureSpecs['pressure_empty_rear'] ?? $pressureSpecs['pressure_light_rear'] ?? 0
        );

        // Gerar nova meta description
        $newMetaDescription = $this->generateMetaDescription(
            $marca, 
            $modelo, 
            $ano, 
            $categoria, 
            $pressaoVazio,
            $vehicleInfo->is_motorcycle ?? false
        );

        // Comparar e atualizar se diferente
        if (($seoData['meta_description'] ?? '') !== $newMetaDescription) {
            $seoData['meta_description'] = $newMetaDescription;
            $updated = true;
        }

        // Atualizar outras tags SEO se necessário
        $newTitle = "Pneus do {$marca} {$modelo} {$ano}: Sinais e Momento da Troca";
        if (($seoData['page_title'] ?? '') !== $newTitle) {
            $seoData['page_title'] = $newTitle;
            $updated = true;
        }

        // Atualizar keywords baseadas no veículo
        $primaryKeyword = "quando trocar pneu {$marca} {$modelo}";
        if (($seoData['primary_keyword'] ?? '') !== $primaryKeyword) {
            $seoData['primary_keyword'] = $primaryKeyword;
            $updated = true;
        }

        if ($updated) {
            $article->seo_data = $seoData;
        }

        return $updated;
    }

    /**
     * Gerar meta description contextualizada (sem pressões)
     */
    protected function generateMetaDescription(
        string $marca, 
        string $modelo, 
        int $ano, 
        string $categoria,
        string $pressao,
        bool $isMotorcycle
    ): string {
        if ($isMotorcycle) {
            return "Guia completo sobre quando trocar os pneus do {$marca} {$modelo} {$ano}. " .
                   "Sinais de desgaste, cronograma de verificação e dicas de manutenção para motocicletas. " .
                   "Saiba identificar o momento ideal da troca.";
        }

        return "Guia completo sobre quando trocar os pneus do {$marca} {$modelo} {$ano}. " .
               "Sinais de desgaste, cronograma de verificação e dicas de manutenção para {$categoria}. " .
               "Mantenha sua segurança em dia.";
    }

    /**
     * Atualizar perguntas frequentes
     */
    protected function updateFrequentQuestions(Article $article, $vehicleInfo): bool
    {
        $content = $article->content;
        
        // Determinar categoria para templates
        $templateCategory = $this->getTemplateCategory($vehicleInfo);
        
        // Gerar novas perguntas
        $newFaq = $this->generateFaq($vehicleInfo, $templateCategory);
        
        // Comparar com FAQ atual
        $currentFaq = $content['perguntas_frequentes'] ?? [];
        
        if ($this->faqIsDifferent($currentFaq, $newFaq)) {
            $content['perguntas_frequentes'] = $newFaq;
            $article->content = $content;
            return true;
        }

        return false;
    }

    /**
     * Gerar FAQ baseado no template e dados do veículo
     */
    protected function generateFaq($vehicleInfo, string $templateCategory): array
    {
        $baseTemplates = $this->faqTemplates[$templateCategory] ?? $this->faqTemplates['car'];
        
        // ⚠️ CORREÇÃO: Lógica melhorada para adicionar templates específicos
        
        // 1. Adicionar templates elétricos se for elétrico (e não foi a categoria base)
        if (($vehicleInfo->is_electric ?? false) === true && $templateCategory !== 'electric') {
            $electricTemplates = $this->faqTemplates['electric'] ?? [];
            $baseTemplates = array_merge($baseTemplates, $electricTemplates);
            
            Log::info('UpdateSeoAndFaqFromVehicleDataCommand: Adicionando templates elétricos', [
                'vehicle' => "{$vehicleInfo->make} {$vehicleInfo->model} {$vehicleInfo->year}",
                'base_category' => $templateCategory,
                'electric_templates_added' => count($electricTemplates)
            ]);
        }

        // 2. ✅ NOVO: Adicionar templates híbridos se for híbrido (e não foi a categoria base)
        if (($vehicleInfo->is_hybrid ?? false) === true && $templateCategory !== 'hybrid') {
            $hybridTemplates = $this->faqTemplates['hybrid'] ?? [];
            $baseTemplates = array_merge($baseTemplates, $hybridTemplates);
            
            Log::info('UpdateSeoAndFaqFromVehicleDataCommand: Adicionando templates híbridos', [
                'vehicle' => "{$vehicleInfo->make} {$vehicleInfo->model} {$vehicleInfo->year}",
                'base_category' => $templateCategory,
                'hybrid_templates_added' => count($hybridTemplates)
            ]);
        }

        $faq = [];
        $pressureSpecs = $vehicleInfo->pressure_specifications ?? [];
        
        // Log para debug
        Log::info('UpdateSeoAndFaqFromVehicleDataCommand: Gerando FAQ', [
            'vehicle' => "{$vehicleInfo->make} {$vehicleInfo->model} {$vehicleInfo->year}",
            'template_category' => $templateCategory,
            'is_electric' => $vehicleInfo->is_electric ?? false,
            'is_hybrid' => $vehicleInfo->is_hybrid ?? false,
            'is_motorcycle' => $vehicleInfo->is_motorcycle ?? false,
            'templates_count' => count($baseTemplates)
        ]);
        
        // Dados para substituição
        $replacements = [
            '{marca}' => $vehicleInfo->make ?? '',
            '{modelo}' => $vehicleInfo->model ?? '',  
            '{ano}' => $vehicleInfo->year ?? '',
            '{medida_pneu}' => $vehicleInfo->tire_size ?? 'original',
            '{pressao_display}' => $this->formatPressure(
                $pressureSpecs['pressure_empty_front'] ?? $pressureSpecs['pressure_light_front'] ?? 0,
                $pressureSpecs['pressure_empty_rear'] ?? $pressureSpecs['pressure_light_rear'] ?? 0
            ),
            '{pressao_carregado_display}' => $this->formatPressure(
                $pressureSpecs['pressure_max_front'] ?? $pressureSpecs['pressure_light_front'] ?? 0,
                $pressureSpecs['pressure_max_rear'] ?? $pressureSpecs['pressure_light_rear'] ?? 0
            )
        ];

        foreach ($baseTemplates as $template) {
            // Validar se template tem estrutura correta
            if (!isset($template['pergunta']) || !isset($template['resposta'])) {
                Log::warning('UpdateSeoAndFaqFromVehicleDataCommand: Template inválido ignorado', [
                    'template' => $template,
                    'vehicle' => "{$vehicleInfo->make} {$vehicleInfo->model} {$vehicleInfo->year}"
                ]);
                continue;
            }

            $pergunta = str_replace(array_keys($replacements), array_values($replacements), $template['pergunta']);
            $resposta = str_replace(array_keys($replacements), array_values($replacements), $template['resposta']);
            
            // Validar se substituições foram feitas corretamente
            if (str_contains($pergunta, '{') || str_contains($resposta, '{')) {
                Log::warning('UpdateSeoAndFaqFromVehicleDataCommand: Substituições incompletas', [
                    'pergunta' => $pergunta,
                    'resposta' => $resposta,
                    'replacements' => $replacements,
                    'vehicle' => "{$vehicleInfo->make} {$vehicleInfo->model} {$vehicleInfo->year}"
                ]);
            }

            // ⚠️ VALIDAÇÃO: Evitar FAQ incorreta baseada no tipo de veículo
            if (!($vehicleInfo->is_electric ?? false) && 
                (str_contains($pergunta, 'veículos elétricos') || str_contains($pergunta, 'elétrico'))) {
                Log::warning('UpdateSeoAndFaqFromVehicleDataCommand: FAQ elétrica ignorada para veículo não-elétrico', [
                    'vehicle' => "{$vehicleInfo->make} {$vehicleInfo->model} {$vehicleInfo->year}",
                    'is_electric' => $vehicleInfo->is_electric ?? false,
                    'pergunta' => $pergunta
                ]);
                continue;
            }

            // ✅ NOVA VALIDAÇÃO: Evitar FAQ híbrida em veículos não-híbridos
            if (!($vehicleInfo->is_hybrid ?? false) && 
                (str_contains($pergunta, 'híbrido') || str_contains($pergunta, 'Híbrido'))) {
                Log::warning('UpdateSeoAndFaqFromVehicleDataCommand: FAQ híbrida ignorada para veículo não-híbrido', [
                    'vehicle' => "{$vehicleInfo->make} {$vehicleInfo->model} {$vehicleInfo->year}",
                    'is_hybrid' => $vehicleInfo->is_hybrid ?? false,
                    'pergunta' => $pergunta
                ]);
                continue;
            }
            
            $faq[] = [
                'pergunta' => $pergunta,
                'resposta' => $resposta
            ];
        }

        // Limitar quantidade de FAQ (máximo 6)
        $faq = array_slice($faq, 0, 6);

        Log::info('UpdateSeoAndFaqFromVehicleDataCommand: FAQ gerada', [
            'vehicle' => "{$vehicleInfo->make} {$vehicleInfo->model} {$vehicleInfo->year}",
            'faq_count' => count($faq),
            'is_electric' => $vehicleInfo->is_electric ?? false,
            'is_hybrid' => $vehicleInfo->is_hybrid ?? false,
            'final_faq' => array_column($faq, 'pergunta') // Log apenas as perguntas para debug
        ]);

        return $faq;
    }

    /**
     * Determinar categoria para templates
     */
    protected function getTemplateCategory($vehicleInfo): string
    {
        // Log para debug
        Log::info('UpdateSeoAndFaqFromVehicleDataCommand: Determinando categoria de template', [
            'vehicle' => "{$vehicleInfo->make} {$vehicleInfo->model} {$vehicleInfo->year}",
            'is_motorcycle' => $vehicleInfo->is_motorcycle ?? false,
            'is_electric' => $vehicleInfo->is_electric ?? false,
            'is_hybrid' => $vehicleInfo->is_hybrid ?? false,
            'main_category' => $vehicleInfo->main_category ?? 'car'
        ]);

        // Prioridade 1: Motocicletas (independente de ser elétrica ou híbrida)
        if (($vehicleInfo->is_motorcycle ?? false) === true) {
            return 'motorcycle';
        }
        
        // Prioridade 2: Veículos elétricos puros (não híbridos)
        if (($vehicleInfo->is_electric ?? false) === true && ($vehicleInfo->is_hybrid ?? false) !== true) {
            return 'electric';
        }

        // ✅ Prioridade 3: Veículos híbridos (podem ser elétricos também)
        if (($vehicleInfo->is_hybrid ?? false) === true) {
            return 'hybrid';
        }
        
        // Prioridade 4: Categoria por tipo de carroceria
        $category = $vehicleInfo->main_category ?? 'car';
        
        if (in_array($category, ['suv', 'suv_electric', 'suv_hybrid'])) {
            return 'suv';
        }

        if (in_array($category, ['pickup'])) {
            return 'pickup';
        }
        
        // Padrão: carro comum
        return 'car';
    }

    /**
     * Verificar se FAQ é diferente
     */
    protected function faqIsDifferent(array $currentFaq, array $newFaq): bool
    {
        if (count($currentFaq) !== count($newFaq)) {
            return true;
        }

        foreach ($newFaq as $index => $newItem) {
            $currentItem = $currentFaq[$index] ?? [];
            
            if (($currentItem['pergunta'] ?? '') !== $newItem['pergunta'] ||
                ($currentItem['resposta'] ?? '') !== $newItem['resposta']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Formatar pressão para exibição
     */
    protected function formatPressure(int $front, int $rear): string
    {
        if ($front === $rear) {
            return "{$front} PSI";
        }
        
        return "{$front}/{$rear} PSI";
    }

    /**
     * Obter nome da categoria
     */
    protected function getCategoryName(string $category): string
    {
        $categoryNames = [
            'hatch' => 'hatchbacks',
            'sedan' => 'sedans',
            'suv' => 'SUVs',
            'pickup' => 'pickups',
            'motorcycle' => 'motocicletas',
            'motorcycle_electric' => 'motocicletas elétricas',
            'car_electric' => 'carros elétricos',
            'van' => 'vans',
            'minivan' => 'minivans'
        ];

        return $categoryNames[$category] ?? 'veículos';
    }

    /**
     * Obter nome do veículo para exibição
     */
    protected function getVehicleName(Article $article): string
    {
        $extractedEntities = data_get($article, 'extracted_entities', []);

        return sprintf(
            '%s %s %s',
            data_get($extractedEntities, 'marca', '?'),
            data_get($extractedEntities, 'modelo', '?'),
            data_get($extractedEntities, 'ano', '?')
        );
    }

    /**
     * Exibir resultados
     */
    protected function showResults(): void
    {
        $this->info('=== RESULTADO DA ATUALIZAÇÃO SEO E FAQ ===');
        $this->newLine();

        $this->line("📄 Artigos processados: <fg=cyan>{$this->processedCount}</>");
        $this->line("🎯 SEO atualizados: <fg=green>{$this->seoUpdatedCount}</>");
        $this->line("❓ FAQ atualizados: <fg=green>{$this->faqUpdatedCount}</>");
        $this->line("⏭️  Artigos ignorados: <fg=yellow>{$this->skippedCount}</>");
        $this->line("❌ Erros: <fg=red>{$this->errorCount}</>");

        if (!empty($this->errors)) {
            $this->newLine();
            $this->error('Erros encontrados:');
            foreach (array_slice($this->errors, 0, 10) as $error) {
                $vehicle = $error['vehicle'] ?? 'Desconhecido';
                $message = $error['error'] ?? $error['reason'] ?? 'Erro desconhecido';
                $this->line("  - {$vehicle}: {$message}");
            }

            if (count($this->errors) > 10) {
                $this->line("  ... e mais " . (count($this->errors) - 10) . " erros");
            }
        }

        $this->newLine();

        // Estatísticas finais do tracking
        $stats = TirePressureCorrection::where('correction_type', self::CORRECTION_TYPE_SEO_FAQ)
            ->get()
            ->groupBy('status')
            ->map->count();

        $this->info('📊 Status do tracking SEO/FAQ:');
        foreach ($stats as $status => $count) {
            $emoji = $this->getStatusEmoji($status);
            $this->line("  {$emoji} {$status}: {$count}");
        }

        $this->newLine();

        // Taxa de sucesso
        $totalProcessed = ($stats['completed'] ?? 0) + ($stats['no_changes'] ?? 0) + ($stats['failed'] ?? 0);
        if ($totalProcessed > 0) {
            $successCount = ($stats['completed'] ?? 0) + ($stats['no_changes'] ?? 0);
            $successRate = round(($successCount / $totalProcessed) * 100, 1);
            $this->line("📈 Taxa de sucesso: <fg=green>{$successRate}%</>");
        }

        // Próximos passos
        $this->newLine();
        $this->info('💡 PRÓXIMOS PASSOS:');
        
        $remaining = $this->calculateRemainingArticles();
        if ($remaining > 0) {
            $this->line("   📝 Restam aproximadamente {$remaining} artigos para processar");
            $this->line('   📋 Execute: php artisan articles:seo-faq-progress');
            $this->line('   🔄 Continue: php artisan articles:update-seo-faq-from-vehicle-data --limit=50');
        } else {
            $this->line('   ✅ Todos os artigos elegíveis foram processados!');
            if (($stats['failed'] ?? 0) > 0) {
                $this->line('   🔄 Para reprocessar falhas: --force --limit=50');
            }
        }

        $this->newLine();

        Log::info('UpdateSeoAndFaqFromVehicleDataCommand: Execução concluída', [
            'processed' => $this->processedCount,
            'seo_updated' => $this->seoUpdatedCount,
            'faq_updated' => $this->faqUpdatedCount,
            'skipped' => $this->skippedCount,
            'errors' => $this->errorCount,
            'success_rate' => $totalProcessed > 0 ? round((($stats['completed'] ?? 0) + ($stats['no_changes'] ?? 0)) / $totalProcessed * 100, 1) : 0
        ]);
    }

    /**
     * Calcular artigos restantes aproximadamente
     */
    protected function calculateRemainingArticles(): int
    {
        $totalValid = $this->countValidArticles();
        $processed = TirePressureCorrection::where('correction_type', self::CORRECTION_TYPE_SEO_FAQ)
            ->whereIn('status', [
                TirePressureCorrection::STATUS_COMPLETED,
                TirePressureCorrection::STATUS_NO_CHANGES
            ])
            ->count();

        return max(0, $totalValid - $processed);
    }

    /**
     * Contar artigos válidos rapidamente
     */
    protected function countValidArticles(): int
    {
        // Cache simples para evitar recontagem custosa
        static $validCount = null;
        
        if ($validCount === null) {
            $validCount = 0;
            Article::where('template', 'when_to_change_tires')
                ->whereNotNull('extracted_entities')
                ->chunk(100, function ($articles) use (&$validCount) {
                    foreach ($articles as $article) {
                        $marca = data_get($article, 'extracted_entities.marca');
                        $modelo = data_get($article, 'extracted_entities.modelo');
                        $ano = data_get($article, 'extracted_entities.ano');
                        
                        if (!empty($marca) && !empty($modelo) && !empty($ano)) {
                            $validCount++;
                        }
                    }
                });
        }
        
        return $validCount;
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
