<?php

namespace Src\ArticleGenerator\Infrastructure\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Src\ArticleGenerator\Infrastructure\Traits\VehicleEntityExtractorTrait;

class ClaudeServiceOilTable
{


	
	use VehicleEntityExtractorTrait;

	protected $apiKey;
	protected $apiUrl = 'https://api.anthropic.com/v1/messages';

	public function __construct()
	{
		$this->apiKey = config('services.claude.api_key');
	}

	/**
	 * Gerar conteúdo sobre tabela de óleo usando a API Claude
	 */
	public function generateOilTableContent($article)
	{
		// Gerar um prompt específico para artigos sobre tabela de óleo
		$prompt = $this->createOilTablePrompt($article);

		try {
			Log::info("Iniciando geração de tabela de óleo para: {$article['title']}");

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
				'system' => "Você é um especialista em lubrificantes automotivos com conhecimento profundo sobre todas as marcas e modelos de veículos (carros, motos, caminhões, tratores). Seu trabalho é criar tabelas completas e precisas de recomendações de óleo por geração/período, considerando as especificações oficiais dos fabricantes. Seja preciso com dados técnicos e intervalos de manutenção. Retorne apenas JSON com o conteúdo solicitado."
			]);

			if ($response->successful()) {
				$content = $response->json('content.0.text');
				$result = $this->processApiResponse($content);

				if ($result) {
					// Corrigir o tipo de veículo usando a API Claude
					$result = $this->correctVehicleTypeWithApi($result, $article);

					// Adicionar variações específicas para tabela de óleo
					$result = $this->addOilTableSpecificVariations($result, $article);

					return $result;
				}
			}

