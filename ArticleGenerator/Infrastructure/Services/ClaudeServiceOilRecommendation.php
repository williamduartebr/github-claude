<?php

namespace Src\ArticleGenerator\Infrastructure\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Src\ArticleGenerator\Infrastructure\Traits\VehicleEntityExtractorTrait;

class ClaudeServiceOilRecommendation
{

	use VehicleEntityExtractorTrait;

	protected $apiKey;
	protected $apiUrl = 'https://api.anthropic.com/v1/messages';

	public function __construct()
	{
		$this->apiKey = config('services.claude.api_key');
	}

	/**
	 * Gerar conteúdo sobre óleo recomendado usando a API Claude
	 */
	public function generateOilRecommendationContent($article)
	{
		// Gerar um prompt específico para artigos sobre óleo recomendado
		$prompt = $this->createOilRecommendationPrompt($article);

		try {
			Log::info("Iniciando geração de conteúdo sobre óleo recomendado para: {$article['title']}");

			// Chamar a API Claude
			$response = Http::timeout(180)->withHeaders([
				'x-api-key' => $this->apiKey,
				'anthropic-version' => '2023-06-01',
				'content-type' => 'application/json',
			])->post($this->apiUrl, [
				// 'model' => 'claude-3-5-sonnet-20241022',
				'model' => 'claude-3-7-sonnet-20250219',
				'max_tokens' => 5000,
				'temperature' => 0.3, // Valor mais alto para maior criatividade
				'messages' => [
					[
						'role' => 'user',
						'content' => $prompt
					]
				],
				'system' => "Você é um experiente redator automotivo brasileiro com mais de 15 anos atuando na criação de conteúdo técnico sobre lubrificantes e óleos para veículos. Seu trabalho é criar conteúdo original, informativo e útil sobre óleos recomendados para veículos específicos. Considere marcas disponíveis no Brasil, preços realistas e especificações técnicas precisas. Retorne apenas JSON com o conteúdo solicitado."
			]);

			if ($response->successful()) {
				$content = $response->json('content.0.text');
				$result = $this->processApiResponse($content);

				if ($result) {
					// Corrigir o tipo de veículo usando a API Claude
					$result = $this->correctVehicleTypeWithApi($result, $article);

					// Adicionar variações específicas para óleo recomendado
					$result = $this->addOilRecommendationSpecificVariations($result, $article);

					return $result;
				}
			}

			Log::error("Falha ao obter resposta válida da API Claude: " . ($response->body() ?? 'Sem resposta'));
			return null;
		} catch (\Exception $e) {
			Log::error("Erro na geração de conteúdo sobre óleo recomendado: " . $e->getMessage());
			return null;
		}
	}

