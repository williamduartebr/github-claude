<?php

namespace Src\GenericArticleGenerator\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\GenericArticleGenerator\Infrastructure\Eloquent\GenerationTempArticle;
use Src\GenericArticleGenerator\Traits\UpdatesMaintenanceEntities;

/**
 * PublishGeneratedHumanizedCommand - v1.2
 * Datas baseadas no horário real do cron
 */

/**
 * USO:
 * php artisan generated-article:publish-humanized --slug=oleo-valvoline-e-bom-analise-atual-e-abrangente
 */
class PublishGeneratedHumanizedCommand extends Command
{
    use UpdatesMaintenanceEntities;

    protected $signature = 'generated-article:publish-humanized 
                        {--limit=1 : Quantidade de artigos para publicar}
                        {--force : Forçar publicação mesmo com slug duplicado (adiciona sufixo)}
                        {--dry-run : Simulação sem publicar}
                        {--auto : Execução automática sem confirmação (para schedules)}
                        {--category= : Filtrar por category_slug}
                        {--slug= : Slug específica (blog)}
                        {--priority= : Filtrar por prioridade (high|medium|low)}';

    protected $description = 'Publicar artigos gerados com DATAS HUMANIZADAS (parecer conteúdo orgânico)';

    private array $stats = [
        'processed' => 0,
        'published' => 0,
        'skipped' => 0,
        'errors' => 0,
        'names_fixed' => 0
    ];

    // ✅ Base de nomes brasileiros para correção automática
    private array $firstNames = [
        // Masculinos
        'Carlos', 'Fernando', 'Roberto', 'José', 'Paulo', 'André', 'Marcos',
        'Rafael', 'Rodrigo', 'Bruno', 'Diego', 'Lucas', 'Thiago', 'Felipe',
        'Gustavo', 'Leonardo', 'Gabriel', 'Matheus', 'Daniel', 'Pedro', 'William',
        'Renato', 'Fábio', 'Marcelo', 'Alexandre', 'Vinícius', 'Leandro',
        'Maurício', 'Eduardo', 'Anderson', 'Wellington', 'Cristiano', 'João',
        'Ricardo', 'Vitor', 'Caio', 'Hugo', 'Samuel', 'Nathan', 'Antônio',
        'Miguel', 'Enzo', 'Luiz', 'Francisco', 'Alan', 'Cláudio', 'Henrique',
        'Jorge', 'Luciano', 'Osvaldo', 'Everton', 'Rogério', 'Sérgio', 'Ivan',
        'Douglas', 'César', 'Murilo', 'Otávio', 'Davi', 'Elias',

        // Femininos
        'Maria', 'Ana', 'Juliana', 'Camila', 'Patrícia', 'Fernanda', 'Aline',
        'Amanda', 'Beatriz', 'Larissa', 'Gabriela', 'Bruna', 'Natália', 'Letícia',
        'Isabela', 'Carla', 'Bianca', 'Tatiane', 'Carolina', 'Sabrina', 'Jéssica',
        'Daniela', 'Luana', 'Elaine', 'Roberta', 'Priscila', 'Renata', 'Simone',
        'Cláudia', 'Luciana', 'Mariana', 'Vanessa', 'Cíntia', 'Débora', 'Tânia',
        'Rafaela', 'Sueli', 'Helena', 'Alice', 'Lívia', 'Valéria', 'Tatiana',
        'Cristiane', 'Nathalia', 'Silvia', 'Viviane'
    ];


    private array $lastNames = [
        'Silva', 'Santos', 'Oliveira', 'Souza', 'Lima', 'Ferreira', 'Costa',
        'Rodrigues', 'Almeida', 'Nascimento', 'Araújo', 'Ribeiro', 'Martins',
        'Carvalho', 'Pereira', 'Gomes', 'Barbosa', 'Rocha', 'Dias', 'Monteiro',
        'Cardoso', 'Machado', 'Freitas', 'Fernandes', 'Soares', 'Mendes',
        'Pinto', 'Moreira', 'Cavalcanti', 'Reis', 'Farias', 'Lopes', 'Teixeira',
        'Correia', 'Moura', 'Batista', 'Campos', 'Barros', 'Sales', 'Melo',
        'Nogueira', 'Tavares', 'Vieira', 'Bezerra', 'Braga', 'Neves', 'Borges',
        'Ramos', 'Cunha', 'Peixoto', 'Leal', 'Viana', 'Xavier', 'Aguiar',
        'Assis', 'Queiroz', 'Azevedo', 'Macedo', 'Fonseca', 'Rezende', 'Torres'
    ];


