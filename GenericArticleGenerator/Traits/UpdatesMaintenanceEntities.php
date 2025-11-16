<?php

namespace Src\GenericArticleGenerator\Traits;

use Illuminate\Support\Facades\Log;
use Src\AutoInfoCenter\Domain\Eloquent\MaintenanceCategory;
use Src\AutoInfoCenter\Domain\Eloquent\MaintenanceSubcategory;
use Src\AutoInfoCenter\Domain\Eloquent\Article;

/**
 * UpdatesMaintenanceEntities Trait
 * 
 * Responsável por ativar o flag 'to_follow' em MaintenanceCategory e MaintenanceSubcategory
 * quando um artigo é publicado nessas categorias.
 * 
 * PRINCÍPIOS APLICADOS:
 * - Single Responsibility: Apenas gerencia a ativação de entidades de manutenção
 * - DRY: Evita duplicação de código entre comandos
 * - Idempotência: Pode ser executado múltiplas vezes com segurança
 * - Cache local: Evita processamento duplicado na mesma execução
 * 
 * USO:
 * use UpdatesMaintenanceEntities;
 * 
 * E dentro do método após criar o Article:
 * $this->activateMaintenanceEntities($article);
 * 
 * @author Claude Sonnet 4.5
 * @version 1.0
 */
trait UpdatesMaintenanceEntities
{
    /**
     * Cache de categorias já processadas para evitar reprocessamento
     * @var array<string, bool>
     */
    private array $processedCategories = [];

    /**
     * Cache de subcategorias já processadas para evitar reprocessamento
     * @var array<int, bool>
     */
    private array $processedSubcategories = [];

    /**
     * Ativa MaintenanceCategory e MaintenanceSubcategory se necessário
     * 
     * Este método:
     * 1. Valida se o artigo tem category_slug
     * 2. Ativa MaintenanceCategory (to_follow = true)
     * 3. Ativa MaintenanceSubcategory (to_follow = true) se existir
     * 4. Usa cache para evitar múltiplas queries na mesma categoria/subcategoria
     * 
     * @param Article $article Artigo publicado
     * @return void
     */
    protected function activateMaintenanceEntities(Article $article): void
    {
        $this->activateMaintenanceCategory($article);
        $this->activateMaintenanceSubcategory($article);
    }

    /**
     * Ativa MaintenanceCategory se necessário
     * 
     * @param Article $article
     * @return void
     */
    private function activateMaintenanceCategory(Article $article): void
    {
        // Validação: artigo precisa ter category_slug
        if (empty($article->category_slug)) {
            return;
        }

        // Cache: evita reprocessamento da mesma categoria
        if (isset($this->processedCategories[$article->category_slug])) {
            return;
        }

        try {
            $category = MaintenanceCategory::where('slug', $article->category_slug)
                ->where('to_follow', false)
                ->first();

            if ($category) {
                $category->update(['to_follow' => true]);

                $this->logMaintenanceCategoryActivation($category, $article);
            }

            // Marcar como processada mesmo se não encontrada
            $this->processedCategories[$article->category_slug] = true;
        } catch (\Exception $e) {
            $this->handleMaintenanceCategoryError($article, $e);
        }
    }

    /**
     * Ativa MaintenanceSubcategory se necessário
     * 
     * @param Article $article
     * @return void
     */
    private function activateMaintenanceSubcategory(Article $article): void
    {
        // Validação: artigo precisa ter maintenance_subcategory_id
        $subcategoryId = $this->extractMaintenanceSubcategoryId($article);

        if (!$subcategoryId) {
            return;
        }

        // Cache: evita reprocessamento da mesma subcategoria
        if (isset($this->processedSubcategories[$subcategoryId])) {
            return;
        }

        try {
            $subcategory = MaintenanceSubcategory::where('id', $subcategoryId)
                ->where('is_published', false)
                ->first();

            if ($subcategory) {
                $subcategory->update(['is_published' => true]);

                $this->logMaintenanceSubcategoryActivation($subcategory, $article);
            }

            // Marcar como processada mesmo se não encontrada
            $this->processedSubcategories[$subcategoryId] = true;
        } catch (\Exception $e) {
            $this->handleMaintenanceSubcategoryError($article, $e);
        }
    }

