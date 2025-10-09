<?php

namespace Src\ContentGeneration\TireSchedule\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;

class FixPlaceholdersCommand extends Command
{
    /**
     * Nome e assinatura do comando.
     */
    protected $signature = 'fix-placeholders 
                           {--slug= : Corrigir apenas um artigo específico}
                           {--limit=100 : Limite de artigos para processar}
                           {--dry-run : Apenas mostrar o que seria corrigido}
                           {--force : Força execução}';

    /**
     * Descrição do comando.
     */
    protected $description = 'Correção emergencial de placeholders N/A N/A N/A sem usar API';

    /**
     * Execute o comando.
     */
    public function handle()
    {
        $this->info('🚨 Correção Emergencial de Placeholders N/A N/A N/A');
        $this->line('');

        if ($this->option('slug')) {
            return $this->fixSingleSlug();
        }

        return $this->fixMultipleArticles();
    }

    /**
     * 🎯 Corrigir slug específico
     */
    protected function fixSingleSlug()
    {
        $slug = $this->option('slug');
        
        $article = TempArticle::where('slug', $slug)
            ->where('domain', 'when_to_change_tires')
            ->first();

        if (!$article) {
            $this->error("❌ Artigo não encontrado: {$slug}");
            return Command::FAILURE;
        }

        $this->info("🎯 Analisando: {$slug}");
        
        $analysis = $this->analyzeArticle($article);
        
        if ($analysis['has_placeholders']) {
            $this->displayPlaceholderIssues($analysis);
            
            if (!$this->option('dry-run')) {
                if ($this->option('force') || $this->confirm('Aplicar correções?')) {
                    $success = $this->fixPlaceholders($article);
                    
                    if ($success) {
                        $this->info('✅ Placeholders corrigidos com sucesso!');
                    } else {
                        $this->error('❌ Falha ao corrigir placeholders.');
                    }
                }
            } else {
                $this->info('🔍 [DRY RUN] Correções que seriam aplicadas mostradas acima.');
            }
        } else {
            $this->info('✅ Nenhum placeholder N/A encontrado!');
        }

        return Command::SUCCESS;
    }

