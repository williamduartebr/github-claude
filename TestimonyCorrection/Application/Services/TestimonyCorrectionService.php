<?php

namespace Src\TestimonyCorrection\Application\Services;

use Src\TestimonyCorrection\Infrastructure\LLM\ClaudeTestimonyClient;
use Src\TestimonyCorrection\Support\PromptBuilder;
use Src\AutoInfoCenter\Domain\Eloquent\Article;

class TestimonyCorrectionService
{
    public function __construct(
        private TestimonyExtractionService $extractor,
        private TestimonyTransformService $transformer,
        private ClaudeTestimonyClient $llm
    ) {}

    public function processDrafts(int $limit): int
    {
        $arts = Article::where('template', 'generic_article')
            ->where(function ($query) {
                $query->whereNull('processed_testimony')
                    ->orWhere('processed_testimony', 'testimonial_draft');
            })
            ->limit($limit)
            ->get();

        $count = 0;

        foreach ($arts as $article) {
            // dd($article->slug);

            $content = $article->content ?? [];
            $blocks = $content['blocks'] ?? [];
            $drafts = $this->extractor->extractDraftBlocks($blocks);

            if (!$drafts) continue;

            $prompt = PromptBuilder::buildPrompt($drafts, $article);
            $payload = ['testimonials' => $drafts];

            $raw = $this->llm->correct($prompt, $payload);
            $corrected = $this->decodeJsonLines($raw);
            if (!$corrected) continue;

            $newBlocks = $this->transformer->applyCorrections($blocks, $corrected);

            // Atualiza apenas content.blocks
            $content['blocks'] = $newBlocks;
            $article->content = $content;

            $article->processed_testimony = 'testimony_final';
            $article->save();

            $count++;
        }
        return $count;
    }

    private function decodeJsonLines(string $raw): array
    {
        $out = [];
        foreach (explode("\n", trim($raw)) as $line) {
            $j = json_decode($line, true);
            if ($j) $out[] = $j;
        }
        return $out;
    }
}
