<?php

namespace App\ContentGeneration\WhenToChangeTires\Infrastructure\Services;

use App\ContentGeneration\WhenToChangeTires\Domain\ValueObjects\TireChangeContent;
use App\ContentGeneration\WhenToChangeTires\Domain\ValueObjects\VehicleData;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ArticleJsonStorageService
{
    protected string $storagePath = 'articles/when-to-change-tires';

    /**
     * Salvar artigo como JSON
     */
    public function saveArticleJson(TireChangeContent $content): string
    {
        $filename = $content->slug . '.json';
        $fullPath = $this->storagePath . '/' . $filename;

        // Criar estrutura JSON completa
        $jsonData = $content->toJsonStructure();

        // Salvar arquivo
        $jsonString = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        Storage::put($fullPath, $jsonString);

        Log::info("Artigo JSON salvo: {$fullPath}");

        return $fullPath;
    }

    /**
     * Verificar se artigo já existe
     */
    public function articleExists(string $slug): bool
    {
        $filename = $slug . '.json';
        $fullPath = $this->storagePath . '/' . $filename;
        
        return Storage::exists($fullPath);
    }

    /**
     * Carregar artigo JSON
     */
    public function loadArticleJson(string $slug): ?array
    {
        $filename = $slug . '.json';
        $fullPath = $this->storagePath . '/' . $filename;

        if (!Storage::exists($fullPath)) {
            return null;
        }

        $jsonContent = Storage::get($fullPath);
        return json_decode($jsonContent, true);
    }

    /**
     * Listar todos os artigos
     */
    public function listArticles(): array
    {
        $files = Storage::files($this->storagePath);
        $articles = [];

        foreach ($files as $file) {
            if (str_ends_with($file, '.json')) {
                $content = Storage::get($file);
                $data = json_decode($content, true);
                
                if ($data) {
                    $articles[] = [
                        'slug' => basename($file, '.json'),
                        'title' => $data['title'] ?? 'Sem título',
                        'make' => $data['vehicle_info']['make'] ?? '',
                        'model' => $data['vehicle_info']['model'] ?? '',
                        'year' => $data['vehicle_info']['year'] ?? '',
                        'created_at' => $data['created_at'] ?? '',
                        'word_count' => $data['metadata']['word_count'] ?? 0,
                        'file_path' => $file
                    ];
                }
            }
        }

        return $articles;
    }

    /**
     * Criar estrutura de diretórios se necessário
     */
    public function ensureDirectoryExists(): void
    {
        if (!Storage::exists($this->storagePath)) {
            Storage::makeDirectory($this->storagePath);
        }
    }

    /**
     * Obter estatísticas dos artigos salvos
     */
    public function getStorageStatistics(): array
    {
        $articles = $this->listArticles();
        
        $stats = [
            'total_articles' => count($articles),
            'by_make' => [],
            'by_year' => [],
            'total_words' => 0,
            'latest_created' => null,
            'storage_size' => 0
        ];

        foreach ($articles as $article) {
            // Contar por marca
            $make = $article['make'];
            $stats['by_make'][$make] = ($stats['by_make'][$make] ?? 0) + 1;

            // Contar por ano
            $year = $article['year'];
            $stats['by_year'][$year] = ($stats['by_year'][$year] ?? 0) + 1;

            // Somar palavras
            $stats['total_words'] += $article['word_count'];

            // Verificar mais recente
            if (!$stats['latest_created'] || $article['created_at'] > $stats['latest_created']) {
                $stats['latest_created'] = $article['created_at'];
            }

            // Calcular tamanho do arquivo
            if (Storage::exists($article['file_path'])) {
                $stats['storage_size'] += Storage::size($article['file_path']);
            }
        }

        // Ordenar estatísticas
        arsort($stats['by_make']);
        arsort($stats['by_year']);

        // Converter tamanho para formato legível
        $stats['storage_size_formatted'] = $this->formatBytes($stats['storage_size']);

        return $stats;
    }

    /**
     * Formatar bytes em formato legível
     */
    protected function formatBytes(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        
        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }

    /**
     * Exportar lote de artigos
     */
    public function exportBatch(array $slugs, string $exportPath = 'exports/tire-articles'): string
    {
        $this->ensureDirectoryExists();
        
        $exportData = [
            'exported_at' => now()->toISOString(),
            'total_articles' => count($slugs),
            'articles' => []
        ];

        foreach ($slugs as $slug) {
            $articleData = $this->loadArticleJson($slug);
            if ($articleData) {
                $exportData['articles'][] = $articleData;
            }
        }

        $exportFilename = 'tire-articles-export-' . date('Y-m-d-H-i-s') . '.json';
        $exportFullPath = $exportPath . '/' . $exportFilename;

        Storage::put($exportFullPath, json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        Log::info("Lote exportado: {$exportFullPath} com " . count($exportData['articles']) . " artigos");

        return $exportFullPath;
    }
}
