<?php

namespace Src\Sitemap\Presentation\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Src\Sitemap\Domain\Services\SitemapService;

class SitemapController
{
    private SitemapService $sitemapService;
    
    public function __construct(SitemapService $sitemapService)
    {
        $this->sitemapService = $sitemapService;
    }
    
    /**
     * Retorna o sitemap index principal
     */
    public function index(): Response
    {
        $sitemapPath = 'sitemaps/sitemap.xml';
        
        if (!Storage::disk('public')->exists($sitemapPath)) {
            $this->sitemapService->generateAll();
        }
        
        $content = Storage::disk('public')->get($sitemapPath);
        
        return response($content, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
            'Last-Modified' => gmdate('D, d M Y H:i:s', Storage::disk('public')->lastModified($sitemapPath)) . ' GMT'
        ]);
    }
    
    /**
     * Retorna sitemaps específicos
     */
    public function show(string $filename): Response
    {
        $sitemapPath = "sitemaps/{$filename}";
        
        if (!Storage::disk('public')->exists($sitemapPath)) {
            abort(404, 'Sitemap não encontrado');
        }
        
        $content = Storage::disk('public')->get($sitemapPath);
        
        return response($content, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
            'Last-Modified' => gmdate('D, d M Y H:i:s', Storage::disk('public')->lastModified($sitemapPath)) . ' GMT'
        ]);
    }
    
    /**
     * API para regenerar sitemaps
     */
    public function regenerate(): \Illuminate\Http\JsonResponse
    {
        try {
            $this->sitemapService->clearCache();
            $results = $this->sitemapService->generateAll();
            
            return response()->json([
                'success' => true,
                'message' => 'Sitemaps regenerados com sucesso',
                'generated' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao regenerar sitemaps: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API para submeter aos motores de busca
     */
    public function submit(): \Illuminate\Http\JsonResponse
    {
        try {
            $results = $this->sitemapService->submitToSearchEngines();
            
            return response()->json([
                'success' => true,
                'message' => 'Sitemaps submetidos aos motores de busca',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao submeter sitemaps: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API para limpar cache
     */
    public function clearCache(): \Illuminate\Http\JsonResponse
    {
        try {
            $this->sitemapService->clearCache();
            
            return response()->json([
                'success' => true,
                'message' => 'Cache dos sitemaps limpo com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar cache: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API para status dos sitemaps
     */
    public function status(): \Illuminate\Http\JsonResponse
    {
        try {
            $sitemapFiles = Storage::disk('public')->files('sitemaps');
            $status = [];
            
            foreach ($sitemapFiles as $file) {
                if (str_ends_with($file, '.xml')) {
                    $filename = basename($file);
                    $status[] = [
                        'filename' => $filename,
                        'size' => Storage::disk('public')->size($file),
                        'last_modified' => Storage::disk('public')->lastModified($file),
                        'url' => url("storage/{$file}")
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'sitemaps' => $status,
                'total_files' => count($status)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter status: ' . $e->getMessage()
            ], 500);
        }
    }
}