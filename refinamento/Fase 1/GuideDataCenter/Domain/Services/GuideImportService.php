<?php

namespace Src\GuideDataCenter\Domain\Services;

use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;

/**
 * Class GuideImportService
 * 
 * Serviço para importação de guias existentes
 */
class GuideImportService
{
    protected $guideRepository;
    protected $creationService;
    protected $validatorService;

    public function __construct(
        GuideRepositoryInterface $guideRepository,
        GuideCreationService $creationService,
        GuideValidatorService $validatorService
    ) {
        $this->guideRepository = $guideRepository;
        $this->creationService = $creationService;
        $this->validatorService = $validatorService;
    }

    /**
     * Importa guias de array
     */
    public function importFromArray(array $guides): array
    {
        $results = [
            'imported' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($guides as $index => $guideData) {
            try {
                $normalized = $this->normalizeImportData($guideData);
                $this->creationService->createGuide($normalized);
                $results['imported']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'index' => $index,
                    'error' => $e->getMessage(),
                    'data' => $guideData,
                ];
            }
        }

        return $results;
    }

    /**
     * Normaliza dados de importação
     */
    protected function normalizeImportData(array $data): array
    {
        // Padroniza campos
        $normalized = [
            'make' => $data['marca'] ?? $data['make'] ?? '',
            'model' => $data['modelo'] ?? $data['model'] ?? '',
            'version' => $data['versao'] ?? $data['version'] ?? null,
            'motor' => $data['motor'] ?? null,
            'fuel' => $data['combustivel'] ?? $data['fuel'] ?? null,
            'year_start' => $data['ano_inicial'] ?? $data['year_start'] ?? null,
            'year_end' => $data['ano_final'] ?? $data['year_end'] ?? null,
            'guide_category_id' => $data['category_id'] ?? $data['guide_category_id'] ?? '',
            'template' => $data['template'] ?? 'default',
            'payload' => $data['conteudo'] ?? $data['payload'] ?? [],
        ];

        return $normalized;
    }

    /**
     * Importa de JSON
     */
    public function importFromJson(string $jsonContent): array
    {
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
        }

        return $this->importFromArray($data);
    }

    /**
     * Corrige estrutura de guias existentes
     */
    public function fixExistingGuides(): array
    {
        $results = ['fixed' => 0, 'errors' => 0];

        $guides = $this->guideRepository->paginate(100);

        foreach ($guides as $guide) {
            try {
                // Corrige slugs
                if (empty($guide->make_slug)) {
                    $guide->make_slug = \Illuminate\Support\Str::slug($guide->make);
                }
                if (empty($guide->model_slug)) {
                    $guide->model_slug = \Illuminate\Support\Str::slug($guide->model);
                }
                
                // Corrige arrays vazios
                if (!is_array($guide->payload)) {
                    $guide->payload = [];
                }
                if (!is_array($guide->links_internal)) {
                    $guide->links_internal = [];
                }

                $guide->save();
                $results['fixed']++;
            } catch (\Exception $e) {
                $results['errors']++;
            }
        }

        return $results;
    }
}
