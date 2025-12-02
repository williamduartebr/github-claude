<?php

namespace Src\TestimonyCorrection\Application\Services;

class TestimonyTransformService
{
    public function applyCorrections(array $blocks, array $corrected): array
    {
        $i = 0;

        foreach ($blocks as &$b) {

            $type = $b['block_type'] ?? '';

            // Somente depoimentos que precisam ser corrigidos
            if (!in_array($type, ['testimonial-draft', 'testimonial'])) {
                continue;
            }

            if (!isset($corrected[$i])) {
                continue;
            }

            $c = $corrected[$i++];

            // Extrair campos em qualquer formato retornado pela IA
            $quote   = $c['quote']   ?? ($c['content']['quote'] ?? '');
            $author  = $c['author']  ?? ($c['content']['author'] ?? '');
            $vehicle = $c['vehicle'] ?? ($c['content']['vehicle'] ?? ($b['content']['vehicle'] ?? ''));
            $context = $c['context'] ?? ($c['content']['context'] ?? '');

            /**
             * ========================================================
             * 🔥 REGRAS AUTOMÁTICAS DE CORREÇÃO DE LOCALIZAÇÃO
             * ========================================================
             */

            $contextLower = mb_strtolower($context);

            // 1) Plataformas que NÃO devem ter cidade
            if (str_contains($contextLower, 'youtube') || str_contains($contextLower, 'tiktok')) {

                // Remover cidade em formatos padrão: "Fulano X., São Paulo-SP"
                $author = preg_replace('/,\s*[A-Za-zÀ-ú\s\-]+$/u', '', $author);

                // Caso fique vazio (muito raro)
                if (trim($author) === '') {
                    $author = 'Usuário do YouTube';
                }
            }

            // 2) Se o quote menciona uma cidade, mas o author tem outra → ajustar
            // Exemplo: texto menciona "São Paulo", author = "José A., Porto Velho-RO"
            $cities = [
                'são paulo', 'rio de janeiro', 'salvador', 'recife', 'manaus', 'fortaleza',
                'belo horizonte', 'curitiba', 'porto alegre', 'brasilia', 'goiânia', 'campinas',
                'florianópolis', 'vitória', 'belém', 'joão pessoa'
            ];

            foreach ($cities as $city) {
                if (str_contains($quote ? mb_strtolower($quote) : '', $city)) {

                    // Ajusta cidade quando incoerente
                    // Remove cidade atual do author
                    $author = preg_replace('/,\s*[A-Za-zÀ-ú\s\-]+$/u', '', $author);

                    // Converte para formato Nome X., Cidade-Estado (somente cidade)
                    $author .= ', ' . ucwords($city) . '-BR';

                    break;
                }
            }

            // 3) REMOVER cidade incoerente com o próprio author
            // Ex: "Thiago A., São Paulo-SP" mas quote diz "voltando pra Salvador"
            foreach ($cities as $city) {
                if (str_contains($quote ? mb_strtolower($quote) : '', $city)) {
                    if (!str_contains(mb_strtolower($author), $city)) {

                        $author = preg_replace('/,\s*[A-Za-zÀ-ú\s\-]+$/u', '', $author);
                        $author .= ', ' . ucwords($city) . '-BR';
                    }
                }
            }

            /**
             * ========================================================
             * 🔥 MONTAGEM FINAL DO BLOCO NORMALIZADO
             * ========================================================
             */
            $normalized = [
                'block_id'      => $c['block_id'] ?? ($b['block_id'] ?? uniqid('testimony-')),
                'block_type'    => 'testimony',
                'display_order' => $b['display_order'] ?? 999,
                'heading'       => $c['heading'] ?? ($b['heading'] ?? null),
                'content'       => [
                    'quote'   => $quote,
                    'author'  => $author,
                    'vehicle' => $vehicle,
                    'context' => $context,
                ]
            ];

            // Substitui o bloco original pelo normalizado
            $b = $normalized;
        }

        return $blocks;
    }
}
