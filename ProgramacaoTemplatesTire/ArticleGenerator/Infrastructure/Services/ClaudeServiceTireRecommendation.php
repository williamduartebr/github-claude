<?php

namespace Src\ArticleGenerator\Infrastructure\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Src\ArticleGenerator\Infrastructure\Traits\VehicleEntityExtractorTrait;

class ClaudeServiceTireRecommendation
{

	use VehicleEntityExtractorTrait;

	protected $apiKey;
	protected $apiUrl = 'https://api.anthropic.com/v1/messages';

	public function __construct()
	{
		$this->apiKey = config('services.claude.api_key');
	}

	/**
	 * Gerar conteúdo sobre pneus recomendados usando a API Claude
	 */
	public function generateTireContent($article)
	{
		// Gerar um prompt específico para artigos sobre pneus recomendados
		$prompt = $this->createTireRecommendationPrompt($article);

		try {
			Log::info("Iniciando geração de conteúdo sobre pneus recomendados para: {$article['title']}");

			// Chamar a API Claude
			$response = Http::timeout(180)->withHeaders([
				'x-api-key' => $this->apiKey,
				'anthropic-version' => '2023-06-01',
				'content-type' => 'application/json',
			])->post($this->apiUrl, [
				'model' => 'claude-sonnet-4-20250514',
				'max_tokens' => 8000,
				'temperature' => 0.3,
				'messages' => [
					[
						'role' => 'user',
						'content' => $prompt
					]
				],
				'system' => "Você é um especialista em pneus automotivos com mais de 20 anos de experiência no mercado brasileiro. Seu trabalho é criar conteúdo técnico, informativo e útil sobre pneus recomendados para veículos específicos. Considere marcas disponíveis no Brasil, preços realistas e especificações técnicas precisas. Retorne apenas JSON com o conteúdo solicitado."
			]);

			if ($response->successful()) {
				$content = $response->json('content.0.text');
				$result = $this->processApiResponse($content);

				if ($result) {
					// Corrigir o tipo de veículo usando a API Claude
					$result = $this->correctVehicleTypeWithApi($result, $article);

					// Adicionar variações específicas para pneus
					$result = $this->addTireSpecificVariations($result, $article);

					return $result;
				}
			}

			Log::error("Falha ao obter resposta válida da API Claude: " . ($response->body() ?? 'Sem resposta'));
			return null;
		} catch (\Exception $e) {
			Log::error("Erro na geração de conteúdo sobre pneus: " . $e->getMessage());
			return null;
		}
	}

