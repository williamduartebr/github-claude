<?php

namespace Src\GenericArticleGenerator\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\AutoInfoCenter\Domain\Eloquent\Article;

/**
 * FixDuplicateTestimonialNamesCommand
 * 
 * Comando cirúrgico para substituir nomes repetidos em testimonials
 * de artigos com template "generic_article".
 * 
 * PROBLEMA:
 * - Vários artigos têm "Ricardo Mendes" repetido nos testimonials
 * - Quebra a credibilidade e aparência de conteúdo duplicado
 * 
 * SOLUÇÃO:
 * - Gera nomes brasileiros aleatórios realistas
 * - Gera idades entre 28-55 anos
 * - Gera cidades e estados brasileiros diversos
 * - Mantém integridade da estrutura JSON
 * 
 * USO:
 * php artisan article:fix-duplicate-testimonial-names
 * php artisan article:fix-duplicate-testimonial-names --dry-run
 * php artisan article:fix-duplicate-testimonial-names --limit=5
 * 
 * @author Claude Sonnet 4.5
 * @version 1.0
 */
class FixDuplicateTestimonialNamesCommand extends Command
{
    protected $signature = 'article:fix-duplicate-testimonial-names 
                            {--dry-run : Simular sem salvar alterações}
                            {--limit= : Limitar número de artigos processados}
                            {--name= : Substituir apenas um nome específico (ex: "Ricardo Mendes")}';

    protected $description = 'Substitui nomes repetidos nos testimonials de artigos generic_article';

    private array $stats = [
        'processed' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => 0
    ];

    // Base de dados de nomes brasileiros realistas
    private array $firstNames = [
        'Carlos',
        'Fernando',
        'Roberto',
        'José',
        'Paulo',
        'André',
        'Marcos',
        'Rafael',
        'Rodrigo',
        'Bruno',
        'Diego',
        'Lucas',
        'Thiago',
        'Felipe',
        'Gustavo',
        'Leonardo',
        'Gabriel',
        'Matheus',
        'Daniel',
        'Pedro',
        'Renato',
        'Fábio',
        'Marcelo',
        'Alexandre',
        'Vinícius',
        'Leandro',
        'Maurício',
        'Eduardo',
        'Anderson',
        'Wellington',
        'Cristiano',
        'João'
    ];

    private array $lastNames = [
        'Silva',
        'Santos',
        'Oliveira',
        'Souza',
        'Lima',
        'Ferreira',
        'Costa',
        'Rodrigues',
        'Almeida',
        'Nascimento',
        'Araújo',
        'Ribeiro',
        'Martins',
        'Carvalho',
        'Pereira',
        'Gomes',
        'Barbosa',
        'Rocha',
        'Dias',
        'Monteiro',
        'Cardoso',
        'Machado',
        'Freitas',
        'Fernandes',
        'Soares',
        'Mendes',
        'Pinto',
        'Moreira',
        'Cavalcanti',
        'Reis',
        'Farias',
        'Lopes'
    ];

    private array $cities = [
        ['city' => 'São Paulo', 'state' => 'SP'],
        ['city' => 'Rio de Janeiro', 'state' => 'RJ'],
        ['city' => 'Belo Horizonte', 'state' => 'MG'],
        ['city' => 'Curitiba', 'state' => 'PR'],
        ['city' => 'Porto Alegre', 'state' => 'RS'],
        ['city' => 'Brasília', 'state' => 'DF'],
        ['city' => 'Salvador', 'state' => 'BA'],
        ['city' => 'Fortaleza', 'state' => 'CE'],
        ['city' => 'Recife', 'state' => 'PE'],
        ['city' => 'Manaus', 'state' => 'AM'],
        ['city' => 'Goiânia', 'state' => 'GO'],
        ['city' => 'Belém', 'state' => 'PA'],
        ['city' => 'Campinas', 'state' => 'SP'],
        ['city' => 'Florianópolis', 'state' => 'SC'],
        ['city' => 'Vitória', 'state' => 'ES'],
        ['city' => 'Santos', 'state' => 'SP'],
        ['city' => 'São Bernardo do Campo', 'state' => 'SP'],
        ['city' => 'Ribeirão Preto', 'state' => 'SP'],
        ['city' => 'Joinville', 'state' => 'SC'],
        ['city' => 'Uberlândia', 'state' => 'MG'],
        ['city' => 'Sorocaba', 'state' => 'SP'],
        ['city' => 'Natal', 'state' => 'RN'],
        ['city' => 'Campo Grande', 'state' => 'MS'],
        ['city' => 'São Luís', 'state' => 'MA'],
        ['city' => 'Maceió', 'state' => 'AL']
    ];

