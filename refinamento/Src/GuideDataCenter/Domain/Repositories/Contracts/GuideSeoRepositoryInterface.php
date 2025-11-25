<?php

namespace Src\GuideDataCenter\Domain\Repositories\Contracts;

use Src\GuideDataCenter\Domain\Mongo\GuideSeo;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface GuideSeoRepositoryInterface
 * 
 * Contrato para operações de repositório de SEO de guias
 */
interface GuideSeoRepositoryInterface
{
    /**
     * Busca SEO de um guia específico
     *
     * @param string $guideId
     * @return GuideSeo|null
     */
    public function getSeoForGuide(string $guideId): ?GuideSeo;

    /**
     * Salva ou atualiza SEO de um guia
     *
     * @param string $guideId
     * @param array $payload
     * @return GuideSeo
     */
    public function saveSeo(string $guideId, array $payload): GuideSeo;

    /**
     * Busca SEO por slug
     *
     * @param string $slug
     * @return GuideSeo|null
     */
    public function findBySlug(string $slug): ?GuideSeo;

    /**
     * Busca SEO por palavra-chave primária
     *
     * @param string $keyword
     * @return Collection
     */
    public function findByPrimaryKeyword(string $keyword): Collection;

    /**
     * Busca SEO por palavra-chave secundária
     *
     * @param string $keyword
     * @return Collection
     */
    public function findBySecondaryKeyword(string $keyword): Collection;

    /**
     * Deleta SEO de um guia
     *
     * @param string $guideId
     * @return bool
     */
    public function deleteSeoForGuide(string $guideId): bool;

    /**
     * Atualiza schema.org de um SEO
     *
     * @param string $guideId
     * @param array $schema
     * @return bool
     */
    public function updateSchema(string $guideId, array $schema): bool;

    /**
     * Lista SEOs com boa pontuação
     *
     * @param float $minScore
     * @param int $limit
     * @return Collection
     */
    public function findByMinScore(float $minScore, int $limit = 50): Collection;

    /**
     * Lista SEOs incompletos
     *
     * @param int $limit
     * @return Collection
     */
    public function findIncomplete(int $limit = 50): Collection;

    /**
     * Conta total de SEOs
     *
     * @return int
     */
    public function count(): int;
}
