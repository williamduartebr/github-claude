{{--
Componente: TIMELINE (Linha do Tempo)

Uso: Marcos temporais, intervalos de manutenção

Estrutura esperada:
{
  "block_type": "timeline",
  "heading": "Intervalos Recomendados",
  "content": {
    "events": [
      {
        "milestone": "30.000 km",
        "action": "Primeira troca",
        "description": "Descrição do que fazer (opcional)"
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

    {{-- Timeline --}}
    @if(!empty($block['content']['events']) && is_array($block['content']['events']))
        <div class="relative">
            {{-- Linha vertical --}}
            <div class="absolute left-5 top-0 bottom-0 w-0.5 bg-gradient-to-b from-blue-500 via-blue-400 to-blue-300"></div>

            {{-- Events --}}
            <div class="space-y-8">
                @foreach($block['content']['events'] as $event)
                    <div class="relative flex items-start">
                        {{-- Ponto na linha --}}
                        <div class="absolute left-0 flex items-center justify-center">
                            <div class="w-10 h-10 rounded-full bg-blue-500 border-4 border-white shadow-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>

                        {{-- Conteúdo --}}
                        <div class="ml-16 bg-white border border-gray-200 rounded-lg shadow-sm p-5 w-full hover:shadow-md transition-shadow">
                            {{-- Milestone --}}
                            @if(!empty($event['milestone']))
                                <div class="mb-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                                        📍 {{ $event['milestone'] }}
                                    </span>
                                </div>
                            @endif

                            {{-- Action --}}
                            @if(!empty($event['action']))
                                <h3 class="text-lg font-semibold text-[#151C25] mb-2">
                                    {{ $event['action'] }}
                                </h3>
                            @endif

                            {{-- Description --}}
                            @if(!empty($event['description']))
                                <p class="text-gray-700 leading-relaxed">
                                    {{ $event['description'] }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Ponto final --}}
            <div class="relative flex items-start mt-8">
                <div class="absolute left-0 flex items-center justify-center">
                    <div class="w-10 h-10 rounded-full bg-green-500 border-4 border-white shadow-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
                <div class="ml-16">
                    <p class="text-sm text-gray-600 italic">
                        Continue seguindo o cronograma de manutenção recomendado
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>