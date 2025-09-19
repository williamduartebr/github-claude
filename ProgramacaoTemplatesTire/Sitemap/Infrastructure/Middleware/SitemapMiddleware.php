<?php

namespace Src\Sitemap\Infrastructure\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SitemapMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Aplicar apenas para rotas de sitemap
        if ($this->isSitemapRequest($request)) {
            $this->applySitemapOptimizations($response, $request);
        }
        
        return $response;
    }
    
    /**
     * Verifica se é uma requisição de sitemap
     */
    private function isSitemapRequest(Request $request): bool
    {
        $path = $request->path();
        
        return str_ends_with($path, '.xml') && 
               (str_contains($path, 'sitemap') || 
                str_starts_with($path, 'sitemap'));
    }
    
    /**
     * Aplica otimizações para sitemaps
     */
    private function applySitemapOptimizations(Response $response, Request $request): void
    {
        // Headers de cache
        $response->headers->set('Cache-Control', 'public, max-age=3600, must-revalidate');
        $response->headers->set('Vary', 'Accept-Encoding');
        
        // Content-Type correto
        if (!$response->headers->has('Content-Type')) {
            $response->headers->set('Content-Type', 'application/xml; charset=utf-8');
        }
        
        // Compressão GZIP se suportada
        if ($this->supportsGzip($request) && !$response->headers->has('Content-Encoding')) {
            $content = $response->getContent();
            if ($content && strlen($content) > 1024) { // Só comprimir se > 1KB
                $compressed = gzencode($content, 6);
                if ($compressed !== false) {
                    $response->setContent($compressed);
                    $response->headers->set('Content-Encoding', 'gzip');
                    $response->headers->set('Content-Length', strlen($compressed));
                }
            }
        }
        
        // Headers de segurança básicos
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        
        // ETag baseado no conteúdo
        if (!$response->headers->has('ETag')) {
            $etag = md5($response->getContent());
            $response->headers->set('ETag', '"' . $etag . '"');
            
            // Verificar If-None-Match
            if ($request->headers->get('If-None-Match') === '"' . $etag . '"') {
                $response->setStatusCode(304);
                $response->setContent('');
            }
        }
    }
    
    /**
     * Verifica se o cliente suporta GZIP
     */
    private function supportsGzip(Request $request): bool
    {
        $acceptEncoding = $request->headers->get('Accept-Encoding', '');
        return str_contains(strtolower($acceptEncoding), 'gzip');
    }
}