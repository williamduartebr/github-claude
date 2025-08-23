<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;
use Illuminate\Support\Facades\Log;

/**
 * ✅ ValidateClonedArticlesCommand - VALIDAR ARTIGOS CLONADOS
 * 
 * FUNCIONALIDADES:
 * - Validar integridade dos artigos clonados
 * - Verificar se template_used foi alterado corretamente
 * - Validar slugs e títulos novos
 * - Detectar duplicações
 * - Relatório de qualidade
 */
class ValidateClonedArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tire-pressure-guide:validate-cloned-articles 
                           {--detailed : Show detailed validation report}
                           {--fix-issues : Automatically fix detected issues}
                           {--export-report= : Export report to file}';

    /**
     * The console command description.
     */
    protected $description = 'Validate cloned calibration articles for data integrity and correctness';

    /**
     * Estatísticas da validação
     */
    private array $stats = [
        'total_cloned' => 0,
        'valid_articles' => 0,
        'issues_found' => 0,
        'issues_fixed' => 0,
        'critical_errors' => 0,
        'warnings' => 0,
    ];

    /**
     * Issues encontradas
     */
    private array $issues = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🔍 INICIANDO VALIDAÇÃO DE ARTIGOS CLONADOS");
        $this->info(str_repeat('=', 60));

        try {
            // 1. Buscar artigos clonados
            $clonedArticles = $this->getClonedArticles();
            
            if ($clonedArticles->isEmpty()) {
                $this->warn("❌ Nenhum artigo clonado encontrado.");
                $this->displayNoClonedArticlesGuidance();
                return 0;
            }

            $this->stats['total_cloned'] = $clonedArticles->count();
            $this->info("📊 Total de artigos clonados encontrados: {$this->stats['total_cloned']}");

            // 2. Executar validações
            $this->runValidations($clonedArticles);

            // 3. Corrigir issues se solicitado
            if ($this->option('fix-issues') && !empty($this->issues)) {
                $this->fixIssues();
            }

            // 4. Gerar relatório
            $this->displayValidationReport();

            // 5. Exportar relatório se solicitado
            if ($exportPath = $this->option('export-report')) {
                $this->exportReport($exportPath);
            }

            return $this->stats['critical_errors'] > 0 ? 1 : 0;

        } catch (\Exception $e) {
            $this->error("❌ Erro durante validação: " . $e->getMessage());
            Log::error("ValidateClonedArticlesCommand failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Buscar artigos clonados
     */
    private function getClonedArticles(): \Illuminate\Database\Eloquent\Collection
    {
        return TirePressureArticle::where('cloned_from_calibration', true)
                                  ->orderBy('make')
                                  ->orderBy('model')
                                  ->orderBy('year')
                                  ->get();
    }

    /**
     * Executar todas as validações
     */
    private function runValidations(\Illuminate\Database\Eloquent\Collection $articles): void
    {
        $this->info("\n🔧 Executando validações...");

        $validations = [
            'validateTemplateUsed' => 'Template Used',
            'validateTitles' => 'Títulos',
            'validateSlugs' => 'Slugs',
            'validateWordPressUrls' => 'WordPress URLs',
            'validateMetaDescriptions' => 'Meta Descriptions',
            'validateOriginalReferences' => 'Referências Originais',
            'validateDuplicates' => 'Duplicações',
            'validateDataIntegrity' => 'Integridade dos Dados'
        ];

        foreach ($validations as $method => $description) {
            $this->line("   🔍 Validando {$description}...");
            $this->$method($articles);
        }

        $this->stats['valid_articles'] = $this->stats['total_cloned'] - $this->stats['issues_found'];
    }

    /**
     * Validar template_used
     */
    private function validateTemplateUsed(\Illuminate\Database\Eloquent\Collection $articles): void
    {
        foreach ($articles as $article) {
            $templateUsed = $article->template_used;
            
            // Verificar se template foi alterado corretamente
            $expectedTemplates = ['tire_calibration_car', 'tire_calibration_motorcycle'];
            
            if (!in_array($templateUsed, $expectedTemplates)) {
                $this->addIssue('critical', "Template usado inválido", $article, [
                    'current_template' => $templateUsed,
                    'expected' => $expectedTemplates,
                    'fix' => 'update_template_used'
                ]);
            }
            
            // Verificar consistência com tipo de veículo
            $vehicleData = $article->vehicle_data ?? [];
            $isMotorcycle = ($vehicleData['is_motorcycle'] ?? false) === true;
            
            $expectedTemplate = $isMotorcycle ? 'tire_calibration_motorcycle' : 'tire_calibration_car';
            
            if ($templateUsed !== $expectedTemplate) {
                $this->addIssue('warning', "Template não corresponde ao tipo de veículo", $article, [
                    'current_template' => $templateUsed,
                    'expected_template' => $expectedTemplate,
                    'vehicle_type' => $isMotorcycle ? 'motorcycle' : 'car',
                    'fix' => 'correct_template_vehicle_type'
                ]);
            }
        }
    }

    /**
     * Validar títulos
     */
    private function validateTitles(\Illuminate\Database\Eloquent\Collection $articles): void
    {
        foreach ($articles as $article) {
            $title = $article->title;
            $expectedPattern = "Calibragem do Pneu do {$article->make} {$article->model} {$article->year}";
            
            if ($title !== $expectedPattern) {
                $this->addIssue('warning', "Título não segue padrão esperado", $article, [
                    'current_title' => $title,
                    'expected_title' => $expectedPattern,
                    'fix' => 'update_title'
                ]);
            }
        }
    }

    /**
     * Validar slugs
     */
    private function validateSlugs(\Illuminate\Database\Eloquent\Collection $articles): void
    {
        foreach ($articles as $article) {
            $slug = $article->slug;
            
            // Verificar padrão do slug
            if (!str_starts_with($slug, 'calibragem-pneu-')) {
                $this->addIssue('warning', "Slug não segue padrão esperado", $article, [
                    'current_slug' => $slug,
                    'expected_pattern' => 'calibragem-pneu-[marca]-[modelo]-[ano]',
                    'fix' => 'update_slug'
                ]);
            }
            
            // Verificar caracteres válidos
            if (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
                $this->addIssue('critical', "Slug contém caracteres inválidos", $article, [
                    'current_slug' => $slug,
                    'fix' => 'sanitize_slug'
                ]);
            }
        }
    }

    /**
     * Validar WordPress URLs
     */
    private function validateWordPressUrls(\Illuminate\Database\Eloquent\Collection $articles): void
    {
        foreach ($articles as $article) {
            $wordpressUrl = $article->wordpress_url;
            $slug = $article->slug;
            
            if ($wordpressUrl !== $slug) {
                $this->addIssue('warning', "WordPress URL não coincide com slug", $article, [
                    'wordpress_url' => $wordpressUrl,
                    'slug' => $slug,
                    'fix' => 'sync_wordpress_url'
                ]);
            }
        }
    }

    /**
     * Validar meta descriptions
     */
    private function validateMetaDescriptions(\Illuminate\Database\Eloquent\Collection $articles): void
    {
        foreach ($articles as $article) {
            $metaDescription = $article->meta_description;
            
            if (empty($metaDescription)) {
                $this->addIssue('warning', "Meta description vazia", $article, [
                    'fix' => 'generate_meta_description'
                ]);
                continue;
            }
            
            // Verificar se contém informações do veículo
            $vehicle = "{$article->make} {$article->model} {$article->year}";
            if (!str_contains($metaDescription, $vehicle)) {
                $this->addIssue('warning', "Meta description não menciona veículo específico", $article, [
                    'current_meta' => $metaDescription,
                    'vehicle' => $vehicle,
                    'fix' => 'update_meta_description'
                ]);
            }
        }
    }

    /**
     * Validar referências aos artigos originais
     */
    private function validateOriginalReferences(\Illuminate\Database\Eloquent\Collection $articles): void
    {
        foreach ($articles as $article) {
            $originalId = $article->original_calibration_article_id;
            
            if (empty($originalId)) {
                $this->addIssue('critical', "Referência ao artigo original ausente", $article, [
                    'fix' => 'find_original_reference'
                ]);
                continue;
            }
            
            // Verificar se artigo original existe
            $originalExists = TirePressureArticle::where('_id', $originalId)->exists();
            if (!$originalExists) {
                $this->addIssue('critical', "Artigo original não encontrado", $article, [
                    'original_id' => $originalId,
                    'fix' => 'remove_invalid_reference'
                ]);
            }
        }
    }

    /**
     * Validar duplicações
     */
    private function validateDuplicates(\Illuminate\Database\Eloquent\Collection $articles): void
    {
        $seen = [];
        
        foreach ($articles as $article) {
            $key = "{$article->make}|{$article->model}|{$article->year}";
            
            if (isset($seen[$key])) {
                $this->addIssue('critical', "Artigo duplicado encontrado", $article, [
                    'duplicate_key' => $key,
                    'original_id' => $seen[$key],
                    'fix' => 'remove_duplicate'
                ]);
            } else {
                $seen[$key] = $article->_id;
            }
        }
    }

    /**
     * Validar integridade dos dados
     */
    private function validateDataIntegrity(\Illuminate\Database\Eloquent\Collection $articles): void
    {
        foreach ($articles as $article) {
            // Verificar campos obrigatórios
            $requiredFields = ['make', 'model', 'year', 'vehicle_data', 'template_type'];
            
            foreach ($requiredFields as $field) {
                if (empty($article->$field)) {
                    $this->addIssue('critical', "Campo obrigatório ausente: {$field}", $article, [
                        'missing_field' => $field,
                        'fix' => 'restore_required_field'
                    ]);
                }
            }
            
            // Verificar template_type
            if ($article->template_type !== 'calibration_clone') {
                $this->addIssue('critical', "Template type incorreto para artigo clonado", $article, [
                    'current_template_type' => $article->template_type,
                    'expected' => 'calibration_clone',
                    'fix' => 'correct_template_type'
                ]);
            }
            
            // Verificar vehicle_data
            $vehicleData = $article->vehicle_data ?? [];
            if (empty($vehicleData['make']) || empty($vehicleData['model']) || empty($vehicleData['year'])) {
                $this->addIssue('warning', "Dados do veículo incompletos", $article, [
                    'vehicle_data_keys' => array_keys($vehicleData),
                    'fix' => 'rebuild_vehicle_data'
                ]);
            }
        }
    }

    /**
     * Adicionar issue à lista
     */
    private function addIssue(string $severity, string $description, TirePressureArticle $article, array $details = []): void
    {
        $this->issues[] = [
            'severity' => $severity,
            'description' => $description,
            'article_id' => $article->_id,
            'vehicle' => "{$article->make} {$article->model} {$article->year}",
            'details' => $details,
            'timestamp' => now()
        ];
        
        $this->stats['issues_found']++;
        
        if ($severity === 'critical') {
            $this->stats['critical_errors']++;
        } elseif ($severity === 'warning') {
            $this->stats['warnings']++;
        }
    }

    /**
     * Corrigir issues automaticamente
     */
    private function fixIssues(): void
    {
        $this->info("\n🔧 CORRIGINDO ISSUES AUTOMATICAMENTE...");
        
        $fixableIssues = collect($this->issues)->filter(function($issue) {
            return isset($issue['details']['fix']);
        });
        
        if ($fixableIssues->isEmpty()) {
            $this->warn("Nenhuma issue pode ser corrigida automaticamente.");
            return;
        }
        
        foreach ($fixableIssues as $issue) {
            try {
                $this->fixIssue($issue);
                $this->stats['issues_fixed']++;
                $this->line("   ✅ Corrigido: {$issue['description']} - {$issue['vehicle']}");
            } catch (\Exception $e) {
                $this->error("   ❌ Falha ao corrigir: {$issue['description']} - {$e->getMessage()}");
            }
        }
    }

    /**
     * Corrigir uma issue específica
     */
    private function fixIssue(array $issue): void
    {
        $article = TirePressureArticle::find($issue['article_id']);
        if (!$article) {
            throw new \Exception("Artigo não encontrado");
        }
        
        $fixType = $issue['details']['fix'];
        
        switch ($fixType) {
            case 'update_template_used':
                $this->fixTemplateUsed($article);
                break;
                
            case 'correct_template_vehicle_type':
                $this->fixTemplateVehicleType($article);
                break;
                
            case 'update_title':
                $this->fixTitle($article);
                break;
                
            case 'update_slug':
            case 'sanitize_slug':
                $this->fixSlug($article);
                break;
                
            case 'sync_wordpress_url':
                $this->fixWordPressUrl($article);
                break;
                
            case 'generate_meta_description':
            case 'update_meta_description':
                $this->fixMetaDescription($article);
                break;
                
            case 'remove_duplicate':
                $this->removeDuplicate($article);
                break;
                
            default:
                throw new \Exception("Tipo de correção não suportado: {$fixType}");
        }
    }

    /**
     * Corrigir template_used
     */
    private function fixTemplateUsed(TirePressureArticle $article): void
    {
        $vehicleData = $article->vehicle_data ?? [];
        $isMotorcycle = ($vehicleData['is_motorcycle'] ?? false) === true;
        
        $correctTemplate = $isMotorcycle ? 'tire_calibration_motorcycle' : 'tire_calibration_car';
        
        $article->update(['template_used' => $correctTemplate]);
    }

    /**
     * Corrigir template baseado no tipo de veículo
     */
    private function fixTemplateVehicleType(TirePressureArticle $article): void
    {
        $this->fixTemplateUsed($article); // Mesmo método
    }

    /**
     * Corrigir título
     */
    private function fixTitle(TirePressureArticle $article): void
    {
        $newTitle = "Calibragem do Pneu do {$article->make} {$article->model} {$article->year}";
        $article->update(['title' => $newTitle]);
    }

    /**
     * Corrigir slug
     */
    private function fixSlug(TirePressureArticle $article): void
    {
        $make = \Illuminate\Support\Str::slug($article->make);
        $model = \Illuminate\Support\Str::slug($article->model);
        $year = $article->year;
        
        $newSlug = "calibragem-pneu-{$make}-{$model}-{$year}";
        
        $article->update([
            'slug' => $newSlug,
            'wordpress_slug' => $newSlug
        ]);
    }

    /**
     * Corrigir WordPress URL
     */
    private function fixWordPressUrl(TirePressureArticle $article): void
    {
        $article->update(['wordpress_url' => $article->slug]);
    }

    /**
     * Corrigir meta description
     */
    private function fixMetaDescription(TirePressureArticle $article): void
    {
        $newMeta = "Guia completo para calibragem do pneu do {$article->make} {$article->model} {$article->year}. Pressões corretas, passo a passo e dicas de manutenção.";
        $article->update(['meta_description' => $newMeta]);
    }

    /**
     * Remover artigo duplicado
     */
    private function removeDuplicate(TirePressureArticle $article): void
    {
        // Por segurança, apenas marcar como duplicado ao invés de deletar
        $article->update([
            'is_duplicate' => true,
            'duplicate_marked_at' => now()
        ]);
    }

    /**
     * Exibir relatório de validação
     */
    private function displayValidationReport(): void
    {
        $this->info("\n" . str_repeat('=', 80));
        $this->info("📊 RELATÓRIO DE VALIDAÇÃO");
        $this->info(str_repeat('=', 80));
        
        // Estatísticas gerais
        $this->info("✅ Estatísticas gerais:");
        $this->line("   • Total de artigos clonados: {$this->stats['total_cloned']}");
        $this->line("   • Artigos válidos: {$this->stats['valid_articles']}");
        $this->line("   • Issues encontradas: {$this->stats['issues_found']}");
        $this->line("   • Erros críticos: {$this->stats['critical_errors']}");
        $this->line("   • Avisos: {$this->stats['warnings']}");
        
        if ($this->option('fix-issues')) {
            $this->line("   • Issues corrigidas: {$this->stats['issues_fixed']}");
        }
        
        // Taxa de sucesso
        $successRate = $this->stats['total_cloned'] > 0 
            ? round(($this->stats['valid_articles'] / $this->stats['total_cloned']) * 100, 1)
            : 0;
        
        $this->info("\n🎯 Taxa de sucesso: {$successRate}%");
        
        // Issues por severidade
        if (!empty($this->issues)) {
            $this->info("\n⚠️  ISSUES ENCONTRADAS:");
            
            $criticalIssues = collect($this->issues)->where('severity', 'critical');
            $warningIssues = collect($this->issues)->where('severity', 'warning');
            
            if ($criticalIssues->isNotEmpty()) {
                $this->error("\n❌ ERROS CRÍTICOS ({$criticalIssues->count()}):");
                foreach ($criticalIssues->take(5) as $issue) {
                    $this->line("   • {$issue['description']} - {$issue['vehicle']}");
                }
                if ($criticalIssues->count() > 5) {
                    $remaining = $criticalIssues->count() - 5;
                    $this->line("   ... e mais {$remaining} erros críticos");
                }
            }
            
            if ($warningIssues->isNotEmpty()) {
                $this->warn("\n⚠️  AVISOS ({$warningIssues->count()}):");
                foreach ($warningIssues->take(3) as $issue) {
                    $this->line("   • {$issue['description']} - {$issue['vehicle']}");
                }
                if ($warningIssues->count() > 3) {
                    $remaining = $warningIssues->count() - 3;
                    $this->line("   ... e mais {$remaining} avisos");
                }
            }
        }
        
        // Mostrar detalhes se solicitado
        if ($this->option('detailed') && !empty($this->issues)) {
            $this->displayDetailedReport();
        }
        
        // Recomendações
        $this->displayRecommendations();
    }

    /**
     * Exibir relatório detalhado
     */
    private function displayDetailedReport(): void
    {
        $this->info("\n📋 RELATÓRIO DETALHADO:");
        $this->info(str_repeat('-', 60));
        
        foreach ($this->issues as $index => $issue) {
            $issueNumber = $index + 1;
            $this->info("\n#{$issueNumber} [{$issue['severity']}] {$issue['description']}");
            $this->line("   Artigo: {$issue['vehicle']} (ID: {$issue['article_id']})");
            
            if (!empty($issue['details'])) {
                foreach ($issue['details'] as $key => $value) {
                    if ($key !== 'fix' && is_string($value)) {
                        $this->line("   {$key}: {$value}");
                    }
                }
            }
        }
    }

    /**
     * Exibir recomendações
     */
    private function displayRecommendations(): void
    {
        $this->info("\n💡 RECOMENDAÇÕES:");
        
        if ($this->stats['critical_errors'] > 0) {
            $this->line("1. ❗ Corrigir erros críticos imediatamente:");
            $this->line("   php artisan tire-pressure-guide:validate-cloned-articles --fix-issues");
        }
        
        if ($this->stats['warnings'] > 0) {
            $this->line("2. ⚠️  Revisar avisos para melhorar qualidade");
        }
        
        if ($this->stats['valid_articles'] > 0) {
            $this->line("3. ✅ Prosseguir com refinamento dos artigos válidos:");
            $this->line("   php artisan tire-pressure-guide:refine-sections --filter-cloned");
        }
        
        $this->line("4. 📊 Gerar relatório detalhado:");
        $this->line("   php artisan tire-pressure-guide:validate-cloned-articles --detailed --export-report=cloned_validation.json");
    }

    /**
     * Exportar relatório para arquivo
     */
    private function exportReport(string $filePath): void
    {
        $report = [
            'validation_timestamp' => now()->toISOString(),
            'statistics' => $this->stats,
            'issues' => $this->issues,
            'command_options' => [
                'detailed' => $this->option('detailed'),
                'fix_issues' => $this->option('fix-issues')
            ]
        ];
        
        $jsonReport = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if (file_put_contents($filePath, $jsonReport)) {
            $this->info("\n💾 Relatório exportado para: {$filePath}");
        } else {
            $this->error("\n❌ Falha ao exportar relatório para: {$filePath}");
        }
    }

    /**
     * Orientações quando não há artigos clonados
     */
    private function displayNoClonedArticlesGuidance(): void
    {
        $this->info("\n💡 ORIENTAÇÕES:");
        $this->line("1. Execute a clonagem primeiro:");
        $this->line("   php artisan tire-pressure-guide:clone-calibration-articles --dry-run");
        $this->line("2. Depois execute a clonagem real:");
        $this->line("   php artisan tire-pressure-guide:clone-calibration-articles");
        $this->line("3. Então execute esta validação novamente:");
        $this->line("   php artisan tire-pressure-guide:validate-cloned-articles");
    }
}