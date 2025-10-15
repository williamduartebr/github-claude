<?php

namespace Src\AutoInfoCenter\Infrastructure\Services;

use Illuminate\Support\Facades\File;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Carbon\Carbon;

/**
 * MockArticleService - VERSÃO 2.0
 * 
 * Suporta DOIS formatos de mock:
 * 1. FORMATO ANTIGO: tire_calibration (content direto)
 * 2. FORMATO NOVO: generic_article (metadata.content_blocks)
 * 
 * @author Claude Sonnet 4
 * @version 2.0 - Dual Format Support
 */
class MockArticleService
{
    private const MOCK_PATH = 'database/mocks/articles/';
    
    public function getMockArticle(string $filename): ?Article
    {
        $filePath = base_path(self::MOCK_PATH . $filename);
        
        if (!File::exists($filePath)) {
            \Log::warning('Mock file not found', ['path' => $filePath]);
            return null;
        }
        
        $jsonData = json_decode(File::get($filePath), true);
        
        if (!$jsonData) {
            \Log::error('Invalid JSON in mock file', ['file' => $filename]);
            return null;
        }
        
        // Converte JSON para objeto Article simulado
        return $this->convertToArticle($jsonData);
    }
    
    public function getAllMockArticles(): array
    {
        $mockFiles = [
            // Tire Calibration (formato antigo)
            'ideal-tire-pressure-honda-civic-2020.json',
            'ideal-tire-pressure-yamaha-mt03-2019.json', 
            'tire-pressure-guide-toyota-corolla-2021.json',
            
            // Generic Articles (formato novo)
            'generic-oil-misturei-5w30-5w40.json',
            'generic-spark-plug-comum-vs-iridium.json',
            'generic-clutch-kit-completo-vs-pecas.json',
        ];
        
        $articles = [];
        foreach ($mockFiles as $file) {
            if ($article = $this->getMockArticle($file)) {
                $articles[] = $article;
            }
        }
        
        return $articles;
    }
    
    /**
     * Converte dados JSON para objeto Article
     * 
     * Suporta DOIS formatos:
     * - ANTIGO: tire_calibration com 'content' direto
     * - NOVO: generic_article com 'metadata.content_blocks'
     */
    private function convertToArticle(array $data): Article
    {
        $article = new Article();
        
        // ===== CAMPOS BÁSICOS =====
        $article->title = $data['title'] ?? 'Sem Título';
        $article->slug = $data['slug'] ?? 'sem-slug'; 
        $article->template = $data['template'] ?? 'generic_article';
        $article->status = $data['status'] ?? 'published';
        
        // ===== CONTENT - DUAL FORMAT SUPPORT =====
        // Formato ANTIGO: 'content' direto
        // Formato NOVO: 'metadata.content_blocks'
        if (isset($data['content'])) {
            // FORMATO ANTIGO (tire_calibration)
            $article->content = $data['content'];
        } elseif (isset($data['metadata']['content_blocks'])) {
            // FORMATO NOVO (generic_article)
            // Content não existe, mas metadata sim
            $article->content = null; // Não há content direto
        } else {
            // Fallback
            $article->content = null;
        }
        
        // ===== CATEGORIA =====
        $article->category_id = $data['category_id'] ?? null;
        $article->category_name = $data['category_name'] ?? '';
        $article->category_slug = $data['category_slug'] ?? '';
        
        // ===== DATAS =====
        $article->created_at = isset($data['created_at']) 
            ? Carbon::parse($data['created_at']) 
            : now();
        $article->updated_at = isset($data['updated_at']) 
            ? Carbon::parse($data['updated_at']) 
            : now();
        $article->published_at = isset($data['published_at']) 
            ? Carbon::parse($data['published_at']) 
            : now();
        
        // ===== AUTOR =====
        $article->author = $data['author'] ?? [
            'name' => 'Equipe Editorial',
            'bio' => 'Especialistas em conteúdo automotivo'
        ];
        
        // ===== ID SIMULADO =====
        $article->_id = $data['id'] ?? 'mock_' . uniqid();
        
        // ===== ATRIBUTOS ESPECÍFICOS POR FORMATO =====
        
        // FORMATO ANTIGO: extracted_entities, vehicle_data diretos
        if (isset($data['extracted_entities'])) {
            $article->setAttribute('extracted_entities', $data['extracted_entities']);
        }
        
        if (isset($data['vehicle_data'])) {
            $article->setAttribute('vehicle_data', $data['vehicle_data']);
        }
        
        // SEO DATA (ambos formatos)
        $article->setAttribute('seo_data', $data['seo_data'] ?? []);
        
        // ===== METADATA (FORMATO NOVO - CRITICAL) =====
        // Generic articles usam metadata.content_blocks
        if (isset($data['metadata'])) {
            $article->setAttribute('metadata', $data['metadata']);
            
            // Debug metadata structure
            \Log::info('Mock Article Metadata Loaded', [
                'slug' => $article->slug,
                'has_content_blocks' => isset($data['metadata']['content_blocks']),
                'blocks_count' => count($data['metadata']['content_blocks'] ?? []),
                'article_topic' => $data['metadata']['article_metadata']['article_topic'] ?? 'not_set'
            ]);
        }
        
        // ===== OUTROS CAMPOS OPCIONAIS =====
        $article->setAttribute('original_post_id', $data['original_post_id'] ?? null);
        $article->setAttribute('formated_updated_at', $data['formated_updated_at'] ?? null);
        $article->setAttribute('canonical_url', $data['canonical_url'] ?? null);
        
        // ===== DEBUG LOG =====
        \Log::info('Mock Article Converted', [
            'slug' => $article->slug,
            'template' => $article->template,
            'format' => isset($data['content']) ? 'ANTIGO (content)' : 'NOVO (metadata)',
            'has_metadata' => isset($data['metadata']),
            'has_content' => isset($data['content']),
            'has_content_blocks' => isset($data['metadata']['content_blocks'])
        ]);
        
        return $article;
    }
    
    /**
     * Identifica o formato do mock
     * 
     * @param array $data
     * @return string 'tire_calibration' | 'generic_article'
     */
    private function identifyFormat(array $data): string
    {
        if (isset($data['metadata']['content_blocks'])) {
            return 'generic_article';
        }
        
        if (isset($data['content'])) {
            return 'tire_calibration';
        }
        
        return 'unknown';
    }
    
    /**
     * Valida estrutura do mock
     * 
     * @param array $data
     * @return bool
     */
    private function validateMockStructure(array $data): bool
    {
        $requiredFields = ['title', 'slug', 'template'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                \Log::error('Mock validation failed: missing field', [
                    'field' => $field,
                    'slug' => $data['slug'] ?? 'unknown'
                ]);
                return false;
            }
        }
        
        return true;
    }
}