    private array $cities = [
        // Sudeste
        ['city' => 'São Paulo', 'state' => 'SP'],
        ['city' => 'Campinas', 'state' => 'SP'],
        ['city' => 'Santos', 'state' => 'SP'],
        ['city' => 'São Bernardo do Campo', 'state' => 'SP'],
        ['city' => 'Santo André', 'state' => 'SP'],
        ['city' => 'Guarulhos', 'state' => 'SP'],
        ['city' => 'Osasco', 'state' => 'SP'],
        ['city' => 'Ribeirão Preto', 'state' => 'SP'],
        ['city' => 'Sorocaba', 'state' => 'SP'],
        ['city' => 'São José dos Campos', 'state' => 'SP'],
        ['city' => 'Taubaté', 'state' => 'SP'],
        ['city' => 'Mogi das Cruzes', 'state' => 'SP'],
        ['city' => 'Bauru', 'state' => 'SP'],
        ['city' => 'Jundiaí', 'state' => 'SP'],
        ['city' => 'Rio de Janeiro', 'state' => 'RJ'],
        ['city' => 'Niterói', 'state' => 'RJ'],
        ['city' => 'Duque de Caxias', 'state' => 'RJ'],
        ['city' => 'Nova Iguaçu', 'state' => 'RJ'],
        ['city' => 'Belford Roxo', 'state' => 'RJ'],
        ['city' => 'Campos dos Goytacazes', 'state' => 'RJ'],
        ['city' => 'Belo Horizonte', 'state' => 'MG'],
        ['city' => 'Uberlândia', 'state' => 'MG'],
        ['city' => 'Contagem', 'state' => 'MG'],
        ['city' => 'Juiz de Fora', 'state' => 'MG'],
        ['city' => 'Betim', 'state' => 'MG'],
        ['city' => 'Montes Claros', 'state' => 'MG'],
        ['city' => 'Governador Valadares', 'state' => 'MG'],
        ['city' => 'Vitória', 'state' => 'ES'],
        ['city' => 'Vila Velha', 'state' => 'ES'],
        ['city' => 'Cariacica', 'state' => 'ES'],
        ['city' => 'Serra', 'state' => 'ES'],

        // Sul
        ['city' => 'Curitiba', 'state' => 'PR'],
        ['city' => 'Londrina', 'state' => 'PR'],
        ['city' => 'Maringá', 'state' => 'PR'],
        ['city' => 'Cascavel', 'state' => 'PR'],
        ['city' => 'Ponta Grossa', 'state' => 'PR'],
        ['city' => 'Porto Alegre', 'state' => 'RS'],
        ['city' => 'Caxias do Sul', 'state' => 'RS'],
        ['city' => 'Pelotas', 'state' => 'RS'],
        ['city' => 'Santa Maria', 'state' => 'RS'],
        ['city' => 'Florianópolis', 'state' => 'SC'],
        ['city' => 'Joinville', 'state' => 'SC'],
        ['city' => 'Blumenau', 'state' => 'SC'],
        ['city' => 'Itajaí', 'state' => 'SC'],
        ['city' => 'Chapecó', 'state' => 'SC'],

        // Nordeste
        ['city' => 'Salvador', 'state' => 'BA'],
        ['city' => 'Feira de Santana', 'state' => 'BA'],
        ['city' => 'Vitória da Conquista', 'state' => 'BA'],
        ['city' => 'Fortaleza', 'state' => 'CE'],
        ['city' => 'Caucaia', 'state' => 'CE'],
        ['city' => 'Juazeiro do Norte', 'state' => 'CE'],
        ['city' => 'Recife', 'state' => 'PE'],
        ['city' => 'Olinda', 'state' => 'PE'],
        ['city' => 'Jaboatão dos Guararapes', 'state' => 'PE'],
        ['city' => 'Caruaru', 'state' => 'PE'],
        ['city' => 'Natal', 'state' => 'RN'],
        ['city' => 'Mossoró', 'state' => 'RN'],
        ['city' => 'João Pessoa', 'state' => 'PB'],
        ['city' => 'Campina Grande', 'state' => 'PB'],
        ['city' => 'Maceió', 'state' => 'AL'],
        ['city' => 'Aracaju', 'state' => 'SE'],
        ['city' => 'São Luís', 'state' => 'MA'],
        ['city' => 'Teresina', 'state' => 'PI'],

        // Centro-Oeste
        ['city' => 'Brasília', 'state' => 'DF'],
        ['city' => 'Goiânia', 'state' => 'GO'],
        ['city' => 'Aparecida de Goiânia', 'state' => 'GO'],
        ['city' => 'Anápolis', 'state' => 'GO'],
        ['city' => 'Campo Grande', 'state' => 'MS'],
        ['city' => 'Cuiabá', 'state' => 'MT'],
        ['city' => 'Rondonópolis', 'state' => 'MT'],
        ['city' => 'Sorriso', 'state' => 'MT'],
        ['city' => 'Alta Floresta', 'state' => 'MT'],

        // Norte
        ['city' => 'Manaus', 'state' => 'AM'],
        ['city' => 'Belém', 'state' => 'PA'],
        ['city' => 'Ananindeua', 'state' => 'PA'],
        ['city' => 'Santarém', 'state' => 'PA'],
        ['city' => 'Palmas', 'state' => 'TO'],
        ['city' => 'Porto Velho', 'state' => 'RO'],
        ['city' => 'Rio Branco', 'state' => 'AC'],
        ['city' => 'Macapá', 'state' => 'AP'],
    ];


