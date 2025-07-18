<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Services;

use Illuminate\Support\Str;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;

/**
 * Serviço para geração inicial de artigos de calibragem
 * 
 * Baseado no sistema atual (generator.php), mas adaptado para DDD
 * Gera conteúdo completo direto na TirePressureArticle (MongoDB)
 */
class InitialArticleGeneratorService
{
    // Templates de introdução para carros
    protected array $introsCarros = [
        "A calibragem correta dos pneus do {make} {model} {year} é essencial para segurança, desempenho e economia. Pneus bem calibrados reduzem o desgaste, melhoram a aderência e ajudam a economizar combustível.",
        "Quer dirigir seu {make} {model} {year} com mais segurança? A calibragem dos pneus é um passo simples que faz toda a diferença, garantindo estabilidade e economia no dia a dia.",
        "Manter os pneus do {make} {model} {year} na pressão certa é chave para um desempenho impecável. Além de economizar combustível, você aumenta a vida útil dos pneus e evita acidentes.",
        "Para quem possui um {make} {model} {year}, saber a pressão ideal dos pneus pode significar mais segurança e economia no bolso. Uma calibragem adequada melhora o comportamento do veículo nas ruas e rodovias.",
        "O {make} {model} {year} precisa de cuidados específicos com seus pneus para garantir uma condução segura e econômica. Uma calibragem correta faz seu carro responder melhor em qualquer situação de tráfego."
    ];

    // Templates de introdução para motos
    protected array $introsMotos = [
        "A calibragem correta dos pneus da sua {make} {model} {year} é fundamental para segurança e desempenho. Pneus bem calibrados oferecem melhor aderência, manuseio e estabilidade, aspectos essenciais para motocicletas.",
        "Para os motociclistas que possuem uma {make} {model} {year}, a pressão correta dos pneus é crucial. Além de garantir uma pilotagem segura, a calibragem adequada prolonga a vida útil dos pneus e melhora a economia de combustível.",
        "A {make} {model} {year} exige atenção especial na calibragem dos pneus. Uma pressão incorreta pode comprometer a estabilidade, especialmente em curvas e frenagens, aumentando o risco de acidentes.",
        "Manter os pneus da sua {make} {model} {year} com a pressão recomendada é um passo simples mas essencial para garantir segurança e performance. Confira as recomendações específicas para este modelo.",
        "Os pneus são o único ponto de contato entre sua {make} {model} {year} e o asfalto. A calibragem correta é fundamental para garantir controle, estabilidade e segurança em todas as condições de pilotagem."
    ];

    // Parágrafos do meio para carros
    protected array $middleCarros = [
        "Calibrar os pneus do {make} {model} corretamente não é apenas uma questão de manutenção, mas um investimento em segurança. Pneus com pressão inadequada podem causar acidentes, especialmente em curvas e frenagens de emergência.",
        "Sabia que o seu {make} {model} {year} pode economizar até 10% de combustível apenas mantendo os pneus na pressão ideal? Esta é uma das formas mais simples e baratas de melhorar a eficiência do seu veículo.",
        "O {make} {model} tem características únicas que influenciam diretamente na escolha da pressão ideal dos pneus. O peso do veículo, distribuição de carga e tipo de pneu são fatores importantes a considerar.",
        "A temperatura do asfalto brasileiro, principalmente no verão, pode afetar a pressão dos pneus do seu {make} {model}. Por isso, é ainda mais importante fazer verificações regulares nessa época do ano."
    ];

