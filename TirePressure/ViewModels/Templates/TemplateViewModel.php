<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Illuminate\Support\Carbon;

abstract class TemplateViewModel
{
   /**
    * Artigo original do banco de dados
    */
   protected Article $article;
   
   /**
    * Dados processados do artigo
    */
   protected array $processedData = [];
   
   /**
    * Nome do template a ser utilizado
    */
   protected string $templateName;

   /**
    * Constructor
    */
   public function __construct(Article $article)
   {
       $this->article = $article;
   }

   /**
    * Processa os dados do artigo para o formato necessário ao template
    * 
    * @return static
    */
   public function processArticleData(): static
   {
       // Dados básicos comuns a todos os templates
       $this->processedData = [
           'id' => $this->article->id,
           'title' => $this->article->title,
           'slug' => $this->article->slug,
           'category' => [
               'name' => $this->article->category_name,
               'slug' => $this->article->category_slug,
           ],
           'updated_at' => $this->article->updated_at,
           'formated_updated_at' => \Carbon\Carbon::parse($this->article->updated_at)->format('d/m/Y'),
           'created_at' => $this->article->created_at,
           'author' => $this->article->author,
           'vehicle_info' => $this->article->vehicle_info,
           'related_topics' => $this->article->related_topics,
           'metadata' => $this->article->metadata,
           'seo_data' => $this->article->seo_data,
       ];
       
       // Processa dados específicos através da implementação concreta
       $this->processTemplateSpecificData();
       
       return $this;
   }
   
   /**
    * Método abstrato para processamento de dados específicos do template
    * Deve ser implementado por cada classe concreta
    */
   abstract protected function processTemplateSpecificData(): void;
   
   /**
    * Retorna o nome do template a ser utilizado
    * 
    * @return string
    */
   public function getTemplateName(): string
   {
       return $this->templateName;
   }
   
   /**
    * Fornece acesso aos dados processados do artigo
    * 
    * @param string|null $key
    * @return mixed
    */
   public function getData(?string $key = null)
   {
       if ($key !== null) {
           return $this->processedData[$key] ?? null;
       }
       
       return $this->processedData;
   }
   
   /**
    * Permite acessar os dados processados como propriedades
    * 
    * @param string $name
    * @return mixed
    */
   public function __get(string $name)
   {
       return $this->getData($name);
   }
   
   /**
    * Verifica se uma propriedade existe nos dados processados
    * 
    * @param string $name
    * @return bool
    */
   public function __isset(string $name): bool
   {
       return isset($this->processedData[$name]);
   }
}