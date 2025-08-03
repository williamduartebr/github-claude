<?php

namespace Src\TirePressureCorrection\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\TirePressureCorrection\Domain\Entities\TirePressureCorrection;
use Src\VehicleData\Domain\Entities\VehicleData;

/**
 * Command para atualizar pressões dos artigos usando dados do VehicleData
 * 
 * Evita chamadas à API do Claude usando dados já validados
 */
class UpdateTirePressuresFromVehicleDataCommand extends Command
{
    protected $signature = 'articles:update-tire-pressures-from-vehicle-data
                           {--limit=100 : Número máximo de artigos para processar}
                           {--dry-run : Simular execução sem salvar}
                           {--force : Forçar atualização mesmo se já processado}
                           {--min-quality-score=6.0 : Score mínimo de qualidade dos dados}';

    protected $description = 'Atualizar pressões dos artigos usando dados validados do VehicleData';

    protected int $processedCount = 0;
    protected int $updatedCount = 0;
    protected int $skippedCount = 0;
    protected int $errorCount = 0;
    protected array $errors = [];
    protected array $yearAlerts = [];

    public function handle(): int
    {
        $this->info('🚀 ATUALIZANDO PRESSÕES VIA VEHICLE DATA');
        $this->newLine();

        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $minQualityScore = (float) $this->option('min-quality-score');

        if ($dryRun) {
            $this->warn('🔍 MODO DRY-RUN: Nenhuma alteração será salva');
            $this->newLine();
        }

        try {
            // Buscar artigos elegíveis
            $articles = $this->getEligibleArticles($limit, $force);

            if ($articles->isEmpty()) {
                $this->info('✅ Nenhum artigo encontrado para processar');
                return Command::SUCCESS;
            }

            $this->info("📊 Artigos encontrados: {$articles->count()}");
            $this->newLine();

            // Processar artigos
            $this->processArticles($articles, $dryRun, $minQualityScore);

            // Exibir resultados
            $this->showResults();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ ERRO: ' . $e->getMessage());
            Log::error('UpdateTirePressuresFromVehicleDataCommand: Erro fatal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Buscar artigos elegíveis para atualização
     */
    protected function getEligibleArticles(int $limit, bool $force): \Illuminate\Support\Collection
    {
        $query = Article::where('template', 'when_to_change_tires')
            ->whereNotNull('extracted_entities')
            ->orderBy('updated_at', 'desc');

        // Se não forçar, excluir já processados recentemente
        if (!$force) {
            $recentlyProcessed = TirePressureCorrection::where('created_at', '>=', now()->subDays(7))
                ->where('status', '!=', TirePressureCorrection::STATUS_FAILED)
                ->pluck('article_id');

            if ($recentlyProcessed->isNotEmpty()) {
                $query->whereNotIn('_id', $recentlyProcessed);
            }
        }

        $articles = $query->limit($limit * 2)->get(); // Pegar mais para compensar filtros

        // Filtrar artigos com dados válidos
        return $articles->filter(function ($article) {
            $marca = data_get($article, 'extracted_entities.marca');
            $modelo = data_get($article, 'extracted_entities.modelo');
            $ano = data_get($article, 'extracted_entities.ano');

            return !empty($marca) && !empty($modelo) && !empty($ano);
        })->take($limit);
    }

    /**
     * Processar artigos
     */
    protected function processArticles(\Illuminate\Support\Collection $articles, bool $dryRun, float $minQualityScore): void
    {
        $progressBar = $this->output->createProgressBar($articles->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');

        foreach ($articles as $article) {
            $vehicleName = $this->getVehicleName($article);
            $progressBar->setMessage("Processando: {$vehicleName}");

            try {
                $result = $this->processArticle($article, $dryRun, $minQualityScore);

                switch ($result['status']) {
                    case 'updated':
                        $this->updatedCount++;
                        if (!empty($result['year_alert'])) {
                            $this->yearAlerts[] = [
                                'vehicle' => $vehicleName,
                                'alert' => $result['year_alert']
                            ];
                        }
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

                Log::error('UpdateTirePressuresFromVehicleDataCommand: Erro ao processar artigo', [
                    'article_id' => $article->_id,
                    'vehicle' => $vehicleName,
                    'error' => $e->getMessage()
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    /**
     * Processar um artigo individual
     */
    protected function processArticle(Article $article, bool $dryRun, float $minQualityScore): array
    {
        $extractedEntities = data_get($article, 'extracted_entities', []);
        $marca = data_get($extractedEntities, 'marca');
        $modelo = data_get($extractedEntities, 'modelo');
        $ano = (int) data_get($extractedEntities, 'ano');

        // Detectar inconsistências de ano
        $anoInconsistencia = $this->detectYearInconsistency($article, $ano);
        if ($anoInconsistencia) {
            return [
                'status' => 'skipped',
                'reason' => "Inconsistência detectada: {$anoInconsistencia}"
            ];
        }

        // Buscar dados do veículo
        $vehicleData = $this->findVehicleData($marca, $modelo, $ano);

        if (!$vehicleData) {
            return [
                'status' => 'skipped',
                'reason' => 'Dados do veículo não encontrados no VehicleData'
            ];
        }

        // Verificar qualidade dos dados
        if ($vehicleData->data_quality_score < $minQualityScore) {
            return [
                'status' => 'skipped',
                'reason' => "Qualidade dos dados insuficiente: {$vehicleData->data_quality_score}"
            ];
        }

        // Verificar se tem especificações de pressão
        $pressureSpecs = $vehicleData->pressure_specifications;
        if (empty($pressureSpecs) || !$this->hasValidPressures($pressureSpecs)) {
            return [
                'status' => 'skipped',
                'reason' => 'Especificações de pressão não disponíveis ou inválidas'
            ];
        }

        // Alerta se usando dados de ano diferente
        $yearDiff = abs($vehicleData->year - $ano);
        $yearAlert = $yearDiff > 0 ? " (usando dados de {$vehicleData->year})" : "";

        if ($dryRun) {
            return [
                'status' => 'updated',
                'reason' => "DRY-RUN: Pressões seriam atualizadas{$yearAlert}"
            ];
        }

        // Criar correção
        $correction = TirePressureCorrection::createForArticle(
            $article,
            TirePressureCorrection::CORRECTION_TYPE_AUTOMATED
        );

        // Extrair pressões do VehicleData
        $correctedPressures = $this->extractPressuresFromVehicleData($pressureSpecs);

        // Log para debug
        Log::info('Pressões extraídas do VehicleData', [
            'vehicle' => "{$marca} {$modelo} {$ano}",
            'original_specs' => $pressureSpecs,
            'corrected_pressures' => $correctedPressures,
            'vehicle_data_id' => $vehicleData->_id
        ]);

        // Verificar se as pressões calculadas são válidas
        if (!$this->validateCalculatedPressures($correctedPressures)) {
            return [
                'status' => 'skipped',
                'reason' => 'Pressões calculadas inválidas: ' . json_encode($correctedPressures)
            ];
        }

        // Aplicar correções no artigo
        $fieldsUpdated = $this->applyCorrections($article, $correctedPressures);

        // Marcar correção como concluída
        $correction->corrected_pressures = $correctedPressures;
        $correction->claude_response = [
            'source' => 'vehicle_data',
            'vehicle_data_id' => $vehicleData->_id,
            'quality_score' => $vehicleData->data_quality_score,
            'method' => 'direct_lookup',
            'year_used' => $vehicleData->year,
            'year_requested' => $ano,
            'year_diff' => $yearDiff
        ];
        $correction->markAsCompleted($correctedPressures, $fieldsUpdated);

        return [
            'status' => 'updated',
            'fields_updated' => count($fieldsUpdated),
            'quality_score' => $vehicleData->data_quality_score,
            'year_alert' => $yearAlert
        ];
    }

    /**
     * Detectar inconsistências de ano
     */
    protected function detectYearInconsistency(Article $article, int $ano): ?string
    {
        $currentYear = now()->year;
        $publishedYear = $article->published_at ? $article->published_at->year : null;
        $scheduledYear = $article->scheduled_at ? $article->scheduled_at->year : null;

        // Se o ano do veículo é futuro comparado ao ano de publicação
        if ($publishedYear && $ano > $publishedYear + 1) {
            return "Ano do veículo ({$ano}) é futuro demais para publicação em {$publishedYear}";
        }

        // Se scheduled_at é 2025 mas vehicle_year é muito antigo
        if ($scheduledYear && $scheduledYear >= 2025 && $ano < 2020) {
            return "Veículo muito antigo ({$ano}) para conteúdo de {$scheduledYear}";
        }

        // Verificar se slug contém ano diferente
        $slugYear = $this->extractYearFromSlug($article->slug);
        if ($slugYear && $slugYear !== $ano) {
            return "Ano na slug ({$slugYear}) difere do extracted_entities ({$ano})";
        }

        return null;
    }

    /**
     * Extrair ano da slug
     */
    protected function extractYearFromSlug(string $slug): ?int
    {
        // Buscar padrão de 4 dígitos que representam ano (1950-2030)
        if (preg_match('/\b(19[5-9]\d|20[0-3]\d)\b/', $slug, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Buscar dados do veículo com fallback inteligente
     */
    protected function findVehicleData(string $marca, string $modelo, int $ano): ?VehicleData
    {
        // 1. Tentar busca exata com ano
        $vehicle = VehicleData::findVehicle($marca, $modelo, $ano);
        if ($vehicle) {
            return $vehicle;
        }

        // 2. Buscar todos os anos disponíveis para análise
        $allYears = VehicleData::findAllYears($marca, $modelo);

        if ($allYears->isEmpty()) {
            // 3. Se não encontrar nada exato, buscar modelos similares
            return $this->findSimilarVehicleData($marca, $modelo, $ano);
        }

        // 4. Priorizar ano mais próximo do artigo
        $bestMatch = null;
        $smallestDiff = PHP_INT_MAX;

        foreach ($allYears as $vehicleOption) {
            $yearDiff = abs($vehicleOption->year - $ano);

            // Priorizar anos próximos (±5 anos para motocicletas que têm menos mudanças)
            if ($yearDiff <= 5 && $yearDiff < $smallestDiff) {
                $bestMatch = $vehicleOption;
                $smallestDiff = $yearDiff;
            }
        }

        // 5. Se encontrou match próximo, usar
        if ($bestMatch) {
            return $bestMatch;
        }

        // 6. Se nenhum ano próximo, verificar se slug contém ano
        $slugHasYear = $this->slugContainsYear($ano);

        if ($slugHasYear) {
            // Se slug tem ano específico, ser mais restritivo
            return null;
        }

        // 7. Se slug não tem ano, usar o melhor disponível (com qualidade alta)
        $bestQuality = $allYears->sortByDesc('data_quality_score')->first();

        if ($bestQuality && $bestQuality->data_quality_score >= 7.0) {
            return $bestQuality;
        }

        return null;
    }

    /**
     * Verificar se a slug contém o ano (evitar usar dados de anos muito diferentes)
     */
    protected function slugContainsYear(int $ano): bool
    {
        $currentSlug = request()->get('slug', '');

        // Se não temos slug no request, assumir que não contém ano
        if (empty($currentSlug)) {
            return false;
        }

        // Verificar se a slug contém o ano
        return str_contains($currentSlug, (string)$ano);
    }

    /**
     * Buscar modelos similares como fallback
     */
    protected function findSimilarVehicleData(string $marca, string $modelo, int $ano): ?VehicleData
    {
        $similarVehicles = VehicleData::findSimilarModels($marca, $modelo, 5);

        if ($similarVehicles->isEmpty()) {
            return null;
        }

        // Priorizar por proximidade de ano e qualidade
        $bestMatch = null;
        $bestScore = 0;

        foreach ($similarVehicles as $similar) {
            $yearDiff = abs($similar->year - $ano);
            $qualityScore = $similar->data_quality_score ?? 0;

            // Score combinado: qualidade alta e ano próximo
            $combinedScore = $qualityScore - ($yearDiff * 0.2);

            if ($combinedScore > $bestScore && $yearDiff <= 7) {
                $bestMatch = $similar;
                $bestScore = $combinedScore;
            }
        }

        return $bestMatch;
    }

    /**
     * Verificar se as pressões são válidas e estão disponíveis
     */
    protected function hasValidPressures(array $pressureSpecs): bool
    {
        // Verificar se tem pelo menos as pressões básicas
        $lightFront = $pressureSpecs['pressure_light_front'] ?? null;
        $lightRear = $pressureSpecs['pressure_light_rear'] ?? null;

        if (empty($lightFront) || empty($lightRear)) {
            return false;
        }

        // Validar faixas de pressão
        if ($lightFront < 10 || $lightFront > 100 || $lightRear < 10 || $lightRear > 100) {
            return false;
        }

        // Verificar se as pressões são coerentes (diferença máxima de 15 PSI)
        $diff = abs($lightFront - $lightRear);
        if ($diff > 15) {
            return false;
        }

        return true;
    }

    /**
     * Extrair pressões do VehicleData para formato do TirePressureCorrection
     */
    protected function extractPressuresFromVehicleData(array $pressureSpecs): array
    {
        return [
            'empty_front' => (int) ($pressureSpecs['pressure_empty_front'] ?? $pressureSpecs['pressure_light_front']),
            'empty_rear' => (int) ($pressureSpecs['pressure_empty_rear'] ?? $pressureSpecs['pressure_light_rear']),
            'loaded_front' => (int) ($pressureSpecs['pressure_max_front'] ?? $pressureSpecs['pressure_light_front']),
            'loaded_rear' => (int) ($pressureSpecs['pressure_max_rear'] ?? $pressureSpecs['pressure_light_rear'])
        ];
    }

    /**
     * Validar pressões extraídas do VehicleData
     */
    protected function validateCalculatedPressures(array $pressures): bool
    {
        // Verificar se todos os valores são numéricos e válidos
        foreach ($pressures as $key => $value) {
            if (!is_numeric($value) || $value < 10 || $value > 100) {
                return false;
            }
        }

        // Verificar se pressão com carga é maior ou igual à vazia
        if ($pressures['loaded_front'] < $pressures['empty_front']) {
            return false;
        }

        if ($pressures['loaded_rear'] < $pressures['empty_rear']) {
            return false;
        }

        return true;
    }

    /**
     * Calcular pressão com carga baseada na pressão normal
     */
    protected function calculateLoadedPressure(int $normalPressure): int
    {
        // Para carros: geralmente +2 a +4 PSI com carga
        // Para motos: geralmente +3 a +6 PSI com carga

        if ($normalPressure <= 30) {
            // Pressões baixas (geralmente motos): +3 PSI
            return $normalPressure + 3;
        } elseif ($normalPressure <= 35) {
            // Pressões médias (carros pequenos/médios): +2 PSI
            return $normalPressure + 2;
        } else {
            // Pressões altas (SUVs/pickups): +4 PSI
            return $normalPressure + 4;
        }
    }

    /**
     * Aplicar correções no artigo (reutilizar do TirePressureCorrectionService)
     */
    protected function applyCorrections(Article $article, array $pressures): array
    {
        $fieldsUpdated = [];
        $content = $article->content;

        // 1. Atualizar campos diretos do artigo
        $article->pressure_empty_front = (string)$pressures['empty_front'];
        $article->pressure_empty_rear = (string)$pressures['empty_rear'];
        $article->pressure_light_front = (string)$pressures['loaded_front'];
        $article->pressure_light_rear = (string)$pressures['loaded_rear'];
        $fieldsUpdated[] = 'pressure_fields';

        // 2. Atualizar pressoes_recomendadas no procedimento_verificacao
        if (isset($content['procedimento_verificacao']['verificacao_pressao']['pressoes_recomendadas'])) {
            $content['procedimento_verificacao']['verificacao_pressao']['pressoes_recomendadas']['vazio'] =
                "{$pressures['empty_front']} PSI (dianteiro) / {$pressures['empty_rear']} PSI (traseiro)";

            $content['procedimento_verificacao']['verificacao_pressao']['pressoes_recomendadas']['com_carga'] =
                "{$pressures['loaded_front']} PSI (dianteiro) / {$pressures['loaded_rear']} PSI (traseiro)";

            $fieldsUpdated[] = 'procedimento_verificacao.pressoes_recomendadas';
        }

        // 3. Atualizar vehicle_data
        if (isset($content['vehicle_data'])) {
            $content['vehicle_data']['pressures'] = [
                'empty_front' => $pressures['empty_front'],
                'empty_rear' => $pressures['empty_rear'],
                'loaded_front' => $pressures['loaded_front'],
                'loaded_rear' => $pressures['loaded_rear']
            ];

            $content['vehicle_data']['pressure_display'] =
                "{$pressures['empty_front']}/{$pressures['empty_rear']} PSI";

            $content['vehicle_data']['pressure_loaded_display'] =
                "{$pressures['loaded_front']}/{$pressures['loaded_rear']} PSI";

            $fieldsUpdated[] = 'vehicle_data.pressures';
        }

        // 4. Aplicar substituições via regex em campos de texto
        $oldPressurePattern = $this->getOldPressurePattern($article);
        $newPressureText = "{$pressures['empty_front']}/{$pressures['empty_rear']} PSI";

        // Campos para substituição via regex
        $textFields = [
            'fatores_durabilidade.calibragem_inadequada.pressao_recomendada',
            'fatores_durabilidade.calibragem_inadequada.descricao',
            'consideracoes_finais'
        ];

        foreach ($textFields as $field) {
            $value = data_get($content, $field);
            if ($value && $oldPressurePattern) {
                $newValue = preg_replace($oldPressurePattern, $newPressureText, $value);
                if ($newValue !== $value) {
                    data_set($content, $field, $newValue);
                    $fieldsUpdated[] = $field;
                }
            }
        }

        // 5. Atualizar perguntas frequentes
        if (isset($content['perguntas_frequentes']) && is_array($content['perguntas_frequentes'])) {
            foreach ($content['perguntas_frequentes'] as $index => $faq) {
                if (isset($faq['resposta']) && $oldPressurePattern) {
                    $newResposta = preg_replace($oldPressurePattern, $newPressureText, $faq['resposta']);
                    if ($newResposta !== $faq['resposta']) {
                        $content['perguntas_frequentes'][$index]['resposta'] = $newResposta;
                        $fieldsUpdated[] = "perguntas_frequentes.{$index}.resposta";
                    }
                }
            }
        }

        // 6. Atualizar SEO meta description
        $seoData = $article->seo_data;
        if (isset($seoData['meta_description']) && $oldPressurePattern) {
            $newMetaDescription = preg_replace($oldPressurePattern, $newPressureText, $seoData['meta_description']);
            if ($newMetaDescription !== $seoData['meta_description']) {
                $seoData['meta_description'] = $newMetaDescription;
                $article->seo_data = $seoData;
                $fieldsUpdated[] = 'seo_data.meta_description';
            }
        }

        // Salvar alterações
        $article->content = $content;
        $article->save();

        return array_unique($fieldsUpdated);
    }

    /**
     * Obter padrão regex para pressões antigas
     */
    protected function getOldPressurePattern(Article $article): ?string
    {
        $oldFront = $article->pressure_empty_front;
        $oldRear = $article->pressure_empty_rear;

        if ($oldFront && $oldRear) {
            return '/\b' . preg_quote($oldFront) . '\s*\/\s*' . preg_quote($oldRear) . '\s*PSI\b/i';
        }

        return '/\b\d{1,3}\s*\/\s*\d{1,3}\s*PSI\b/i';
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
        $this->info('=== RESULTADO DA ATUALIZAÇÃO ===');
        $this->newLine();

        $this->line("📄 Artigos processados: <fg=cyan>{$this->processedCount}</>");
        $this->line("✅ Artigos atualizados: <fg=green>{$this->updatedCount}</>");
        $this->line("⏭️  Artigos ignorados: <fg=yellow>{$this->skippedCount}</>");
        $this->line("❌ Erros: <fg=red>{$this->errorCount}</>");

        if (!empty($this->yearAlerts)) {
            $this->newLine();
            $this->warn('⚠️  Alertas de ano:');
            foreach (array_slice($this->yearAlerts, 0, 10) as $alert) {
                $this->line("  - {$alert['vehicle']}: {$alert['alert']}");
            }

            if (count($this->yearAlerts) > 10) {
                $this->line("  ... e mais " . (count($this->yearAlerts) - 10) . " alertas");
            }
        }

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

        // Estatísticas finais
        $stats = TirePressureCorrection::getStats();
        $this->info('📊 Status geral das correções:');
        $this->line("  Pendentes: {$stats['pending']}");
        $this->line("  Concluídas: {$stats['completed']}");
        $this->line("  Sem alterações: {$stats['no_changes']}");
        $this->line("  Falhas: {$stats['failed']}");

        Log::info('UpdateTirePressuresFromVehicleDataCommand: Execução concluída', [
            'processed' => $this->processedCount,
            'updated' => $this->updatedCount,
            'skipped' => $this->skippedCount,
            'errors' => $this->errorCount
        ]);
    }
}
