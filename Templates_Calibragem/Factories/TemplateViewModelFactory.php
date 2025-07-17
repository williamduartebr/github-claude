<?php

namespace Src\AutoInfoCenter\Factories;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\AutoInfoCenter\ViewModels\Templates\OilTableViewModel;
use Src\AutoInfoCenter\ViewModels\Templates\TemplateViewModel;
use Src\AutoInfoCenter\ViewModels\Templates\GenericArticleViewModel;
use Src\AutoInfoCenter\ViewModels\Templates\OilRecommendationViewModel;
use Src\AutoInfoCenter\ViewModels\Templates\ReviewScheduleCarViewModel;
use Src\AutoInfoCenter\ViewModels\Templates\WhenToChangeTiresViewModel;
use Src\AutoInfoCenter\ViewModels\Templates\TireRecommendationViewModel;
use Src\AutoInfoCenter\ViewModels\Templates\ReviewScheduleHybridViewModel;
use Src\AutoInfoCenter\ViewModels\Templates\ReviewScheduleElectricViewModel;
use Src\AutoInfoCenter\ViewModels\Templates\ReviewScheduleMotorcycleViewModel;

class TemplateViewModelFactory
{
   /**
    * Mapeamento de tipos de template para classes ViewModel
    */
   private const TEMPLATE_VIEWMODELS = [
       'oil_recommendation' => OilRecommendationViewModel::class,
       'tire_recommendation' => TireRecommendationViewModel::class,
       'oil_table' => OilTableViewModel::class,
       'review_schedule_car' => ReviewScheduleCarViewModel::class,
       'review_schedule_motorcycle' => ReviewScheduleMotorcycleViewModel::class,
       'review_schedule_hybrid' => ReviewScheduleHybridViewModel::class,
       'review_schedule_electric' => ReviewScheduleElectricViewModel::class,
       'when_to_change_tires' => WhenToChangeTiresViewModel::class,
       
       // Adicione mais mapeamentos conforme implementar novos ViewModels
   ];

   /**
    * Cria uma instância do ViewModel adequado para o tipo de template
    *
    * @param string $templateType
    * @param Article $article
    * @return TemplateViewModel
    */
   public function make(string $templateType, Article $article): TemplateViewModel
   {
       // Verifica se existe um ViewModel específico para o tipo de template
       if (isset(self::TEMPLATE_VIEWMODELS[$templateType])) {
           $viewModelClass = self::TEMPLATE_VIEWMODELS[$templateType];
           
           try {
               // Usa o container para resolver dependências
               return App::make($viewModelClass, ['article' => $article]);
           } catch (\Exception $e) {
               Log::error("Erro ao criar ViewModel para template {$templateType}: " . $e->getMessage());
           }
       }
       
       // Se não encontrar um ViewModel específico ou ocorrer erro, retorna o ViewModel genérico
       return App::make(GenericArticleViewModel::class, ['article' => $article]);
   }
}