    public function handle(): int
    {
        $this->displayHeader();

        $dryRun = $this->option('dry-run');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $targetName = $this->option('name');

        // Buscar artigos generic_article
        $query = Article::where('template', 'generic_article')
            ->where('status', 'published');

        if ($limit) {
            $query->limit($limit);
        }

        $articles = $query->get();

        if ($articles->isEmpty()) {
            $this->warn('⚠️ Nenhum artigo generic_article encontrado!');
            return self::SUCCESS;
        }

        $this->info("📊 {$articles->count()} artigos generic_article encontrados");
        $this->newLine();

        if ($dryRun) {
            $this->warn('🧪 MODO DRY-RUN: Nenhuma alteração será salva');
            $this->newLine();
        }

        foreach ($articles as $article) {
            $this->processArticle($article, $dryRun, $targetName);
        }

        $this->displayFinalStats();

        return self::SUCCESS;
    }

    /**
     * Processar artigo individual
     */
    private function processArticle(Article $article, bool $dryRun, ?string $targetName): void
    {
        try {
            $this->stats['processed']++;

            $content = $article->content;

            if (!isset($content['blocks']) || !is_array($content['blocks'])) {
                $this->stats['skipped']++;
                return;
            }

            $changed = false;
            $testimonialCount = 0;

            // Percorrer blocos procurando testimonials
            foreach ($content['blocks'] as &$block) {
                if (!isset($block['block_type']) || $block['block_type'] !== 'testimonial') {
                    continue;
                }

                if (!isset($block['content']['author'])) {
                    continue;
                }

                $testimonialCount++;
                $currentAuthor = $block['content']['author'];
                $currentContext = $block['content']['context'] ?? '';

                // Extrair nome atual do author (sem idade/cidade)
                $currentName = $this->extractName($currentAuthor);

                // ✅ NOVO: Também verificar nomes no context
                $nameInContext = $this->findNameInContext($currentContext);

                // Se targetName foi especificado, verificar se bate no author OU no context
                if ($targetName) {
                    $matchesAuthor = $currentName === $targetName;
                    $matchesContext = $nameInContext === $targetName;

                    if (!$matchesAuthor && !$matchesContext) {
                        continue;
                    }
                }

                // Gerar novo autor
                $newName = $this->generateRandomName();
                $newAge = rand(28, 55);
                $newLocation = $this->getRandomCity();

                $newAuthor = "{$newName}, {$newAge} anos, {$newLocation['city']}-{$newLocation['state']}";
                $newFirstName = explode(' ', $newName)[0];

                // Atualizar author
                $block['content']['author'] = $newAuthor;

                // ✅ Substituir nome no context
                if (!empty($currentContext)) {
                    $oldContext = $currentContext;

                    // Se encontrou nome no context, substituir
                    if ($nameInContext) {
                        $oldFirstName = explode(' ', $nameInContext)[0];
                        $block['content']['context'] = str_replace(
                            $oldFirstName,
                            $newFirstName,
                            $oldContext
                        );
                    } else {
                        // Fallback: tentar substituir pelo nome do author antigo
                        $oldFirstName = explode(' ', $currentName)[0];
                        $block['content']['context'] = str_replace(
                            $oldFirstName,
                            $newFirstName,
                            $oldContext
                        );
                    }

                    $contextChanged = $oldContext !== $block['content']['context'];
                } else {
                    $contextChanged = false;
                }

                $changed = true;

                $this->line("   📝 Testimonial {$testimonialCount}:");
                $this->line("      Author Antes: {$currentAuthor}");
                $this->line("      Author Depois: {$newAuthor}");
                if ($contextChanged) {
                    $this->line("      Context: Nome atualizado ({$oldFirstName} → {$newFirstName})");
                }
            }

            if ($changed) {
                if (!$dryRun) {
                    $article->content = $content;
                    $article->save();
                }

                $this->info("✅ [{$this->stats['processed']}] {$article->title}");
                $this->line("   {$testimonialCount} testimonial(s) atualizado(s)");
                $this->stats['updated']++;
            } else {
                $this->line("⏭️ [{$this->stats['processed']}] {$article->title}");
                $this->line("   Sem testimonials para atualizar");
                $this->stats['skipped']++;
            }

            $this->newLine();
        } catch (\Exception $e) {
            $this->error("💥 Erro no artigo {$article->_id}: {$e->getMessage()}");
            $this->stats['errors']++;

            Log::error('FixDuplicateTestimonialNames: Erro ao processar artigo', [
                'article_id' => $article->_id,
                'title' => $article->title,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Encontrar nome brasileiro comum no texto do context
     */
    private function findNameInContext(string $context): ?string
    {
        if (empty($context)) {
            return null;
        }

        // Lista de primeiros nomes comuns para detectar
        $commonNames = array_merge($this->firstNames, [
            'Roberto',
            'Ricardo',
            'Fernando',
            'Carlos',
            'André',
            'Paulo',
            'Marcos',
            'Pedro',
            'João',
            'José',
            'Francisco',
            'Antonio'
        ]);

        // Procurar por nome no início de frase
        foreach ($commonNames as $name) {
            // Procurar padrão: "Nome participa" ou "Nome tem" ou "Acompanhamos o veículo de Nome"
            if (preg_match('/\b' . preg_quote($name, '/') . '\b/i', $context)) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Extrair apenas o nome (sem idade e cidade)
     */
    private function extractName(string $author): string
    {
        // "Ricardo Mendes, 34 anos, São Paulo-SP" -> "Ricardo Mendes"
        $parts = explode(',', $author);
        return trim($parts[0] ?? $author);
    }

    /**
     * Gerar nome aleatório brasileiro
     */
    private function generateRandomName(): string
    {
        $firstName = $this->firstNames[array_rand($this->firstNames)];
        $lastName = $this->lastNames[array_rand($this->lastNames)];

        return "{$firstName} {$lastName}";
    }

    /**
     * Obter cidade aleatória
     */
    private function getRandomCity(): array
    {
        return $this->cities[array_rand($this->cities)];
    }

    /**
     * Exibir header
     */
    private function displayHeader(): void
    {
        $this->info('╔═══════════════════════════════════════════════════════════╗');
        $this->info('║   🔧 CORRIGIR NOMES DUPLICADOS EM TESTIMONIALS          ║');
        $this->info('║   Template: generic_article                              ║');
        $this->info('╚═══════════════════════════════════════════════════════════╝');
        $this->newLine();
    }

    /**
     * Exibir estatísticas finais
     */
    private function displayFinalStats(): void
    {
        $this->info('╔═══════════════════════════════════════════════════════════╗');
        $this->info('║                    📊 RESULTADO                          ║');
        $this->info('╚═══════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->line("📄 Artigos processados: {$this->stats['processed']}");
        $this->line("✅ Artigos atualizados: {$this->stats['updated']}");
        $this->line("⏭️ Artigos pulados: {$this->stats['skipped']}");
        $this->line("❌ Erros: {$this->stats['errors']}");
        $this->newLine();

        if ($this->stats['updated'] > 0) {
            $this->info('✅ Nomes dos testimonials atualizados com sucesso!');
            $this->line('   Os artigos agora têm autores únicos e realistas');
        }

        if ($this->stats['errors'] > 0) {
            $this->warn("⚠️ {$this->stats['errors']} erro(s) encontrado(s). Verifique os logs.");
        }
    }
}
