<?php

namespace Src\VehicleData\Domain\Entities;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

/**
 * VehicleData Model - Dados centralizados de veículos
 * 
 * Armazena informações técnicas dos veículos extraídas dos artigos
 * de pressão de pneus para uso futuro em outros módulos
 */
class VehicleData extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $table = 'vehicle_data';
    protected $guarded = ['_id'];

    /**
     * Campos que devem ser convertidos em tipos nativos
     */
    protected $casts = [
        'pressure_specifications' => 'array',
        'tire_specifications' => 'array',
        'vehicle_features' => 'array',
        'engine_data' => 'array',
        'transmission_data' => 'array',
        'fuel_data' => 'array',
        'dimensions' => 'array',
        'market_data' => 'array',
        'technical_specs' => 'array',
        'source_articles' => 'array',
        'extracted_at' => 'datetime',
        'last_validated_at' => 'datetime',
        'data_quality_score' => 'float', // Mudado de decimal para float
        'is_verified' => 'boolean',
        'is_premium' => 'boolean',
        'has_tpms' => 'boolean',
        'is_motorcycle' => 'boolean',
        'is_electric' => 'boolean',
        'is_hybrid' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Constantes para categorias de veículos
     */
    const CATEGORY_HATCH = 'hatch';
    const CATEGORY_SEDAN = 'sedan';
    const CATEGORY_SUV = 'suv';
    const CATEGORY_PICKUP = 'pickup';
    const CATEGORY_VAN = 'van';
    const CATEGORY_MOTORCYCLE = 'motorcycle';
    const CATEGORY_ELECTRIC = 'car_electric';
    const CATEGORY_TRUCK = 'truck';
    const CATEGORY_COMMERCIAL = 'commercial';

    /**
     * Constantes para segmentos
     */
    const SEGMENT_A = 'A'; // Micro
    const SEGMENT_B = 'B'; // Compacto
    const SEGMENT_C = 'C'; // Médio
    const SEGMENT_D = 'D'; // Grande/SUV
    const SEGMENT_E = 'E'; // Executivo
    const SEGMENT_F = 'F'; // Pickup/Comercial
    const SEGMENT_MOTO = 'MOTO'; // Motocicleta

    /**
     * Constantes para status de validação
     */
    const STATUS_PENDING = 'pending';
    const STATUS_VALIDATED = 'validated';
    const STATUS_NEEDS_REVIEW = 'needs_review';
    const STATUS_REJECTED = 'rejected';

    // =======================================================================
    // MÉTODOS DE BUSCA AVANÇADA
    // =======================================================================

    /**
     * Buscar veículos por critérios flexíveis
     */
    public static function search(array $criteria): \Illuminate\Database\Eloquent\Collection
    {
        $query = self::query();

        // Busca por marca (obrigatória)
        if (!empty($criteria['make'])) {
            $query->where('make', 'LIKE', "%{$criteria['make']}%");
        }

        // Busca por modelo (obrigatória)
        if (!empty($criteria['model'])) {
            $query->where('model', 'LIKE', "%{$criteria['model']}%");
        }

        // Busca por ano (opcional)
        if (!empty($criteria['year'])) {
            $query->where('year', $criteria['year']);
        }

        // Busca por categoria (opcional)
        if (!empty($criteria['category'])) {
            $query->where('main_category', $criteria['category']);
        }

        // Ordenar por relevância (ano mais recente primeiro, depois por qualidade)
        $query->orderByDesc('year')
              ->orderByDesc('data_quality_score');

        return $query->get();
    }

    /**
     * Buscar veículo específico (make + model + year)
     */
    public static function findVehicle(string $make, string $model, ?int $year = null)
    {
        $query = self::where('make', 'LIKE', "%{$make}%")
                     ->where('model', 'LIKE', "%{$model}%");

        if ($year) {
            $query->where('year', $year);
        }

        // Se não especificar ano, pegar o mais recente
        if (!$year) {
            $query->orderByDesc('year');
        }

        return $query->first();
    }

    /**
     * Buscar todos os anos disponíveis para make + model
     */
    public static function findAllYears(string $make, string $model): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('make', 'LIKE', "%{$make}%")
                   ->where('model', 'LIKE', "%{$model}%")
                   ->orderByDesc('year')
                   ->get(['year', 'make', 'model', 'main_category', 'data_quality_score']);
    }

    /**
     * Buscar modelos similares de uma marca
     */
    public static function findSimilarModels(string $make, string $model, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        // Buscar modelos que contenham parte do nome
        $modelWords = explode(' ', $model);
        $query = self::where('make', 'LIKE', "%{$make}%");

        // Buscar por cada palavra do modelo
        $query->where(function ($q) use ($modelWords) {
            foreach ($modelWords as $word) {
                if (strlen($word) > 2) { // Ignorar palavras muito pequenas
                    $q->orWhere('model', 'LIKE', "%{$word}%");
                }
            }
        });

        return $query->orderByDesc('year')
                     ->orderByDesc('data_quality_score')
                     ->limit($limit)
                     ->get(['make', 'model', 'year', 'main_category']);
    }

    /**
     * Buscar veículos por marca com filtros opcionais
     */
    public static function findByMakeWithFilters(string $make, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = self::where('make', 'LIKE', "%{$make}%");

        // Filtro por categoria
        if (!empty($filters['category'])) {
            $query->where('main_category', $filters['category']);
        }

        // Filtro por segmento
        if (!empty($filters['segment'])) {
            $query->where('vehicle_segment', $filters['segment']);
        }

        // Filtro por ano mínimo/máximo
        if (!empty($filters['year_min'])) {
            $query->where('year', '>=', $filters['year_min']);
        }
        if (!empty($filters['year_max'])) {
            $query->where('year', '<=', $filters['year_max']);
        }

        // Filtro por características
        if (!empty($filters['is_electric'])) {
            $query->where('is_electric', true);
        }
        if (!empty($filters['is_premium'])) {
            $query->where('is_premium', true);
        }
        if (!empty($filters['has_tpms'])) {
            $query->where('has_tpms', true);
        }

        return $query->orderBy('model')
                     ->orderByDesc('year')
                     ->get();
    }

    /**
     * Busca fuzzy por nome completo do veículo
     */
    public static function fuzzySearch(string $searchTerm, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        $searchWords = explode(' ', strtolower($searchTerm));
        
        $query = self::query();

        // Buscar por qualquer palavra no make ou model
        $query->where(function ($q) use ($searchWords) {
            foreach ($searchWords as $word) {
                if (strlen($word) > 1) {
                    $q->orWhere('make', 'LIKE', "%{$word}%")
                      ->orWhere('model', 'LIKE', "%{$word}%");
                }
            }
        });

        return $query->orderByDesc('data_quality_score')
                     ->orderByDesc('year')
                     ->limit($limit)
                     ->get(['make', 'model', 'year', 'main_category', 'data_quality_score']);
    }

    /**
     * Buscar veículos com pressões específicas
     */
    public static function findByPressureRange(int $minPressure, int $maxPressure): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('pressure_specifications.pressure_light_front', '>=', $minPressure)
                   ->where('pressure_specifications.pressure_light_front', '<=', $maxPressure)
                   ->orWhere('pressure_specifications.pressure_light_rear', '>=', $minPressure)
                   ->where('pressure_specifications.pressure_light_rear', '<=', $maxPressure)
                   ->orderBy('make')
                   ->orderBy('model')
                   ->orderByDesc('year')
                   ->get();
    }

    /**
     * Sugerir veículos baseado em busca parcial
     */
    public static function suggest(string $term, int $limit = 5): array
    {
        $term = strtolower($term);
        
        // Buscar marcas que começam com o termo
        $makes = self::where('make', 'LIKE', "{$term}%")
                     ->distinct('make')
                     ->pluck('make')
                     ->take($limit)
                     ->toArray();

        // Buscar modelos que começam com o termo
        $models = self::where('model', 'LIKE', "{$term}%")
                      ->distinct('model')
                      ->pluck('model')
                      ->take($limit)
                      ->toArray();

        // Buscar veículos completos que contenham o termo
        $vehicles = self::where(function ($query) use ($term) {
                        $query->where('make', 'LIKE', "%{$term}%")
                              ->orWhere('model', 'LIKE', "%{$term}%");
                    })
                    ->select('make', 'model', 'year')
                    ->orderByDesc('year')
                    ->limit($limit)
                    ->get()
                    ->map(function ($vehicle) {
                        return "{$vehicle->make} {$vehicle->model} {$vehicle->year}";
                    })
                    ->toArray();

        return [
            'makes' => $makes,
            'models' => $models,
            'vehicles' => $vehicles
        ];
    }

    // =======================================================================
    // SCOPES PARA BUSCA
    // =======================================================================

    /**
     * Buscar por termo genérico
     */
    public function scopeSearchTerm($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('make', 'LIKE', "%{$term}%")
              ->orWhere('model', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Buscar por ano aproximado (± 2 anos)
     */
    public function scopeNearYear($query, int $year, int $tolerance = 2)
    {
        return $query->whereBetween('year', [$year - $tolerance, $year + $tolerance]);
    }

    /**
     * Filtrar apenas veículos com dados completos
     */
    public function scopeComplete($query)
    {
        return $query->whereNotNull('pressure_specifications')
                     ->where('data_quality_score', '>=', 6.0);
    }

    /**
     * Filtrar por marca
     */
    public function scopeByMake($query, string $make)
    {
        return $query->where('make', $make);
    }

    /**
     * Filtrar por modelo
     */
    public function scopeByModel($query, string $model)
    {
        return $query->where('model', $model);
    }

    /**
     * Filtrar por ano
     */
    public function scopeByYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Filtrar por categoria
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('main_category', $category);
    }

    /**
     * Filtrar por segmento
     */
    public function scopeBySegment($query, string $segment)
    {
        return $query->where('vehicle_segment', $segment);
    }

    /**
     * Apenas veículos elétricos
     */
    public function scopeElectric($query)
    {
        return $query->where('is_electric', true);
    }

    /**
     * Apenas veículos híbridos
     */
    public function scopeHybrid($query)
    {
        return $query->where('is_hybrid', true);
    }

    /**
     * Apenas motocicletas
     */
    public function scopeMotorcycles($query)
    {
        return $query->where('is_motorcycle', true);
    }

    /**
     * Apenas veículos premium
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    /**
     * Veículos com TPMS
     */
    public function scopeWithTpms($query)
    {
        return $query->where('has_tpms', true);
    }

    /**
     * Dados validados
     */
    public function scopeValidated($query)
    {
        return $query->where('validation_status', self::STATUS_VALIDATED);
    }

    /**
     * Dados pendentes de validação
     */
    public function scopePendingValidation($query)
    {
        return $query->where('validation_status', self::STATUS_PENDING);
    }

    /**
     * Buscar por veículo específico
     */
    public function scopeByVehicle($query, string $make, string $model, int $year)
    {
        return $query->where('make', $make)
                    ->where('model', $model)
                    ->where('year', $year);
    }

    // =======================================================================
    // MÉTODOS DE INSTÂNCIA
    // =======================================================================

    /**
     * Gerar slug único para o veículo
     */
    public function generateSlug(): string
    {
        return \Str::slug($this->make . '-' . $this->model . '-' . $this->year);
    }

    /**
     * Nome completo do veículo
     */
    public function getVehicleFullNameAttribute(): string
    {
        return "{$this->make} {$this->model} {$this->year}";
    }

    /**
     * Obter pressão recomendada formatada
     */
    public function getPressureDisplayAttribute(): string
    {
        if (!isset($this->pressure_specifications)) {
            return 'N/A';
        }

        $front = $this->pressure_specifications['pressure_light_front'] ?? 0;
        $rear = $this->pressure_specifications['pressure_light_rear'] ?? 0;

        if ($front == $rear) {
            return "{$front} PSI";
        }

        return "Dianteiros: {$front} PSI / Traseiros: {$rear} PSI";
    }

    /**
     * Verificar se é veículo elétrico baseado em dados
     */
    public function detectIfElectric(): bool
    {
        $electricIndicators = [
            'ev', 'electric', 'elétrico', 'híbrido', 'hybrid',
            'phev', 'bev', 'e-tron', 'tesla', 'leaf'
        ];

        $searchText = strtolower($this->make . ' ' . $this->model);
        
        foreach ($electricIndicators as $indicator) {
            if (str_contains($searchText, $indicator)) {
                return true;
            }
        }

        return $this->main_category === self::CATEGORY_ELECTRIC;
    }

    /**
     * Calcular score de qualidade dos dados
     */
    public function calculateDataQualityScore(): float
    {
        $score = 0;
        $maxScore = 100;

        // Dados básicos obrigatórios (30 pontos)
        if (!empty($this->make)) $score += 5;
        if (!empty($this->model)) $score += 5;
        if (!empty($this->year) && $this->year > 1990) $score += 5;
        if (!empty($this->main_category)) $score += 5;
        if (!empty($this->vehicle_segment)) $score += 5;
        if (!empty($this->tire_size)) $score += 5;

        // Especificações de pressão (40 pontos)
        if (isset($this->pressure_specifications['pressure_light_front'])) $score += 10;
        if (isset($this->pressure_specifications['pressure_light_rear'])) $score += 10;
        if (isset($this->pressure_specifications['pressure_max_front'])) $score += 10;
        if (isset($this->pressure_specifications['pressure_max_rear'])) $score += 10;

        // Especificações técnicas (20 pontos)
        if (!empty($this->engine_data)) $score += 5;
        if (!empty($this->transmission_data)) $score += 5;
        if (!empty($this->fuel_data)) $score += 5;
        if (!empty($this->dimensions)) $score += 5;

        // Features e características (10 pontos)
        if (isset($this->is_premium)) $score += 2;
        if (isset($this->has_tpms)) $score += 2;
        if (isset($this->is_electric)) $score += 2;
        if (isset($this->is_hybrid)) $score += 2;
        if (isset($this->is_motorcycle)) $score += 2;

        $finalScore = ($score / $maxScore) * 10; // Escala 0-10

        $this->update(['data_quality_score' => round($finalScore, 2)]);

        return $finalScore;
    }

    /**
     * Marcar como validado
     */
    public function markAsValidated(): void
    {
        $this->update([
            'validation_status' => self::STATUS_VALIDATED,
            'last_validated_at' => now(),
            'is_verified' => true
        ]);
    }

    /**
     * Adicionar artigo fonte
     */
    public function addSourceArticle(string $articleId, string $templateType): void
    {
        $sources = $this->source_articles ?? [];
        
        $sources[] = [
            'article_id' => $articleId,
            'template_type' => $templateType,
            'extracted_at' => now()->toISOString()
        ];

        $this->update(['source_articles' => $sources]);
    }

    // =======================================================================
    // MÉTODOS ESTÁTICOS
    // =======================================================================

    /**
     * Criar ou atualizar dados do veículo
     */
    public static function createOrUpdateFromArticle(array $vehicleData, string $articleId): self
    {
        $vehicle = self::byVehicle(
            $vehicleData['make'],
            $vehicleData['model'],
            $vehicleData['year']
        )->first();

        if (!$vehicle) {
            $vehicle = self::create([
                'make' => $vehicleData['make'],
                'model' => $vehicleData['model'],
                'year' => $vehicleData['year'],
                'tire_size' => $vehicleData['tire_size'] ?? null,
                'main_category' => $vehicleData['main_category'] ?? null,
                'vehicle_segment' => $vehicleData['vehicle_segment'] ?? null,
                'vehicle_type' => $vehicleData['vehicle_type'] ?? null,
                'pressure_specifications' => self::extractPressureSpecs($vehicleData),
                'tire_specifications' => self::extractTireSpecs($vehicleData),
                'vehicle_features' => self::extractVehicleFeatures($vehicleData),
                'is_premium' => $vehicleData['is_premium'] ?? false,
                'has_tpms' => $vehicleData['has_tpms'] ?? false,
                'is_motorcycle' => $vehicleData['is_motorcycle'] ?? false,
                'is_electric' => self::detectElectricFromData($vehicleData),
                'is_hybrid' => self::detectHybridFromData($vehicleData),
                'validation_status' => self::STATUS_PENDING,
                'extracted_at' => now(),
                'source_articles' => [
                    [
                        'article_id' => $articleId,
                        'extracted_at' => now()->toISOString()
                    ]
                ]
            ]);
        } else {
            // Atualizar dados existentes
            $vehicle->addSourceArticle($articleId, 'tire_pressure');
            $vehicle->update([
                'pressure_specifications' => self::extractPressureSpecs($vehicleData),
                'tire_specifications' => self::extractTireSpecs($vehicleData),
                'vehicle_features' => self::extractVehicleFeatures($vehicleData),
            ]);
        }

        $vehicle->calculateDataQualityScore();

        return $vehicle;
    }

    /**
     * Extrair especificações de pressão
     */
    protected static function extractPressureSpecs(array $data): array
    {
        return [
            'pressure_empty_front' => $data['pressure_empty_front'] ?? null,
            'pressure_empty_rear' => $data['pressure_empty_rear'] ?? null,
            'pressure_light_front' => $data['pressure_light_front'] ?? null,
            'pressure_light_rear' => $data['pressure_light_rear'] ?? null,
            'pressure_max_front' => $data['pressure_max_front'] ?? null,
            'pressure_max_rear' => $data['pressure_max_rear'] ?? null,
            'pressure_spare' => $data['pressure_spare'] ?? null,
            'pressure_display' => $data['pressure_display'] ?? null,
            'empty_pressure_display' => $data['empty_pressure_display'] ?? null,
            'loaded_pressure_display' => $data['loaded_pressure_display'] ?? null,
        ];
    }

    /**
     * Extrair especificações de pneus
     */
    protected static function extractTireSpecs(array $data): array
    {
        return [
            'tire_size' => $data['tire_size'] ?? null,
            'recommended_brands' => $data['recommended_tire_brands'] ?? [],
            'seasonal_recommendations' => $data['seasonal_tires'] ?? [],
        ];
    }

    /**
     * Extrair características do veículo
     */
    protected static function extractVehicleFeatures(array $data): array
    {
        return [
            'vehicle_full_name' => $data['vehicle_full_name'] ?? null,
            'url_slug' => $data['url_slug'] ?? null,
            'category_normalized' => $data['category_normalized'] ?? null,
            'recommended_oil' => $data['recommended_oil'] ?? null,
        ];
    }

    /**
     * Detectar se é elétrico
     */
    protected static function detectElectricFromData(array $data): bool
    {
        if (($data['main_category'] ?? '') === 'car_electric') {
            return true;
        }

        $electricKeywords = ['electric', 'ev', 'tesla', 'leaf', 'bolt', 'e-tron'];
        $searchText = strtolower(($data['make'] ?? '') . ' ' . ($data['model'] ?? ''));

        foreach ($electricKeywords as $keyword) {
            if (str_contains($searchText, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detectar se é híbrido
     */
    protected static function detectHybridFromData(array $data): bool
    {
        $hybridKeywords = ['hybrid', 'híbrido', 'phev', 'hev'];
        $searchText = strtolower(($data['make'] ?? '') . ' ' . ($data['model'] ?? ''));

        foreach ($hybridKeywords as $keyword) {
            if (str_contains($searchText, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obter estatísticas gerais
     */
    public static function getStatistics(): array
    {
        // Converter valores Decimal128 para float
        $avgScore = self::avg('data_quality_score');
        $averageScore = 0;
        
        if ($avgScore !== null) {
            if ($avgScore instanceof \MongoDB\BSON\Decimal128) {
                $averageScore = (float) $avgScore->__toString();
            } else {
                $averageScore = (float) $avgScore;
            }
        }

        return [
            'total_vehicles' => self::count(),
            'by_category' => [
                'hatch' => self::byCategory(self::CATEGORY_HATCH)->count(),
                'sedan' => self::byCategory(self::CATEGORY_SEDAN)->count(),
                'suv' => self::byCategory(self::CATEGORY_SUV)->count(),
                'pickup' => self::byCategory(self::CATEGORY_PICKUP)->count(),
                'motorcycle' => self::byCategory(self::CATEGORY_MOTORCYCLE)->count(),
                'electric' => self::byCategory(self::CATEGORY_ELECTRIC)->count(),
            ],
            'by_segment' => [
                'A' => self::bySegment(self::SEGMENT_A)->count(),
                'B' => self::bySegment(self::SEGMENT_B)->count(),
                'C' => self::bySegment(self::SEGMENT_C)->count(),
                'D' => self::bySegment(self::SEGMENT_D)->count(),
                'F' => self::bySegment(self::SEGMENT_F)->count(),
            ],
            'features' => [
                'premium' => self::premium()->count(),
                'electric' => self::electric()->count(),
                'hybrid' => self::hybrid()->count(),
                'with_tpms' => self::withTpms()->count(),
            ],
            'validation' => [
                'validated' => self::validated()->count(),
                'pending' => self::pendingValidation()->count(),
            ],
            'quality_scores' => [
                'average' => round($averageScore, 2),
                'high_quality' => self::where('data_quality_score', '>=', 8)->count(),
                'needs_improvement' => self::where('data_quality_score', '<', 6)->count(),
            ]
        ];
    }
}