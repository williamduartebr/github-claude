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
     * 🆕 NOVA FUNCIONALIDADE: Renderiza a view do template com dados processados
     */
    public function renderTemplate(string $filename)
    {
        $article = $this->mockService->getMockArticle($filename);
        
        if (!$article) {
            return response()->json([
                'error' => "Mock não encontrado: {$filename}"
            ], 404);
        }
        
        try {
            // 1. Detecta template
            $templateType = $this->templateDetector->detectTemplate($article);
            
            // 2. Cria ViewModel
            $viewModel = $this->viewModelFactory->make($templateType, $article);
            
            // 3. Processa dados
            $processedData = $viewModel->processArticleData();
            
            // 4. Renderiza template específico
            $templateName = $processedData->getTemplateName();
            $viewPath = "auto-info-center::article.templates.{$templateName}";

            // 5. Retorna view renderizada
            return view($viewPath, ['article' => $processedData]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao renderizar template',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile()),
                'template_detected' => $templateType ?? 'unknown'
            ], 500);
        }
    }

    /**
     * 🆕 NOVA FUNCIONALIDADE: Renderiza a view do template AMP com dados processados
     */
    public function renderTemplateAmp(string $filename)
    {
        $article = $this->mockService->getMockArticle($filename);
        
        if (!$article) {
            return response()->json([
                'error' => "Mock não encontrado: {$filename}"
            ], 404);
        }
        
        try {
            // 1. Detecta template
            $templateType = $this->templateDetector->detectTemplate($article);
            
            // 2. Cria ViewModel
            $viewModel = $this->viewModelFactory->make($templateType, $article);
            
            // 3. Processa dados
            $processedData = $viewModel->processArticleData();
            
            // 4. Renderiza template AMP específico
            $templateName = $processedData->getTemplateName();
            $viewPath = "auto-info-center::article.templates.amp.{$templateName}";

            // 5. Retorna view AMP renderizada
            return view($viewPath, [
                'article' => $processedData,
                'canonical' => $processedData->getData()['canonical_url'] ?? ''
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao renderizar template AMP',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile()),
                'template_detected' => $templateType ?? 'unknown'
            ], 500);
        }
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
    
    /**
     * 🆕 DEBUG: Visualiza dados processados pelo ViewModel
     */
    public function debugProcessedData(string $filename)
    {
        $article = $this->mockService->getMockArticle($filename);
        
        if (!$article) {
            return response()->json([
                'error' => "Mock não encontrado: {$filename}"
            ], 404);
        }
        
        try {
            // 1. Detecta template
            $templateType = $this->templateDetector->detectTemplate($article);
            
            // 2. Cria ViewModel
            $viewModel = $this->viewModelFactory->make($templateType, $article);
            
            // 3. Processa dados
            $processedData = $viewModel->processArticleData();
            
            // 4. Retorna dados estruturados para análise
            return response()->json([
                'template_detected' => $templateType,
                'template_name' => $processedData->getTemplateName(),
                'processed_data_keys' => array_keys($processedData->getData()),
                'processed_data' => $processedData->getData(),
                'breadcrumbs' => $processedData->getBreadcrumbs() ?? [],
                'canonical_url' => $processedData->getCanonicalUrl() ?? ''
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao processar dados',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ], 500);
        }
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