    // Parágrafos do meio para motos
    protected array $middleMotos = [
        "Os pneus de motocicletas suportam cargas diferentes nos eixos dianteiro e traseiro, o que explica as diferentes pressões recomendadas. Na {make} {model}, essa distribuição foi cuidadosamente calculada para garantir o melhor desempenho.",
        "A calibragem correta dos pneus da {make} {model} impacta diretamente na pilotagem. Uma pressão excessiva pode reduzir a área de contato do pneu com o solo, diminuindo a aderência. Já uma pressão muito baixa aumenta o desgaste e pode causar instabilidade.",
        "Muitos motociclistas negligenciam a pressão dos pneus, mas na {make} {model} {year}, este é um fator crucial. Estudos mostram que pneus com 20% de pressão abaixo do recomendado podem reduzir sua vida útil em até 30%.",
        "Para sua {make} {model} {year}, é importante calibrar os pneus quando estiverem frios, pois a pressão aumenta com o aquecimento durante a pilotagem. Uma verificação regular, pelo menos quinzenal, é fundamental para sua segurança."
    ];

    // Conclusões (para ambos)
    protected array $conclusions = [
        "Com a calibragem certa, seu {make} {model} {year} estará pronto para rodar com segurança e economia!",
        "Mantenha os pneus do {make} {model} {year} calibrados e aproveite uma direção mais tranquila e econômica.",
        "Seguindo essas orientações para o {make} {model} {year}, você garante mais segurança, economia e durabilidade para seus pneus.",
        "Nunca negligencie a calibragem dos pneus do seu {make} {model} {year}. Este pequeno detalhe faz toda a diferença na sua segurança e no seu bolso.",
        "Calibragem correta no {make} {model} {year} significa mais que economia: é sinônimo de conforto, segurança e responsabilidade com o meio ambiente."
    ];

    // Dicas específicas por marca/modelo (carros)
    protected array $tipsCarros = [
        'Hyundai HB20' => 'O HB20 é ideal para uso urbano, então mantenha os pneus calibrados para economizar combustível em trajetos curtos.',
        'Chevrolet Onix' => 'O Onix tem suspensão macia, exigindo calibragem regular para evitar desgaste irregular.',
        'Nissan Kicks' => 'O Kicks é perfeito para SUVs urbanos, e a calibragem correta melhora a estabilidade em rodovias.',
        'Fiat Argo' => 'O Argo tem um perfil compacto que exige atenção especial à pressão dos pneus para garantir conforto e estabilidade.',
        'Volkswagen Gol' => 'O tradicional Gol tem uma suspensão mais firme, e a calibragem adequada complementa esse comportamento para melhor desempenho.',
        'Toyota Corolla' => 'O Corolla é conhecido pela durabilidade, e manter a calibragem correta contribui para aumentar ainda mais a vida útil dos componentes da suspensão.',
        'Honda Civic' => 'O Civic tem um comportamento esportivo, e a calibragem correta potencializa a precisão nas curvas sem comprometer o conforto.',
        'Volkswagen T-Cross' => 'O T-Cross tem uma altura em relação ao solo que favorece o uso urbano, mas a calibragem correta é essencial para manter a estabilidade em curvas.',
        'Jeep Renegade' => 'O Renegade, com sua vocação off-road, exige atenção especial à calibragem para diferentes tipos de terreno. Aumente 2-3 PSI para uso fora de estrada.',
        'Fiat Toro' => 'A Toro, por ser uma picape com suspensão independente, tem um comportamento único. A calibragem adequada é fundamental para manter o equilíbrio entre capacidade de carga e conforto.'
    ];

    // Dicas específicas por marca (motos)
    protected array $tipsMotos = [
        'Honda' => 'Para Hondas, é importante lembrar que o desgaste irregular do pneu dianteiro pode indicar problemas na suspensão dianteira.',
        'Yamaha' => 'Modelos Yamaha com pneus de perfil mais baixo são mais sensíveis à pressão correta para manter a estabilidade em curvas.',
        'Suzuki' => 'Nas Suzuki, verifique regularmente o alinhamento da roda traseira, pois pode afetar tanto o desgaste quanto a pressão ideal do pneu.',
        'Kawasaki' => 'Motos Kawasaki com carenagem completa podem ter os pneus aquecidos mais rapidamente, afetando a pressão durante o uso.',
        'BMW' => 'As motocicletas BMW têm sistemas de monitoramento de pressão dos pneus que são bastante precisos, mas ainda assim é recomendável verificar manualmente a cada 15 dias.',
        'Triumph' => 'As Triumph são conhecidas pelo desempenho em curvas, mas isso exige uma calibragem precisa dos pneus para manter a aderência ideal.',
        'Ducati' => 'As Ducati esportivas têm pneus de perfil baixo que exigem atenção especial à calibragem para evitar o superaquecimento.'
    ];

