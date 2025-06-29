<?php

namespace Src\AutoInfoCenter\Domain\Services;

use Src\AutoInfoCenter\Domain\Repositories\ArticleRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class ArticleService
{
   public function __construct(
       private ArticleRepositoryInterface $articleRepository
   ) {}

   /**
    * Encontra um artigo pela sua slug
    *
    * @param string $slug
    * @return \Src\AutoInfoCenter\Domain\Eloquent\Article|null
    */
   public function findBySlug(string $slug)
   {
       try {
           $article = $this->articleRepository->findBySlug($slug);
           
           if (!$article) {
               Log::info("Artigo não encontrado com slug: {$slug}");
               return null;
           }
           
           if ($article->status !== 'published') {
               Log::info("Tentativa de acesso a artigo não publicado: {$slug}");
               return null;
           }
           
           return $article;
       } catch (ModelNotFoundException $e) {
           Log::error("Erro ao buscar artigo com slug {$slug}: " . $e->getMessage());
           return null;
       } catch (\Exception $e) {
           Log::error("Erro inesperado ao buscar artigo com slug {$slug}: " . $e->getMessage());
           return null;
       }
   }

   /**
    * Encontra artigos relacionados a um artigo específico
    *
    * @param \Src\AutoInfoCenter\Domain\Eloquent\Article $article
    * @param int $limit
    * @return \Illuminate\Database\Eloquent\Collection
    */
   public function findRelatedArticles($article, int $limit = 4)
   {
       try {
           return $this->articleRepository->findRelated($article, $limit);
       } catch (\Exception $e) {
           Log::error("Erro ao buscar artigos relacionados: " . $e->getMessage());
           return collect([]);
       }
   }
   
   /**
    * Atualiza contagem de visualizações do artigo
    *
    * @param \Src\AutoInfoCenter\Domain\Eloquent\Article $article
    * @return bool
    */
   public function incrementViewCount($article)
   {
       try {
           return $this->articleRepository->incrementViewCount($article);
       } catch (\Exception $e) {
           Log::error("Erro ao incrementar contagem de visualizações: " . $e->getMessage());
           return false;
       }
   }
}