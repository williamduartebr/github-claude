<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Domain\Enums;


/**
 * Enum BlockType
 * 
 * Define os tipos de blocos disponíveis no sistema de guias.
 * 
 * Sistema de blocos genéricos reutilizáveis que servem para todas as
 * 13 categorias de guias (Óleo, Fluidos, Calibragem, Pneus, Bateria,
 * Revisão, Consumo, Câmbio, Arrefecimento, Suspensão, Problemas Comuns,
 * Recalls, Comparações).
 * 
 * @author Claude Sonnet 4.5
 * @version 1.0
 * @date 2024-12-14
 */
enum BlockType: string
{
    /**
     * Hero - Cabeçalho do guia
     * 
     * Usado em: TODAS as categorias
     * 
     * Campos esperados:
     * - title: string
     * - description: string
     * - badges: array [{text, color}]
     */
    case HERO = 'hero';

    /**
     * Disclaimer - Aviso importante
     * 
     * Usado em: TODAS as categorias
     * 
     * Campos esperados:
     * - text: string
     * - type: string (warning|info|danger)
     */
    case DISCLAIMER = 'disclaimer';

    /**
     * Specs Grid - Grid de especificações técnicas
     * 
     * Usado em: Óleo, Fluidos, Calibragem, Pneus, Bateria,
     *           Câmbio, Arrefecimento, Suspensão
     * 
     * Campos esperados:
     * - heading: string (opcional)
     * - specs: array [{label, value}]
     * - note: string (opcional)
     */
    case SPECS_GRID = 'specs_grid';

    /**
     * Compatible Items - Lista de itens compatíveis
     * 
     * Usado em: Óleo, Fluidos, Pneus, Bateria, Câmbio
     * 
     * Campos esperados:
     * - heading: string
     * - items: array [{name, spec}]
     * - note: string (opcional)
     */
    case COMPATIBLE_ITEMS = 'compatible_items';

    /**
     * Intervals - Intervalos de troca/manutenção ou condições
     * 
     * Usado em: Óleo, Revisão, Fluidos, Arrefecimento, Calibragem,
     *           Pneus, Bateria, Câmbio, Suspensão
     * 
     * Campos esperados:
     * - heading: string
     * - conditions: array [{label, value}]
     * - note: string (opcional)
     */
    case INTERVALS = 'intervals';

    /**
     * Table - Tabela comparativa
     * 
     * Usado em: Consumo, Comparações, Revisão, Problemas Comuns, Recalls
     * 
     * Campos esperados:
     * - heading: string
     * - headers: array
     * - rows: array
     * - caption: string (opcional)
     * - footer: string (opcional)
     */
    case TABLE = 'table';

    /**
     * Text - Bloco de texto corrido
     * 
     * Usado em: Problemas Comuns, Recalls, Revisão, Comparações
     * 
     * Campos esperados:
     * - heading: string (opcional)
     * - content: string
     */
    case TEXT = 'text';

    /**
     * List - Lista com ícones/bullets
     * 
     * Usado em: Problemas Comuns, Recalls, Revisão, Suspensão, Consumo
     * 
     * Campos esperados:
     * - heading: string
     * - items: array (strings)
     * - icon: string (opcional)
     */
    case LIST = 'list';

    /**
     * Related Guides - Grid de guias relacionados
     * 
     * Usado em: TODAS as categorias
     * 
     * Campos esperados:
     * - heading: string
     * - guides: array [{name, icon, url}]
     */
    case RELATED_GUIDES = 'related_guides';

    /**
     * Cluster - Links essenciais do veículo
     * 
     * Usado em: TODAS as categorias
     * 
     * Campos esperados:
     * - heading: string
     * - items: array [{title, icon, url}]
     */
    case CLUSTER = 'cluster';

