{{--
Componente: TESTIMONIAL (Depoimentos/Casos Reais)

Uso: Experiências reais de usuários

Estrutura esperada:
{
  "block_type": "testimonial",
  "heading": "Experiências Reais",
  "content": {
    "cases": [
      {
        "user": "Nome - Carro",
        "situation": "Descrição do caso",
        "result": "Resultado obtido",
        "observation": "Observação adicional (opcional)"
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

    {{-- Cases --}}
    @if(!empty($block['content']['cases']) && is_array($block['content']['cases']))
        <div class="space-y-6">
            @foreach($block['content']['cases'] as $case)
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 border-l-4 border-blue-500 rounded-r-lg p-6 shadow-sm">
                    {{-- User info --}}
                    @if(!empty($case['user']))
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $case['user'] }}
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- Situation --}}
                    @if(!empty($case['situation']))
                        <div class="mb-3">
                            <p class="text-sm text-gray-600 font-medium mb-1">Situação:</p>
                            <p class="text-gray-800 leading-relaxed">
                                {{ $case['situation'] }}
                            </p>
                        </div>
                    @endif

                    {{-- Result --}}
                    @if(!empty($case['result']))
                        <div class="mb-3">
                            <p class="text-sm text-gray-600 font-medium mb-1">Resultado:</p>
                            <p class="text-gray-800 leading-relaxed font-medium">
                                {{ $case['result'] }}
                            </p>
                        </div>
                    @endif

                    {{-- Observation --}}
                    @if(!empty($case['observation']))
                        <div class="mt-4 pt-4 border-t border-gray-300">
                            <p class="text-sm text-gray-600 italic">
                                💬 {{ $case['observation'] }}
                            </p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>