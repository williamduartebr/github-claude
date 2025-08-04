<?php

namespace Src\TirePressureCorrection\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\TirePressureCorrection\Domain\Entities\TirePressureCorrection;

/**
 * Command para aplicar correções de pressão coletadas anteriormente
 */
class ApplyTirePressuresCommand extends Command
{
    protected $signature = 'articles:apply-tire-pressures 
                           {--limit=100 : Número máximo de correções para aplicar}
                           {--dry-run : Simular aplicação sem salvar}
                           {--status=pending : Status das correções a aplicar}';

    protected $description = 'Aplicar correções de pressão de pneus previamente coletadas';

    public function handle(): int
    {
        $this->info('=== APLICAÇÃO DE CORREÇÕES DE PRESSÃO ===');
        $this->newLine();

        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');
        $status = $this->option('status');

        if ($dryRun) {
            $this->warn('🔍 MODO DRY-RUN: Nenhuma alteração será salva');
            $this->newLine();
        }

        // Buscar correções pendentes
        $corrections = $this->getCorrectionsToApply($status, $limit);

        if ($corrections->isEmpty()) {
            $this->info('✅ Nenhuma correção pendente encontrada');
            return Command::SUCCESS;
        }

        $this->info("📊 Correções encontradas: {$corrections->count()}");
        $this->newLine();

        // Mostrar prévia
        $this->showPreview($corrections);

        // Confirmar aplicação
        if (!$dryRun && !$this->confirm('Deseja aplicar as correções?')) {
            $this->info('Operação cancelada');
            return Command::SUCCESS;
        }

        // Aplicar correções
        $results = $this->applyCorrections($corrections, $dryRun);

        // Exibir resultados
        $this->showResults($results);

        return Command::SUCCESS;
    }

    /**
     * Buscar correções para aplicar
     */
    protected function getCorrectionsToApply(string $status, int $limit): \Illuminate\Support\Collection
    {
        return TirePressureCorrection::where('status', $status)
            ->whereNotNull('corrected_pressures')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Mostrar prévia das correções
     */
    protected function showPreview(\Illuminate\Support\Collection $corrections): void
    {
        $this->info('📋 Prévia das correções a serem aplicadas:');
        $this->newLine();

        $headers = ['#', 'Veículo', 'Artigo', 'Pressões Atuais', 'Novas Pressões'];
        $rows = [];

        foreach ($corrections->take(10) as $index => $correction) {
            $currentPressures = sprintf(
                '%s/%s → %s/%s',
                $correction->original_pressures['empty_front'] ?? '?',
                $correction->original_pressures['empty_rear'] ?? '?',
                $correction->original_pressures['light_front'] ?? '?',
                $correction->original_pressures['light_rear'] ?? '?'
            );

            $newPressures = sprintf(
                '%s/%s → %s/%s',
                $correction->corrected_pressures['empty_front'] ?? '?',
                $correction->corrected_pressures['empty_rear'] ?? '?',
                $correction->corrected_pressures['loaded_front'] ?? '?',
                $correction->corrected_pressures['loaded_rear'] ?? '?'
            );

            $rows[] = [
                $index + 1,
                $correction->vehicle_name,
                \Str::limit($correction->article_slug, 30),
                $currentPressures,
                $newPressures
            ];
        }

        $this->table($headers, $rows);

        if ($corrections->count() > 10) {
            $this->info("... e mais " . ($corrections->count() - 10) . " correções");
        }

        $this->newLine();
    }

    /**
     * Aplicar correções
     */
    protected function applyCorrections(\Illuminate\Support\Collection $corrections, bool $dryRun): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'fields_updated' => 0,
            'errors' => []
        ];