	/**
	 * Cria um prompt especializado para artigos sobre pneus recomendados
	 */
	protected function createTireRecommendationPrompt($article)
	{
		$title = $article['title'];
		$keywords = $article['keywords'] ?? '';

		$promptTemplates = [
			"Preciso de um guia completo sobre pneus recomendados para {$title}. Os proprietários deste modelo querem saber exatamente quais pneus comprar, as medidas corretas, marcas confiáveis disponíveis no Brasil e dicas de manutenção. Foque em informações práticas e específicas para este veículo.",

			"Como especialista em pneus, crie um artigo detalhado sobre as melhores opções de pneus para {$title}. Inclua especificações oficiais, comparativo de marcas, preços aproximados do mercado brasileiro e orientações sobre quando trocar os pneus.",

			"Estou criando conteúdo sobre pneus para {$title}. O proprietário precisa saber as medidas originais, melhores marcas para diferentes tipos de uso (urbano, estrada, economia), indicadores de desgaste e dicas de conservação. Conteúdo técnico mas acessível."
		];

		$selectedTemplate = $promptTemplates[array_rand($promptTemplates)];

		// Variações para títulos evitando sempre "Guia Completo"
		$titleVariations = [
			"Guia Completo",
			"Manual Definitivo",
			"Especificações Detalhadas",
			"Guia Técnico",
			"Manual de Referência",
			"Informações Completas",
			"Recomendações Oficiais",
			"Análise Técnica"
		];

		$selectedTitleVariation = $titleVariations[array_rand($titleVariations)];

		$jsonInstructions = <<<EOT
        
        IMPORTANTE SOBRE PONTUAÇÃO: Escreva textos fluidos e naturais. NÃO use pontos no meio de frases. Use vírgulas para separar ideias relacionadas. Exemplo correto: "garantir segurança, economia e desempenho", exemplo ERRADO: "garantir segurança. Economia e desempenho".
        
        Forneça sua resposta apenas em formato JSON com esta estrutura específica para artigos sobre pneus recomendados:
        
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
            "page_title": "Pneus Recomendados para [MARCA MODELO ANO] - {$selectedTitleVariation}",
            "meta_description": "Melhores pneus para [MARCA MODELO]. Especificações oficiais, preços, marcas recomendadas e dicas de manutenção. Guia completo 2024.",
            "url_slug": "pneus-recomendados-para-[marca-modelo-formatado]",
            "h1": "Pneus Recomendados para [MARCA MODELO ANO]",
            "h2_tags": [
              "Especificações Oficiais",
              "Melhores Pneus Dianteiros",
              "Melhores Pneus Traseiros", 
              "Comparativo por Tipo de Uso",
              "Guia de Desgaste e Substituição",
              "Dicas de Manutenção",
              "Perguntas Frequentes"
            ],
            "faq_questions": [
              "Posso usar pneus de medidas diferentes das originais?",
              "Qual a diferença entre pneus diagonais e radiais?",
              "Com que frequência devo trocar os pneus?",
              "É normal o pneu traseiro desgastar mais rápido?"
            ],
            "primary_keyword": "pneus recomendados [marca modelo]",
            "secondary_keywords": ["pneus", "calibragem", "desgaste", "medidas"],
            "related_topics": ["calibragem de pneus", "óleo de motor", "manutenção preventiva", "consumo de combustível"]
          },
          "sections": {
            "introducao": "...",
            "especificacoes_oficiais": {
              "pneu_dianteiro": {
                "medida_original": "...",
                "indice_carga": "...",
                "indice_velocidade": "...",
                "pressao_recomendada": "...",
                "capacidade_carga": "..."
              },
              "pneu_traseiro": {
                "medida_original": "...",
                "indice_carga": "...",
                "indice_velocidade": "...",
                "pressao_recomendada": "...",
                "capacidade_carga": "..."
              }
            },
            "pneus_dianteiros": [
              {
                "categoria": "Melhor Custo-Benefício",
                "nome_pneu": "...",
                "medida": "...",
                "indice_carga": "...",
                "indice_velocidade": "...",
                "tipo": "...",
                "preco_medio": "...",
                "durabilidade": "...",
                "caracteristicas": "..."
              },
              {
                "categoria": "Melhor Durabilidade",
                "nome_pneu": "...",
                "medida": "...",
                "indice_carga": "...",
                "indice_velocidade": "...",
                "tipo": "...",
                "preco_medio": "...",
                "durabilidade": "...",
                "caracteristicas": "..."
              }
            ],
            "pneus_traseiros": [
              {
                "categoria": "Melhor Custo-Benefício",
                "nome_pneu": "...",
                "medida": "...",
                "indice_carga": "...",
                "indice_velocidade": "...",
                "tipo": "...",
                "preco_medio": "...",
                "durabilidade": "...",
                "caracteristicas": "..."
              }
            ],
            "comparativo_uso": [
              {
                "tipo_uso": "Uso Urbano Diário",
                "melhor_dianteiro": "...",
                "melhor_traseiro": "...",
                "caracteristicas": "..."
              },
              {
                "tipo_uso": "Entregadores/Uso Intenso",
                "melhor_dianteiro": "...",
                "melhor_traseiro": "...",
                "caracteristicas": "..."
              }
            ],
            "guia_desgaste": {
              "indicadores_desgaste": [
                {
                  "indicador": "TWI (Tread Wear Indicator)",
                  "descricao": "..."
                }
              ],
              "quando_substituir": [
                {
                  "situacao": "Quilometragem máxima",
                  "descricao": "..."
                }
              ]
            },
            "dicas_manutencao": [
              {
                "categoria": "Calibragem Correta",
                "dicas": ["...", "...", "..."]
              },
              {
                "categoria": "Inspeção Regular",
                "dicas": ["...", "...", "..."]
              }
            ],
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
        
        1. O page_title DEVE seguir EXATAMENTE este formato: "Pneus Recomendados para [MARCA MODELO ANO] - {$selectedTitleVariation}"
        2. O H1 DEVE seguir EXATAMENTE este formato: "Pneus Recomendados para [MARCA MODELO ANO]"
        3. O url_slug DEVE seguir o formato: "pneus-recomendados-para-[marca-modelo-ano-formatado]"
        4. A meta_description DEVE começar com "Melhores pneus para [MARCA MODELO]"
        5. A primary_keyword DEVE ser "pneus recomendados [marca modelo]"
        
        Palavras-chave a considerar: {$keywords}
        
        IMPORTANTE: Para pneus de motocicletas, use medidas como 60/100-17 (dianteiro) e 80/100-14 (traseiro). Para carros, use medidas como 175/70R14 ou 185/60R15. Inclua preços realistas do mercado brasileiro e marcas disponíveis localmente.
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
					'model' => 'claude-sonnet-4-20250514',
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
	 * Adiciona variações específicas para conteúdo sobre pneus
	 */
	protected function addTireSpecificVariations($content, $article)
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
			'moto' => ['moto', 'motocicleta', 'scooter', 'cb', 'pop', 'fan', 'titan'],
			'caminhão' => ['caminhão', 'caminhao', 'truck'],
			'SUV' => ['suv', 'crossover'],
			'picape' => ['picape', 'pickup'],
			'utilitário' => ['utilitario', 'utilitário', 'van', 'furgão']
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

	/**
	 * Gerar conteúdo corrigido para artigos de pneus recomendados (versão 3.7)
	 */
	public function generateTireContentFixed($article)
	{
		// Gerar um prompt específico para correção de artigos sobre pneus recomendados
		$prompt = $this->createTireFixPrompt($article);

		try {
			Log::info("Iniciando correção de conteúdo sobre pneus para: {$article['title']}");

			// Chamar a API Claude (versão 3.7 para economia)
			$response = Http::timeout(120)->withHeaders([
				'x-api-key' => $this->apiKey,
				'anthropic-version' => '2023-06-01',
				'content-type' => 'application/json',
			])->post($this->apiUrl, [
				'model' => 'claude-3-7-sonnet-20250219', // Versão mais econômica
				'max_tokens' => 4000, // Reduzido para economizar
				'temperature' => 0.2, // Mais determinístico
				'messages' => [
					[
						'role' => 'user',
						'content' => $prompt
					]
				],
				'system' => "Você é um especialista em pneus automotivos com foco em correção de estruturas de conteúdo. Seu trabalho é gerar apenas a seção 'sections' do JSON com a estrutura correta para artigos de pneus recomendados. Seja preciso e econômico nas respostas."
			]);

			if ($response->successful()) {
				$content = $response->json('content.0.text');
				$result = $this->processApiResponse($content);

				if ($result) {
					Log::info("Conteúdo de pneus corrigido com sucesso para: {$article['title']}");
					return $result;
				}
			}

			Log::error("Falha ao corrigir conteúdo da API Claude: " . ($response->body() ?? 'Sem resposta'));
			return null;
		} catch (\Exception $e) {
			Log::error("Erro na correção de conteúdo sobre pneus: " . $e->getMessage());
			return null;
		}
	}

	/**
	 * Cria um prompt otimizado para correção de estrutura de pneus
	 */
	protected function createTireFixPrompt($article)
	{
		$title = $article['title'];
		$keywords = $article['keywords'] ?? '';
		$entities = $article['extracted_entities'] ?? [];

		// Extrair informações da entidade para personalizar o conteúdo
		$marca = $entities['marca'] ?? '';
		$modelo = $entities['modelo'] ?? '';
		$tipoVeiculo = $entities['tipo_veiculo'] ?? '';

		$prompt = "Preciso corrigir a estrutura de conteúdo para o artigo '{$title}'. ";
		
		if ($marca && $modelo) {
			$prompt .= "É sobre pneus para {$marca} {$modelo}. ";
		}
		
		if ($tipoVeiculo) {
			$prompt .= "Tipo de veículo: {$tipoVeiculo}. ";
		}

		$prompt .= "Gere APENAS as seções de conteúdo com informações técnicas precisas sobre pneus.";

		$jsonInstructions = <<<EOT
        
        IMPORTANTE: Retorne APENAS a estrutura JSON das seções, sem explicações adicionais.
        
        Estrutura necessária para artigos de pneus recomendados:
        
        {
          "sections": {
            "introducao": "...",
            "especificacoes_oficiais": {
              "pneu_dianteiro": {
                "medida_original": "...",
                "indice_carga": "...",
                "indice_velocidade": "...",
                "pressao_recomendada": "...",
                "capacidade_carga": "..."
              },
              "pneu_traseiro": {
                "medida_original": "...",
                "indice_carga": "...",
                "indice_velocidade": "...",
                "pressao_recomendada": "...",
                "capacidade_carga": "..."
              }
            },
            "pneus_dianteiros": [
              {
                "categoria": "Melhor Custo-Benefício",
                "nome_pneu": "...",
                "medida": "...",
                "indice_carga": "...",
                "indice_velocidade": "...",
                "tipo": "...",
                "preco_medio": "...",
                "durabilidade": "...",
                "caracteristicas": "..."
              },
              {
                "categoria": "Melhor Durabilidade",
                "nome_pneu": "...",
                "medida": "...",
                "indice_carga": "...",
                "indice_velocidade": "...",
                "tipo": "...",
                "preco_medio": "...",
                "durabilidade": "...",
                "caracteristicas": "..."
              }
            ],
            "pneus_traseiros": [
              {
                "categoria": "Melhor Custo-Benefício",
                "nome_pneu": "...",
                "medida": "...",
                "indice_carga": "...",
                "indice_velocidade": "...",
                "tipo": "...",
                "preco_medio": "...",
                "durabilidade": "...",
                "caracteristicas": "..."
              }
            ],
            "comparativo_uso": [
              {
                "tipo_uso": "Uso Urbano Diário",
                "melhor_dianteiro": "...",
                "melhor_traseiro": "...",
                "caracteristicas": "..."
              },
              {
                "tipo_uso": "Entregadores/Uso Intenso",
                "melhor_dianteiro": "...",
                "melhor_traseiro": "...",
                "caracteristicas": "..."
              }
            ],
            "guia_desgaste": {
              "indicadores_desgaste": [
                {
                  "indicador": "TWI (Tread Wear Indicator)",
                  "descricao": "..."
                }
              ],
              "quando_substituir": [
                {
                  "situacao": "Quilometragem máxima",
                  "descricao": "..."
                }
              ]
            },
            "dicas_manutencao": [
              {
                "categoria": "Calibragem Correta",
                "dicas": ["...", "...", "..."]
              },
              {
                "categoria": "Inspeção Regular",
                "dicas": ["...", "...", "..."]
              }
            ],
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
          }
        }
        
        REGRAS:
        1. Para motos: use medidas como 60/100-17 (dianteiro) e 80/100-14 (traseiro)
        2. Para carros: use medidas como 175/70R14 ou 185/60R15
        3. Inclua preços realistas do mercado brasileiro
        4. Use marcas disponíveis localmente (Pirelli, Michelin, Bridgestone, Levorin, etc.)
        5. Seja específico e técnico, mas acessível
        
        Palavras-chave: {$keywords}
        EOT;

		return $prompt . $jsonInstructions;
	}
}
