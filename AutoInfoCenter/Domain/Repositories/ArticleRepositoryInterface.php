<?php

namespace Src\AutoInfoCenter\Domain\Repositories;

use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Illuminate\Database\Eloquent\Collection;

interface ArticleRepositoryInterface
{
   /**
    * Encontra um artigo pela sua slug
    *
    * @param string $slug
    * @return Article|null
    */
   public function findBySlug(string $slug): ?Article;
   
   /**
    * Encontra artigos relacionados a um artigo específico
    *
    * @param Article $article
    * @param int $limit
    * @return Collection
    */
   public function findRelated(Article $article, int $limit = 4): Collection;
   
   /**
    * Incrementa a contagem de visualizações de um artigo
    *
    * @param Article $article
    * @return bool
    */
   public function incrementViewCount(Article $article): bool;
   
   /**
    * Busca artigos por categoria
    *
    * @param string $categorySlug
    * @param int $limit
    * @param int $offset
    * @return Collection
    */
   public function findByCategory(string $categorySlug, int $limit = 10, int $offset = 0): Collection;
   
   /**
    * Conta total de artigos por categoria
    *
    * @param string $categorySlug
    * @return int
    */
   public function countByCategory(string $categorySlug): int;
   
   /**
    * Busca artigos por categoria e veículo
    *
    * @param string $categorySlug
    * @param string $make
    * @param string|null $model
    * @param string|null $year
    * @param int $limit
    * @param int $offset
    * @return Collection
    */
   public function findByCategoryAndVehicle(string $categorySlug, string $make, ?string $model = null, ?string $year = null, int $limit = 10, int $offset = 0): Collection;
   
   /**
    * Conta artigos por categoria e veículo
    *
    * @param string $categorySlug
    * @param string $make
    * @param string|null $model
    * @param string|null $year
    * @return int
    */
   public function countByCategoryAndVehicle(string $categorySlug, string $make, ?string $model = null, ?string $year = null): int;
   
   /**
    * Busca artigos para um veículo específico
    *
    * @param string $make Marca do veículo
    * @param string|null $model Modelo do veículo (opcional)
    * @param string|null $year Ano do veículo (opcional)
    * @param int $limit
    * @return Collection
    */
   public function findByVehicle(string $make, ?string $model = null, ?string $year = null, int $limit = 10): Collection;
   
   /**
    * Busca artigos por termos de pesquisa
    *
    * @param string $searchTerm
    * @param int $limit
    * @return Collection
    */
   public function search(string $searchTerm, int $limit = 10): Collection;
   
   /**
    * Obtém artigos populares baseados em visualizações
    *
    * @param int $limit
    * @return Collection
    */
   public function getPopular(int $limit = 5): Collection;
   
   /**
    * Obtém artigos mais recentes
    *
    * @param int $limit
    * @return Collection
    */
   public function getRecent(int $limit = 6): Collection;
}