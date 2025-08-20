<?php

namespace Src\VehicleData\Domain\Entities;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

/**
 * VehicleEnrichmentGroup Model - Grupos de veículos para enrichment otimizado
 * 
 * Armazena agrupamentos inteligentes de veículos por make+model+geração
 * para otimizar chamadas da API Claude (963 → ~200 chamadas)
 */
class VehicleEnrichmentGroup extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $table = 'vehicle_enrichment_groups';
    protected $guarded = ['_id'];

    /**
     * Campos que devem ser convertidos em tipos nativos
     */
    protected $casts = [
        'group_vehicles' => 'array',
        'representative_data' => 'array',
        'sibling_vehicles' => 'array',
        'enriched_data' => 'array',
        'propagation_results' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'enriched_at' => 'datetime',
        'propagated_at' => 'datetime',
        'is_enriched' => 'boolean',
        'is_propagated' => 'boolean',
        'enrichment_attempts' => 'integer',
        'propagation_attempts' => 'integer',
    ];

    /**
     * Constantes para status de processamento
     */
    const STATUS_PENDING = 'pending';
    const STATUS_ENRICHING = 'enriching';
    const STATUS_ENRICHED = 'enriched';
    const STATUS_PROPAGATING = 'propagating';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * Constantes para prioridade
     */
    const PRIORITY_HIGH = 'high';    // Marcas premium, modelos populares
    const PRIORITY_MEDIUM = 'medium'; // Modelos comuns
    const PRIORITY_LOW = 'low';      // Modelos raros, muito antigos

    // =======================================================================
    // SCOPES
    // =======================================================================

    /**
     * Grupos pendentes para enrichment
     */
    public function scopePendingEnrichment($query)
    {
        return $query->where('processing_status', self::STATUS_PENDING)
                    ->orWhere('processing_status', self::STATUS_FAILED);
    }

    /**
     * Grupos já enriquecidos mas pendentes para propagação
     */
    public function scopePendingPropagation($query)
    {
        return $query->where('processing_status', self::STATUS_ENRICHED)
                    ->where('is_enriched', true)
                    ->where('is_propagated', false);
    }

    /**
     * Grupos completamente processados
     */
    public function scopeCompleted($query)
    {
        return $query->where('processing_status', self::STATUS_COMPLETED)
                    ->where('is_enriched', true)
                    ->where('is_propagated', true);
    }

    /**
     * Por prioridade
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Por marca
     */
    public function scopeByMake($query, string $make)
    {
        return $query->where('make', $make);
    }

    /**
     * Por categoria
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('main_category', $category);
    }

    // =======================================================================
    // MÉTODOS DE INSTÂNCIA
    // =======================================================================

    /**
     * Marcar como em processo de enrichment
     */
    public function markAsEnriching(): void
    {
        $this->update([
            'processing_status' => self::STATUS_ENRICHING,
            'enrichment_attempts' => ($this->enrichment_attempts ?? 0) + 1,
            'last_enrichment_attempt_at' => now()
        ]);
    }

    /**
     * Marcar enrichment como concluído
     */
    public function markAsEnriched(array $enrichedData): void
    {
        $this->update([
            'processing_status' => self::STATUS_ENRICHED,
            'is_enriched' => true,
            'enriched_data' => $enrichedData,
            'enriched_at' => now(),
            'enrichment_error' => null
        ]);
    }

    /**
     * Marcar enrichment como falhou
     */
    public function markEnrichmentAsFailed(string $error): void
    {
        $this->update([
            'processing_status' => self::STATUS_FAILED,
            'is_enriched' => false,
            'enrichment_error' => $error,
            'last_enrichment_attempt_at' => now()
        ]);
    }

    /**
     * Marcar como em processo de propagação
     */
    public function markAsPropagating(): void
    {
        $this->update([
            'processing_status' => self::STATUS_PROPAGATING,
            'propagation_attempts' => ($this->propagation_attempts ?? 0) + 1,
            'last_propagation_attempt_at' => now()
        ]);
    }

    /**
     * Marcar propagação como concluída
     */
    public function markAsPropagated(array $propagationResults): void
    {
        $this->update([
            'processing_status' => self::STATUS_COMPLETED,
            'is_propagated' => true,
            'propagation_results' => $propagationResults,
            'propagated_at' => now(),
            'propagation_error' => null
        ]);
    }

    /**
     * Marcar propagação como falhou
     */
    public function markPropagationAsFailed(string $error): void
    {
        $this->update([
            'processing_status' => self::STATUS_FAILED,
            'is_propagated' => false,
            'propagation_error' => $error,
            'last_propagation_attempt_at' => now()
        ]);
    }

    /**
     * Obter informações do representante
     */
    public function getRepresentativeInfo(): array
    {
        $rep = $this->representative_data;
        
        return [
            'vehicle_id' => $rep['_id'] ?? $rep['id'] ?? null,
            'make' => $rep['make'] ?? $this->make,
            'model' => $rep['model'] ?? $this->model,
            'year' => $rep['year'] ?? null,
            'category' => $rep['main_category'] ?? $this->main_category,
            'full_name' => ($rep['make'] ?? $this->make) . ' ' . 
                          ($rep['model'] ?? $this->model) . ' ' . 
                          ($rep['year'] ?? 'N/A')
        ];
    }

    /**
     * Obter IDs dos veículos irmãos
     */
    public function getSiblingVehicleIds(): array
    {
        $siblings = $this->sibling_vehicles ?? [];
        $ids = [];
        
        foreach ($siblings as $sibling) {
            $id = $sibling['_id'] ?? $sibling['id'] ?? null;
            if ($id) {
                $ids[] = $id;
            }
        }
        
        return $ids;
    }

    /**
     * Contar total de veículos no grupo
     */
    public function getTotalVehiclesCount(): int
    {
        return ($this->group_vehicles['count'] ?? 0);
    }

    /**
     * Verificar se pode tentar enrichment novamente
     */
    public function canRetryEnrichment(): bool
    {
        $maxAttempts = 3;
        $cooldownMinutes = 30;

        if (($this->enrichment_attempts ?? 0) >= $maxAttempts) {
            return false;
        }

        if ($this->last_enrichment_attempt_at) {
            $lastAttempt = Carbon::parse($this->last_enrichment_attempt_at);
            return $lastAttempt->addMinutes($cooldownMinutes)->isPast();
        }

        return true;
    }

    /**
     * Verificar se pode tentar propagação novamente
     */
    public function canRetryPropagation(): bool
    {
        $maxAttempts = 3;
        $cooldownMinutes = 10;

        if (($this->propagation_attempts ?? 0) >= $maxAttempts) {
            return false;
        }

        if ($this->last_propagation_attempt_at) {
            $lastAttempt = Carbon::parse($this->last_propagation_attempt_at);
            return $lastAttempt->addMinutes($cooldownMinutes)->isPast();
        }

        return true;
    }

    // =======================================================================
    // MÉTODOS ESTÁTICOS
    // =======================================================================

    /**
     * Criar grupo de enrichment
     */
    public static function createGroup(array $groupData): self
    {
        $representative = $groupData['representative'];
        $siblings = $groupData['siblings'] ?? [];
        $groupInfo = $groupData['group_info'];

        // Garantir que temos o ID do representante (seja _id ou id)
        $representativeId = $representative['_id'] ?? $representative['id'] ?? null;
        
        if (!$representativeId) {
            throw new \Exception('Representative ID not found in data');
        }

        return self::create([
            // Identificação básica
            'generation_key' => $groupInfo['generation_key'],
            'make' => $groupInfo['make'],
            'model' => $groupInfo['model'],
            'main_category' => $groupInfo['category'],
            'year_span' => $groupInfo['year_span'],
            
            // Dados do grupo
            'group_vehicles' => [
                'count' => $groupInfo['vehicle_count'],
                'years' => explode('-', $groupInfo['year_span']),
                'category' => $groupInfo['category']
            ],
            
            // Representante
            'representative_data' => $representative,
            'representative_vehicle_id' => $representativeId,
            
            // Irmãos
            'sibling_vehicles' => $siblings,
            'sibling_count' => count($siblings),
            
            // Status inicial
            'processing_status' => self::STATUS_PENDING,
            'priority' => self::calculatePriority($groupInfo),
            'is_enriched' => false,
            'is_propagated' => false,
            'enrichment_attempts' => 0,
            'propagation_attempts' => 0
        ]);
    }

    /**
     * Calcular prioridade do grupo
     */
    protected static function calculatePriority(array $groupInfo): string
    {
        $make = $groupInfo['make'];
        $category = $groupInfo['category'];
        $vehicleCount = $groupInfo['vehicle_count'];

        // Marcas premium
        $premiumMakes = ['BMW', 'Mercedes-Benz', 'Audi', 'Porsche', 'Lexus'];
        if (in_array($make, $premiumMakes)) {
            return self::PRIORITY_HIGH;
        }

        // Marcas populares
        $popularMakes = ['Honda', 'Toyota', 'Chevrolet', 'Ford', 'Hyundai', 'Volkswagen'];
        if (in_array($make, $popularMakes)) {
            return self::PRIORITY_HIGH;
        }

        // Categorias importantes
        $importantCategories = ['hatch', 'sedan', 'suv', 'pickup'];
        if (in_array($category, $importantCategories)) {
            return self::PRIORITY_MEDIUM;
        }

        // Grupos com muitos veículos
        if ($vehicleCount >= 5) {
            return self::PRIORITY_MEDIUM;
        }

        return self::PRIORITY_LOW;
    }

    /**
     * Obter estatísticas de processamento
     */
    public static function getProcessingStats(): array
    {
        $total = self::count();
        $pending = self::pendingEnrichment()->count();
        $enriched = self::where('is_enriched', true)->count();
        $propagated = self::where('is_propagated', true)->count();
        $completed = self::completed()->count();
        $failed = self::where('processing_status', self::STATUS_FAILED)->count();

        return [
            'total_groups' => $total,
            'pending_enrichment' => $pending,
            'enriched' => $enriched,
            'pending_propagation' => $enriched - $propagated,
            'completed' => $completed,
            'failed' => $failed,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0
        ];
    }

    /**
     * Limpar grupos antigos
     */
    public static function cleanupOldGroups(int $daysOld = 30): int
    {
        $cutoffDate = now()->subDays($daysOld);
        
        return self::where('created_at', '<', $cutoffDate)
                  ->where('processing_status', self::STATUS_COMPLETED)
                  ->delete();
    }

    /**
     * Resetar grupos com falha para nova tentativa
     */
    public static function resetFailedGroups(): int
    {
        return self::where('processing_status', self::STATUS_FAILED)
                  ->whereRaw('enrichment_attempts < 3')
                  ->update([
                      'processing_status' => self::STATUS_PENDING,
                      'enrichment_error' => null,
                      'propagation_error' => null
                  ]);
    }

    /**
     * Obter próximo lote para processamento
     */
    public static function getNextBatchForEnrichment(int $batchSize = 10): \Illuminate\Support\Collection
    {
        return self::pendingEnrichment()
                  ->where(function($query) {
                      $query->whereNull('last_enrichment_attempt_at')
                            ->orWhere('last_enrichment_attempt_at', '<', now()->subMinutes(30));
                  })
                  ->orderBy('priority', 'desc')
                  ->orderBy('created_at', 'asc')
                  ->limit($batchSize)
                  ->get();
    }

    /**
     * Obter próximo lote para propagação
     */
    public static function getNextBatchForPropagation(int $batchSize = 20): \Illuminate\Support\Collection
    {
        return self::pendingPropagation()
                  ->where(function($query) {
                      $query->whereNull('last_propagation_attempt_at')
                            ->orWhere('last_propagation_attempt_at', '<', now()->subMinutes(10));
                  })
                  ->orderBy('priority', 'desc')
                  ->orderBy('enriched_at', 'asc')
                  ->limit($batchSize)
                  ->get();
    }
}