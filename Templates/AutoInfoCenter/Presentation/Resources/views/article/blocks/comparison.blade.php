{{--
Componente: COMPARISON (Comparação)

Uso: Comparar 2+ opções com pros/cons

Estrutura esperada:
{
  "block_type": "comparison",
  "heading": "Título da Comparação",
  "content": {
    "items": [
      {
        "label": "Opção A",
        "pros": ["Vantagem 1", "Vantagem 2"],
        "cons": ["Desvantagem 1"],
        "conclusion": "Conclusão sobre A"
      }
    ]
  }
}

@author Claude Sonnet 4
@version 1.0
--}}

<div class="mb-8">
    {{-- Heading --}}
    @if(!empty($block['heading']))
        <h2 class="text-2xl font-semibold text-[#151C25] mb-6">
            {{ $block['heading'] }}
        </h2>
    @endif

    {{-- Grid de comparação --}}
    @if(!empty($block['content']['items']) && is_array($block['content']['items']))
        <div class="grid grid-cols-1 md:grid-cols-{{ count($block['content']['items']) > 2 ? '3' : '2' }} gap-6">
            @foreach($block['content']['items'] as $item)
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow">
                    {{-- Label --}}
                    <h3 class="text-lg font-bold text-[#151C25] mb-4 pb-2 border-b-2 border-blue-500">
                        {{ $item['label'] ?? 'Opção' }}
                    </h3>

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
    @endif
</div>