<?php

namespace Src\GuideDataCenter\Domain\Repositories\Contracts;

use Src\GuideDataCenter\Domain\Mongo\GuideCategory;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface GuideCategoryRepositoryInterface
 * 
 * Contrato para operações de repositório de categorias de guias
 */
interface GuideCategoryRepositoryInterface
{
    /**
     * Busca categoria por slug
     *
     * @param string $slug
     * @return GuideCategory|null
     */
    public function findBySlug(string $slug): ?GuideCategory;

    /**
     * Busca categoria por ID
     *
     * @param string $id
     * @return GuideCategory|null
     */
    public function findById(string $id): ?GuideCategory;

    /**
     * Lista todas as categorias ativas
     *
     * @return Collection
     */
    public function getAllActive(): Collection;

    /**
     * Lista todas as categorias ordenadas
     *
     * @return Collection
     */
    public function getAllOrdered(): Collection;

    /**
     * Cria uma nova categoria
     *
     * @param array $data
     * @return GuideCategory
     */
    public function createCategory(array $data): GuideCategory;

    /**
     * Atualiza uma categoria
     *
     * @param string $id
     * @param array $data
     * @return GuideCategory
     */
    public function updateCategory(string $id, array $data): GuideCategory;

    /**
     * Deleta uma categoria
     *
     * @param string $id
     * @return bool
     */
    public function deleteCategory(string $id): bool;

    /**
     * Busca categorias por termo
     *
     * @param string $term
     * @return Collection
     */
    public function search(string $term): Collection;

    /**
     * Conta total de categorias
     *
     * @return int
     */
    public function count(): int;

    /**
     * Atualiza ordem das categorias
     *
     * @param array $orderMap ['category_id' => order_number]
     * @return bool
     */
    public function updateOrder(array $orderMap): bool;
}
