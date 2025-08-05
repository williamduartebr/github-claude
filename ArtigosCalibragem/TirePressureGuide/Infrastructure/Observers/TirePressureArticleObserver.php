<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Observers;

use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;

/**
 * Observer para sincronização automática de vehicle_data entre artigos irmãos
 * 
 * FUNCIONALIDADE:
 * - Detecta atualizações de vehicle_data_version para v3.1
 * - Sincroniza automaticamente o artigo irmão (mesmo veículo)
 * - Evita loops infinitos e duplicação de trabalho
 */
class TirePressureArticleObserver
{
    /**
     * Handle the TirePressureArticle "updated" event.
     */
    public function updated(TirePressureArticle $article): void
    {
        // Só sincronizar se vehicle_data_version foi atualizado para v3.1
        if ($this->shouldSyncSibling($article)) {
            $this->syncSiblingArticle($article);
        }
    }

    /**
     * Verificar se deve sincronizar artigo irmão
     */
    protected function shouldSyncSibling(TirePressureArticle $article): bool
    {
        // 1. Verificar se vehicle_data_version foi atualizado para v3.1
        if (!$article->wasChanged('vehicle_data_version') || $article->vehicle_data_version !== 'v3.1') {
            return false;
        }

        // 2. Verificar se tem dados de veículo válidos
        $vehicleData = $article->vehicle_data;
        if (empty($vehicleData) || !is_array($vehicleData)) {
            return false;
        }

        // 3. Verificar se tem make, model, year
        if (empty($vehicleData['make']) || empty($vehicleData['model']) || empty($vehicleData['year'])) {
            return false;
        }

        // 4. Verificar se não é uma atualização de sincronização (evitar loop)
        if ($this->isFromSyncOperation($article)) {
            return false;
        }

        return true;
    }

