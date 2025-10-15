{{--
Componente: COST (Análise de Custo)

Uso: Comparação financeira de opções

Estrutura esperada:
{
  "block_type": "cost",
  "heading": "Análise de Custo",
  "content": {
    "scenarios": [
      {
        "option": "Opção A",
        "cost": "R$ 200-400",
        "duration": "Duração/vida útil",
        "recommendation": "Quando vale a pena",
        "savings": "Economia estimada (opcional)"
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

    {{-- Scenarios --}}
    @if(!empty($block['content']['scenarios']) && is_array($block['content']['scenarios']))
        <div class="space-y-4">
            @foreach($block['content']['scenarios'] as $scenario)
                <div class="bg-white border-2 border-gray-200 rounded-lg p-6 hover:border-blue-400 transition-colors">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Coluna Esquerda --}}
                        <div>
                            {{-- Option --}}
                            @if(!empty($scenario['option']))
                                <h3 class="text-lg font-bold text-[#151C25] mb-3">
                                    {{ $scenario['option'] }}
                                </h3>
                            @endif

                            {{-- Cost --}}
                            @if(!empty($scenario['cost']))
                                <div class="mb-3">
                                    <p class="text-sm text-gray-600 font-medium">Custo:</p>
                                    <p class="text-2xl font-bold text-green-600">
                                        {{ $scenario['cost'] }}
                                    </p>
                                </div>
                            @endif

                            {{-- Duration --}}
                            @if(!empty($scenario['duration']))
                                <div class="mb-3">
                                    <p class="text-sm text-gray-600 font-medium">Duração:</p>
                                    <p class="text-gray-800">
                                        {{ $scenario['duration'] }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        {{-- Coluna Direita --}}
                        <div>
                            {{-- Recommendation --}}
                            @if(!empty($scenario['recommendation']))
                                <div class="mb-3">
                                    <p class="text-sm text-gray-600 font-medium mb-2">Recomendado para:</p>
                                    <p class="text-gray-800 leading-relaxed">
                                        {{ $scenario['recommendation'] }}
                                    </p>
                                </div>
                            @endif

                            {{-- Savings --}}
                            @if(!empty($scenario['savings']))
                                <div class="bg-green-50 border border-green-200 rounded-md p-3 mt-3">
                                    <div class="flex items-start">
                                        <svg class="h-5 w-5 text-green-600 mr-2 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <div>
                                            <p class="text-xs text-green-700 font-semibold mb-1">Economia:</p>
                                            <p class="text-sm text-green-800">
                                                {{ $scenario['savings'] }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>