    /**
     * Retorna todos os valores possíveis
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Retorna label amigável do tipo de bloco
     */
    public function label(): string
    {
        return match($this) {
            self::HERO => 'Cabeçalho',
            self::DISCLAIMER => 'Aviso Importante',
            self::SPECS_GRID => 'Grade de Especificações',
            self::COMPATIBLE_ITEMS => 'Itens Compatíveis',
            self::INTERVALS => 'Intervalos/Condições',
            self::TABLE => 'Tabela Comparativa',
            self::TEXT => 'Texto',
            self::LIST => 'Lista',
            self::RELATED_GUIDES => 'Guias Relacionados',
            self::CLUSTER => 'Links Essenciais',
        };
    }

    /**
     * Retorna descrição do tipo de bloco
     */
    public function description(): string
    {
        return match($this) {
            self::HERO => 'Título, descrição e badges do guia',
            self::DISCLAIMER => 'Avisos e notas importantes',
            self::SPECS_GRID => 'Grid com especificações técnicas (3-4 colunas)',
            self::COMPATIBLE_ITEMS => 'Lista de produtos/itens compatíveis',
            self::INTERVALS => 'Intervalos de troca ou condições de uso',
            self::TABLE => 'Tabela com headers e rows',
            self::TEXT => 'Bloco de texto corrido',
            self::LIST => 'Lista com bullets ou ícones',
            self::RELATED_GUIDES => 'Grid com outros guias do mesmo veículo',
            self::CLUSTER => 'Links para conteúdos essenciais',
        };
    }

    /**
     * Retorna categorias que usam este tipo de bloco
     */
    public function usedInCategories(): array
    {
        return match($this) {
            self::HERO, self::DISCLAIMER, self::RELATED_GUIDES, self::CLUSTER => [
                'oleo', 'fluidos', 'calibragem', 'pneus', 'bateria', 
                'revisao', 'consumo', 'cambio', 'arrefecimento', 
                'suspensao', 'problemas-comuns', 'recalls', 'comparacoes'
            ],
            self::SPECS_GRID => [
                'oleo', 'fluidos', 'calibragem', 'pneus', 'bateria',
                'cambio', 'arrefecimento', 'suspensao'
            ],
            self::COMPATIBLE_ITEMS => [
                'oleo', 'fluidos', 'pneus', 'bateria', 'cambio'
            ],
            self::INTERVALS => [
                'oleo', 'revisao', 'fluidos', 'arrefecimento', 'calibragem',
                'pneus', 'bateria', 'cambio', 'suspensao'
            ],
            self::TABLE => [
                'consumo', 'comparacoes', 'revisao', 'problemas-comuns', 'recalls'
            ],
            self::TEXT => [
                'problemas-comuns', 'recalls', 'revisao', 'comparacoes'
            ],
            self::LIST => [
                'problemas-comuns', 'recalls', 'revisao', 'suspensao', 'consumo'
            ],
        };
    }

    /**
     * Verifica se o bloco é usado em uma categoria específica
     */
    public function isUsedInCategory(string $categorySlug): bool
    {
        return in_array($categorySlug, $this->usedInCategories(), true);
    }

    /**
     * Valida estrutura de dados do bloco
     */
    public function validateData(array $data): bool
    {
        return match($this) {
            self::HERO => isset($data['title'], $data['description']),
            self::DISCLAIMER => isset($data['text']),
            self::SPECS_GRID => isset($data['specs']) && is_array($data['specs']),
            self::COMPATIBLE_ITEMS => isset($data['items']) && is_array($data['items']),
            self::INTERVALS => isset($data['conditions']) && is_array($data['conditions']),
            self::TABLE => isset($data['headers'], $data['rows']) && is_array($data['headers']) && is_array($data['rows']),
            self::TEXT => isset($data['content']),
            self::LIST => isset($data['items']) && is_array($data['items']),
            self::RELATED_GUIDES => isset($data['guides']) && is_array($data['guides']),
            self::CLUSTER => isset($data['items']) && is_array($data['items']),
        };
    }
}
