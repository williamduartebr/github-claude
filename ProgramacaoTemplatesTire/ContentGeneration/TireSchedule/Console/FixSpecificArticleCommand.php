<?php

namespace Src\ContentGeneration\TireSchedule\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Src\ContentGeneration\TireSchedule\Infrastructure\Eloquent\TireArticleCorrection as ArticleCorrection;

class FixSpecificArticleCommand extends Command
{
    /**
     * Nome e assinatura do comando.
     */
    protected $signature = 'fix-specific-article 
                           {slug : Slug do artigo para corrigir}
                           {--force : Força execução}
                           {--create-correction : Cria correção mesmo se já existir}';

    /**
     * Descrição do comando.
     */
    protected $description = 'Corrige artigo específico: placeholders, pressões e cria correções pendentes';

    /**
     * Execute o comando.
     */
    public function handle()
    {
        $slug = $this->argument('slug');
        
        $this->info("🎯 Corrigindo artigo específico: {$slug}");
        $this->line('');

        // Buscar artigo
        $article = TempArticle::where('slug', $slug)
            ->where('domain', 'when_to_change_tires')
            ->first();

        if (!$article) {
            $this->error("❌ Artigo não encontrado: {$slug}");
            return Command::FAILURE;
        }

        $this->info("✅ Artigo encontrado: {$article->title}");
        
        // Passo 1: Corrigir placeholders e pressões diretamente
        $fixResult = $this->fixArticleDirectly($article);
        
        if ($fixResult) {
            $this->info("✅ Correções diretas aplicadas com sucesso!");
        }

        // Passo 2: Criar correção de pressão se necessário
        $correctionResult = $this->createPressureCorrection($article);
        
        if ($correctionResult) {
            $this->info("✅ Correção de pressão criada!");
        }

        // Passo 3: Processar correção imediatamente
        $processResult = $this->processImmediately($article);
        
        if ($processResult) {
            $this->info("✅ Correção processada via Claude API!");
        }

        $this->line('');
        $this->info("🎉 Processo concluído para: {$slug}");
        
        return Command::SUCCESS;
    }

    /**
     * 🔧 Corrigir artigo diretamente
     */
    private function fixArticleDirectly(TempArticle $article): bool
    {
        try {
            $updated = false;
            $content = $article->content ?? [];
            $vehicleData = $article->vehicle_data ?? [];
            
            // Corrigir {year} na introdução
            if (isset($content['introducao']) && strpos($content['introducao'], '{year}') !== false) {
                $year = $vehicleData['vehicle_year'] ?? date('Y');
                $content['introducao'] = str_replace('{year}', $year, $content['introducao']);
                $updated = true;
                $this->info("  • {year} substituído por {$year}");
            }

            // Corrigir pressões carregadas zeradas (problema crítico)
            $pressures = $vehicleData['pressures'] ?? [];
            
            if (($pressures['loaded_front'] ?? 0) == 0 || ($pressures['loaded_rear'] ?? 0) == 0) {
                $emptyFront = $pressures['empty_front'] ?? 29;
                $emptyRear = $pressures['empty_rear'] ?? 32;
                $isMotorcycle = $vehicleData['is_motorcycle'] ?? false;
                
                // Para motos: +2-3 PSI do vazio
                $loadedFront = $emptyFront + ($isMotorcycle ? 2 : 3);
                $loadedRear = $emptyRear + ($isMotorcycle ? 2 : 3);
                
                $vehicleData['pressures']['loaded_front'] = $loadedFront;
                $vehicleData['pressures']['loaded_rear'] = $loadedRear;
                $vehicleData['pressure_loaded_display'] = "{$loadedFront}/{$loadedRear} PSI";
                
                $updated = true;
                $this->info("  • Pressões carregadas corrigidas: {$loadedFront}/{$loadedRear} PSI");
            }

            // Salvar se houve mudanças
            if ($updated) {
                $article->update([
                    'content' => $content,
                    'vehicle_data' => $vehicleData,
                    'updated_at' => now()
                ]);
                
                Log::info("✅ Artigo corrigido diretamente: {$article->slug}");
                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->error("❌ Erro ao corrigir artigo: " . $e->getMessage());
            Log::error("❌ Erro ao corrigir artigo {$article->slug}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 🔧 Criar correção de pressão
     */
    private function createPressureCorrection(TempArticle $article): bool
    {
        try {
            // Verificar se já existe
            $existing = ArticleCorrection::where('article_slug', $article->slug)
                ->where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->exists();

            if ($existing && !$this->option('create-correction')) {
                $this->warn("  • Correção de pressão já existe (use --create-correction para forçar)");
                return false;
            }

            // Preparar dados originais
            $originalData = [
                'title' => $article->title,
                'domain' => $article->domain,
                'vehicle_data' => $article->vehicle_data ?? [],
                'current_content' => [
                    'introducao' => $article->content['introducao'] ?? '',
                    'consideracoes_finais' => $article->content['consideracoes_finais'] ?? ''
                ],
                'current_pressures' => $article->vehicle_data['pressures'] ?? [],
                'priority' => 'high',
                'force_created' => true,
                'created_via' => 'fix-specific-article-command'
            ];

            // Criar correção
            $correction = ArticleCorrection::createCorrection(
                $article->slug,
                ArticleCorrection::TYPE_TIRE_PRESSURE_FIX,
                $originalData,
                'Correção forçada via comando específico'
            );

            if ($correction) {
                $this->info("  • Correção criada: {$correction->_id}");
                Log::info("✅ Correção de pressão criada para: {$article->slug}");
                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->error("❌ Erro ao criar correção: " . $e->getMessage());
            Log::error("❌ Erro ao criar correção para {$article->slug}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 🚀 Processar correção imediatamente
     */
    private function processImmediately(TempArticle $article): bool
    {
        try {
            // Buscar correção pendente
            $correction = ArticleCorrection::where('article_slug', $article->slug)
                ->where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_PENDING)
                ->first();

            if (!$correction) {
                $this->warn("  • Nenhuma correção pendente encontrada");
                return false;
            }

            // Usar o service legado para processar
            $tireCorrectionService = app(\Src\ContentGeneration\TireSchedule\Infrastructure\Services\TireCorrectionService::class);
            
            $this->info("  • Processando via Claude API...");
            $success = $tireCorrectionService->processTireCorrection($correction);

            if ($success) {
                $this->info("  • ✅ Processamento concluído com sucesso!");
                Log::info("✅ Correção processada com sucesso: {$article->slug}");
                return true;
            } else {
                $this->warn("  • ⚠️ Processamento falhou, mas correção direta já foi aplicada");
                return false;
            }
        } catch (\Exception $e) {
            $this->error("❌ Erro no processamento: " . $e->getMessage());
            Log::error("❌ Erro ao processar correção para {$article->slug}: " . $e->getMessage());
            return false;
        }
    }
}