<?php
namespace Src\AutoInfoCenter\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Src\AutoInfoCenter\Domain\Services\TemplateDetectorService;
use Src\AutoInfoCenter\Factories\TemplateViewModelFactory;
use Src\AutoInfoCenter\Infrastructure\Services\MockArticleService;

class TestMockController extends Controller
{
    public function __construct(
        private MockArticleService $mockService,
        private TemplateDetectorService $templateDetector,
        private TemplateViewModelFactory $viewModelFactory
    ) {}
    
    /**
     * Testa todos os mocks
     */
    public function testAllMocks()
    {
        $articles = $this->mockService->getAllMockArticles();
        $results = [];
        
        foreach ($articles as $article) {
            $results[] = $this->testSingleMock($article);
        }
        
        return response()->json([
            'total_tested' => count($results),
            'results' => $results
        ]);
    }
    
    /**
     * Testa um mock específico
     */
    public function testMock(string $filename)
    {
        $article = $this->mockService->getMockArticle($filename);
        
        if (!$article) {
            return response()->json([
                'error' => "Mock não encontrado: {$filename}"
            ], 404);
        }
        
        $result = $this->testSingleMock($article);
        
        return response()->json($result);
    }
    
    /**
     * 🔍 DEBUG: Apenas verificar Article sem processar
     */
    public function debugMock(string $filename)
    {
        $article = $this->mockService->getMockArticle($filename);
        
        if (!$article) {
            return response()->json([
                'error' => "Mock não encontrado: {$filename}"
            ], 404);
        }
        
        return response()->json([
            'article_title' => $article->title,
            'article_template' => $article->template,
            'extracted_entities_exists' => isset($article->extracted_entities),
            'extracted_entities' => $article->extracted_entities ?? 'NOT_SET',
            'marca_exists' => isset($article->extracted_entities['marca']),
            'marca_value' => $article->extracted_entities['marca'] ?? 'NOT_FOUND',
            'all_attributes' => $article->getAttributes(),
            'vehicle_data' => $article->vehicle_data ?? 'NOT_SET'
        ]);
    }
    
    private function testSingleMock($article): array
    {
        try {
            // 🔍 DEBUG ANTES DE PROCESSAR
            \Log::info('Pre-processing Article Debug:', [
                'title' => $article->title,
                'template' => $article->template,
                'extracted_entities' => $article->extracted_entities ?? 'NOT_SET',
                'marca' => $article->extracted_entities['marca'] ?? 'NOT_FOUND'
            ]);
            
            // 1. Detecta template
            $templateType = $this->templateDetector->detectTemplate($article);
            
            // 2. Cria ViewModel
            $viewModel = $this->viewModelFactory->make($templateType, $article);
            
            // 3. Processa dados
            $processedData = $viewModel->processArticleData();
            
            // 4. Valida estrutura
            $validation = $this->validateStructure($processedData->getData());
            
            return [
                'success' => true,
                'article_title' => $article->title,
                'template_detected' => $templateType,
                'viewmodel_class' => get_class($viewModel),
                'template_name' => $processedData->getTemplateName(),
                'validation' => $validation,
                'data_keys' => array_keys($processedData->getData()),
                'vehicle_data' => $processedData->getData()['vehicle_data'] ?? null
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'article_title' => $article->title ?? 'Unknown',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'debug_info' => [
                    'extracted_entities' => $article->extracted_entities ?? 'NOT_SET',
                    'marca_exists' => isset($article->extracted_entities['marca']),
                    'all_attributes' => $article->getAttributes()
                ]
            ];
        }
    }
    
    private function validateStructure(array $data): array
    {
        $validation = [
            'required_keys_present' => 0,
            'missing_keys' => [],
            'structure_issues' => []
        ];
        
        $requiredKeys = [
            'title', 'content', 'seo_data', 'structured_data', 
            'canonical_url', 'breadcrumbs'
        ];
        
        foreach ($requiredKeys as $key) {
            if (isset($data[$key])) {
                $validation['required_keys_present']++;
            } else {
                $validation['missing_keys'][] = $key;
            }
        }
        
        return $validation;
    }
}