    /**
     * 📊 Corrigir múltiplos artigos
     */
    protected function fixMultipleArticles()
    {
        $limit = (int) $this->option('limit');
        
        $this->info("📊 Buscando artigos com placeholders N/A (limite: {$limit})...");
        
        // Buscar artigos que provavelmente têm placeholders
        $articles = TempArticle::where('domain', 'when_to_change_tires')
            ->where('status', 'draft')
            ->where(function($query) {
                $query->where('seo_data.page_title', 'like', '%N/A N/A N/A%')
                      ->orWhere('seo_data.meta_description', 'like', '%N/A N/A N/A%')
                      ->orWhere('content', 'like', '%N/A N/A N/A%');
            })
            ->limit($limit)
            ->get();

        if ($articles->isEmpty()) {
            $this->info('✅ Nenhum artigo com placeholders N/A encontrado!');
            return Command::SUCCESS;
        }

        $this->info("📋 Encontrados {$articles->count()} artigos com possíveis placeholders.");
        $this->line('');

        $results = [
            'analyzed' => 0,
            'has_placeholders' => 0,
            'fixed' => 0,
            'errors' => 0
        ];

        $progressBar = $this->output->createProgressBar($articles->count());
        $progressBar->start();

        foreach ($articles as $article) {
            $results['analyzed']++;
            
            $analysis = $this->analyzeArticle($article);
            
            if ($analysis['has_placeholders']) {
                $results['has_placeholders']++;
                
                if (!$this->option('dry-run')) {
                    $success = $this->fixPlaceholders($article);
                    
                    if ($success) {
                        $results['fixed']++;
                    } else {
                        $results['errors']++;
                    }
                }
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Exibir resultados
        $this->table(['Métrica', 'Valor'], [
            ['📊 Analisados', $results['analyzed']],
            ['⚠️ Com placeholders', $results['has_placeholders']],
            ['✅ Corrigidos', $results['fixed']],
            ['❌ Erros', $results['errors']]
        ]);

        return Command::SUCCESS;
    }

    /**
     * 🔍 Analisar artigo em busca de placeholders
     */
    private function analyzeArticle(TempArticle $article): array
    {
        $seoData = $article->seo_data ?? [];
        $content = $article->content ?? [];
        $vehicleData = $article->vehicle_data ?? [];
        
        $issues = [];
        $hasPlaceholders = false;

        // Verificar page_title
        $pageTitle = $seoData['page_title'] ?? '';
        if (strpos($pageTitle, 'N/A N/A N/A') !== false) {
            $issues[] = "page_title: {$pageTitle}";
            $hasPlaceholders = true;
        }

        // Verificar meta_description
        $metaDescription = $seoData['meta_description'] ?? '';
        if (strpos($metaDescription, 'N/A N/A N/A') !== false) {
            $issues[] = "meta_description: " . substr($metaDescription, 0, 80) . '...';
            $hasPlaceholders = true;
        }

        // Verificar FAQs
        $faqs = $content['perguntas_frequentes'] ?? [];
        if (is_array($faqs)) {
            foreach ($faqs as $index => $faq) {
                $pergunta = $faq['pergunta'] ?? '';
                $resposta = $faq['resposta'] ?? '';
                
                if (strpos($pergunta, 'N/A N/A N/A') !== false) {
                    $issues[] = "FAQ {$index} pergunta: " . substr($pergunta, 0, 60) . '...';
                    $hasPlaceholders = true;
                }
                
                if (strpos($resposta, 'N/A N/A N/A') !== false) {
                    $issues[] = "FAQ {$index} resposta: " . substr($resposta, 0, 60) . '...';
                    $hasPlaceholders = true;
                }
            }
        }

        return [
            'has_placeholders' => $hasPlaceholders,
            'issues' => $issues,
            'vehicle_data' => $vehicleData
        ];
    }

    /**
     * 📋 Exibir problemas encontrados
     */
    private function displayPlaceholderIssues(array $analysis)
    {
        $this->warn('⚠️ Placeholders N/A N/A N/A encontrados:');
        foreach ($analysis['issues'] as $issue) {
            $this->line("  • {$issue}");
        }
        
        $vehicleData = $analysis['vehicle_data'];
        $vehicleName = $vehicleData['vehicle_name'] ?? 'N/A';
        $vehicleBrand = $vehicleData['vehicle_brand'] ?? 'N/A';
        $vehicleModel = $vehicleData['vehicle_model'] ?? 'N/A';
        $vehicleYear = $vehicleData['vehicle_year'] ?? 'N/A';
        
        $this->line('');
        $this->info("📝 Dados do veículo disponíveis:");
        $this->line("  • Nome: {$vehicleName}");
        $this->line("  • Marca: {$vehicleBrand}");
        $this->line("  • Modelo: {$vehicleModel}");  
        $this->line("  • Ano: {$vehicleYear}");
    }

    /**
     * 🔧 Aplicar correções de placeholders
     */
    private function fixPlaceholders(TempArticle $article): bool
    {
        try {
            $vehicleData = $article->vehicle_data ?? [];
            $content = $article->content ?? [];
            $seoData = $article->seo_data ?? [];
            
            $vehicleName = $vehicleData['vehicle_name'] ?? 'N/A';
            $vehicleBrand = $vehicleData['vehicle_brand'] ?? 'N/A';
            $vehicleModel = $vehicleData['vehicle_model'] ?? 'N/A';
            $vehicleYear = $vehicleData['vehicle_year'] ?? date('Y');
            
            // Se não temos dados do veículo, não podemos corrigir
            if ($vehicleName === 'N/A' || $vehicleBrand === 'N/A' || $vehicleModel === 'N/A') {
                Log::warning("Dados de veículo insuficientes para {$article->slug}");
                return false;
            }
            
            $fullVehicleName = "{$vehicleBrand} {$vehicleModel} {$vehicleYear}";
            $updated = false;

            // Corrigir page_title
            if (isset($seoData['page_title']) && strpos($seoData['page_title'], 'N/A N/A N/A') !== false) {
                $seoData['page_title'] = str_replace('N/A N/A N/A', $fullVehicleName, $seoData['page_title']);
                $updated = true;
            }

            // Corrigir meta_description
            if (isset($seoData['meta_description']) && strpos($seoData['meta_description'], 'N/A N/A N/A') !== false) {
                $seoData['meta_description'] = str_replace('N/A N/A N/A', $fullVehicleName, $seoData['meta_description']);
                $updated = true;
            }

            // Corrigir FAQs
            if (isset($content['perguntas_frequentes']) && is_array($content['perguntas_frequentes'])) {
                foreach ($content['perguntas_frequentes'] as $index => $faq) {
                    if (isset($faq['pergunta']) && strpos($faq['pergunta'], 'N/A N/A N/A') !== false) {
                        $content['perguntas_frequentes'][$index]['pergunta'] = str_replace('N/A N/A N/A', $fullVehicleName, $faq['pergunta']);
                        $updated = true;
                    }
                    
                    if (isset($faq['resposta']) && strpos($faq['resposta'], 'N/A N/A N/A') !== false) {
                        $content['perguntas_frequentes'][$index]['resposta'] = str_replace('N/A N/A N/A', $fullVehicleName, $faq['resposta']);
                        $updated = true;
                    }
                }
            }

            // Aplicar correções
            if ($updated) {
                $article->update([
                    'content' => $content,
                    'seo_data' => $seoData,
                    'updated_at' => now()
                ]);

                Log::info("✅ Placeholders corrigidos para {$article->slug}");
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error("❌ Erro ao corrigir placeholders para {$article->slug}: " . $e->getMessage());
            return false;
        }
    }
}