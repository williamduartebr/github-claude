<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Src\AutoInfoCenter\Domain\Eloquent\Article;

class GenericArticleViewModel extends TemplateViewModel
{
   /**
    * Nome do template a ser utilizado
    */
   protected string $templateName = 'generic_article';

   /**
    * Processa dados específicos para o template genérico
    */
   protected function processTemplateSpecificData(): void
   {
       // Processa o conteúdo de maneira genérica
       $content = $this->article->content;
       
       // Título e introdução
       $this->processedData['main_title'] = $this->article->title;
       $this->processedData['introduction'] = $content['introducao'] ?? '';
       
       // Conteúdo principal - transforma em seções estruturadas
       $this->processMainContent($content);
       
       // Metadados
       $this->processedData['tags'] = $this->article->tags ?? [];
       
       // Informações do veículo
       $this->processVehicleInfo();
       
       // Informações de SEO
       $this->processSeoData();
   }
   
   /**
    * Processa o conteúdo principal do artigo
    * 
    * @param array $content
    */
   private function processMainContent(array $content): void
   {
       $sections = [];
       
       // Tenta extrair seções do conteúdo de forma genérica
       foreach ($content as $key => $value) {
           // Ignora chaves específicas que já tratamos ou que são metadados
           if (in_array($key, ['introducao', 'template'])) {
               continue;
           }
           
           // Processa cada tipo de conteúdo de forma adequada
           if (is_string($value)) {
               // Conteúdo de texto simples
               $sections[] = [
                   'type' => 'text',
                   'title' => $this->formatSectionTitle($key),
                   'content' => $value
               ];
           } elseif (is_array($value) && $this->isSequentialArray($value)) {
               // Lista de itens (array sequencial)
               $sections[] = [
                   'type' => 'list',
                   'title' => $this->formatSectionTitle($key),
                   'items' => $value
               ];
           } elseif (is_array($value)) {
               // Objeto/estrutura associativa
               $sections[] = [
                   'type' => 'structured',
                   'title' => $this->formatSectionTitle($key),
                   'structure' => $value
               ];
           }
       }
       
       $this->processedData['content_sections'] = $sections;
   }
   
   /**
    * Verifica se um array é sequencial (lista numérica)
    * 
    * @param array $array
    * @return bool
    */
   private function isSequentialArray(array $array): bool
   {
       return array_keys($array) === range(0, count($array) - 1);
   }
   
   /**
    * Formata o título da seção a partir da chave
    * 
    * @param string $key
    * @return string
    */
   private function formatSectionTitle(string $key): string
   {
       // Remove underscores e converte para título
       $title = str_replace('_', ' ', $key);
       return ucwords($title);
   }
   
   /**
    * Processa informações do veículo
    */
   private function processVehicleInfo(): void
   {
       $vehicleInfo = $this->article->vehicle_info;
       
       if (!empty($vehicleInfo)) {
           // Formata informações do veículo para exibição
           $this->processedData['vehicle'] = [
               'name' => trim($vehicleInfo['make'] . ' ' . $vehicleInfo['model']),
               'year' => $vehicleInfo['year'] ?? '',
               'engine' => $vehicleInfo['engine'] ?? '',
               'version' => $vehicleInfo['version'] ?? '',
               'fuel' => $this->formatFuelType($vehicleInfo['fuel'] ?? ''),
               'category' => $vehicleInfo['category'] ?? '',
           ];
       }
   }
   
   /**
    * Formata o tipo de combustível
    * 
    * @param string $fuel
    * @return string
    */
   private function formatFuelType(string $fuel): string
   {
       $fuelTypes = [
           'flex' => 'Flex (Gasolina/Etanol)',
           'gasoline' => 'Gasolina',
           'ethanol' => 'Etanol',
           'diesel' => 'Diesel',
           'electric' => 'Elétrico',
           'hybrid' => 'Híbrido',
           'cng' => 'GNV',
       ];
       
       return $fuelTypes[$fuel] ?? $fuel;
   }
   
   /**
    * Processa dados de SEO
    */
   private function processSeoData(): void
   {
       $seoData = $this->article->seo_data;
       
       $this->processedData['seo'] = [
           'title' => $seoData['page_title'] ?? $this->article->title,
           'description' => $seoData['meta_description'] ?? '',
           'canonical' => url('/info/' . $this->article->category_slug . '/' . $this->article->slug),
           'h1' => $seoData['h1'] ?? $this->article->title,
           'faq' => $seoData['faq_questions'] ?? [],
       ];
   }
}