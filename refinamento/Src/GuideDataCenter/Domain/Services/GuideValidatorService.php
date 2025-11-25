<?php

namespace Src\GuideDataCenter\Domain\Services;

/**
 * Class GuideValidatorService
 * 
 * Valida estrutura e dados dos guias
 */
class GuideValidatorService
{
    /**
     * Valida dados de criação de guia
     */
    public function validateGuideData(array $data): bool
    {
        $this->validateRequiredFields($data);
        $this->validateVehicleData($data);
        $this->validateYearRange($data);
        $this->validatePayload($data);

        return true;
    }

    /**
     * Valida campos obrigatórios
     */
    protected function validateRequiredFields(array $data): void
    {
        $required = ['make', 'model', 'guide_category_id'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Field '{$field}' is required");
            }
        }
    }

    /**
     * Valida dados do veículo
     */
    protected function validateVehicleData(array $data): void
    {
        if (!empty($data['make']) && strlen($data['make']) < 2) {
            throw new \InvalidArgumentException("Make must have at least 2 characters");
        }

        if (!empty($data['model']) && strlen($data['model']) < 2) {
            throw new \InvalidArgumentException("Model must have at least 2 characters");
        }
    }

    /**
     * Valida range de anos
     */
    protected function validateYearRange(array $data): void
    {
        if (!empty($data['year_start'])) {
            $yearStart = (int) $data['year_start'];
            
            if ($yearStart < 1900 || $yearStart > (date('Y') + 2)) {
                throw new \InvalidArgumentException("Invalid year_start: {$yearStart}");
            }

            if (!empty($data['year_end'])) {
                $yearEnd = (int) $data['year_end'];
                
                if ($yearEnd < $yearStart) {
                    throw new \InvalidArgumentException("year_end must be >= year_start");
                }
            }
        }
    }

    /**
     * Valida payload do guia
     */
    protected function validatePayload(array $data): void
    {
        if (!empty($data['payload']) && !is_array($data['payload'])) {
            throw new \InvalidArgumentException("Payload must be an array");
        }
    }

    /**
     * Valida coerência entre make/model/version
     */
    public function validateVehicleCoherence(array $data): bool
    {
        // Implementação básica - pode ser expandida
        return true;
    }
}