    /**
     * Sincronizar artigo irmão
     */
    protected function syncSiblingArticle(TirePressureArticle $article): void
    {
        try {
            $vehicleData = $article->vehicle_data;
            $vehicleIdentifier = $this->generateVehicleIdentifier($vehicleData);

            Log::info('TirePressureArticleObserver: Iniciando sincronização', [
                'article_id' => $article->_id,
                'vehicle' => $vehicleIdentifier,
                'template' => $article->template_type
            ]);

            // Buscar artigo irmão
            $siblingArticle = $this->findSiblingArticle($article, $vehicleData);

            if (!$siblingArticle) {
                Log::warning('TirePressureArticleObserver: Artigo irmão não encontrado', [
                    'vehicle' => $vehicleIdentifier,
                    'template_original' => $article->template_type
                ]);
                return;
            }

            // Verificar se irmão precisa de sincronização
            if ($siblingArticle->vehicle_data_version === 'v3.1') {
                Log::info('TirePressureArticleObserver: Artigo irmão já está atualizado', [
                    'sibling_id' => $siblingArticle->_id,
                    'vehicle' => $vehicleIdentifier
                ]);
                return;
            }

            // Sincronizar dados
            $this->applySyncToSibling($siblingArticle, $article->vehicle_data, $vehicleIdentifier);

            Log::info('TirePressureArticleObserver: Sincronização concluída', [
                'original_id' => $article->_id,
                'sibling_id' => $siblingArticle->_id,
                'vehicle' => $vehicleIdentifier,
                'templates_synced' => [$article->template_type, $siblingArticle->template_type]
            ]);

        } catch (\Exception $e) {
            Log::error('TirePressureArticleObserver: Erro na sincronização', [
                'article_id' => $article->_id,
                'vehicle' => $vehicleIdentifier ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Encontrar artigo irmão (mesmo veículo, template diferente)
     */
    protected function findSiblingArticle(TirePressureArticle $article, array $vehicleData): ?TirePressureArticle
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];

        // Buscar usando diferentes estratégias
        $strategies = [
            // Estratégia 1: Usar sibling_article_id se disponível
            function () use ($article) {
                if (!empty($article->sibling_article_id)) {
                    return TirePressureArticle::where('_id', $article->sibling_article_id)->first();
                }
                return null;
            },

            // Estratégia 2: Busca direta pelos dados do veículo
            function () use ($make, $model, $year, $article) {
                return TirePressureArticle::where('vehicle_data.make', $make)
                    ->where('vehicle_data.model', $model)
                    ->where('vehicle_data.year', $year)
                    ->where('template_type', '!=', $article->template_type)
                    ->where('_id', '!=', $article->_id)
                    ->first();
            },

            // Estratégia 3: Busca por campos individuais
            function () use ($make, $model, $year, $article) {
                return TirePressureArticle::where('make', $make)
                    ->where('model', $model)
                    ->where('year', $year)
                    ->where('template_type', '!=', $article->template_type)
                    ->where('_id', '!=', $article->_id)
                    ->first();
            },

            // Estratégia 4: Regex no vehicle_data (fallback)
            function () use ($vehicleData, $article) {
                $urlSlug = $vehicleData['url_slug'] ?? '';
                if (!empty($urlSlug)) {
                    return TirePressureArticle::whereRaw([
                        'vehicle_data' => ['$regex' => preg_quote($urlSlug)]
                    ])
                        ->where('template_type', '!=', $article->template_type)
                        ->where('_id', '!=', $article->_id)
                        ->first();
                }
                return null;
            }
        ];

        // Tentar cada estratégia até encontrar
        foreach ($strategies as $index => $strategy) {
            $sibling = $strategy();
            if ($sibling) {
                Log::info("TirePressureArticleObserver: Irmão encontrado usando estratégia " . ($index + 1), [
                    'sibling_id' => $sibling->_id,
                    'sibling_template' => $sibling->template_type
                ]);
                return $sibling;
            }
        }

        return null;
    }

    /**
     * Aplicar sincronização no artigo irmão
     */
    protected function applySyncToSibling(TirePressureArticle $siblingArticle, array $correctedVehicleData, string $vehicleIdentifier): void
    {
        // Marcar como operação de sincronização para evitar loops
        $this->markAsSyncOperation($siblingArticle);

        // Aplicar os mesmos dados corrigidos
        $siblingArticle->vehicle_data = $correctedVehicleData;
        $siblingArticle->vehicle_data_version = 'v3.1';
        $siblingArticle->vehicle_data_corrected_at = now();

        // Atualizar campos derivados se existirem
        if (isset($correctedVehicleData['pressure_light_front'])) {
            $siblingArticle->pressure_light_front = (string) $correctedVehicleData['pressure_light_front'];
        }

        if (isset($correctedVehicleData['pressure_light_rear'])) {
            $siblingArticle->pressure_light_rear = (string) $correctedVehicleData['pressure_light_rear'];
        }

        if (isset($correctedVehicleData['pressure_spare'])) {
            $siblingArticle->pressure_spare = (string) $correctedVehicleData['pressure_spare'];
        }

        // Salvar sem disparar observers novamente
        $siblingArticle->saveQuietly();

        Log::info('TirePressureArticleObserver: Dados sincronizados', [
            'sibling_id' => $siblingArticle->_id,
            'vehicle' => $vehicleIdentifier,
            'synced_fields' => [
                'vehicle_data_version' => 'v3.1',
                'pressure_light_front' => $correctedVehicleData['pressure_light_front'] ?? null,
                'pressure_light_rear' => $correctedVehicleData['pressure_light_rear'] ?? null,
                'pressure_spare' => $correctedVehicleData['pressure_spare'] ?? null
            ]
        ]);
    }

    /**
     * Verificar se é uma operação de sincronização (evitar loops)
     */
    protected function isFromSyncOperation(TirePressureArticle $article): bool
    {
        // Verificar se foi marcado como operação de sync
        return $article->getAttribute('_sync_operation') === true;
    }

    /**
     * Marcar como operação de sincronização
     */
    protected function markAsSyncOperation(TirePressureArticle $article): void
    {
        $article->setAttribute('_sync_operation', true);
    }

    /**
     * Gerar identificador do veículo
     */
    protected function generateVehicleIdentifier(array $vehicleData): string
    {
        return "{$vehicleData['make']} {$vehicleData['model']} {$vehicleData['year']}";
    }
}