    // Avisos específicos por modelo (carros)
    protected array $warningsCarros = [
        'Hyundai HB20' => 'Atenção: O HB20 equipado com pneus 185/65 R15 pode apresentar subesterço em curvas se a pressão dianteira estiver muito baixa.',
        'Chevrolet Onix' => 'Atenção: O sistema de monitoramento de pressão do Onix pode demorar até 20 minutos para atualizar após a calibragem.',
        'Nissan Kicks' => 'Atenção: Pneus do Kicks com pressão muito alta podem reduzir a tração em pisos molhados, um cuidado importante em dias de chuva.',
        'Fiat Argo' => 'Atenção: O Argo com calibragem incorreta pode apresentar desgaste prematuro na suspensão dianteira.',
        'Volkswagen Gol' => 'Atenção: O Gol pode apresentar vibração no volante se houver diferença significativa de pressão entre os pneus dianteiros.',
        'Toyota Corolla' => 'Atenção: O Corolla pode ter o consumo de combustível aumentado em até 15% com pneus mal calibrados devido ao seu peso.',
        'Honda Civic' => 'Atenção: O Civic equipado com pneus de perfil baixo exige calibragem precisa para evitar danos aos aros em caso de buracos.',
        'Volkswagen T-Cross' => 'Atenção: O T-Cross requer atenção especial à calibragem dos pneus traseiros quando totalmente carregado para manter a estabilidade.'
    ];

    // Avisos para motos
    protected array $warningsMotos = [
        'Honda' => 'Atenção: Motos Honda podem ter a estabilidade comprometida se a diferença de pressão entre pneu dianteiro e traseiro for maior que o recomendado.',
        'Yamaha' => 'Atenção: Modelos Yamaha com sistema ABS são particularmente sensíveis à pressão correta dos pneus para funcionamento ideal do sistema de freios.',
        'Suzuki' => 'Atenção: Pneus da Suzuki com pressão excessivamente alta podem comprometer a aderência em piso molhado, especialmente em curvas.',
        'Kawasaki' => 'Atenção: As Kawasaki esportivas precisam de calibragem precisa para evitar aquecimento excessivo dos pneus em condução esportiva.',
        'BMW' => 'Atenção: Motocicletas BMW com sistema de monitoramento de pressão (TPMS) podem apresentar alertas apenas quando a pressão já está bem abaixo do ideal.',
        'Triumph' => 'Atenção: As Triumph requerem pressões diferentes para pilotagem solo ou com garupa, consulte sempre o manual específico do seu modelo.',
        'Ducati' => 'Atenção: Modelos Ducati esportivos exigem verificação mais frequente da pressão dos pneus, especialmente após uso em temperaturas elevadas.'
    ];

