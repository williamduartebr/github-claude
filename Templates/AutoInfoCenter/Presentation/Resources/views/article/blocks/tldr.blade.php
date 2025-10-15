{{--
Componente: TLDR (Too Long; Didn't Read - Resposta Rápida)

Uso: Caixa de resposta rápida/resumo executivo do artigo

Estrutura esperada:
{
  "block_type": "tldr",
  "heading": "Resposta Rápida",
  "content": {
    "answer": "Resposta direta e objetiva em 1-2 linhas",
    "key_points": [
      "Ponto principal 1",
      "Ponto principal 2",
      "Ponto principal 3"
    ]
  }
}

Exemplo de uso:
- Artigos de troubleshooting (problema → solução rápida)
- Comparações (A vs B → qual melhor)
- Dúvidas urgentes (preciso fazer X agora?)
- Ideal para usuários que querem resposta imediata

@author Claude Sonnet 4
@version 1.0
--}}

<div class="bg-gradient-to-r from-blue-50 to-blue-100 border-l-4 border-blue-500 rounded-r-lg shadow-sm p-6 mb-8">
    {{-- Heading --}}
    @if(!empty($block['heading']))
        <div class="flex items-center mb-4">
            <svg class="h-6 w-6 text-blue-600 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-xl font-bold text-blue-900">
                {{ $block['heading'] }}
            </h3>
        </div>
    @endif

    {{-- Resposta direta --}}
    @if(!empty($block['content']['answer']))
        <div class="mb-4">
            <p class="text-base text-gray-800 font-medium leading-relaxed">
                {{ $block['content']['answer'] }}
            </p>
        </div>
    @endif

    {{-- Pontos-chave (lista com checkmarks) --}}
    @if(!empty($block['content']['key_points']) && is_array($block['content']['key_points']))
        <div class="space-y-2 mt-4">
            @foreach($block['content']['key_points'] as $point)
                <div class="flex items-start">
                    <div class="flex-shrink-0 mt-0.5">
                        <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <p class="ml-3 text-sm text-gray-700 leading-relaxed">
                        {{ $point }}
                    </p>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Indicator visual de TL;DR --}}
    <div class="mt-4 pt-4 border-t border-blue-200">
        <p class="text-xs text-blue-600 font-semibold uppercase tracking-wide">
            📌 Resposta Direta • Continue lendo para detalhes
        </p>
    </div>
</div>