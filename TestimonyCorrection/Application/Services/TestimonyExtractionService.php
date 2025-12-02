<?php

namespace Src\TestimonyCorrection\Application\Services;

class TestimonyExtractionService
{
    public function extractDraftBlocks(array $blocks): array
    {
        return array_values(array_filter($blocks, function ($b) {

            $type = $b['block_type'] ?? '';

            // → novos artigos (draft)
            if ($type === 'testimonial-draft') {
                return true;
            }

            // → artigos recentes pré-draft (30–45 dias)
            if ($type === 'testimonial') {
                return true;
            }

            // → NÃO pegar depoimentos já corrigidos
            if ($type === 'testimony') {
                return false;
            }

            return false;
        }));
    }
}