	/**
	 * Cria um prompt especializado para artigos sobre óleo recomendado
	 */
	protected function createOilRecommendationPrompt($article)
	{
		$title = $article['title'];
		$keywords = $article['keywords'] ?? '';

		$promptTemplates = [
			"Preciso de um guia completo sobre óleo recomendado para {$title}. Os proprietários deste modelo querem saber exatamente qual óleo usar, especificações técnicas, marcas confiáveis disponíveis no Brasil e procedimentos de troca. Foque em informações práticas e específicas para este veículo.",

			"Como especialista em lubrificantes, crie um artigo detalhado sobre qual óleo é mais adequado para {$title}. Inclua especificações oficiais, alternativas premium e econômicas, procedimentos de troca e orientações sobre condições de uso.",

			"Estou criando conteúdo sobre óleo de motor para {$title}. O proprietário precisa saber as especificações oficiais, melhores marcas para diferentes condições de uso, procedimento de troca e cuidados especiais. Conteúdo técnico mas acessível."
		];

		$selectedTemplate = $promptTemplates[array_rand($promptTemplates)];

		// Variações para títulos evitando sempre "Guia Completo"
		$titleVariations = [
			"Guia Completo",
			"Manual Definitivo",
			"Especificações Técnicas",
			"Recomendações Oficiais",
			"Manual de Referência",
			"Informações Completas",
			"Análise Técnica",
			"Guia Prático"
		];

		$selectedTitleVariation = $titleVariations[array_rand($titleVariations)];

		$jsonInstructions = <<<EOT
        
        IMPORTANTE SOBRE PONTUAÇÃO: Escreva textos fluidos e naturais. NÃO use pontos no meio de frases. Use vírgulas para separar ideias relacionadas. Exemplo correto: "proteção, economia e durabilidade", exemplo ERRADO: "proteção. Economia e durabilidade".
        
        Forneça sua resposta apenas em formato JSON com esta estrutura específica para artigos sobre óleo recomendado:
        
        {
          "extracted_entities": {
            "marca": "...",
            "modelo": "...",
            "ano": "...",
            "motorizacao": "...",
            "versao": "...",
            "tipo_veiculo": "...",
            "categoria": "...",
            "combustivel": "..."
          },
          "seo": {
            "page_title": "Óleo Recomendado para [MARCA MODELO ANO] - {$selectedTitleVariation}",
            "meta_description": "Óleo recomendado para [MARCA MODELO]. Especificações oficiais, marcas, viscosidade e procedimento de troca. Guia técnico completo.",
            "url_slug": "oleo-recomendado-para-[marca-modelo-formatado]",
            "h1": "Óleo Recomendado para [MARCA MODELO ANO]",
            "h2_tags": [
              "Especificações Oficiais do Fabricante",
              "Alternativas Premium e Econômicas",
              "Benefícios do Óleo Correto",
              "Condições Especiais de Uso",
              "Procedimento de Troca",
              "Perguntas Frequentes"
            ],
            "faq_questions": [
              "Posso usar óleo com viscosidade diferente da recomendada?",
              "Vale a pena usar óleo 100% sintético?",
              "O que significam as classificações API e ILSAC?",
              "Posso misturar óleos de marcas diferentes?"
            ],
            "primary_keyword": "óleo recomendado [marca modelo]",
            "secondary_keywords": ["óleo motor", "troca óleo", "viscosidade", "óleo sintético"],
            "related_topics": ["filtro de óleo", "manutenção preventiva", "fluidos", "consumo de combustível"]
          },
          "sections": {
            "introducao": "...",
            "recomendacoes_fabricante": {
              "nome_oleo": "...",
              "classificacao": "...",
              "viscosidade": "...",
              "especificacao": "...",
              "marcas_recomendadas": ["...", "...", "..."]
            },
            "alternativa_premium": {
              "nome_oleo": "...",
              "classificacao": "...",
              "viscosidade": "...",
              "especificacao": "...",
              "beneficios": ["...", "...", "..."],
              "preco_medio": "..."
            },
            "opcao_economica": {
              "nome_oleo": "...",
              "classificacao": "...",
              "viscosidade": "...",
              "especificacao": "...",
              "limitacoes": "...",
              "preco_medio": "..."
            },
            "especificacoes": {
              "capacidade_oleo": "...",
              "capacidade_sem_filtro": "...",
              "viscosidade": "...",
              "especificacao_minima": "...",
              "intervalo_troca": "...",
              "filtro_oleo": "...",
              "codigo_filtro": "..."
            },
            "beneficios": [
              {
                "titulo": "Proteção contra Desgaste",
                "descricao": "..."
              },
              {
                "titulo": "Economia de Combustível",
                "descricao": "..."
              },
              {
                "titulo": "Redução de Emissões",
                "descricao": "..."
              }
            ],
            "condicoes_uso": {
              "severo": {
                "condicoes": ["Trânsito intenso com paradas frequentes", "Trajetos curtos (menos de 8 km)", "Uso em estradas de terra", "Clima muito quente ou frio"],
                "recomendacao": "..."
              },
              "normal": {
                "condicoes": ["Percursos longos em rodovias", "Trânsito fluido", "Pouca exposição a poeira", "Clima temperado"],
                "recomendacao": "..."
              },
              "dica_adicional": "..."
            },
            "procedimento": [
              {
                "passo": "Preparação do Veículo",
                "descricao": "..."
              },
              {
                "passo": "Drenagem do Óleo Usado",
                "descricao": "..."
              },
              {
                "passo": "Troca do Filtro",
                "descricao": "..."
              },
              {
                "passo": "Adição do Óleo Novo",
                "descricao": "..."
              }
            ],
            "nota_ambiental": "...",
            "perguntas_frequentes": [
              {
                "pergunta": "...",
                "resposta": "..."
              }
            ],
            "consideracoes_finais": "..."
          },
          "meta": {
            "words_count": 0,
            "estimated_reading_time": 0,
            "article_tone": "técnico-informativo",
            "published_date": "..."
          },
          "related_content": [
            {
              "title": "...",
              "slug": "...",
              "icon": "..."
            }
          ]
        }
        
        REGRAS OBRIGATÓRIAS PARA SEO:
        
        1. O page_title DEVE seguir EXATAMENTE este formato: "Óleo Recomendado para [MARCA MODELO ANO] - {$selectedTitleVariation}"
        2. O H1 DEVE seguir EXATAMENTE este formato: "Óleo Recomendado para [MARCA MODELO ANO]"
        3. O url_slug DEVE seguir o formato: "oleo-recomendado-para-[marca-modelo-formatado]"
        4. A meta_description DEVE começar com "Óleo recomendado para [MARCA MODELO]"
        5. A primary_keyword DEVE ser "óleo recomendado [marca modelo]"
        
        Palavras-chave a considerar: {$keywords}
        
        IMPORTANTE: 
        - Para motos, use capacidades menores (0,8-1,5L) e óleos específicos (10W-40, 20W-50)
        - Para carros, use capacidades típicas (3,5-5,5L) e óleos modernos (0W-20, 5W-30)
        - Para caminhões, use capacidades maiores (8-15L) e óleos para serviço pesado (15W-40)
        - Inclua preços realistas do mercado brasileiro
        - Seja preciso com especificações API/ILSAC e códigos de filtros
        EOT;

		return $selectedTemplate . $jsonInstructions;
	}

