<?php

namespace Src\GuideDataCenter\Domain\Services;

use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;
use Illuminate\Support\Str;

/**
 * Class GuideCreationService
 * 
 * Serviço responsável pela criação completa de guias com:
 * - Validação de payload
 * - Normalização de slugs
 * - Criação de SEO inicial
 * - Criação de cluster básico
 */
class GuideCreationService
{
    /**
     * @var GuideRepositoryInterface
     */
    protected $guideRepository;

    /**
     * @var GuideCategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var GuideValidatorService
     */
    protected $validator;

    /**
     * @var GuideSeoService
     */
    protected $seoService;

    /**
     * @var GuideClusterService
     */
    protected $clusterService;

    /**
     * Constructor
     */
    public function __construct(
        GuideRepositoryInterface $guideRepository,
        GuideCategoryRepositoryInterface $categoryRepository,
        GuideValidatorService $validator,
        GuideSeoService $seoService,
        GuideClusterService $clusterService
    ) {
        $this->guideRepository = $guideRepository;
        $this->categoryRepository = $categoryRepository;
        $this->validator = $validator;
        $this->seoService = $seoService;
        $this->clusterService = $clusterService;
    }

    /**
     * Cria um novo guia completo
     *
     * @param array $data
     * @return Guide
     * @throws \Exception
     */
    public function createGuide(array $data): Guide
    {
        // 1. Valida os dados de entrada
        $this->validator->validateGuideData($data);

        // 2. Normaliza os dados
        $normalizedData = $this->normalizeGuideData($data);

        // 3. Valida unicidade do slug
        $this->ensureUniqueSlug($normalizedData['slug']);

        // 4. Cria o guia
        $guide = $this->guideRepository->createGuide($normalizedData);

        // 5. Cria SEO inicial
        $this->seoService->createInitialSeo($guide);

        // 6. Cria cluster básico
        $this->clusterService->createBasicCluster($guide);

        return $guide->refresh();
    }

    /**
     * Cria guia a partir de template
     *
     * @param string $template
     * @param array $vehicleData
     * @param array $content
     * @return Guide
     */
    public function createFromTemplate(string $template, array $vehicleData, array $content): Guide
    {
        $data = array_merge([
            'template' => $template,
            'payload' => $content,
        ], $vehicleData);

        return $this->createGuide($data);
    }

    /**
     * Normaliza os dados do guia
     *
     * @param array $data
     * @return array
     */
    protected function normalizeGuideData(array $data): array
    {
        // Normaliza slugs de marca e modelo
        $data['make_slug'] = $this->normalizeSlug($data['make'] ?? '');
        $data['model_slug'] = $this->normalizeSlug($data['model'] ?? '');

        // Gera slug único do guia
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateGuideSlug($data);
        }

        // Gera URL
        if (empty($data['url'])) {
            $data['url'] = $this->generateGuideUrl($data);
        }

        // Normaliza anos
        $data['year_start'] = isset($data['year_start']) ? (int) $data['year_start'] : null;
        $data['year_end'] = isset($data['year_end']) ? (int) $data['year_end'] : null;

        // Garante que payload é array
        if (empty($data['payload']) || !is_array($data['payload'])) {
            $data['payload'] = [];
        }

        // Inicializa arrays vazios
        $data['links_internal'] = $data['links_internal'] ?? [];
        $data['links_related'] = $data['links_related'] ?? [];
        $data['seo'] = $data['seo'] ?? [];

        return $data;
    }

    /**
     * Normaliza uma string para slug
     *
     * @param string $text
     * @return string
     */
    protected function normalizeSlug(string $text): string
    {
        $slug = Str::slug($text);
        
        // Remove acentos e caracteres especiais
        $slug = $this->removeAccents($slug);
        
        // Converte para minúsculas
        $slug = strtolower($slug);
        
        // Remove caracteres não permitidos
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        
        // Remove hífens duplicados
        $slug = preg_replace('/-+/', '-', $slug);
        
        // Remove hífens do início e fim
        $slug = trim($slug, '-');

        return $slug;
    }

    /**
     * Remove acentos de uma string
     *
     * @param string $text
     * @return string
     */
    protected function removeAccents(string $text): string
    {
        $unwanted = [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
        ];

        return strtr($text, $unwanted);
    }

    /**
     * Gera slug único para o guia
     *
     * @param array $data
     * @return string
     */
    protected function generateGuideSlug(array $data): string
    {
        $parts = [
            $data['make_slug'] ?? '',
            $data['model_slug'] ?? '',
        ];

        // Adiciona categoria se disponível
        if (!empty($data['guide_category_id'])) {
            $category = $this->categoryRepository->findById($data['guide_category_id']);
            if ($category) {
                $parts[] = $category->slug;
            }
        }

        // Adiciona versão se disponível
        if (!empty($data['version'])) {
            $parts[] = $this->normalizeSlug($data['version']);
        }

        // Adiciona anos se disponível
        if (!empty($data['year_start'])) {
            if (!empty($data['year_end']) && $data['year_end'] != $data['year_start']) {
                $parts[] = $data['year_start'] . '-' . $data['year_end'];
            } else {
                $parts[] = (string) $data['year_start'];
            }
        }

        $slug = implode('-', array_filter($parts));

        return $slug ?: 'guide-' . uniqid();
    }

    /**
     * Gera URL do guia
     *
     * @param array $data
     * @return string
     */
    protected function generateGuideUrl(array $data): string
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $slug = $data['slug'] ?? $this->generateGuideSlug($data);

        return $baseUrl . '/guias/' . $slug;
    }

    /**
     * Garante que o slug é único
     *
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    protected function ensureUniqueSlug(string $slug): void
    {
        $existing = $this->guideRepository->findBySlug($slug);

        if ($existing) {
            throw new \Exception("A guide with slug '{$slug}' already exists");
        }
    }

    /**
     * Duplica um guia existente com novos dados
     *
     * @param string $guideId
     * @param array $overrides
     * @return Guide
     */
    public function duplicateGuide(string $guideId, array $overrides = []): Guide
    {
        $original = $this->guideRepository->findById($guideId);

        if (!$original) {
            throw new \Exception("Guide with ID {$guideId} not found");
        }

        $data = array_merge(
            $original->toArray(),
            $overrides
        );

        // Remove ID para criar novo registro
        unset($data['_id']);
        unset($data['id']);

        // Força geração de novo slug
        $data['slug'] = '';

        return $this->createGuide($data);
    }

    /**
     * Importa múltiplos guias em lote
     *
     * @param array $guidesData
     * @return array ['success' => [], 'errors' => []]
     */
    public function batchCreate(array $guidesData): array
    {
        $results = [
            'success' => [],
            'errors' => [],
        ];

        foreach ($guidesData as $index => $data) {
            try {
                $guide = $this->createGuide($data);
                $results['success'][] = [
                    'index' => $index,
                    'guide_id' => $guide->_id,
                    'slug' => $guide->slug,
                ];
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'index' => $index,
                    'data' => $data,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