    /**
     * Extrai o maintenance_subcategory_id do artigo
     * 
     * Tenta múltiplas fontes:
     * 1. subcategory_id direto (campo padrão)
     * 2. maintenance_subcategory_id direto
     * 3. metadata.maintenance_subcategory_id
     * 
     * @param Article $article
     * @return int|null
     */
    private function extractMaintenanceSubcategoryId(Article $article): ?int
    {
        // Tentar campo direto subcategory_id
        if (!empty($article->subcategory_id) && is_numeric($article->subcategory_id)) {
            return (int) $article->subcategory_id;
        }

        // Tentar campo maintenance_subcategory_id
        if (isset($article->maintenance_subcategory_id) && is_numeric($article->maintenance_subcategory_id)) {
            return (int) $article->maintenance_subcategory_id;
        }

        // Tentar em metadata
        if (!empty($article->metadata['maintenance_subcategory_id'])) {
            return (int) $article->metadata['maintenance_subcategory_id'];
        }

        return null;
    }

    /**
     * Log de ativação de MaintenanceCategory
     * 
     * @param MaintenanceCategory $category
     * @param Article $article
     * @return void
     */
    private function logMaintenanceCategoryActivation(MaintenanceCategory $category, Article $article): void
    {
        if (method_exists($this, 'info')) {
            $this->info("   ✅ MaintenanceCategory '{$category->slug}' ativada (to_follow = true)");
        }

        Log::info('MaintenanceCategory ativada', [
            'category_id' => $category->id,
            'category_slug' => $category->slug,
            'category_name' => $category->name,
            'article_id' => $article->_id ?? $article->id,
            'article_slug' => $article->slug,
        ]);
    }

    /**
     * Log de ativação de MaintenanceSubcategory
     * 
     * @param MaintenanceSubcategory $subcategory
     * @param Article $article
     * @return void
     */
    private function logMaintenanceSubcategoryActivation(MaintenanceSubcategory $subcategory, Article $article): void
    {
        if (method_exists($this, 'info')) {
            $this->info("   ✅ MaintenanceSubcategory '{$subcategory->slug}' ativada (to_follow = true)");
        }

        Log::info('MaintenanceSubcategory ativada', [
            'subcategory_id' => $subcategory->id,
            'subcategory_slug' => $subcategory->slug,
            'subcategory_name' => $subcategory->name,
            'category_id' => $subcategory->maintenance_category_id,
            'article_id' => $article->_id ?? $article->id,
            'article_slug' => $article->slug,
        ]);
    }

    /**
     * Tratamento de erro ao ativar MaintenanceCategory
     * 
     * @param Article $article
     * @param \Exception $e
     * @return void
     */
    private function handleMaintenanceCategoryError(Article $article, \Exception $e): void
    {
        $message = "Erro ao ativar MaintenanceCategory '{$article->category_slug}'";

        if (method_exists($this, 'warn')) {
            $this->warn("   ⚠️ {$message}: {$e->getMessage()}");
        }

        Log::warning($message, [
            'category_slug' => $article->category_slug,
            'article_id' => $article->_id ?? $article->id,
            'article_slug' => $article->slug,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    /**
     * Tratamento de erro ao ativar MaintenanceSubcategory
     * 
     * @param Article $article
     * @param \Exception $e
     * @return void
     */
    private function handleMaintenanceSubcategoryError(Article $article, \Exception $e): void
    {
        $subcategoryId = $this->extractMaintenanceSubcategoryId($article);
        $message = "Erro ao ativar MaintenanceSubcategory ID: {$subcategoryId}";

        if (method_exists($this, 'warn')) {
            $this->warn("   ⚠️ {$message}: {$e->getMessage()}");
        }

        Log::warning($message, [
            'subcategory_id' => $subcategoryId,
            'article_id' => $article->_id ?? $article->id,
            'article_slug' => $article->slug,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    /**
     * Limpa o cache de categorias/subcategorias processadas
     * Útil para testes ou quando quiser reprocessar
     * 
     * @return void
     */
    protected function clearMaintenanceEntitiesCache(): void
    {
        $this->processedCategories = [];
        $this->processedSubcategories = [];
    }
}
