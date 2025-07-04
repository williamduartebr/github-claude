<?php

namespace Src\ContentGeneration\WhenToChangeTiresWithYear\Application\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\Entities\TireChangeArticle;

class WhenToChangeTiresApplicationService
{
    /**
     * Obter estatísticas dos artigos
     */
    public function getArticleStats(): array
    {
        try {
            return [
                'total' => TireChangeArticle::count(),
                'generated' => TireChangeArticle::where('generation_status', 'generated')->count(),
                'published' => TireChangeArticle::where('generation_status', 'published')->count(),
                'claude_enhanced' => TireChangeArticle::where('generation_status', 'claude_enhanced')->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter estatísticas dos artigos de pneu: ' . $e->getMessage());
            return [
                'total' => 0,
                'generated' => 0,
                'published' => 0,
                'claude_enhanced' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obter estatísticas do TempArticle
     */
    public function getTempArticleStats(): array
    {
        $total = TempArticle::count();
        $whenToChangeTires = TempArticle::where('source', 'when_to_change_tires')->count();

        return [
            'total' => $total,
            'when_to_change_tires' => $whenToChangeTires
        ];
    }

    /**
     * Publicar artigos para TempArticle collection
     */
    public function publishToTempArticles(
        string $status = 'generated',
        int $limit = 100,
        bool $dryRun = false,
        ?callable $progressCallback = null
    ): object {
        $results = (object)[
            'published' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        try {
            $articles = TireChangeArticle::where('generation_status', $status)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            $totalArticles = $articles->count();
            $processed = 0;

            foreach ($articles as $article) {
                try {
                    // Verificar se já existe no TempArticle
                    if ($this->tempArticleExists($article->slug)) {
                        $results->skipped++;
                        $results->errors[] = "Artigo já existe no TempArticle: {$article->slug}";
                        continue;
                    }

                    if (!$dryRun) {
                        $tempArticleData = $this->convertToTempArticleFormat($article);

                        $tempArticle = new TempArticle();
                        $tempArticle->fill($tempArticleData);

                        if ($tempArticle->save()) {
                            $results->published++;
                        } else {
                            $results->failed++;
                            $results->errors[] = "Falha ao salvar no TempArticle: {$article->slug}";
                        }
                    } else {
                        $results->published++; // Simular para dry run
                    }
                } catch (\Exception $e) {
                    $results->failed++;
                    $results->errors[] = "Erro ao publicar {$article->slug}: " . $e->getMessage();
                }

                $processed++;
                if ($progressCallback) {
                    $progressCallback($processed, $totalArticles);
                }
            }
        } catch (\Exception $e) {
            $results->errors[] = "Erro ao buscar artigos: " . $e->getMessage();
        }

        return $results;
    }

    /**
     * Verificar se artigo já existe no TempArticle
     */
    private function tempArticleExists(string $slug): bool
    {
        try {
            return TempArticle::where('slug', $slug)->exists();
        } catch (\Exception $e) {
            Log::error('Erro ao verificar existência no TempArticle: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Converter artigo para formato TempArticle
     */
    private function convertToTempArticleFormat($article): array
    {
        $articleContent = is_string($article->article_content)
            ? json_decode($article->article_content, true)
            : $article->article_content;

        // Extrair entidades do veículo baseado na estrutura tire_change_articles
        $extractedEntities = [
            'marca' => $article->make ?? '',
            'modelo' => $article->model ?? '',
            'ano' => (string)($article->year ?? ''),
            'medida_pneu' => $article->tire_size ?? '',
            'versao' => 'Todas',
            'tipo_veiculo' => $this->mapVehicleType($article->category ?? 'hatch'),
            'categoria' => $article->category ?? 'hatch',
            'combustivel' => 'flex'
        ];

        // Dados SEO formatados
        $seoFormatted = [
            'page_title' => $article->title,
            'meta_description' => $article->meta_description ?? $this->generateMetaDescription($article),
            'url_slug' => $article->wordpress_url ?? Str::slug($article->title),
            'h1' => $article->title,
            'h2_tags' => $this->extractH2Tags($articleContent),
            'primary_keyword' => $this->generatePrimaryKeyword($article),
            'secondary_keywords' => $article->seo_keywords ?? $this->generateSecondaryKeywords($article),
            'meta_robots' => 'index,follow',
            'canonical_url' => $article->canonical_url ?? config('app.url') . '/info/' . Str::slug($article->title),
            'schema_type' => 'Article',
            'focus_keywords' => $this->generateFocusKeywords($article)
        ];

        return [
            'original_post_id' => $article->blog_id ?? null,
            'title' => $article->title,
            'slug' => $article->wordpress_url ?? Str::slug($article->title),
            'new_slug' => $article->slug,
            'content' => $articleContent,
            'extracted_entities' => $extractedEntities,
            'seo_data' => $seoFormatted,
            'source' => 'when_to_change_tires',
            'category_id' => 30,
            'category_name' => 'Pneus e Rodas',
            'category_slug' => 'pneus-rodas',
            'published_at' => $article->blog_published_time ?? null,
            'modified_at' => $article->blog_modified_time ?? null,
            'blog_status' => $article->blog_status ?? null,
            'domain' => $article->template_used ?? 'when_to_change_tires',
            'status' => 'draft',
            'template' => $article->template_used ?? 'when_to_change_tires',
            'quality_score' => (float)($article->content_score ?? 7.0),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    /**
     * Mapear tipo de veículo
     */
    private function mapVehicleType(string $type): string
    {
        return match (strtolower($type)) {
            'motorcycle', 'moto', 'motocicleta' => 'motocicleta',
            'electric', 'elétrico' => 'elétrico',
            'hybrid', 'híbrido' => 'híbrido',
            default => 'carro'
        };
    }

    /**
     * Gerar meta description
     */
    private function generateMetaDescription($article): string
    {
        $make = $article->make ?? '';
        $model = $article->model ?? '';
        $year = $article->year ?? '';

        return "Saiba quando trocar os pneus do {$make} {$model} {$year}. Sinais de desgaste, vida útil, pressão ideal e dicas de manutenção para máxima segurança.";
    }

    /**
     * Extrair H2 tags do conteúdo
     */
    private function extractH2Tags(array $content): array
    {
        return [
            'Quando Trocar os Pneus',
            'Sinais de Desgaste',
            'Pressão dos Pneus',
            'Vida Útil dos Pneus',
            'Dicas de Manutenção',
            'Especificações Técnicas',
            'Perguntas Frequentes'
        ];
    }

    /**
     * Gerar palavra-chave primária
     */
    private function generatePrimaryKeyword($article): string
    {
        $make = strtolower($article->make ?? '');
        $model = strtolower($article->model ?? '');

        return "quando trocar pneu {$make} {$model}";
    }

    /**
     * Gerar palavras-chave secundárias
     */
    private function generateSecondaryKeywords($article): array
    {
        $make = strtolower($article->make ?? '');
        $model = strtolower($article->model ?? '');
        $year = $article->year ?? '';

        return [
            "pneu {$make} {$model}",
            "trocar pneu {$make} {$model} {$year}",
            "vida util pneu {$make}",
            "pressao pneu {$make} {$model}",
            "desgaste pneu",
            "manutencao pneu {$make}"
        ];
    }

    /**
     * Gerar focus keywords
     */
    private function generateFocusKeywords($article): array
    {
        return [
            'quando trocar pneus',
            'sinais desgaste pneu',
            'vida útil pneu',
            'pressão pneu',
            'manutenção pneu'
        ];
    }
}