    /**
     * Gerar artigo completo para um veículo
     */
    public function generateArticle(array $vehicleData, string $batchId): ?TirePressureArticle
    {
        try {
            // Validar dados básicos
            if (!$this->validateVehicleData($vehicleData)) {
                return null;
            }

            // Determinar se é motocicleta
            $isMotorcycle = $vehicleData['is_motorcycle'] ?? false;

            // Gerar dados do artigo
            $articleData = $this->buildArticleData($vehicleData, $isMotorcycle, $batchId);

            // Criar artigo na model
            $article = new TirePressureArticle($articleData);
            $article->save();

            return $article;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erro ao gerar artigo para veículo", [
                'vehicle' => $vehicleData['vehicle_identifier'] ?? 'Desconhecido',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Validar dados do veículo
     */
    protected function validateVehicleData(array $vehicleData): bool
    {
        $required = ['make', 'model', 'year', 'tire_size'];

        foreach ($required as $field) {
            if (empty($vehicleData[$field])) {
                return false;
            }
        }

        // Validar pressões básicas
        if (empty($vehicleData['pressure_empty_front']) || empty($vehicleData['pressure_empty_rear'])) {
            return false;
        }

        return true;
    }

    /**
     * Construir dados completos do artigo
     */
    protected function buildArticleData(array $vehicleData, bool $isMotorcycle, string $batchId): array
    {
        // Dados básicos
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];
        $vehicleIdentifier = "{$make} {$model} {$year}";

        // Gerar slug WordPress compatível
        $wordpressSlug = $this->generateWordPressSlug($make, $model, $year);

        // Determinar template
        $template = $isMotorcycle ? 'tire_pressure_guide_motorcycle' : 'tire_pressure_guide_car';

        // Gerar conteúdo estruturado
        $articleContent = $this->generateArticleContent($vehicleData, $isMotorcycle);

        // Dados SEO
        $title = "Calibragem do Pneu " . ($isMotorcycle ? "da" : "do") . " {$make} {$model} {$year}";
        $metaDescription = $this->generateMetaDescription($vehicleData, $isMotorcycle);
        $seoKeywords = $this->generateSeoKeywords($vehicleData);

        return [
            // Dados básicos do veículo
            'make' => $make,
            'model' => $model,
            'year' => $year,
            'tire_size' => $vehicleData['tire_size'],
            'vehicle_data' => $vehicleData['vehicle_data'] ?? $vehicleData,

            // Conteúdo do artigo
            'title' => $title,
            'slug' => $wordpressSlug,
            'wordpress_slug' => $wordpressSlug,
            'article_content' => $articleContent,
            'template_used' => $template,

            // SEO e URLs
            'meta_description' => $metaDescription,
            'seo_keywords' => $seoKeywords,
            'wordpress_url' => "https://blog.mercadoveiculos.com/{$wordpressSlug}/",
            'canonical_url' => "https://mercadoveiculos.com/info/{$wordpressSlug}/",

            // Status inicial
            'generation_status' => 'generated', // Etapa 1 concluída

            // Dados técnicos de pressão
            'pressure_empty_front' => $vehicleData['pressure_empty_front'],
            'pressure_empty_rear' => $vehicleData['pressure_empty_rear'],
            'pressure_light_front' => $vehicleData['pressure_light_front'] ?? null,
            'pressure_light_rear' => $vehicleData['pressure_light_rear'] ?? null,
            'pressure_max_front' => $vehicleData['pressure_max_front'] ?? null,
            'pressure_max_rear' => $vehicleData['pressure_max_rear'] ?? null,
            'pressure_spare' => $vehicleData['pressure_spare'] ?? null,

            // Classificação
            'category' => $vehicleData['main_category'] ?? ($isMotorcycle ? 'Motocicletas' : 'Carros'),

            // Controle
            'batch_id' => $batchId,
            'processed_at' => now(),
            'quality_checked' => false,
            'content_score' => 7.5, // Score inicial baseado em template

            // Status blog
            'blog_status' => 'draft',
            'blog_synced' => false
        ];
    }

    /**
     * Gerar slug WordPress compatível
     */
    protected function generateWordPressSlug(string $make, string $model, string $year): string
    {
        $makeSlug = $this->slugify($make);
        $modelSlug = $this->slugify($model);

        return "calibragem-pneu-{$makeSlug}-{$modelSlug}-{$year}";
    }

    /**
     * Converter string para slug
     */
    protected function slugify(string $text): string
    {
        // Remover acentos
        $text = $this->removeAccents($text);

        // Converter para minúsculas
        $text = strtolower($text);

        // Remover caracteres especiais e substituir por hífen
        $text = preg_replace('/[^a-z0-9\-_]/', '-', $text);

        // Remover hífens múltiplos
        $text = preg_replace('/-+/', '-', $text);

        // Remover hífens do início e fim
        return trim($text, '-');
    }

    /**
     * Remover acentos
     */
    protected function removeAccents(string $text): string
    {
        $unwanted = [
            'á' => 'a',
            'à' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'é' => 'e',
            'è' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'í' => 'i',
            'ì' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ó' => 'o',
            'ò' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ú' => 'u',
            'ù' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ý' => 'y',
            'ÿ' => 'y',
            'ñ' => 'n',
            'ç' => 'c',
            'Á' => 'A',
            'À' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'É' => 'E',
            'È' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Í' => 'I',
            'Ì' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ó' => 'O',
            'Ò' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ú' => 'U',
            'Ù' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            'Ñ' => 'N',
            'Ç' => 'C',
        ];

        return strtr($text, $unwanted);
    }

    /**
     * Gerar conteúdo estruturado do artigo
     */
    protected function generateArticleContent(array $vehicleData, bool $isMotorcycle): array
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];

