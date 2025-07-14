<?php

namespace Src\ArticleGenerator\Presentation\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection;
use Src\ArticleGenerator\Infrastructure\Services\ArticleCorrectionService;

class ArticleCorrectionController extends Controller
{
    protected $correctionService;

    public function __construct(ArticleCorrectionService $correctionService)
    {
        $this->correctionService = $correctionService;
    }

    /**
     * Lista todas as correções
     */
    public function index(Request $request)
    {
        $query = ArticleCorrection::query();

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('correction_type', $request->type);
        }

        if ($request->has('article_slug')) {
            $query->where('article_slug', 'like', '%' . $request->article_slug . '%');
        }

        $corrections = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $corrections,
            'stats' => [
                'pending' => ArticleCorrection::where('status', ArticleCorrection::STATUS_PENDING)->count(),
                'processing' => ArticleCorrection::where('status', ArticleCorrection::STATUS_PROCESSING)->count(),
                'completed' => ArticleCorrection::where('status', ArticleCorrection::STATUS_COMPLETED)->count(),
                'failed' => ArticleCorrection::where('status', ArticleCorrection::STATUS_FAILED)->count(),
            ]
        ]);
    }

    /**
     * Cria uma nova correção para um artigo
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'article_slug' => 'required|string',
            'correction_type' => 'required|string|in:introduction_fix,seo_fix,content_enhancement,entity_extraction',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $slug = $request->article_slug;
            $type = $request->correction_type;

            // Verificar se o artigo existe
            $article = Article::where('slug', $slug)->first();
            if (!$article) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Artigo não encontrado'
                ], 404);
            }

            // Verificar se já existe correção pendente do mesmo tipo
            $existingCorrection = ArticleCorrection::where('article_slug', $slug)
                ->where('correction_type', $type)
                ->whereIn('status', [ArticleCorrection::STATUS_PENDING, ArticleCorrection::STATUS_PROCESSING])
                ->first();

            if ($existingCorrection) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Já existe uma correção pendente deste tipo para este artigo',
                    'existing_correction_id' => $existingCorrection->_id
                ], 409);
            }

            // Criar correção baseada no tipo
            if ($type === ArticleCorrection::TYPE_INTRODUCTION_FIX) {
                $correction = $this->correctionService->fixIntroductionAndSeo($slug);
            } else {
                // Para outros tipos, criar correção básica
                $originalData = $this->extractOriginalData($article, $type);
                $correction = ArticleCorrection::createCorrection(
                    $slug,
                    $type,
                    $originalData,
                    $request->description
                );
            }

            if ($correction) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Correção criada com sucesso',
                    'data' => $correction
                ], 201);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Falha ao criar correção'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error("Erro ao criar correção: " . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Mostra detalhes de uma correção específica
     */
    public function show($id)
    {
        $correction = ArticleCorrection::find($id);

        if (!$correction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Correção não encontrada'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $correction
        ]);
    }

    /**
     * Processa uma correção específica
     */
    public function process($id)
    {
        $correction = ArticleCorrection::find($id);

        if (!$correction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Correção não encontrada'
            ], 404);
        }

        if ($correction->status !== ArticleCorrection::STATUS_PENDING) {
            return response()->json([
                'status' => 'error',
                'message' => 'Correção não está pendente'
            ], 409);
        }

        try {
            $success = $this->correctionService->processCorrection($correction);

            if ($success) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Correção processada com sucesso',
                    'data' => $correction->fresh()
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Falha ao processar correção'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error("Erro ao processar correção {$id}: " . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Processa múltiplas correções
     */
    public function processBatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'correction_ids' => 'required|array',
            'correction_ids.*' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $correctionIds = $request->correction_ids;
        $results = [];

        foreach ($correctionIds as $id) {
            try {
                $correction = ArticleCorrection::find($id);
                
                if (!$correction) {
                    $results[$id] = ['status' => 'error', 'message' => 'Correção não encontrada'];
                    continue;
                }

                if ($correction->status !== ArticleCorrection::STATUS_PENDING) {
                    $results[$id] = ['status' => 'error', 'message' => 'Correção não está pendente'];
                    continue;
                }

                $success = $this->correctionService->processCorrection($correction);
                
                $results[$id] = [
                    'status' => $success ? 'success' : 'error',
                    'message' => $success ? 'Processada com sucesso' : 'Falha ao processar'
                ];

                // Delay para evitar rate limiting
                sleep(1);

            } catch (\Exception $e) {
                Log::error("Erro ao processar correção {$id}: " . $e->getMessage());
                $results[$id] = ['status' => 'error', 'message' => 'Erro interno'];
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Processamento em lote concluído',
            'results' => $results
        ]);
    }

    /**
     * Remove uma correção
     */
    public function destroy($id)
    {
        $correction = ArticleCorrection::find($id);

        if (!$correction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Correção não encontrada'
            ], 404);
        }

        // Só permite deletar se estiver pendente ou falhou
        if (!in_array($correction->status, [ArticleCorrection::STATUS_PENDING, ArticleCorrection::STATUS_FAILED])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Só é possível deletar correções pendentes ou que falharam'
            ], 409);
        }

        $correction->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Correção removida com sucesso'
        ]);
    }

    /**
     * Busca um artigo por slug e retorna dados para análise
     */
    public function analyzeArticle($slug)
    {
        $article = Article::where('slug', $slug)->first();

        if (!$article) {
            return response()->json([
                'status' => 'error',
                'message' => 'Artigo não encontrado'
            ], 404);
        }

        // Verificar se há problemas na introdução
        $introducao = $article->content['introducao'] ?? '';
        $hasIntroductionIssues = $this->detectIntroductionIssues($introducao);

        // Verificar correções existentes
        $existingCorrections = ArticleCorrection::where('article_slug', $slug)->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'article' => [
                    'slug' => $article->slug,
                    'title' => $article->title,
                    'introducao' => $introducao,
                    'seo_data' => $article->seo_data,
                    'extracted_entities' => $article->extracted_entities
                ],
                'analysis' => [
                    'has_introduction_issues' => $hasIntroductionIssues,
                    'issues_detected' => $this->getDetectedIssues($introducao),
                    'suggested_corrections' => $this->getSuggestedCorrections($hasIntroductionIssues)
                ],
                'existing_corrections' => $existingCorrections,
                'correction_stats' => [
                    'pending' => $existingCorrections->where('status', ArticleCorrection::STATUS_PENDING)->count(),
                    'completed' => $existingCorrections->where('status', ArticleCorrection::STATUS_COMPLETED)->count(),
                    'failed' => $existingCorrections->where('status', ArticleCorrection::STATUS_FAILED)->count()
                ]
            ]
        ]);
    }

    /**
     * Extrai dados originais baseado no tipo de correção
     */
    protected function extractOriginalData($article, $type)
    {
        switch ($type) {
            case ArticleCorrection::TYPE_INTRODUCTION_FIX:
                return [
                    'title' => $article->title,
                    'introducao' => $article->content['introducao'] ?? '',
                    'seo_data' => [
                        'page_title' => $article->seo_data['page_title'] ?? '',
                        'meta_description' => $article->seo_data['meta_description'] ?? ''
                    ]
                ];

            case ArticleCorrection::TYPE_SEO_FIX:
                return [
                    'seo_data' => $article->seo_data
                ];

            case ArticleCorrection::TYPE_ENTITY_EXTRACTION:
                return [
                    'extracted_entities' => $article->extracted_entities,
                    'title' => $article->title
                ];

            default:
                return [
                    'content' => $article->content,
                    'title' => $article->title
                ];
        }
    }

    /**
     * Detecta problemas na introdução
     */
    protected function detectIntroductionIssues($introducao)
    {
        if (empty($introducao)) {
            return true;
        }

        // Verificar padrões problemáticos
        $issues = [
            // Frases interrompidas ou mal formatadas
            preg_match('/\s{2,}/', $introducao), // Múltiplos espaços
            preg_match('/,\s*,/', $introducao), // Vírgulas duplas
            preg_match('/\.\s*\./', $introducao), // Pontos duplos
            strpos($introducao, ' específico para motores') !== false, // Texto mal inserido
            preg_match('/[a-z]\s+[A-Z][a-z]/', $introducao), // Quebras estranhas no meio de frases
            strlen($introducao) < 100, // Muito curta
            substr_count($introducao, '.') < 2, // Muito poucas frases
        ];

        return in_array(true, $issues, true);
    }

    /**
     * Lista problemas detectados
     */
    protected function getDetectedIssues($introducao)
    {
        $issues = [];

        if (empty($introducao)) {
            $issues[] = 'Introdução vazia';
        }

        if (preg_match('/\s{2,}/', $introducao)) {
            $issues[] = 'Múltiplos espaços consecutivos';
        }

        if (preg_match('/,\s*,/', $introducao)) {
            $issues[] = 'Vírgulas duplicadas';
        }

        if (strpos($introducao, ' específico para motores') !== false) {
            $issues[] = 'Texto mal inserido detectado';
        }

        if (strlen($introducao) < 100) {
            $issues[] = 'Introdução muito curta';
        }

        if (substr_count($introducao, '.') < 2) {
            $issues[] = 'Muito poucas frases';
        }

        return $issues;
    }

    /**
     * Sugere correções baseado nos problemas detectados
     */
    protected function getSuggestedCorrections($hasIssues)
    {
        if (!$hasIssues) {
            return [];
        }

        return [
            [
                'type' => ArticleCorrection::TYPE_INTRODUCTION_FIX,
                'name' => 'Correção de Introdução e SEO',
                'description' => 'Corrige problemas de formatação na introdução e otimiza dados SEO'
            ]
        ];
    }
}