    public function handle(): int
    {
        $this->displayHeader();

        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');
        $auto = $this->option('auto');

        $articlesToPublish = $this->getArticlesToPublish($limit);

        if ($articlesToPublish->isEmpty()) {
            $this->warn('⚠️ Nenhum artigo gerado encontrado para publicação!');
            if (!$auto) {
                $this->displaySuggestions();
            }
            return self::SUCCESS;
        }

        $this->displayArticlesSummary($articlesToPublish);

        if ($dryRun) {
            $this->info('🧪 DRY-RUN: Simulação concluída sem publicar');
            $this->displayDateExamples();
            return self::SUCCESS;
        }

        if (!$auto) {
            if (!$this->confirm("Publicar {$articlesToPublish->count()} artigo(s) com datas humanizadas?", true)) {
                $this->info('❌ Operação cancelada');
                return self::SUCCESS;
            }
        } else {
            $this->info("🤖 Modo automático: Publicando {$articlesToPublish->count()} artigo(s)...");
        }

        $this->newLine();

        foreach ($articlesToPublish as $index => $tempArticle) {
            $processed = $this->stats['processed'] + 1;
            $this->info("📄 [{$processed}] {$tempArticle->title}");
            $this->publishArticle($tempArticle);
            $this->newLine();
        }

        $this->displayFinalStats();

        if ($this->stats['published'] > 0) {
            $this->info('🔄 Limpando cache...');
            Artisan::call('optimize:clear');
            $this->info('✅ Cache limpo');
        }

        return $this->stats['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function getArticlesToPublish(int $limit=1)
    {
        $query = GenerationTempArticle::where('generation_status', 'generated')
            ->whereNull('published_article_id');

        if ($category = $this->option('category')) {
            $query->where('category_slug', $category);
        }

        if ($priority = $this->option('priority')) {
            $query->where('generation_priority', $priority);
        }

        if ($slug = $this->option('slug')) {
            $query->where('slug', $slug);
        }

        return $query->orderBy('generated_at', 'asc')
            ->limit($limit)
            ->get();
    }

    private function publishArticle(GenerationTempArticle $tempArticle): void
    {
        try {
            if (empty($tempArticle->generated_json)) {
                $this->error("   ❌ generated_json vazio!");
                $this->stats['errors']++;
                $this->stats['processed']++;
                return;
            }

            $json = $tempArticle->generated_json;

            $requiredFields = ['title', 'slug', 'seo_data', 'metadata'];
            foreach ($requiredFields as $field) {
                if (!isset($json[$field])) {
                    $this->error("   ❌ Campo obrigatório ausente: {$field}");
                    $this->stats['errors']++;
                    $this->stats['processed']++;
                    return;
                }
            }

            $slug = $json['slug'];
            if (Article::where('slug', $slug)->exists()) {
                if ($this->option('force')) {
                    $slug = $this->generateUniqueSlug($slug);
                    $this->warn("   ⚠️ Slug duplicado! Usando: {$slug}");
                } else {
                    $this->warn("   ⏭️ Artigo já existe (slug: {$slug}). Use --force para adicionar sufixo");
                    $this->stats['skipped']++;
                    $this->stats['processed']++;
                    return;
                }
            }

            $dates = $this->generateHumanizedDates();
            $content = $this->extractContent($json);
            
            $metadata = $json['metadata'] ?? [];
            if (isset($metadata['content_blocks'])) {
                unset($metadata['content_blocks']);
            }

            $this->line("   📁 Categoria: {$json['category_name']} > {$json['subcategory_name']}");
            $this->line("   🔗 Slug: {$slug}");
            $this->line("   📅 Created: " . Carbon::instance($dates['created_at'])->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s'));
            $this->line("   📅 Updated: " . Carbon::instance($dates['updated_at'])->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s'));

            $article = Article::create([
                'title' => $json['title'],
                'slug' => $slug,
                'template' => $json['template'] ?? 'generic_article',
                'category_id' => $json['category_id'],
                'category_name' => $json['category_name'],
                'category_slug' => $json['category_slug'],
                'subcategory_id' => $json['subcategory_id'] ?? null,
                'subcategory_name' => $json['subcategory_name'] ?? null,
                'subcategory_slug' => $json['subcategory_slug'] ?? null,
                'content' => $content,
                'seo_data' => $json['seo_data'],
                'metadata' => $metadata,
                'extracted_entities' => $json['extracted_entities'] ?? [],
                'tags' => $this->extractTags($json),
                'related_topics' => $this->extractRelatedTopics($json),
                'status' => 'published_temp',
                'created_at' => $dates['created_at'],
                'updated_at' => $dates['updated_at'],
            ]);

            // ✅ NOVO: Corrigir nomes duplicados automaticamente
            $namesFixed = $this->fixDuplicateTestimonialNames($article);
            if ($namesFixed > 0) {
                $this->line("   🔧 {$namesFixed} nome(s) corrigido(s) automaticamente");
                $this->stats['names_fixed'] += $namesFixed;
            }

            $tempArticle->markAsPublished($article->_id);
            $this->activateMaintenanceEntities($article);

            $this->info("   ✅ Publicado com sucesso! ID: {$article->_id}");
            $this->stats['published']++;

            Log::info('PublishGeneratedHumanized: Artigo publicado', [
                'temp_article_id' => $tempArticle->_id,
                'article_id' => $article->_id,
                'title' => $article->title,
                'slug' => $article->slug,
                'category' => $json['category_name'],
                'names_fixed' => $namesFixed,
            ]);
        } catch (\Exception $e) {
            $this->error("   💥 Erro: " . $e->getMessage());
            $this->stats['errors']++;

            Log::error('PublishGeneratedHumanized: Erro ao publicar', [
                'temp_article_id' => $tempArticle->_id ?? 'N/A',
                'title' => $tempArticle->title ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        $this->stats['processed']++;
    }

    /**
     * ✅ NOVO: Corrigir nomes duplicados nos testimonials
     */
    private function fixDuplicateTestimonialNames(Article $article): int
    {
        $content = $article->content;
        $namesFixed = 0;

        if (!isset($content['blocks']) || !is_array($content['blocks'])) {
            return 0;
        }

        foreach ($content['blocks'] as &$block) {
            if (!isset($block['block_type']) || $block['block_type'] !== 'testimonial') {
                continue;
            }

            if (!isset($block['content']['author'])) {
                continue;
            }

            // Gerar novo nome
            $newName = $this->generateRandomName();
            $newAge = rand(28, 55);
            $newLocation = $this->getRandomCity();
            $newAuthor = "{$newName}, {$newAge} anos, {$newLocation['city']}-{$newLocation['state']}";

            // ✅ Extrair NOME COMPLETO do author antigo
            $oldAuthor = $block['content']['author'];
            $oldFullName = trim(explode(',', $oldAuthor)[0] ?? ''); // "Leonardo Rodrigues"
            $oldFirstName = explode(' ', $oldFullName)[0] ?? '';     // "Leonardo"

            $newFirstName = explode(' ', $newName)[0];

            // Atualizar author
            $block['content']['author'] = $newAuthor;

            // ✅ Substituir NOME COMPLETO no context (prioridade 1)
            if (!empty($block['content']['context']) && !empty($oldFullName)) {
                // Tentar substituir nome completo primeiro
                if (strpos($block['content']['context'], $oldFullName) !== false) {
                    $block['content']['context'] = str_replace(
                        $oldFullName,
                        $newName,
                        $block['content']['context']
                    );
                } 
                // Fallback: substituir só primeiro nome
                elseif (!empty($oldFirstName)) {
                    $block['content']['context'] = str_replace(
                        $oldFirstName,
                        $newFirstName,
                        $block['content']['context']
                    );
                }
            }

            // ✅ Substituir NOME COMPLETO no heading (prioridade 1)
            if (!empty($block['heading']) && !empty($oldFullName)) {
                // Tentar substituir nome completo primeiro
                if (strpos($block['heading'], $oldFullName) !== false) {
                    $block['heading'] = str_replace(
                        $oldFullName,
                        $newName,
                        $block['heading']
                    );
                } 
                // Fallback: substituir só primeiro nome
                elseif (!empty($oldFirstName)) {
                    $block['heading'] = str_replace(
                        $oldFirstName,
                        $newFirstName,
                        $block['heading']
                    );
                }
            }

            $namesFixed++;
        }

        if ($namesFixed > 0) {
            $article->content = $content;
            $article->save();
        }

        return $namesFixed;
    }

    private function generateRandomName(): string
    {
        $firstName = $this->firstNames[array_rand($this->firstNames)];
        $lastName = $this->lastNames[array_rand($this->lastNames)];
        return "{$firstName} {$lastName}";
    }

    private function getRandomCity(): array
    {
        return $this->cities[array_rand($this->cities)];
    }

    private function extractContent(array $json): array
    {
        if (isset($json['content']) && is_array($json['content'])) {
            $this->line("   ✅ Content encontrado na raiz (estrutura correta)");
            return $json['content'];
        }

        if (isset($json['metadata']['content_blocks']) && is_array($json['metadata']['content_blocks'])) {
            $this->warn("   ⚠️ Content encontrado em metadata.content_blocks (estrutura antiga)");
            $this->line("   🔄 Convertendo para estrutura correta...");
            return ['blocks' => $json['metadata']['content_blocks']];
        }

        $this->error("   ❌ Content não encontrado em lugar nenhum!");
        Log::error('PublishGeneratedHumanized: Content não encontrado', [
            'title' => $json['title'] ?? 'N/A',
            'has_content_root' => isset($json['content']),
            'has_metadata_content_blocks' => isset($json['metadata']['content_blocks']),
        ]);

        return [];
    }

    private function generateHumanizedDates(): array
    {
        $timezone = 'America/Sao_Paulo';
        $now = Carbon::now($timezone);

        // created_at: -10 a +5 minutos do horário do cron
        $minutesBeforeExecution = rand(-10, 5);
        $createdAt = $now->copy()->addMinutes($minutesBeforeExecution);

        // updated_at: +30 a +120 minutos depois do created_at
        $minutesAfterCreation = rand(30, 120);
        $updatedAt = $createdAt->copy()->addMinutes($minutesAfterCreation);

        return [
            'created_at' => $createdAt->toDateTime(), // MongoDB precisa de DateTime
            'updated_at' => $updatedAt->toDateTime()
        ];
    }

    private function generateUniqueSlug(string $baseSlug): string
    {
        $counter = 1;
        $newSlug = $baseSlug;

        while (Article::where('slug', $newSlug)->exists()) {
            $newSlug = $baseSlug . '-' . $counter;
            $counter++;

            if ($counter > 100) {
                $newSlug = $baseSlug . '-' . uniqid();
                break;
            }
        }

        return $newSlug;
    }

    private function extractTags(array $json): array
    {
        $tags = [];

        if (!empty($json['seo_data']['primary_keyword'])) {
            $tags[] = $json['seo_data']['primary_keyword'];
        }

        if (!empty($json['seo_data']['secondary_keywords'])) {
            if (is_array($json['seo_data']['secondary_keywords'])) {
                $tags = array_merge($tags, $json['seo_data']['secondary_keywords']);
            }
        }

        if (!empty($json['metadata']['keywords'])) {
            if (is_array($json['metadata']['keywords'])) {
                $tags = array_merge($tags, $json['metadata']['keywords']);
            }
        }

        return array_unique(array_filter($tags));
    }

    private function extractRelatedTopics(array $json): array
    {
        $topics = [];

        if (!empty($json['metadata']['related_content'])) {
            foreach ($json['metadata']['related_content'] as $related) {
                if (!empty($related['title'])) {
                    $topics[] = [
                        'title' => $related['title'],
                        'slug' => $related['slug'] ?? \Illuminate\Support\Str::slug($related['title']),
                        'icon' => $related['icon'] ?? null
                    ];
                }
            }
        }

        if (empty($topics) && !empty($json['seo_data']['related_topics'])) {
            foreach ($json['seo_data']['related_topics'] as $topic) {
                if (is_string($topic)) {
                    $topics[] = [
                        'title' => $topic,
                        'slug' => \Illuminate\Support\Str::slug($topic),
                        'icon' => null
                    ];
                }
            }
        }

        return $topics;
    }

    private function displayHeader(): void
    {
        $this->info('╔═══════════════════════════════════════════════════════════╗');
        $this->info('║   📤 PUBLICAR ARTIGOS - DATAS HUMANIZADAS 🕐            ║');
        $this->info('║   + Correção Automática de Nomes v1.1                    ║');
        $this->info('╚═══════════════════════════════════════════════════════════╝');
        $this->newLine();
    }

    private function displayArticlesSummary($articles): void
    {
        $this->info('📋 ARTIGOS PRONTOS PARA PUBLICAÇÃO:');
        $this->table(
            ['#', 'Título', 'Categoria', 'Modelo', 'Custo', 'Gerado em'],
            $articles->map(function ($article, $index) {
                return [
                    $index + 1,
                    \Illuminate\Support\Str::limit($article->title, 40),
                    $article->generated_json['category_name'] ?? 'N/A',
                    strtoupper($article->generation_model_used ?? 'N/A'),
                    number_format($article->generation_cost ?? 0, 2),
                    $article->generated_at ? $article->generated_at->format('d/m/Y H:i') : 'N/A',
                ];
            })
        );
        $this->newLine();
    }

    private function displayDateExamples(): void
    {
        $this->newLine();
        $this->info('📅 EXEMPLO DE DATAS HUMANIZADAS:');

        $timezone = new \DateTimeZone('America/Sao_Paulo');

        for ($i = 1; $i <= 3; $i++) {
            $dates = $this->generateHumanizedDates();
            $created = Carbon::instance($dates['created_at'])->setTimezone('America/Sao_Paulo');
            $updated = Carbon::instance($dates['updated_at'])->setTimezone('America/Sao_Paulo');
            
            $this->line("   Exemplo {$i}:");
            $this->line("   • Created: " . $created->format('d/m/Y H:i:s'));
            $this->line("   • Updated: " . $updated->format('d/m/Y H:i:s'));
            $this->line("   • Diferença: " . $created->diffForHumans($updated));
            $this->newLine();
        }
    }

    private function displayFinalStats(): void
    {
        $this->info('╔═══════════════════════════════════════════════════════════╗');
        $this->info('║                    📊 RESULTADO                          ║');
        $this->info('╚═══════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->line("✅ Publicados: {$this->stats['published']}");
        $this->line("🔧 Nomes corrigidos: {$this->stats['names_fixed']}");
        $this->line("⏭️ Pulados: {$this->stats['skipped']}");
        $this->line("❌ Erros: {$this->stats['errors']}");
        $this->line("📊 Total processado: {$this->stats['processed']}");
        $this->newLine();

        if ($this->stats['published'] > 0) {
            $this->info('✅ Artigos publicados com datas humanizadas e nomes únicos!');
            $this->line('   Os artigos parecem ter sido publicados de forma orgânica ao longo do tempo');
            $this->line('   Todos os testimonials têm autores únicos');
        }
    }

    private function displaySuggestions(): void
    {
        $this->newLine();
        $this->info('💡 SUGESTÕES:');
        $this->line('   • Gere artigos primeiro: php artisan temp-article:generate-standard');
        $this->line('   • Valide artigos: php artisan temp-article:validate');
        $this->line('   • Verifique status: php artisan temp-article:stats');
        $this->newLine();

        $pending = GenerationTempArticle::where('generation_status', 'pending')->count();
        $generated = GenerationTempArticle::where('generation_status', 'generated')
            ->whereNull('published_article_id')
            ->count();

        $this->line("📊 Status atual:");
        $this->line("   Pendentes de geração: {$pending}");
        $this->line("   Gerados (prontos): {$generated}");
        $this->newLine();
    }
}