        // Selecionar templates apropriados
        $intros = $isMotorcycle ? $this->introsMotos : $this->introsCarros;
        $middles = $isMotorcycle ? $this->middleMotos : $this->middleCarros;
        $tips = $isMotorcycle ? $this->tipsMotos : $this->tipsCarros;
        $warnings = $isMotorcycle ? $this->warningsMotos : $this->warningsCarros;

        // Gerar conteúdo com placeholders substituídos
        $intro = $this->replacePlaceholders($intros[array_rand($intros)], $vehicleData);
        $middle = $this->replacePlaceholders($middles[array_rand($middles)], $vehicleData);
        $conclusion = $this->replacePlaceholders($this->conclusions[array_rand($this->conclusions)], $vehicleData);

        // Dicas específicas
        $tip = $this->getSpecificTip($vehicleData, $tips);
        $warning = $this->getSpecificWarning($vehicleData, $warnings);

        // Estrutura do artigo
        return [
            'sections' => [
                'introduction' => [
                    'title' => 'A Importância da Calibragem Correta',
                    'content' => $intro,
                    'type' => 'text'
                ],
                'middle_content' => [
                    'title' => 'Impacto da Calibragem no Desempenho',
                    'content' => $middle,
                    'type' => 'text'
                ],
                'pressure_table' => [
                    'title' => "Qual a Pressão Ideal para " . ($isMotorcycle ? "a" : "o") . " {$make} {$model} {$year}?",
                    'content' => $this->generatePressureTable($vehicleData, $isMotorcycle),
                    'type' => 'table'
                ],
                'how_to_calibrate' => [
                    'title' => "Como Calibrar os Pneus " . ($isMotorcycle ? "da" : "do") . " {$make} {$model}",
                    'content' => $this->generateHowToSteps($isMotorcycle),
                    'type' => 'list'
                ],
                'maintenance_checklist' => [
                    'title' => 'Checklist de Manutenção dos Pneus',
                    'content' => $this->generateMaintenanceChecklist($isMotorcycle),
                    'type' => 'checklist'
                ],
                'faq' => [
                    'title' => 'Perguntas Frequentes',
                    'content' => $this->generateFAQ($vehicleData, $isMotorcycle),
                    'type' => 'faq'
                ],
                'conclusion' => [
                    'title' => 'Conclusão',
                    'content' => $conclusion,
                    'type' => 'text'
                ]
            ],
            'warnings' => [
                [
                    'type' => 'warning',
                    'content' => $warning
                ]
            ],
            'tips' => [
                [
                    'type' => 'tip',
                    'content' => $tip
                ]
            ],
            'metadata' => [
                'vehicle_type' => $isMotorcycle ? 'motorcycle' : 'car',
                'generated_at' => now()->toISOString(),
                'template_version' => '1.0'
            ]
        ];
    }

    /**
     * Substituir placeholders no texto
     */
    protected function replacePlaceholders(string $text, array $vehicleData): string
    {
        return str_replace(
            ['{make}', '{model}', '{year}'],
            [$vehicleData['make'], $vehicleData['model'], $vehicleData['year']],
            $text
        );
    }

    /**
     * Obter dica específica para o veículo
     */
    protected function getSpecificTip(array $vehicleData, array $tips): string
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $fullModel = "{$make} {$model}";

        // Tentar modelo completo primeiro
        if (isset($tips[$fullModel])) {
            return $tips[$fullModel];
        }

        // Tentar apenas marca
        if (isset($tips[$make])) {
            return $tips[$make];
        }

        // Dica genérica
        $isMotorcycle = $vehicleData['is_motorcycle'] ?? false;
        return $isMotorcycle ?
            'Sempre verifique o manual do proprietário para as pressões específicas da sua motocicleta.' :
            'Consulte o manual para dicas específicas do seu veículo.';
    }

    /**
     * Obter aviso específico para o veículo
     */
    protected function getSpecificWarning(array $vehicleData, array $warnings): string
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $fullModel = "{$make} {$model}";

        // Tentar modelo completo primeiro
        if (isset($warnings[$fullModel])) {
            return $warnings[$fullModel];
        }

        // Tentar apenas marca
        if (isset($warnings[$make])) {
            return $warnings[$make];
        }

        // Aviso genérico
        $isMotorcycle = $vehicleData['is_motorcycle'] ?? false;
        return $isMotorcycle ?
            'Atenção: Calibrar os pneus da sua motocicleta com pressão incorreta pode afetar drasticamente a estabilidade e aderência, comprometendo sua segurança.' :
            'Lembre-se sempre de verificar o manual do proprietário para informações específicas do seu veículo.';
    }

    /**
     * Gerar tabela de pressões
     */
    protected function generatePressureTable(array $vehicleData, bool $isMotorcycle): array
    {
        $pressureEmptyFront = $vehicleData['pressure_empty_front'] ?? 30;
        $pressureEmptyRear = $vehicleData['pressure_empty_rear'] ?? 28;
        $pressureLightFront = $vehicleData['pressure_light_front'] ?? $pressureEmptyFront + 2;
        $pressureLightRear = $vehicleData['pressure_light_rear'] ?? $pressureEmptyRear + 2;
        $pressureMaxFront = $vehicleData['pressure_max_front'] ?? $pressureEmptyFront + 6;
        $pressureMaxRear = $vehicleData['pressure_max_rear'] ?? $pressureEmptyRear + 6;
        $pressureSpare = $vehicleData['pressure_spare'] ?? 35;

        if ($isMotorcycle) {
            return [
                'headers' => ['Pneu', 'Pressão Normal (psi)', 'Pressão Máxima (psi)', 'Observações'],
                'rows' => [
                    ['Dianteiro', $pressureEmptyFront, $pressureMaxFront, 'Calibrar a frio'],
                    ['Traseiro', $pressureEmptyRear, $pressureMaxRear, 'Ajustar com passageiro']
                ]
            ];
        } else {
            return [
                'headers' => ['Situação do Veículo', 'Dianteiros (psi)', 'Traseiros (psi)', 'Observações'],
                'rows' => [
                    ['Veículo vazio', $pressureEmptyFront, $pressureEmptyRear, 'Ideal para uso diário'],
                    ['Com carga leve', $pressureLightFront, $pressureLightRear, 'Recomendado para viagens curtas'],
                    ['Com carga máxima', $pressureMaxFront, $pressureMaxRear, 'Essencial para segurança'],
                    ['Estepe', $pressureSpare, '–', 'Verifique a cada 15 dias']
                ]
            ];
        }
    }

    /**
     * Gerar passos de calibragem
     */
    protected function generateHowToSteps(bool $isMotorcycle): array
    {
        if ($isMotorcycle) {
            return [
                'Consulte a pressão recomendada no manual da sua motocicleta ou na etiqueta/adesivo da moto.',
                'Use um calibrador digital confiável para verificar a pressão atual.',
                'Ajuste a pressão conforme a tabela acima, considerando se você pilota sozinho ou com garupa.',
                'Nunca exceda os valores máximos recomendados pelo fabricante.',
                'Recoloque as tampas das válvulas para evitar entrada de sujeira e umidade.'
            ];
        } else {
            return [
                'Consulte a pressão recomendada no manual do veículo ou na etiqueta da porta do motorista.',
                'Use um calibrador digital confiável para verificar a pressão atual.',
                'Ajuste a pressão conforme a tabela acima, considerando a carga do veículo.',
                'Verifique todos os pneus, incluindo o estepe.',
                'Recoloque as tampas das válvulas para evitar entrada de sujeira.'
            ];
        }
    }

    /**
     * Gerar checklist de manutenção
     */
    protected function generateMaintenanceChecklist(bool $isMotorcycle): array
    {
        if ($isMotorcycle) {
            return [
                'Faça a calibragem semanalmente e sempre antes de viagens longas.',
                'Inspecione os pneus visualmente para cortes, objetos encravados, bolhas ou desgaste irregular.',
                'Verifique a profundidade dos sulcos (mínimo legal é 1,6mm, mas para motos recomenda-se trocar antes de chegar a 2mm).',
                'Observe o padrão de desgaste - desgaste central indica pressão excessiva; desgaste nas laterais indica pressão baixa.',
                'Em motos, não é possível fazer rodízio dos pneus como nos carros, mas é recomendável trocar os dois pneus juntos.'
            ];
        } else {
            return [
                'Faça a calibragem a cada 15 dias ou antes de viagens longas.',
                'Inspecione os pneus visualmente para cortes, bolhas ou desgaste irregular.',
                'Realize alinhamento e balanceamento a cada 10.000 km.',
                'Considere o rodízio dos pneus para desgaste uniforme.',
                'Verifique a profundidade dos sulcos (mínimo legal é 1,6mm).'
            ];
        }
    }

    /**
     * Gerar FAQ básico
     */
    protected function generateFAQ(array $vehicleData, bool $isMotorcycle): array
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];

        if ($isMotorcycle) {
            return [
                [
                    'question' => 'Com que frequência devo calibrar os pneus da minha moto?',
                    'answer' => 'Recomenda-se calibrar os pneus pelo menos a cada 7 dias para motos. Antes de viagens longas, é essencial verificar a pressão.'
                ],
                [
                    'question' => 'Por que a pressão do pneu traseiro é diferente do dianteiro em motocicletas?',
                    'answer' => 'O pneu traseiro suporta mais peso e transferência de força durante aceleração, exigindo uma pressão diferente para otimizar desempenho e segurança.'
                ],
                [
                    'question' => "Como a temperatura afeta a pressão dos pneus da {$make} {$model}?",
                    'answer' => 'A cada 10°C de aumento na temperatura ambiente, a pressão do pneu pode aumentar cerca de 1 PSI. Por isso, é importante calibrar os pneus frios e considerar as variações climáticas.'
                ]
            ];
        } else {
            return [
                [
                    'question' => 'Com que frequência devo calibrar os pneus do meu carro?',
                    'answer' => 'Recomenda-se calibrar os pneus pelo menos a cada 15 dias ou antes de viagens longas.'
                ],
                [
                    'question' => 'Posso usar pressões diferentes das recomendadas?',
                    'answer' => 'Não é recomendado. As pressões foram calculadas pelos engenheiros para garantir segurança e desempenho ideal.'
                ],
                [
                    'question' => "A pressão dos pneus do {$make} {$model} afeta o consumo de combustível?",
                    'answer' => 'Sim, pneus com baixa pressão podem aumentar o consumo em até 10% devido à maior resistência ao rolamento.'
                ]
            ];
        }
    }

    /**
     * Gerar meta descrição SEO
     */
    protected function generateMetaDescription(array $vehicleData, bool $isMotorcycle): string
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];
        $pressureDisplay = $vehicleData['vehicle_data']['pressure_empty_display'] ??
            "{$vehicleData['pressure_empty_front']}/{$vehicleData['pressure_empty_rear']} PSI";

        if ($isMotorcycle) {
            return "Saiba a pressão ideal para calibrar os pneus da sua {$make} {$model} {$year}. Pressões: {$pressureDisplay}. Veja dicas e tabela completa para segurança e performance!";
        } else {
            return "Saiba a pressão ideal para calibrar os pneus do {$make} {$model} {$year}. Pressões: {$pressureDisplay}. Veja dicas e tabela completa para segurança e economia!";
        }
    }

    /**
     * Gerar palavras-chave SEO
     */
    protected function generateSeoKeywords(array $vehicleData): array
    {
        $make = strtolower($vehicleData['make']);
        $model = strtolower($vehicleData['model']);
        $year = $vehicleData['year'];
        $isMotorcycle = $vehicleData['is_motorcycle'] ?? false;

        $keywords = [
            "calibragem pneu {$make} {$model} {$year}",
            "pressão pneu {$make} {$model}",
            "calibragem {$make} {$model}",
            "pneu {$make} {$model} {$year}"
        ];

        if ($isMotorcycle) {
            $keywords = array_merge($keywords, [
                "calibragem moto {$make}",
                "pressão pneu motocicleta",
                "manutenção moto {$make}",
                "pneu moto {$model}"
            ]);
        } else {
            $keywords = array_merge($keywords, [
                "calibragem carro {$make}",
                "pressão pneu carro",
                "manutenção automotiva",
                "economia combustível"
            ]);
        }

        return $keywords;
    }

    /**
     * Obter template apropriado para o veículo
     */
    public function getTemplateForVehicle(array $vehicleData): string
    {
        $isMotorcycle = $vehicleData['is_motorcycle'] ?? false;
        return $isMotorcycle ? 'tire_pressure_guide_motorcycle' : 'tire_pressure_guide_car';
    }

    /**
     * Calcular score de qualidade do conteúdo
     */
    public function calculateContentScore(array $articleContent): float
    {
        $score = 5.0; // Score base

        // Verificar seções obrigatórias
        $requiredSections = ['introduction', 'pressure_table', 'how_to_calibrate', 'conclusion'];
        $sectionsPresent = 0;

        foreach ($requiredSections as $section) {
            if (isset($articleContent['sections'][$section])) {
                $sectionsPresent++;
            }
        }

        $score += ($sectionsPresent / count($requiredSections)) * 2; // +2 pontos máximo

        // Verificar qualidade do conteúdo
        if (isset($articleContent['sections']['faq'])) {
            $score += 0.5; // FAQ presente
        }

        if (isset($articleContent['tips']) && count($articleContent['tips']) > 0) {
            $score += 0.5; // Dicas presentes
        }

        if (isset($articleContent['warnings']) && count($articleContent['warnings']) > 0) {
            $score += 0.5; // Avisos presentes
        }

        // Verificar tabela de pressões
        if (isset($articleContent['sections']['pressure_table']['content']['rows'])) {
            $rows = count($articleContent['sections']['pressure_table']['content']['rows']);
            if ($rows >= 3) {
                $score += 0.5; // Tabela completa
            }
        }

        return min(10.0, round($score, 1)); // Máximo 10, arredondado
    }
}
