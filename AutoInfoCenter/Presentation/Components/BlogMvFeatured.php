<?php

namespace Src\AutoInfoCenter\Presentation\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Componente Destaque do Blog
 * 
 * Este componente é responsável por buscar e exibir um post destacado
 * aleatoriamente entre os 3 mais recentes do blog do MercadoVeículos.
 * Utiliza a API REST do WordPress para obter os dados e
 * possui sistema de cache para otimizar a performance.
 * 
 * @package App\View\Components
 */
class BlogMvFeatured extends Component
{
    /**
     * Post destacado
     *
     * @var array|null
     */
    public $featuredPost = null;

    /**
     * Tempo de cache em segundos
     * 
     * @var int
     */
    private const CACHE_TIME = 3600; // 1 hora

    /**
     * Quantidade de posts a serem obtidos para selecionar o destaque
     * 
     * @var int
     */
    private const POSTS_COUNT = 3;

    /**
     * URL padrão para imagem quando não encontrada
     * 
     * @var string
     */
    private const DEFAULT_IMAGE = 'https://mercadoveiculos.com/blog/wp-content/uploads/2024/04/cropped-logo-32x32.png';

    /**
     * Inicializa o componente
     */
    public function __construct()
    {
        $this->featuredPost = $this->getRandomFeatured();
    }

    /**
     * Obtém um post destacado aleatório
     *
     * @return array|null
     */
    private function getRandomFeatured(): ?array
    {
        $posts = Cache::remember('blog_featured_posts_v1', self::CACHE_TIME, function () {
            try {
                // Tenta obter via API do WordPress
                $response = Http::get('https://mercadoveiculos.com/blog/wp-json/wp/v2/posts', [
                    'per_page' => self::POSTS_COUNT,
                    'orderby' => 'date',
                    'order' => 'desc',
                    '_embed' => 1 // Inclui mídia e outros dados relacionados
                ]);

                if ($response->successful()) {
                    return $this->processWordPressPosts($response->json());
                }

                return $this->processRSSFeed();
            } catch (\Exception $e) {
                Log::error('Erro ao carregar posts do blog para destaque', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return [];
            }
        });

        if (empty($posts)) {
            return null;
        }

        // Seleciona um post aleatório entre os disponíveis
        return $posts[array_rand($posts)];
    }

    /**
     * Processa os posts obtidos via WordPress API
     *
     * @param array $wpPosts Posts do WordPress
     * @return array Posts processados
     */
    private function processWordPressPosts(array $wpPosts): array
    {
        $posts = [];

        foreach ($wpPosts as $post) {
            $imageUrl = $this->getWordPressImage($post);

            // Obtém o conteúdo completo para destaque
            $content = strip_tags(html_entity_decode($post['content']['rendered']));
            $excerpt = strip_tags(html_entity_decode($post['excerpt']['rendered']));

            $posts[] = [
                'title' => html_entity_decode($post['title']['rendered']),
                'link' => $post['link'],
                'description' => !empty($excerpt) ? $excerpt : (mb_strlen($content) > 160 ? mb_substr($content, 0, 157) . '...' : $content),
                'imageUrl' => $imageUrl
            ];
        }

        return $posts;
    }

    /**
     * Obtém a URL da imagem de um post do WordPress
     *
     * @param array $post Post do WordPress
     * @return string URL da imagem
     */
    private function getWordPressImage($post): string
    {
        try {
            // Verifica imagem em destaque
            if (!empty($post['_embedded']['wp:featuredmedia'][0])) {
                $media = $post['_embedded']['wp:featuredmedia'][0];

                // Para o destaque, queremos uma imagem maior
                if (!empty($media['media_details']['sizes']['large'])) {
                    return $media['media_details']['sizes']['large']['source_url'];
                }

                // Ou usa a URL completa
                return $media['source_url'];
            }

            // Se não tem imagem em destaque, procura no conteúdo
            if (!empty($post['content']['rendered'])) {
                $doc = new \DOMDocument();
                @$doc->loadHTML(mb_convert_encoding($post['content']['rendered'], 'HTML-ENTITIES', 'UTF-8'));

                $images = $doc->getElementsByTagName('img');
                foreach ($images as $img) {
                    $src = $img->getAttribute('src');
                    if ($src && !strpos($src, 'logo')) {
                        // Tenta gerar versão grande
                        return $this->getImageVersion($src, '800x600');
                    }
                }
            }

            return self::DEFAULT_IMAGE;
        } catch (\Exception $e) {
            Log::error('Erro ao obter imagem do WordPress para destaque', [
                'error' => $e->getMessage(),
                'post_id' => $post['id'] ?? 'unknown'
            ]);
            return self::DEFAULT_IMAGE;
        }
    }

    /**
     * Gera URL para versão específica de uma imagem
     *
     * @param string $url URL original da imagem
     * @param string $size Tamanho desejado (ex: '800x600')
     * @return string URL da versão da imagem
     */
    private function getImageVersion(string $url, string $size): string
    {
        $pathinfo = pathinfo($url);
        if (!isset($pathinfo['dirname'], $pathinfo['filename'], $pathinfo['extension'])) {
            return $url;
        }

        // Remove qualquer dimensão existente
        $filename = preg_replace('/-\d+x\d+$/', '', $pathinfo['filename']);

        // Cria a versão com o tamanho desejado
        return $pathinfo['dirname'] . '/' . $filename . '-' . $size . '.' . $pathinfo['extension'];
    }

    /**
     * Processa o feed RSS (método de fallback)
     *
     * @return array Posts processados
     */
    private function processRSSFeed(): array
    {
        try {
            $rss = simplexml_load_file('https://mercadoveiculos.com/blog/feed/');
            if (!$rss) {
                Log::error('Não foi possível carregar o feed RSS para destaque');
                return [];
            }

            $posts = [];
            $count = 0;

            foreach ($rss->channel->item as $item) {
                if ($count >= self::POSTS_COUNT) break;

                $content = $item->children('content', true);
                $description = strip_tags((string)$item->description);

                $posts[] = [
                    'title' => (string)$item->title,
                    'link' => (string)$item->link,
                    'description' => $description,
                    'imageUrl' => self::DEFAULT_IMAGE // Usa imagem padrão no fallback
                ];

                $count++;
            }

            return $posts;
        } catch (\Exception $e) {
            Log::error('Erro ao processar RSS para destaque', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Renderiza o componente
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render(): View|Closure|string
    {
        return view('auto-info-center::components.blog-mv-featured');
    }
}
