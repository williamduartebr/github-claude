<?php

namespace Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\Repositories;

use Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\Entities\TireChangeArticle;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\ValueObjects\VehicleData;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\ValueObjects\TireChangeContent;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TireChangeArticleRepositoryInterface
{
    /**
     * Criar artigo a partir do conteúdo gerado
     */
    public function createFromContent(VehicleData $vehicle, TireChangeContent $content, array $options = []): ?TireChangeArticle;

    /**
     * Verificar se existe artigo para o veículo (com year)
     */
    public function existsForVehicle(string $make, string $model, int $year): bool;

    /**
     * Buscar artigo por slug
     */
    public function findBySlug(string $slug): ?TireChangeArticle;

    /**
     * Buscar artigo por veículo (com year)
     */
    public function findByVehicle(string $make, string $model, int $year): ?TireChangeArticle;

    /**
     * Buscar artigos por lote
     */
    public function findByBatchId(string $batchId): Collection;

    /**
     * Contar artigos por lote
     */
    public function countByBatchId(string $batchId): int;

    /**
     * Buscar artigos por status
     */
    public function findByStatus(string $status): Collection;

    /**
     * Buscar artigos paginados
     */
    public function findPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Obter artigos prontos para refinamento Claude
     */
    public function getReadyForClaudeEnhancement(int $limit = 50): Collection;

    /**
     * Contar total de artigos
     */
    public function count(): int;

    /**
     * Contar artigos gerados hoje
     */
    public function countGeneratedToday(): int;

    /**
     * Obter distribuição por status
     */
    public function getStatusDistribution(): array;

    /**
     * Obter estatísticas gerais
     */
    public function getStatistics(): array;

    /**
     * Buscar por múltiplos critérios
     */
    public function findByCriteria(array $criteria): Collection;

    /**
     * Atualizar status do artigo
     */
    public function updateStatus(int $id, string $status): bool;

    /**
     * Marcar como refinado pelo Claude
     */
    public function markAsClaudeEnhanced(int $id, array $enhancementData = []): bool;

    /**
     * Obter artigos com problemas de qualidade
     */
    public function getWithQualityIssues(): Collection;

    /**
     * Exportar artigos para transferência
     */
    public function getForTransfer(int $limit = 100): Collection;

    /**
     * Deletar artigos antigos
     */
    public function deleteOlderThan(\DateTimeInterface $date): int;

    /**
     * Método que verifica apenas make+model
     */
    public function existsForVehicleModel(string $make, string $model, int $year): bool;
}
