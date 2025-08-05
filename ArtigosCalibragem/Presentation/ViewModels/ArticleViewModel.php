<?php

namespace Src\AutoInfoCenter\ViewModels;

use Src\AutoInfoCenter\Domain\Services\ArticleService;
use Src\AutoInfoCenter\Domain\Services\TemplateDetectorService;
use Src\AutoInfoCenter\Factories\TemplateViewModelFactory;
use Illuminate\Support\Facades\Cache;

class ArticleViewModel
{
   /**
    * @var int Tempo de cache em minutos
    */
   private const CACHE_MINUTES = 60;

   public function __construct(
       private ArticleService $articleService,
       private TemplateDetectorService $templateDetector,
       private TemplateViewModelFactory $viewModelFactory
   ) {}

   /**
    * Obtém os dados de um artigo processados para seu template específico
    *
    * @param string $slug
    * @return mixed|null
    */
   public function getArticleBySlug(string $slug)
   {
       $cacheKey = "article:{$slug}";
       
       return Cache::remember($cacheKey, self::CACHE_MINUTES, function () use ($slug) {
           // Obtém o artigo do repositório
           $article = $this->articleService->findBySlug($slug);
           
           if (!$article) {
               return null;
           }
           
           // Detecta o tipo de template com base no campo template
           $templateType = $this->templateDetector->detectTemplate($article);
           
           // Cria o ViewModel específico para o tipo de template
           $templateViewModel = $this->viewModelFactory->make($templateType, $article);
           
           // Processa os dados do artigo para o formato específico do template
           return $templateViewModel->processArticleData();
       });
   }
}