	/**
	 * Processa a resposta da API
	 */
	protected function processApiResponse($content)
	{
		// Limpar formatação markdown, se presente
		if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
			$content = $matches[1];
		}

		// Extrair JSON da resposta
		$firstBrace = strpos($content, '{');
		$lastBrace = strrpos($content, '}');

		if ($firstBrace !== false && $lastBrace !== false) {
			$jsonContent = substr($content, $firstBrace, $lastBrace - $firstBrace + 1);
			$result = json_decode($jsonContent, true);

			if (json_last_error() === JSON_ERROR_NONE && is_array($result)) {
				return $result;
			}

			Log::error("Erro decodificando JSON: " . json_last_error_msg());
		}

		return null;
	}

	/**
	 * Corrige o tipo de veículo usando a API Claude
	 */
	protected function correctVehicleTypeWithApi($result, $article)
	{
		try {
			$response = Http::timeout(60)
				->withHeaders([
					'x-api-key' => $this->apiKey,
					'anthropic-version' => '2023-06-01',
					'content-type' => 'application/json',
				])
				->retry(3, 1000)
				->post($this->apiUrl, [
					'model' => 'claude-3-5-sonnet-20240620',
					'max_tokens' => 10,
					'temperature' => 0.0,
					'messages' => [
						[
							'role' => 'user',
							'content' => "Qual o tipo de veículo mencionado neste título? Responda apenas com o tipo (carro, moto, caminhão, SUV, picape, utilitário, etc). Título: {$article['title']}"
						]
					]
				]);

			if ($response->successful()) {
				$vehicleType = trim($response->json('content.0.text'));

				if (!isset($result['extracted_entities'])) {
					$result['extracted_entities'] = [];
				}

				$result['extracted_entities']['tipo_veiculo'] = $vehicleType;
				Log::info("Tipo de veículo corrigido para: {$vehicleType}");
			}
		} catch (\Exception $e) {
			Log::error("Erro ao corrigir tipo de veículo: " . $e->getMessage());

			// Fallback para extração básica
			if (!isset($result['extracted_entities'])) {
				$result['extracted_entities'] = [];
			}
			$result['extracted_entities']['tipo_veiculo'] = $this->detectVehicleTypeFromTitle($article['title']);
		}

		return $result;
	}

	/**
	 * Adiciona variações específicas para conteúdo sobre óleo recomendado
	 */
	protected function addOilRecommendationSpecificVariations($content, $article)
	{
		// Garantir que temos entidades extraídas
		if (!isset($content['extracted_entities']) || empty($content['extracted_entities']['marca'])) {
			$content['extracted_entities'] = $this->extractEntitiesFromTitle($article['title']);
		}

		return $content;
	}

	/**
	 * Detecta tipo de veículo básico do título
	 */
	protected function detectVehicleTypeFromTitle($title)
	{
		$tiposVeiculo = [
			'moto' => ['moto', 'motocicleta', 'scooter', 'cb', 'pop', 'fan', 'titan', 'hornet'],
			'caminhão' => ['caminhão', 'caminhao', 'truck', 'cargo', 'atego', 'accelo'],
			'SUV' => ['suv', 'crossover', 'compass', 'renegade', 'tiguan'],
			'picape' => ['picape', 'pickup', 'hilux', 'ranger', 'amarok', 'frontier', 's10'],
			'utilitário' => ['utilitario', 'utilitário', 'van', 'furgão', 'furgao', 'ducato', 'sprinter']
		];

		foreach ($tiposVeiculo as $tipo => $palavrasChave) {
			foreach ($palavrasChave as $palavra) {
				if (stripos($title, $palavra) !== false) {
					return $tipo;
				}
			}
		}

		return 'carro'; // Default
	}
}
