<?php

namespace Src\ContentGeneration\WhenToChangeTires\Infrastructure\Repositories;

use Src\ContentGeneration\WhenToChangeTires\Domain\Repositories\TireChangeArticleRepositoryInterface;
use Src\ContentGeneration\WhenToChangeTires\Domain\Entities\TireChangeArticle;
use Src\ContentGeneration\WhenToChangeTires\Domain\ValueObjects\VehicleData;
use Src\ContentGeneration\WhenToChangeTires\Domain\ValueObjects\TireChangeContent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TireChangeArticleRepository implements TireChangeArticleRepositoryInterface
{
    /**
     * Criar artigo a partir do conteúdo gerado
     */
    public function createFromContent(VehicleData $vehicle, TireChangeContent $content, array $options = []): ?TireChangeArticle
    {
        try {
            $jsonData = $content->toJsonStructure();
            $wordCount = $content->getWordCount();

            $articleData = [
                'make' => $vehicle->make,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'tire_size' => $vehicle->tireSize,
                'vehicle_data' => $vehicle->toArray(),
                'title' => $jsonData['title'],
                'slug' => $jsonData['slug'],
                'article_content' => json_encode($jsonData['content'], JSON_UNESCAPED_UNICODE),
                'template_used' => $jsonData['template'],
                'meta_description' => $jsonData['seo_data']['meta_description'],
                'seo_keywords' => $jsonData['seo_data']['secondary_keywords'] ?? [],
                'wordpress_url' => $jsonData['seo_data']['url_slug'],
                'canonical_url' => $jsonData['seo_data']['canonical_url'] ?? null,
                'generation_status' => 'generated',
                'pressure_empty_front' => $vehicle->pressureEmptyFront,
                'pressure_empty_rear' => $vehicle->pressureEmptyRear,
                'pressure_light_front' => $vehicle->pressureLightFront,
                'pressure_light_rear' => $vehicle->pressureLightRear,
                'pressure_max_front' => $vehicle->pressureMaxFront,
                'pressure_max_rear' => $vehicle->pressureMaxRear,
                'pressure_spare' => $vehicle->pressureSpare,
                'category' => $vehicle->category,
                'recommended_oil' => $vehicle->recommendedOil,
                'quality_checked' => true,
                'content_score' => $this->calculateContentScore($wordCount, $jsonData),
                'batch_id' => $options['batch_id'] ?? 'manual_' . date('Ymd_His'),
                'processed_at' => now()
            ];

            $article = TireChangeArticle::create($articleData);

            // Marcar como gerado
            $article->markAsGenerated();

            // Limpar cache
            $this->clearRelatedCache($vehicle);

            Log::info("Artigo criado com sucesso", [
                'id' => $article->id,
                'vehicle' => $vehicle->getVehicleIdentifier(),
                'slug' => $article->slug
            ]);

            return $article;
        } catch (\Exception $e) {
            Log::error("Erro criando TireChangeArticle: " . $e->getMessage(), [
                'vehicle' => $vehicle->getVehicleIdentifier(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Calcular score do conteúdo
     */
    protected function calculateContentScore(int $wordCount, array $jsonData): float
    {
        $score = 5.0; // Base

        // Score por quantidade de palavras
        if ($wordCount >= 2000) $score += 2.0;
        elseif ($wordCount >= 1500) $score += 1.5;
        elseif ($wordCount >= 1000) $score += 1.0;
        elseif ($wordCount >= 800) $score += 0.5;

        // Score por estrutura do conteúdo
        $contentSections = $jsonData['content'] ?? [];
        $requiredSections = ['introducao', 'sintomas_desgaste', 'fatores_durabilidade', 'cronograma_verificacao'];

        $sectionScore = 0;
        foreach ($requiredSections as $section) {
            if (isset($contentSections[$section]) && !empty($contentSections[$section])) {
                $sectionScore += 0.5;
            }
        }
        $score += $sectionScore;

        // Score por SEO
        $seoData = $jsonData['seo_data'] ?? [];
        if (!empty($seoData['meta_description'])) $score += 0.3;
        if (!empty($seoData['secondary_keywords'])) $score += 0.2;

        return min(10.0, round($score, 1));
    }

    /**
     * Verificar se existe artigo para o veículo
     */
    public function existsForVehicle(string $make, string $model, int $year): bool
    {
        $cacheKey = "tire_article_exists_{$make}_{$model}_{$year}";

        return Cache::remember($cacheKey, 3600, function () use ($make, $model, $year) {
            return TireChangeArticle::where('make', $make)
                ->where('model', $model)
                ->where('year', $year)
                ->exists();
        });
    }

    /**
     * Buscar artigo por slug
     */
    public function findBySlug(string $slug): ?TireChangeArticle
    {
        return TireChangeArticle::where('slug', $slug)->first();
    }

    /**
     * Buscar artigo por veículo
     */
    public function findByVehicle(string $make, string $model, int $year): ?TireChangeArticle
    {
        return TireChangeArticle::where('make', $make)
            ->where('model', $model)
            ->where('year', $year)
            ->first();
    }

    /**
     * Buscar artigos por lote
     */
    public function findByBatchId(string $batchId): Collection
    {
        return TireChangeArticle::where('batch_id', $batchId)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Contar artigos por lote
     */
    public function countByBatchId(string $batchId): int
    {
        return TireChangeArticle::where('batch_id', $batchId)->count();
    }

    /**
     * Buscar artigos por status
     */
    public function findByStatus(string $status): Collection
    {
        return TireChangeArticle::where('generation_status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Buscar artigos paginados
     */
    public function findPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = TireChangeArticle::query();

        // Aplicar filtros
        $query = $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Aplicar filtros à query
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['make'])) {
            $query->where('make', $filters['make']);
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['status'])) {
            $query->where('generation_status', $filters['status']);
        }

        if (!empty($filters['year_from'])) {
            $query->where('year', '>=', $filters['year_from']);
        }

        if (!empty($filters['year_to'])) {
            $query->where('year', '<=', $filters['year_to']);
        }

        if (!empty($filters['batch_id'])) {
            $query->where('batch_id', $filters['batch_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('make', 'LIKE', "%{$search}%")
                    ->orWhere('model', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filters['quality_checked'])) {
            $query->where('quality_checked', $filters['quality_checked']);
        }

        if (!empty($filters['min_score'])) {
            $query->where('content_score', '>=', $filters['min_score']);
        }

        return $query;
    }

    /**
     * Obter artigos prontos para refinamento Claude
     */
    public function getReadyForClaudeEnhancement(int $limit = 50): Collection
    {
        return TireChangeArticle::readyForClaude()
            ->where('claude_enhancement_count', '<', 3) // Máximo 3 refinamentos
            ->orderBy('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Contar total de artigos
     */
    public function count(): int
    {
        return Cache::remember('tire_articles_total_count', 300, function () {
            return TireChangeArticle::count();
        });
    }

    /**
     * Contar artigos gerados hoje
     */
    public function countGeneratedToday(): int
    {
        return TireChangeArticle::whereDate('created_at', today())->count();
    }

    /**
     * Obter distribuição por status
     */
    public function getStatusDistribution(): array
    {
        return Cache::remember('tire_articles_status_distribution', 600, function () {
            return TireChangeArticle::selectRaw('generation_status, COUNT(*) as count')
                ->groupBy('generation_status')
                ->pluck('count', 'generation_status')
                ->toArray();
        });
    }

    /**
     * Obter estatísticas gerais
     */
    public function getStatistics(): array
    {
        return Cache::remember('tire_articles_statistics', 1800, function () {
            $stats = [
                'total_articles' => TireChangeArticle::count(),
                'by_status' => $this->getStatusDistribution(),
                'by_make' => [],
                'by_category' => [],
                'by_year' => [],
                'quality_metrics' => [],
                'recent_activity' => []
            ];

            // Estatísticas por marca
            $stats['by_make'] = TireChangeArticle::selectRaw('make, COUNT(*) as count')
                ->groupBy('make')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->pluck('count', 'make')
                ->toArray();

            // Estatísticas por categoria
            $stats['by_category'] = TireChangeArticle::selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->orderBy('count', 'desc')
                ->pluck('count', 'category')
                ->toArray();

            // Estatísticas por ano
            $stats['by_year'] = TireChangeArticle::selectRaw('year, COUNT(*) as count')
                ->groupBy('year')
                ->orderBy('year', 'desc')
                ->pluck('count', 'year')
                ->toArray();

            // Métricas de qualidade
            $stats['quality_metrics'] = [
                'average_score' => TireChangeArticle::avg('content_score'),
                'high_quality' => TireChangeArticle::where('content_score', '>=', 8.0)->count(),
                'needs_improvement' => TireChangeArticle::where('content_score', '<', 6.0)->count(),
                'claude_enhanced' => TireChangeArticle::where('claude_enhancement_count', '>', 0)->count()
            ];

            // Atividade recente
            $stats['recent_activity'] = [
                'created_today' => TireChangeArticle::whereDate('created_at', today())->count(),
                'created_this_week' => TireChangeArticle::where('created_at', '>=', now()->startOfWeek())->count(),
                'created_this_month' => TireChangeArticle::where('created_at', '>=', now()->startOfMonth())->count()
            ];

            return $stats;
        });
    }

    /**
     * Buscar por múltiplos critérios
     */
    public function findByCriteria(array $criteria): Collection
    {
        $query = TireChangeArticle::query();

        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query->get();
    }

    /**
     * Atualizar status do artigo
     */
    public function updateStatus(int $id, string $status): bool
    {
        try {
            $article = TireChangeArticle::findOrFail($id);
            $article->update(['generation_status' => $status]);

            // Limpar cache
            $this->clearRelatedCache($article);

            Log::info("Status atualizado", [
                'article_id' => $id,
                'new_status' => $status
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Erro atualizando status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marcar como refinado pelo Claude
     */
    public function markAsClaudeEnhanced(int $id, array $enhancementData = []): bool
    {
        try {
            $article = TireChangeArticle::findOrFail($id);
            $article->markAsClaudeEnhanced($enhancementData);

            // Limpar cache
            $this->clearRelatedCache($article);

            Log::info("Artigo marcado como refinado pelo Claude", [
                'article_id' => $id,
                'enhancement_count' => $article->claude_enhancement_count
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Erro marcando como Claude enhanced: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obter artigos com problemas de qualidade
     */
    public function getWithQualityIssues(): Collection
    {
        return TireChangeArticle::where(function ($query) {
            $query->where('content_score', '<', 6.0)
                ->orWhere('quality_checked', false)
                ->orWhereNotNull('quality_issues');
        })
            ->orderBy('content_score')
            ->get();
    }

    /**
     * Obter artigos para transferência
     */
    public function getForTransfer(int $limit = 100): Collection
    {
        return TireChangeArticle::where('generation_status', 'claude_enhanced')
            ->where('content_score', '>=', 7.0)
            ->orderBy('claude_last_enhanced_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Deletar artigos antigos
     */
    public function deleteOlderThan(\DateTimeInterface $date): int
    {
        $count = TireChangeArticle::where('created_at', '<', $date)->count();

        if ($count > 0) {
            TireChangeArticle::where('created_at', '<', $date)->delete();

            // Limpar todo o cache
            Cache::tags(['tire_articles'])->flush();

            Log::info("Artigos antigos deletados", [
                'count' => $count,
                'before_date' => $date->format('Y-m-d')
            ]);
        }

        return $count;
    }

    /**
     * Limpar cache relacionado
     */
    protected function clearRelatedCache($article): void
    {
        if (is_object($article)) {
            $cacheKey = "tire_article_exists_{$article->make}_{$article->model}_{$article->year}";
            Cache::forget($cacheKey);
        }

        Cache::forget('tire_articles_total_count');
        Cache::forget('tire_articles_status_distribution');
        Cache::forget('tire_articles_statistics');
    }

    /**
     * Método que verifica apenas make+model
     */
    public function existsForVehicleModel(string $make, string $model): bool
    {
        return TireChangeArticle::where('make', $make)
            ->where('model', $model)
            ->exists();
    }
}