        $progressBar = $this->output->createProgressBar($corrections->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');

        foreach ($corrections as $correction) {
            $progressBar->setMessage("Aplicando: {$correction->article_slug}");

            try {
                // Marcar como processando
                if (!$dryRun) {
                    $correction->markAsProcessing();
                }

                // Buscar artigo
                $article = Article::find($correction->article_id);

                if (!$article) {
                    throw new \Exception('Artigo não encontrado');
                }

                if ($dryRun) {
                    // Simular aplicação
                    $this->line("\n  [DRY-RUN] {$article->slug}");
                    $results['success']++;
                } else {
                    // Aplicar correção real
                    $fieldsUpdated = $this->applyCorrection($article, $correction);

                    // Marcar como concluída
                    $correction->markAsCompleted($correction->corrected_pressures, $fieldsUpdated);

                    $results['success']++;
                    $results['fields_updated'] += count($fieldsUpdated);
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'correction_id' => $correction->_id,
                    'article' => $correction->article_slug,
                    'error' => $e->getMessage()
                ];

                if (!$dryRun) {
                    $correction->markAsFailed($e->getMessage());
                }

                Log::error('ApplyTirePressuresCommand: Erro ao aplicar correção', [
                    'correction_id' => $correction->_id,
                    'error' => $e->getMessage()
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        return $results;
    }

    /**
     * Aplicar correção em um artigo
     */
    protected function applyCorrection(Article $article, TirePressureCorrection $correction): array
    {
        $fieldsUpdated = [];
        $pressures = $correction->corrected_pressures;
        $content = $article->content;

        // 1. Atualizar campos diretos
        $article->pressure_empty_front = (string)$pressures['empty_front'];
        $article->pressure_empty_rear = (string)$pressures['empty_rear'];
        $article->pressure_light_front = (string)$pressures['loaded_front'];
        $article->pressure_light_rear = (string)$pressures['loaded_rear'];
        $fieldsUpdated[] = 'pressure_fields';

        // 2. Atualizar pressoes_recomendadas
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

            $fieldsUpdated[] = 'vehicle_data';
        }

        // 4. Preparar padrões de substituição
        $replacements = $this->prepareReplacements($article, $pressures);

        // 5. Aplicar substituições em campos de texto
        $textFieldsUpdated = $this->applyTextReplacements($content, $replacements);
        $fieldsUpdated = array_merge($fieldsUpdated, $textFieldsUpdated);

        // 6. Atualizar SEO
        $seoFieldsUpdated = $this->updateSeoData($article, $replacements);
        $fieldsUpdated = array_merge($fieldsUpdated, $seoFieldsUpdated);

        // Salvar alterações
        $article->content = $content;
        $article->save();

        return array_unique($fieldsUpdated);
    }

    /**
     * Preparar padrões de substituição
     */
    protected function prepareReplacements(Article $article, array $newPressures): array
    {
        $replacements = [];

        // Padrão para pressões vazias (ex: "29/36 PSI" → "25/33 PSI")
        $oldEmpty = $article->pressure_empty_front && $article->pressure_empty_rear
            ? "{$article->pressure_empty_front}/{$article->pressure_empty_rear}"
            : null;

        $newEmpty = "{$newPressures['empty_front']}/{$newPressures['empty_rear']}";

        if ($oldEmpty) {
            // Escapar caracteres especiais para regex
            $escapedOld = preg_quote($oldEmpty, '/');

            // Variações do padrão
            $replacements[] = [
                'pattern' => '/\b' . $escapedOld . '\s*PSI\b/i',
                'replacement' => $newEmpty . ' PSI'
            ];
        }

        // Padrão genérico para qualquer pressão no formato XX/YY PSI
        // IMPORTANTE: Usar com cuidado para não substituir pressões erradas
        $replacements[] = [
            'pattern' => '/\b\d{1,3}\s*\/\s*\d{1,3}\s*PSI\b/i',
            'replacement' => $newEmpty . ' PSI',
            'context_check' => true // Aplicar com cuidado
        ];

        return $replacements;
    }

    /**
     * Aplicar substituições em campos de texto
     */
    protected function applyTextReplacements(array &$content, array $replacements): array
    {
        $fieldsUpdated = [];

        // Campos para verificar
        $textFields = [
            'fatores_durabilidade.calibragem_inadequada.pressao_recomendada',
            'fatores_durabilidade.calibragem_inadequada.descricao',
            'consideracoes_finais'
        ];

        foreach ($textFields as $field) {
            $value = data_get($content, $field);
            if ($value && is_string($value)) {
                $newValue = $value;

                foreach ($replacements as $replacement) {
                    if (isset($replacement['context_check']) && $replacement['context_check']) {
                        // Aplicar apenas se o contexto menciona pressão
                        if (stripos($value, 'press') !== false) {
                            $newValue = preg_replace($replacement['pattern'], $replacement['replacement'], $newValue);
                        }
                    } else {
                        $newValue = preg_replace($replacement['pattern'], $replacement['replacement'], $newValue);
                    }
                }

                if ($newValue !== $value) {
                    data_set($content, $field, $newValue);
                    $fieldsUpdated[] = $field;
                }
            }
        }

        // Perguntas frequentes
        if (isset($content['perguntas_frequentes']) && is_array($content['perguntas_frequentes'])) {
            foreach ($content['perguntas_frequentes'] as $index => $faq) {
                if (isset($faq['resposta'])) {
                    $newResposta = $faq['resposta'];

                    foreach ($replacements as $replacement) {
                        $newResposta = preg_replace($replacement['pattern'], $replacement['replacement'], $newResposta);
                    }

                    if ($newResposta !== $faq['resposta']) {
                        $content['perguntas_frequentes'][$index]['resposta'] = $newResposta;
                        $fieldsUpdated[] = "perguntas_frequentes.{$index}.resposta";
                    }
                }
            }
        }

        return $fieldsUpdated;
    }

    /**
     * Atualizar dados SEO
     */
    protected function updateSeoData(Article $article, array $replacements): array
    {
        $fieldsUpdated = [];
        $seoData = $article->seo_data;

        if (!is_array($seoData)) {
            return $fieldsUpdated;
        }

        // Atualizar meta description
        if (isset($seoData['meta_description'])) {
            $newMetaDescription = $seoData['meta_description'];

            foreach ($replacements as $replacement) {
                $newMetaDescription = preg_replace($replacement['pattern'], $replacement['replacement'], $newMetaDescription);
            }

            if ($newMetaDescription !== $seoData['meta_description']) {
                $seoData['meta_description'] = $newMetaDescription;
                $article->seo_data = $seoData;
                $fieldsUpdated[] = 'seo_data.meta_description';
            }
        }

        return $fieldsUpdated;
    }

    /**
     * Exibir resultados
     */
    protected function showResults(array $results): void
    {
        $this->info('=== RESULTADO DA APLICAÇÃO ===');
        $this->newLine();

        $this->line("✅ Correções aplicadas: <fg=green>{$results['success']}</>");
        $this->line("📝 Campos atualizados: <fg=cyan>{$results['fields_updated']}</>");
        $this->line("❌ Falhas: <fg=red>{$results['failed']}</>");

        if (!empty($results['errors'])) {
            $this->newLine();
            $this->error('Erros encontrados:');
            foreach ($results['errors'] as $error) {
                $this->line("  - {$error['article']}: {$error['error']}");
            }
        }

        $this->newLine();

        // Estatísticas gerais
        $stats = TirePressureCorrection::getStats();
        $this->info('📊 Status das correções:');
        $this->line("  Pendentes: {$stats['pending']}");
        $this->line("  Concluídas: {$stats['completed']}");
        $this->line("  Falhas: {$stats['failed']}");
        // $this->line("  Taxa de sucesso: {$stats['success_rate']}%");
    }
}
