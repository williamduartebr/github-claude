<?php

namespace Src\AutoInfoCenter\ViewModels\Templates\Traits;

/**
 * Trait para validação de termos genéricos em versões de veículos
 * 
 * Rejeita automaticamente especificações que contenham termos genéricos/fallback
 * nas versões dos veículos, garantindo qualidade dos dados exibidos.
 */
trait GenericTermsValidationTrait
{
    /**
     * Lista de termos genéricos proibidos
     * 
     * @var array
     */
    private array $forbiddenTerms = [
        'base',
        'básica',
        'premium',
        'standard',
        'comfort',
        'style',
        'entry',
        'top',
        'full',
        'intermediária',
        'superior',
        'completa',
        'padrão',
        'único',
        'inicial',
        'nome',
        'oficial',
        'preenchido'
    ];

    /**
     * Verifica se uma string contém termos genéricos proibidos
     * 
     * @param string $version Nome da versão a ser validada
     * @return bool True se contém termos genéricos, false caso contrário
     */
    private function containsGenericTerms(string $version): bool
    {
        if (empty(trim($version))) {
            return false;
        }

        $versionLower = strtolower(trim($version));

        foreach ($this->forbiddenTerms as $term) {
            if (str_contains($versionLower, $term)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica se uma tabela contém versões com termos genéricos proibidos
     * 
     * @param array $table Tabela com condições que possuem versões
     * @return bool True se alguma versão contém termos genéricos
     */
    private function hasGenericVersionTerms(array $table): bool
    {
        if (empty($table['condicoes'])) {
            return false;
        }

        foreach ($table['condicoes'] as $condition) {
            $version = $condition['versao'] ?? '';
            if (!empty($version) && $this->containsGenericTerms($version)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filtra array de especificações removendo itens com termos genéricos
     * 
     * @param array $specifications Array de especificações
     * @param string $versionKey Chave que contém a versão (default: 'versao')
     * @return array Array filtrado sem termos genéricos
     */
    private function filterGenericTerms(array $specifications, string $versionKey = 'versao'): array
    {
        return array_filter($specifications, function ($spec) use ($versionKey) {
            $version = $spec[$versionKey] ?? '';
            return !empty($version) && !$this->containsGenericTerms($version);
        });
    }

    /**
     * Verifica se algum item do array contém termos genéricos
     * 
     * @param array $items Array de itens para verificar
     * @param string $versionKey Chave que contém a versão (default: 'versao')
     * @return bool True se algum item contém termos genéricos
     */
    private function hasAnyGenericTerms(array $items, string $versionKey = 'versao'): bool
    {
        foreach ($items as $item) {
            $version = $item[$versionKey] ?? '';
            if (!empty($version) && $this->containsGenericTerms($version)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtém lista de termos proibidos (para debugging ou logs)
     * 
     * @return array Lista de termos genéricos proibidos
     */
    private function getForbiddenTerms(): array
    {
        return $this->forbiddenTerms;
    }

    /**
     * Adiciona termos proibidos customizados (se necessário)
     * 
     * @param array $customTerms Termos adicionais a serem proibidos
     * @return void
     */
    private function addForbiddenTerms(array $customTerms): void
    {
        $this->forbiddenTerms = array_merge(
            $this->forbiddenTerms,
            array_map('strtolower', $customTerms)
        );

        // Remove duplicatas
        $this->forbiddenTerms = array_unique($this->forbiddenTerms);
    }
}
