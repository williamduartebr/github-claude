<?php

namespace Src\ArticleGenerator\Infrastructure\Services;

use Src\AutoInfoCenter\Domain\Eloquent\Article;

class RelatedArticlesService
{
    /**
     * Atualiza os artigos relacionados para todos os artigos
     *
     * @return int Número de artigos atualizados
     */
    public function updateAllRelatedArticles()
    {
        $articles = Article::where('status', 'published')->get();
        
        $updatedCount = 0;
        
        foreach ($articles as $article) {
            if ($this->updateArticleRelatedTopics($article)) {
                $updatedCount++;
            }
        }
        
        return $updatedCount;
    }
    
    /**
     * Atualiza os tópicos relacionados para um artigo específico
     *
     * @param Article $article
     * @return bool
     */
    public function updateArticleRelatedTopics(Article $article)
    {
        // Verificar se já existem tópicos relacionados com slugs válidos
        if (!empty($article->related_topics) && $this->validateRelatedTopics($article->related_topics)) {
            // Verificar se os artigos relacionados existem
            $this->validateAndUpdateRelatedTopicSlugs($article);
            return true;
        }
        
        // Buscar tópicos a partir do modelo e das tags existentes
        $relatedTopics = $this->findRelatedTopicsByTags($article);
        
        if (!empty($relatedTopics)) {
            $article->related_topics = $relatedTopics;
            $article->save();
            return true;
        }
        
        return false;
    }
    
    /**
     * Valida se os tópicos relacionados têm slugs corretos
     *
     * @param array $relatedTopics
     * @return bool
     */
    private function validateRelatedTopics($relatedTopics)
    {
        if (empty($relatedTopics) || !is_array($relatedTopics)) {
            return false;
        }
        
        foreach ($relatedTopics as $topic) {
            if (empty($topic['slug']) || empty($topic['title'])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Valida e atualiza os slugs dos tópicos relacionados
     *
     * @param Article $article
     * @return void
     */
    private function validateAndUpdateRelatedTopicSlugs(Article $article)
    {
        $updated = false;
        $relatedTopics = $article->related_topics;
        
        foreach ($relatedTopics as &$topic) {
            // Verificar se o slug existe
            $exists = Article::where('slug', $topic['slug'])->exists();
            
            // Se não existir, tentar encontrar por título
            if (!$exists) {
                $matchedArticle = Article::where('title', 'like', '%' . $topic['title'] . '%')
                    ->where('status', 'published')
                    ->first();
                
                if ($matchedArticle) {
                    $topic['slug'] = $matchedArticle->slug;
                    $updated = true;
                }
            }
        }
        
        if ($updated) {
            $article->related_topics = $relatedTopics;
            $article->save();
        }
    }
    
    /**
     * Encontra tópicos relacionados com base nas tags e entidades
     *
     * @param Article $article
     * @param int $limit
     * @return array
     */
    private function findRelatedTopicsByTags(Article $article, $limit = 4)
    {
        $query = Article::where('_id', '!=', $article->_id)
            ->where('status', 'published');
        
        // Buscar por tags
        if (!empty($article->tags)) {
            $query->where(function($q) use ($article) {
                $q->whereIn('tags', $article->tags);
            });
        }
        
        // Buscar por entidades extraídas
        if (!empty($article->extracted_entities)) {
            $relevantEntities = ['marca', 'modelo', 'categoria', 'tipo_veiculo'];
            
            foreach ($relevantEntities as $entity) {
                if (!empty($article->extracted_entities[$entity])) {
                    $query->orWhere('extracted_entities.' . $entity, $article->extracted_entities[$entity]);
                }
            }
        }
        
        // Buscar artigos da mesma categoria
        $query->orWhere('category_slug', $article->category_slug);
        
        // Obter resultados
        $relatedArticles = $query->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get(['title', 'slug']);
        
        // Transformar em formato adequado para related_topics
        $topics = [];
        
        foreach ($relatedArticles as $related) {
            $topics[] = [
                'title' => $related->title,
                'slug' => $related->slug,
                'icon' => null
            ];
        }
        
        return $topics;
    }
}