			Log::error("Falha ao obter resposta válida da API Claude: " . ($response->body() ?? 'Sem resposta'));
			return null;
		} catch (\Exception $e) {
			Log::error("Erro na geração de tabela de óleo: " . $e->getMessage());
			return null;
		}
	}

	/**
	 * Cria um prompt especializado para artigos sobre tabela de óleo
	 */
	protected function createOilTablePrompt($article)
	{
		$title = $article['title'];
		$keywords = $article['keywords'] ?? '';

		$promptTemplates = [
			"Preciso de uma tabela completa de óleos para {$title}. Inclua todas as gerações/períodos do modelo, especificações oficiais do fabricante, capacidades, intervalos de troca e filtros recomendados. Organize por geração e motor, com informações técnicas precisas.",

			"Como especialista em lubrificantes, crie uma tabela detalhada de óleos para {$title}. Divida por gerações, motores e períodos de fabricação. Inclua viscosidades recomendadas, capacidades, códigos de filtros e intervalos de manutenção baseados nas especificações oficiais.",

			"Estou criando uma tabela de referência de óleos para {$title}. Preciso de informações organizadas por geração/período, tipo de motor, óleo recomendado, capacidade com e sem filtro, e intervalos de troca para uso normal e severo."
		];

		$selectedTemplate = $promptTemplates[array_rand($promptTemplates)];

		// Variações para títulos evitando sempre "Guia Completo"
		$titleVariations = [
			"Manual Completo",
			"Referência Técnica",
			"Especificações Detalhadas",
			"Guia Definitivo",
			"Manual de Referência",
			"Informações Completas",
			"Dados Técnicos",
			"Especificações Oficiais"
		];

		$selectedTitleVariation = $titleVariations[array_rand($titleVariations)];

		$jsonInstructions = <<<EOT
        
        IMPORTANTE SOBRE PONTUAÇÃO: Escreva textos fluidos e naturais. NÃO use pontos no meio de frases. Use vírgulas para separar ideias relacionadas. Exemplo correto: "especificações técnicas, capacidades e intervalos", exemplo ERRADO: "especificações técnicas. Capacidades e intervalos".
        
        Forneça sua resposta apenas em formato JSON com esta estrutura específica para tabela de óleo:
        
        {
          "extracted_entities": {
            "marca": "...",
            "modelo": "...",
            "tipo_veiculo": "...",
            "primeira_geracao": "...",
            "geracao_atual": "...",
            "anos_fabricacao": "...",
            "categoria": "...",
            "combustivel": "..."
          },
          "seo": {
            "page_title": "Tabela de Óleo [MARCA MODELO] - {$selectedTitleVariation}",
            "meta_description": "Tabela completa de óleo para [MARCA MODELO]. Especificações por geração, capacidades, intervalos e filtros recomendados. Informações técnicas precisas.",
            "url_slug": "tabela-oleo-[marca-modelo-formatado]",
            "h1": "Tabela de Óleo [MARCA MODELO]",
            "h2_tags": [
              "Tabela de Óleo por Geração e Motor",
              "Especificações Detalhadas por Tipo de Óleo",
              "Filtros de Óleo Recomendados",
              "Intervalos de Troca por Condição de Uso",
              "Perguntas Frequentes"
            ],
            "faq_questions": [
              "Posso usar óleo sintético no modelo mais antigo?",
              "Qual a diferença entre as especificações API e ILSAC?",
              "É necessário trocar o filtro a cada troca de óleo?",
              "Com que frequência devo verificar o nível de óleo?"
            ],
            "primary_keyword": "tabela óleo [marca modelo]",
            "secondary_keywords": ["óleo motor", "tabela óleo", "viscosidade", "manutenção"],
            "related_topics": ["filtro de óleo", "manutenção preventiva", "fluidos", "troca de óleo"]
          },
          "sections": {
            "introducao": "...",
            "tabela_oleo": [
              {
                "geracao": "12ª Geração",
                "periodo": "2019-Atual",
                "motor": "2.0 Dynamic Force (M20A-FKS)",
                "oleo_recomendado": "0W-20 Sintético",
                "capacidade": "4,5 litros",
                "capacidade_sem_filtro": "4,3 litros",
                "intervalo_troca": "10.000 km ou 12 meses",
                "especificacao_minima": "API SN, ILSAC GF-5"
              },
              {
                "geracao": "11ª Geração",
                "periodo": "2014-2019",
                "motor": "1.8 Dual VVT-i (2ZR-FE)",
                "oleo_recomendado": "5W-30 Sintético",
                "capacidade": "4,2 litros",
                "capacidade_sem_filtro": "4,0 litros",
                "intervalo_troca": "10.000 km ou 12 meses",
                "especificacao_minima": "API SL/SM, ILSAC GF-4"
              }
            ],
            "especificacoes_oleo": [
              {
                "tipo_oleo": "0W-20 Sintético",
                "aplicacao": "Motores mais recentes e híbridos",
                "caracteristicas": [
                  "Viscosidade extremamente baixa a frio (0W)",
                  "Viscosidade leve a quente (20)",
                  "Melhora economia de combustível em até 3%",
                  "Especificação mínima: API SN, ILSAC GF-5"
                ],
                "marcas_recomendadas": ["Toyota Genuine", "Mobil 1", "Castrol Edge", "Shell Helix Ultra"]
              },
              {
                "tipo_oleo": "5W-30 Sintético/Semissintético",
                "aplicacao": "Motores 2008-2019 (não-híbridos)",
                "caracteristicas": [
                  "Bom fluxo a baixas temperaturas (5W)",
                  "Viscosidade média a quente (30)",
                  "Excelente proteção contra desgaste",
                  "Especificação mínima: API SL/SM, ILSAC GF-4"
                ],
                "marcas_recomendadas": ["Toyota Genuine", "Castrol Magnatec", "Mobil Super", "Lubrax Valora"]
              }
            ],
            "filtros_oleo": [
              {
                "geracao": "12ª e 11ª Geração",
                "motor": "1.8/2.0 (2ZR-FE, 2ZR-FXE, 3ZR-FE, M20A-FKS)",
                "codigo_original": "04152-YZZA1",
                "equivalentes_aftermarket": ["Fram PH10060", "Tecfil PSL560", "Mann HU7154X"]
              },
              {
                "geracao": "9ª Geração",
                "motor": "1.6/1.8 (3ZZ-FE, 1ZZ-FE)",
                "codigo_original": "90915-YZZJ1",
                "equivalentes_aftermarket": ["Fram PH5317", "Tecfil PSL156", "Mann W65/3"]
              }
            ],
            "intervalos_troca": [
              {
                "tipo_uso": "Uso Normal",
                "intervalo": "10.000 km ou 12 meses",
                "cor_badge": "green",
                "icone": "check",
                "condicoes": [
                  "Percursos longos em rodovias",
                  "Trânsito fluido em vias urbanas",
                  "Pouca exposição a poeira e sujeira",
                  "Clima temperado sem extremos"
                ],
                "observacoes": "Aplicável para todos os modelos a partir de 2008, incluindo híbridos, utilizando óleos sintéticos recomendados."
              },
              {
                "tipo_uso": "Uso Severo",
                "intervalo": "5.000 km ou 6 meses",
                "cor_badge": "yellow",
                "icone": "warning",
                "condicoes": [
                  "Trânsito intenso com paradas frequentes",
                  "Trajetos curtos (menos de 8 km por viagem)",
                  "Uso em estradas de terra ou com muita poeira",
                  "Clima muito quente ou muito frio",
                  "Uso em aplicativos de transporte"
                ],
                "observacoes": "Recomendado para todos os modelos. Para veículos 2008+ com uso severo, considere óleos sintéticos de alta performance."
              },
              {
                "tipo_uso": "Modelos Antigos (1998-2008)",
                "intervalo": "5.000 km ou 6 meses",
                "cor_badge": "gray",
                "icone": "clock",
                "condicoes": [
                  "Intervalos menores independente do tipo de uso",
                  "Sistemas de controle de emissões mais sensíveis",
                  "Motores com maior quilometragem acumulada",
                  "Alta probabilidade de contaminação do óleo"
                ],
                "observacoes": "Para veículos com mais de 150.000 km, considere usar óleos de viscosidade 10W-40 e reduzir o intervalo para 4.000 km em condições severas."
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
            "article_tone": "técnico-referência",
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
        
        1. O page_title DEVE seguir EXATAMENTE este formato: "Tabela de Óleo [MARCA MODELO] - {$selectedTitleVariation}"
        2. O H1 DEVE seguir EXATAMENTE este formato: "Tabela de Óleo [MARCA MODELO]"
        3. O url_slug DEVE seguir o formato: "tabela-oleo-[marca-modelo-formatado]"
        4. A meta_description DEVE começar com "Tabela completa de óleo para [MARCA MODELO]"
        5. A primary_keyword DEVE ser "tabela óleo [marca modelo]"
        
        Palavras-chave a considerar: {$keywords}
        
        IMPORTANTE: 
        - Para motos, use capacidades menores (0,8-1,5L) e óleos específicos (10W-40, 20W-50)
        - Para carros, use capacidades típicas (3,5-5,5L) e óleos modernos (0W-20, 5W-30)
        - Para caminhões, use capacidades maiores (8-15L) e óleos para serviço pesado (15W-40)
        - Sempre inclua pelo menos 3-4 gerações/períodos diferentes
        - Seja preciso com códigos de filtros e especificações API/ILSAC
        - Considere híbridos e versões especiais do modelo
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
	 * Adiciona variações específicas para conteúdo sobre tabela de óleo
	 */
	protected function addOilTableSpecificVariations($content, $article)
	{
		// Garantir que temos entidades extraídas
		if (!isset($content['extracted_entities']) || empty($content['extracted_entities']['marca'])) {
			$content['extracted_entities'] = $this->extractEntitiesFromTitle($article['title']);
		}

		return $content;
	}

	/**
	 * Insere referências específicas sobre tabela de óleo para o veículo
	 * REMOVIDO: Esta função foi substituída pela correção automática via helper
	 */

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
