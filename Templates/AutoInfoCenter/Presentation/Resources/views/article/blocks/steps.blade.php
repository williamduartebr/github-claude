{{--
Componente: STEPS (Passo a Passo)

Uso: Tutorial sequencial numerado

Estrutura esperada:
{
  "block_type": "steps",
  "heading": "Como Fazer",
  "content": {
    "steps": [
      {
        "number": 1,
        "title": "Título do Passo",
        "description": "Descrição",
        "details": ["Detalhe A", "Detalhe B"],
        "tip": "Dica importante (opcional)"
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

    {{-- Steps --}}
    @if(!empty($block['content']['steps']) && is_array($block['content']['steps']))
        <div class="space-y-6">
            @foreach($block['content']['steps'] as $step)
                <div class="flex">
                    {{-- Número do passo --}}
                    <div class="flex-shrink-0 mr-4">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-500 text-white font-bold text-lg shadow-md">
                            {{ $step['number'] ?? $loop->iteration }}
                        </div>
                        
                        {{-- Linha conectora (exceto no último) --}}
                        @if(!$loop->last)
                            <div class="w-0.5 h-full bg-blue-200 ml-5 mt-2"></div>
                        @endif
                    </div>

                    {{-- Conteúdo do passo --}}
                    <div class="flex-1 pb-6">
                        {{-- Título --}}
                        @if(!empty($step['title']))
                            <h3 class="text-lg font-semibold text-[#151C25] mb-2">
                                {{ $step['title'] }}
                            </h3>
                        @endif

                        {{-- Descrição --}}
                        @if(!empty($step['description']))
                            <p class="text-gray-700 leading-relaxed mb-3">
                                {{ $step['description'] }}
                            </p>
                        @endif

                        {{-- Detalhes (sub-itens) --}}
                        @if(!empty($step['details']) && is_array($step['details']))
                            <ul class="space-y-1 ml-4 mb-3">
                                @foreach($step['details'] as $detail)
                                    <li class="text-sm text-gray-600 flex items-start">
                                        <span class="text-blue-500 mr-2 mt-0.5">▸</span>
                                        <span>{{ $detail }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        {{-- Dica/Tip --}}
                        @if(!empty($step['tip']))
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mt-3">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </div>
                                    <p class="ml-3 text-sm text-yellow-800">
                                        <strong>Dica:</strong> {{ $step['tip'] }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>