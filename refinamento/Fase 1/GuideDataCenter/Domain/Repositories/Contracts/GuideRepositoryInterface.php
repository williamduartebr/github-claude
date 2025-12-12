<?php

namespace Src\GuideDataCenter\Domain\Repositories\Contracts;


use Src\GuideDataCenter\Domain\Mongo\Guide;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface GuideRepositoryInterface
 * 
 * Contrato para operações de repositório de guias
 */
interface GuideRepositoryInterface
{
    /**
     * Busca guia por slug
     *
     * @param string $slug
     * @return Guide|null
     */
    public function findBySlug(string $slug): ?Guide;

    /**
     * Busca guia por veículo específico
     *
     * @param string $makeSlug
     * @param string $modelSlug
     * @param int|null $year
     * @return Guide|null
     */
    public function findByVehicle(string $makeSlug, string $modelSlug, ?int $year = null): ?Guide;

    /**
     * Busca guias por categoria
     *
     * @param string $categorySlug
     * @param int $limit
     * @return Collection
     */
    public function findByCategory(string $categorySlug, int $limit = 50): Collection;

    /**
     * Lista guias por marca
     *
     * @param string $makeSlug
     * @param int $limit
     * @return Collection
     */
    public function listByMake(string $makeSlug, int $limit = 100): Collection;

    /**
     * Lista guias por modelo
     *
     * @param string $modelSlug
     * @param int $limit
     * @return Collection
     */
    public function listByModel(string $modelSlug, int $limit = 100): Collection;

    /**
     * Lista guias por template
     *
     * @param string $template
     * @param int $limit
     * @return Collection
     */
    public function listByTemplate(string $template, int $limit = 100): Collection;

    /**
     * Busca guias por termo
     *
     * @param string $term
     * @param int $limit
     * @return Collection
     */
    public function search(string $term, int $limit = 50): Collection;

    /**
     * Cria um novo guia
     *
     * @param array $data
     * @return Guide
     */
    public function createGuide(array $data): Guide;

    /**
     * Atualiza um guia existente
     *
     * @param string $id
     * @param array $data
     * @return Guide
     */
    public function updateGuide(string $id, array $data): Guide;

    /**
     * Deleta um guia
     *
     * @param string $id
     * @return bool
     */
    public function deleteGuide(string $id): bool;

    /**
     * Busca por ID
     *
     * @param string $id
     * @return Guide|null
     */
    public function findById(string $id): ?Guide;

    /**
     * Lista todos os guias com paginação
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15);

    /**
     * Busca guias por múltiplos filtros
     *
     * @param array $filters
     * @return Collection
     */
    public function findByFilters(array $filters): Collection;

    /**
     * Conta total de guias
     *
     * @return int
     */
    public function count(): int;

    /**
     * Busca guias por ano
     *
     * @param int $year
     * @param int $limit
     * @return Collection
     */
    public function findByYear(int $year, int $limit = 100): Collection;

    /**
     * Busca guias relacionados
     *
     * @param Guide $guide
     * @param int $limit
     * @return Collection
     */
    public function findRelated(Guide $guide, int $limit = 10): Collection;

    /**
     * Busca guias por marca e modelo
     *
     * @param string $makeSlug
     * @param string $modelSlug
     * @return Collection
     */
    public function findByMakeAndModel(string $makeSlug, string $modelSlug): Collection;
}
