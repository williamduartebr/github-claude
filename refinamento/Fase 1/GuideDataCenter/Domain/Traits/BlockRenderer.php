<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Domain\Traits;

use Src\GuideDataCenter\Domain\Enums\BlockType;

/**
 * Trait BlockRenderer
 * 
 * Adiciona funcionalidades de manipulação de blocos de conteúdo
 * ao modelo Guide.
 * 
 * Permite adicionar, remover, reordenar e validar blocos de forma
 * programática, seguindo os princípios SOLID.
 * 
 * @author Claude Sonnet 4.5
 * @version 1.0
 * @date 2024-12-14
 */
trait BlockRenderer
{
    /**
     * Retorna todos os blocos ordenados
     */
    public function getContentBlocks(): array
    {
        $blocks = $this->content_blocks ?? [];
        
        return collect($blocks)
            ->sortBy('order')
            ->values()
            ->toArray();
    }

    /**
     * Retorna blocos de um tipo específico
     */
    public function getBlocksByType(BlockType|string $type): array
    {
        $typeValue = $type instanceof BlockType ? $type->value : $type;
        
        return collect($this->content_blocks ?? [])
            ->where('type', $typeValue)
            ->sortBy('order')
            ->values()
            ->toArray();
    }

    /**
     * Adiciona um bloco ao guia
     */
    public function addBlock(BlockType|string $type, array $data, ?int $order = null): self
    {
        $typeValue = $type instanceof BlockType ? $type->value : $type;
        $blocks = $this->content_blocks ?? [];
        
        // Se order não fornecido, coloca no final
        if ($order === null) {
            $maxOrder = collect($blocks)->max('order') ?? 0;
            $order = $maxOrder + 1;
        }
        
        $blocks[] = [
            'type' => $typeValue,
            'order' => $order,
            'data' => $data,
        ];
        
        $this->content_blocks = $blocks;
        
        return $this;
    }

    /**
     * Remove blocos de um tipo específico
     */
    public function removeBlocksByType(BlockType|string $type): self
    {
        $typeValue = $type instanceof BlockType ? $type->value : $type;
        
        $this->content_blocks = collect($this->content_blocks ?? [])
            ->filter(fn($block) => $block['type'] !== $typeValue)
            ->values()
            ->toArray();
        
        return $this;
    }

    /**
     * Atualiza um bloco específico pelo índice
     */
    public function updateBlock(int $index, array $data): self
    {
        $blocks = $this->content_blocks ?? [];
        
        if (isset($blocks[$index])) {
            $blocks[$index]['data'] = array_merge(
                $blocks[$index]['data'] ?? [],
                $data
            );
            $this->content_blocks = $blocks;
        }
        
        return $this;
    }

    /**
     * Reordena os blocos
     */
    public function reorderBlocks(): self
    {
        $blocks = collect($this->content_blocks ?? [])
            ->sortBy('order')
            ->values()
            ->map(function ($block, $index) {
                $block['order'] = $index + 1;
                return $block;
            })
            ->toArray();
        
        $this->content_blocks = $blocks;
        
        return $this;
    }

    /**
     * Valida estrutura de todos os blocos
     */
    public function validateBlocks(): array
    {
        $errors = [];
        
        foreach ($this->content_blocks ?? [] as $index => $block) {
            // Verifica campos obrigatórios
            if (!isset($block['type'])) {
                $errors[] = "Bloco #{$index}: campo 'type' é obrigatório";
                continue;
            }
            
            if (!isset($block['order'])) {
                $errors[] = "Bloco #{$index}: campo 'order' é obrigatório";
            }
            
            if (!isset($block['data'])) {
                $errors[] = "Bloco #{$index}: campo 'data' é obrigatório";
                continue;
            }
            
            // Valida tipo de bloco
            try {
                $blockType = BlockType::from($block['type']);
                
                // Valida estrutura de dados específica do tipo
                if (!$blockType->validateData($block['data'])) {
                    $errors[] = "Bloco #{$index} ({$block['type']}): estrutura de dados inválida";
                }
            } catch (\ValueError $e) {
                $errors[] = "Bloco #{$index}: tipo '{$block['type']}' inválido";
            }
        }
        
        return $errors;
    }

    /**
     * Verifica se o guia possui um tipo de bloco
     */
    public function hasBlockType(BlockType|string $type): bool
    {
        $typeValue = $type instanceof BlockType ? $type->value : $type;
        
        return collect($this->content_blocks ?? [])
            ->contains('type', $typeValue);
    }

    /**
     * Conta quantos blocos de um tipo existem
     */
    public function countBlockType(BlockType|string $type): int
    {
        $typeValue = $type instanceof BlockType ? $type->value : $type;
        
        return collect($this->content_blocks ?? [])
            ->where('type', $typeValue)
            ->count();
    }

    /**
     * Retorna o primeiro bloco de um tipo
     */
    public function getFirstBlockOfType(BlockType|string $type): ?array
    {
        $typeValue = $type instanceof BlockType ? $type->value : $type;
        
        return collect($this->content_blocks ?? [])
            ->where('type', $typeValue)
            ->sortBy('order')
            ->first();
    }

    /**
     * Remove todos os blocos
     */
    public function clearBlocks(): self
    {
        $this->content_blocks = [];
        return $this;
    }

    /**
     * Define blocos a partir de um array
     */
    public function setBlocks(array $blocks): self
    {
        $this->content_blocks = $blocks;
        return $this;
    }

    /**
     * Retorna estatísticas dos blocos
     */
    public function getBlocksStats(): array
    {
        $blocks = $this->content_blocks ?? [];
        
        return [
            'total' => count($blocks),
            'types' => collect($blocks)->pluck('type')->unique()->values()->toArray(),
            'by_type' => collect($blocks)->countBy('type')->toArray(),
            'has_errors' => !empty($this->validateBlocks()),
        ];
    }
}
