{{--
Componente: COMPARISON (Comparação)

Uso: Comparar 2+ opções com diferentes formatos

Estrutura esperada (DUAL - suporta 2 formatos):

FORMATO 1: Comparação Aspecto vs Aspecto (Lubrax JSON)
{
  "block_type": "comparison",
  "heading": "Lubrax 5W30 vs. Outros Óleos",
  "content": {
    "intro": "Comparamos o desempenho...",
    "items": [
      {
        "aspect": "Economia de combustível",
        "option_a": "Lubrax 5W30: 7,2%",
        "option_b": "Óleos originais: 0%"
      }
    ],
    "conclusion": "Conclusão geral"
  }
}

FORMATO 2: Prós e Contras (Velas JSON)
{
  "block_type": "comparison",
  "heading": "Vela Dupla Iridium vs Comum",
  "content": {
    "intro": "Diferenças fundamentais...",
    "items": [
      {
        "title": "Vela Dupla Iridium",
        "features": ["Feature 1", "Feature 2"],
        "pros": ["Vantagem 1"],
        "cons": ["Desvantagem 1"],
        "conclusion": "Conclusão"
      }
    ]
  }
}

@author Claude Sonnet 4.5
@version 2.0 - Suporta múltiplos formatos de comparação
--}}


@if(!empty($block['heading']))
    <hr class="my-12 border-t border-gray-200" />
@endif


@dump('comparison')

<div class="mb-8">
    {{-- Heading --}}
    @if(!empty($block['heading']))
        <h2 class="text-2xl font-semibold text-[#151C25] mb-6">
            {{ $block['heading'] }}
        </h2>
    @endif

    {{-- Intro --}}
    @if(!empty($block['content']['intro']))
        <p class="text-gray-700 leading-relaxed mb-6">
            {{ $block['content']['intro'] }}
        </p>
    @endif

    {{-- Items --}}
    @if(!empty($block['content']['items']) && is_array($block['content']['items']))
        @php
            $items = $block['content']['items'];
            $firstItem = $items[0] ?? [];
            
            // Detectar formato: aspecto-vs-aspecto OU prós-contras
            $isAspectComparison = !empty($firstItem['aspect']) && (!empty($firstItem['option_a']) || !empty($firstItem['option_b']));
            $isProsConsComparison = !empty($firstItem['title']) && (isset($firstItem['pros']) || isset($firstItem['cons']) || isset($firstItem['features']));
        @endphp

        @if($isAspectComparison)
            {{-- FORMATO 1: Comparação Aspecto vs Aspecto (Tabela vertical) --}}
            <div class="space-y-4">
                @foreach($items as $item)
                    <div class="bg-white border-2 border-gray-200 rounded-lg p-5 hover:border-blue-400 transition-colors shadow-sm">
                        {{-- Aspecto --}}
                        @if(!empty($item['aspect']))
                            <h3 class="text-base font-bold text-[#151C25] mb-3 pb-2 border-b border-gray-200">
                                {{ $item['aspect'] }}
                            </h3>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Opção A --}}
                            @if(!empty($item['option_a']))
                                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                    <div class="flex items-start">
                                        <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <p class="text-sm text-gray-800 leading-relaxed">
                                            {{ $item['option_a'] }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            {{-- Opção B --}}
                            @if(!empty($item['option_b']))
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-start">
                                        <svg class="h-5 w-5 text-gray-500 mr-2 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                        </svg>
                                        <p class="text-sm text-gray-800 leading-relaxed">
                                            {{ $item['option_b'] }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

        @elseif($isProsConsComparison)
            {{-- FORMATO 2: Prós e Contras (Grid de cards) --}}
            <div class="grid grid-cols-1 md:grid-cols-{{ count($items) > 2 ? '3' : '2' }} gap-6">
                @foreach($items as $item)
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow">
                        {{-- Título --}}
                        <h3 class="text-lg font-bold text-[#151C25] mb-4 pb-2 border-b-2 border-blue-500">
                            {{ $item['title'] ?? 'Opção' }}
                        </h3>

                        {{-- Features (se houver) --}}
                        @if(!empty($item['features']) && is_array($item['features']))
                            <div class="mb-4">
                                <h4 class="text-sm font-semibold text-blue-700 mb-2">Características:</h4>
                                <ul class="space-y-1">
                                    @foreach($item['features'] as $feature)
                                        <li class="text-sm text-gray-700 flex items-start">
                                            <span class="text-blue-500 mr-2">•</span>
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Pros --}}
                        @if(!empty($item['pros']) && is_array($item['pros']))
                            <div class="mb-4">
                                <h4 class="text-sm font-semibold text-green-700 mb-2 flex items-center">
                                    <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Vantagens
                                </h4>
                                <ul class="space-y-1">
                                    @foreach($item['pros'] as $pro)
                                        <li class="text-sm text-gray-700 flex items-start">
                                            <span class="text-green-500 mr-2">✓</span>
                                            <span>{{ $pro }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Cons --}}
                        @if(!empty($item['cons']) && is_array($item['cons']))
                            <div class="mb-4">
                                <h4 class="text-sm font-semibold text-red-700 mb-2 flex items-center">
                                    <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    Desvantagens
                                </h4>
                                <ul class="space-y-1">
                                    @foreach($item['cons'] as $con)
                                        <li class="text-sm text-gray-700 flex items-start">
                                            <span class="text-red-500 mr-2">✗</span>
                                            <span>{{ $con }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Conclusion --}}
                        @if(!empty($item['conclusion']))
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <p class="text-sm text-gray-600 italic">
                                    <strong>Conclusão:</strong> {{ $item['conclusion'] }}
                                </p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

        @else
            {{-- Fallback: Renderização simples --}}
            <div class="space-y-4">
                @foreach($items as $item)
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <pre class="text-xs text-gray-600">{{ json_encode($item, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    {{-- Conclusion --}}
    @if(!empty($block['content']['conclusion']))
        <div class="mt-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg">
            <p class="text-gray-800 leading-relaxed">
                <strong>Conclusão:</strong> {{ $block['content']['conclusion'] }}
            </p>
        </div>
    @endif
</div>