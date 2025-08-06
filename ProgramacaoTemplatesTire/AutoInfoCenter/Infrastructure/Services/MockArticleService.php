<?php
namespace Src\AutoInfoCenter\Infrastructure\Services;

use Illuminate\Support\Facades\File;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Carbon\Carbon;

class MockArticleService
{
    private const MOCK_PATH = 'database/mocks/articles/';
    
    public function getMockArticle(string $filename): ?Article
    {
        $filePath = base_path(self::MOCK_PATH . $filename);
        
        if (!File::exists($filePath)) {
            return null;
        }
        
        $jsonData = json_decode(File::get($filePath), true);
        
        // Converte JSON para objeto Article simulado
        return $this->convertToArticle($jsonData);
    }
    
    public function getAllMockArticles(): array
    {
        $mockFiles = [
            'ideal-tire-pressure-honda-civic-2020.json',
            'ideal-tire-pressure-yamaha-mt03-2019.json', 
            'tire-pressure-guide-toyota-corolla-2021.json',
            'tire-pressure-guide-honda-cb600f-2018.json'
        ];
        
        $articles = [];
        foreach ($mockFiles as $file) {
            if ($article = $this->getMockArticle($file)) {
                $articles[] = $article;
            }
        }
        
        return $articles;
    }
    
    private function convertToArticle(array $data): Article
    {
        $article = new Article();
        
        // ✅ CAMPOS BÁSICOS
        $article->title = $data['title'];
        $article->slug = $data['slug']; 
        $article->template = $data['template'];
        $article->content = $data['content'];
        $article->status = $data['status'] ?? 'published';
        
        // 🚨 DEBUG: FORÇAR ENTIDADES EXTRAÍDAS
        $extractedEntities = $data['extracted_entities'] ?? [];
        
        // ✅ FORÇA DEFINIÇÃO DE ATRIBUTOS
        $article->setAttribute('extracted_entities', $extractedEntities);
        $article->setAttribute('seo_data', $data['seo_data'] ?? []);
        $article->setAttribute('vehicle_data', $data['vehicle_data'] ?? []);
        
        // ✅ CATEGORIA
        $article->category_id = $data['category_id'] ?? null;
        $article->category_name = $data['category_name'] ?? '';
        $article->category_slug = $data['category_slug'] ?? '';
        
        // ✅ DATAS
        $article->created_at = isset($data['created_at']) 
            ? Carbon::parse($data['created_at']) 
            : now();
        $article->updated_at = isset($data['updated_at']) 
            ? Carbon::parse($data['updated_at']) 
            : now();
        $article->published_at = isset($data['published_at']) 
            ? Carbon::parse($data['published_at']) 
            : now();
        
        // ✅ AUTOR (mock)
        $article->author = [
            'name' => 'Equipe Editorial',
            'bio' => 'Especialistas em conteúdo automotivo'
        ];
        
        // ✅ ID SIMULADO
        $article->_id = $data['id'] ?? 'mock_' . uniqid();
        
        // ✅ ATRIBUTOS DINÂMICOS - Para compatibilidade com ViewModels
        $article->setAttribute('original_post_id', $data['original_post_id'] ?? null);
        $article->setAttribute('metadata', $data['metadata'] ?? []);
        
        // 🔍 DEBUG: Verificar se foi definido corretamente
        \Log::info('Article Debug:', [
            'extracted_entities' => $article->extracted_entities,
            'marca_exists' => isset($article->extracted_entities['marca']),
            'marca_value' => $article->extracted_entities['marca'] ?? 'NOT_FOUND'
        ]);
        
        return $article;
    }
}