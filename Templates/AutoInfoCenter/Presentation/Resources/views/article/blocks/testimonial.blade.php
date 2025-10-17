{{--
Componente: TESTIMONIAL (Depoimentos/Casos Reais)

Estrutura esperada (DUAL):

ESTRUTURA 1 - Depoimento simples:
{
  "block_type": "testimonial",
  "heading": "Experiência Real: O Caso do HB20",
  "content": {
    "quote": "Meu HB20 2018 sempre foi econômico...",
    "author": "Ricardo Mendes, 42 anos, São Paulo-SP",
    "vehicle": "Hyundai HB20 1.6 2018",
    "context": "Ricardo participou do nosso teste..."
  }
}

ESTRUTURA 2 - Múltiplos casos:
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

@author Claude Sonnet 4.5
@version 2.0
--}}

@if(!empty($block['heading']))
    <hr class="my-12 border-t border-gray-200" />
@endif

<div class="mb-8">
    {{-- Heading --}}
    @if(!empty($block['heading']))
        <h2 class="text-2xl font-semibold text-[#151C25] mb-6">
            {{ $block['heading'] }}
        </h2>
    @endif

    {{-- ESTRUTURA 1: Depoimento Simples (quote) --}}
    @if(!empty($block['content']['quote']))
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 border-l-4 border-blue-500 rounded-r-xl p-6 shadow-sm">
            {{-- Quote --}}
            <div class="mb-4">
                <svg class="h-8 w-8 text-blue-400 mb-2" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                </svg>
                <p class="text-gray-800 text-lg leading-relaxed italic">
                    "{{ $block['content']['quote'] }}"
                </p>
            </div>

            {{-- Author --}}
            @if(!empty($block['content']['author']))
                <div class="flex items-center mb-3">
                    <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center mr-3">
                        <svg class="w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">{{ $block['content']['author'] }}</p>
                        @if(!empty($block['content']['vehicle']))
                            <p class="text-sm text-gray-600">{{ $block['content']['vehicle'] }}</p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Context --}}
            @if(!empty($block['content']['context']))
                <div class="mt-4 pt-4 border-t border-blue-200">
                    <p class="text-sm text-gray-600 italic">
                        ℹ️ {{ $block['content']['context'] }}
                    </p>
                </div>
            @endif
        </div>

    {{-- ESTRUTURA 2: Múltiplos Casos --}}
    @elseif(!empty($block['content']['cases']) && is_array($block['content']['cases']))
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