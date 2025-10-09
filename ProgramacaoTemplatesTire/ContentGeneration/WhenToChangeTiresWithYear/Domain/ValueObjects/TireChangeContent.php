<?php

namespace Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\ValueObjects;

class TireChangeContent
{
    public function __construct(
        public readonly string $title,
        public readonly string $slug,
        public readonly string $template,
        public readonly array $content,
        public readonly array $extractedEntities,
        public readonly array $seoData,
        public readonly array $metadata,
        public readonly array $tags,
        public readonly array $relatedTopics,
        public readonly array $vehicleInfo,
        public readonly array $filterData
    ) {}

    public function toJsonStructure(): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'template' => $this->template,
            'category_id' => 1,
            'category_name' => 'Quando Trocar Pneus',
            'category_slug' => 'quando-trocar-pneus',
            'content' => $this->content,
            'extracted_entities' => $this->extractedEntities,
            'seo_data' => $this->seoData,
            'metadata' => $this->metadata,
            'tags' => $this->tags,
            'related_topics' => $this->relatedTopics,
            'status' => 'generated',
            'vehicle_info' => $this->vehicleInfo,
            'filter_data' => $this->filterData,
            'author' => [
                'name' => 'Sistema Automático',
                'bio' => 'Conteúdo gerado automaticamente com dados técnicos precisos'
            ],
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString()
        ];
    }

    /**
     * Validação corrigida - mais flexível
     */
    public function isValid(): bool
    {
        // Validações básicas obrigatórias
        if (empty($this->title)) {
            return false;
        }

        if (empty($this->slug)) {
            return false;
        }

        if (empty($this->content)) {
            return false;
        }

        // Verificar se content tem pelo menos introdução
        if (!isset($this->content['introducao']) || empty($this->content['introducao'])) {
            return false;
        }

        // Verificar se tem pelo menos 3 seções principais
        $requiredSections = ['introducao', 'sintomas_desgaste', 'fatores_durabilidade'];
        $sectionsFound = 0;

        foreach ($requiredSections as $section) {
            if (isset($this->content[$section]) && !empty($this->content[$section])) {
                $sectionsFound++;
            }
        }

        // Pelo menos 2 das 3 seções obrigatórias
        if ($sectionsFound < 2) {
            return false;
        }

        // Verificar dados SEO básicos
        if (empty($this->seoData['meta_description'])) {
            return false;
        }

        // Verificar informações do veículo
        if (empty($this->vehicleInfo['make']) || empty($this->vehicleInfo['model'])) {
            return false;
        }

        return true;
    }

    public function getWordCount(): int
    {
        $text = '';
        $this->extractTextFromContent($this->content, $text);
        return str_word_count($text);
    }

    private function extractTextFromContent($content, &$text): void
    {
        if (is_string($content)) {
            $text .= ' ' . strip_tags($content);
        } elseif (is_array($content)) {
            foreach ($content as $value) {
                $this->extractTextFromContent($value, $text);
            }
        }
    }

    /**
     * Método para debug - retorna detalhes da validação
     */
    public function getValidationDetails(): array
    {
        $details = [
            'has_title' => !empty($this->title),
            'has_slug' => !empty($this->slug),
            'has_content' => !empty($this->content),
            'has_introducao' => isset($this->content['introducao']) && !empty($this->content['introducao']),
            'has_meta_description' => !empty($this->seoData['meta_description']),
            'has_vehicle_info' => !empty($this->vehicleInfo['make']) && !empty($this->vehicleInfo['model']),
            'content_sections' => array_keys($this->content),
            'word_count' => $this->getWordCount()
        ];

        $details['is_valid'] = $this->isValid();

        return $details;
    }
}
