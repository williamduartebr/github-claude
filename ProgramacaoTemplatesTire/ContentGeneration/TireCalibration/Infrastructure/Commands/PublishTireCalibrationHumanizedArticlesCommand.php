<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Src\AutoInfoCenter\Domain\Eloquent\MaintenanceCategory;
use Illuminate\Console\Command;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;

class PublishTireCalibrationHumanizedArticlesCommand extends Command
{
    protected $signature = 'articles-temp:publish-drafts-humanized {--limit=1}';
    protected $description = 'Publica artigos temporários com datas humanizadas';

    public function handle()
    {
        $limit = (int) $this->option('limit');

        $draftArticles = TempArticle::where('status', 'draft')
            ->take($limit)
            ->get();

        if ($draftArticles->isEmpty()) {
            $this->info('Nenhum artigo encontrado para publicação.');
            return Command::SUCCESS;
        }

        $draftArticle = $draftArticles->first();

        try {
            if (Article::where('slug', $draftArticle->slug)->exists()) {
                $this->info('Artigo com slug já existe: ' . $draftArticle->slug);
                return Command::SUCCESS;
            }

            $dates = $this->generateHumanizedDates();

            Article::create([
                'title' => $draftArticle->title,
                'slug' => $draftArticle->slug,
                'template' => $draftArticle->template,
                'category_id' => $draftArticle->category_id,
                'category_name' => $draftArticle->category_name,
                'category_slug' => $draftArticle->category_slug,
                'content' => $draftArticle->content,
                'extracted_entities' => $draftArticle->extracted_entities,
                'seo_data' => $draftArticle->seo_data,
                'metadata' => $draftArticle->metadata,
                'tags' => $this->extractTags($draftArticle),
                'related_topics' => $this->extractRelatedTopics($draftArticle),
                'status' => 'published',
                'created_at' => $dates['created_at'],
                'updated_at' => $dates['updated_at'],
                'vehicle_info' => $draftArticle->vehicle_info,
                'filter_data' => $draftArticle->filter_data,
            ]);

            $draftArticle->update(['status' => 'published']);
            $processed = 1;
        } catch (\Exception $e) {
            $this->error("Erro: {$e->getMessage()}");
            return Command::FAILURE;
        }

        $this->info("Publicado: {$processed} artigo");
        return Command::SUCCESS;
    }

    private function generateHumanizedDates(): array
    {
        $now = Carbon::now('America/Sao_Paulo')->subHours(4);
        $cutoffDate = Carbon::parse('2025-09-20', 'America/Sao_Paulo');

        $minutes = rand(0, 6);
        $seconds = rand(0, 59);
        $updatedAt = $now->setTime($now->hour, $minutes, $seconds);

        if ($now->lessThanOrEqualTo($cutoffDate)) {
            $daysToSubtract = rand(0, 4);
            $createdMinutes = rand(0, 59);
            $createdSeconds = rand(0, 59);
            $createdAt = $now->copy()->subDays($daysToSubtract)->setTime($now->hour, $createdMinutes, $createdSeconds);

            // Garante que created_at seja anterior ao updated_at
            if ($createdAt->greaterThanOrEqualTo($updatedAt)) {
                $createdAt = $updatedAt->copy()->subMinutes(rand(1, 60));
            }
        } else {
            $createdMinutes = rand(0, 59);
            $createdSeconds = rand(0, 59);
            $createdAt = $now->copy()->setTime($now->hour, $createdMinutes, $createdSeconds);

            // Garante que created_at seja anterior ao updated_at
            if ($createdAt->greaterThanOrEqualTo($updatedAt)) {
                $createdAt = $updatedAt->copy()->subMinutes(rand(1, 60));
            }
        }

        return [
            'created_at' => $createdAt->utc(),
            'updated_at' => $updatedAt->utc()
        ];
    }

    private function extractTags($article): array
    {
        $tags = [];

        if (!empty($article->seo_data['primary_keyword'])) {
            $tags[] = $article->seo_data['primary_keyword'];
        }

        if (!empty($article->seo_data['secondary_keywords'])) {
            $tags = array_merge($tags, $article->seo_data['secondary_keywords']);
        }

        return array_unique(array_filter($tags));
    }

    private function extractRelatedTopics($article): array
    {
        if (!empty($article->metadata['related_content'])) {
            return $article->metadata['related_content'];
        }

        if (!empty($article->seo_data['related_topics'])) {
            return array_map(fn($topic) => [
                'title' => $topic,
                'slug' => Str::slug($topic)
            ], $article->seo_data['related_topics']);
        }

        return [];
    }
}
