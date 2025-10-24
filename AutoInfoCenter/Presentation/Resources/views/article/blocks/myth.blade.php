{{--
Componente: MYTH (Mito vs Realidade)

Uso: Desmistificar crenças comuns com evidências de testes reais

Estrutura esperada (baseada nos JSONs reais):
{
  "block_type": "myth",
  "heading": "Mitos e Verdades Sobre o Lubrax 5W30",
  "content": {
    "intro": "Durante nosso teste de 6 meses...",
    "myths": [
      {
        "myth": "Afirmação comum do público",
        "reality": "VERDADEIRO | FALSO | PARCIALMENTE VERDADEIRO | MITO",
        "explanation": "Explicação técnica detalhada",
        "evidence": "Evidências do teste realizado"
      }
    ]
  }
}

@author Claude Sonnet 4.5
@version 2.1 - Refatorado baseado em JSONs reais do sistema
--}}

@if(!empty($block['heading']))
    <hr class="my-12 border-t border-gray-200" />
@endif

<div class="mb-10">

    {{-- Heading --}}
    @if(!empty($block['heading']))
        <h2 class="text-2xl font-bold text-[#151C25] mb-4 flex items-center">
            <svg class="h-7 w-7 text-[#EC6608] mr-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{ $block['heading'] }}
        </h2>
    @endif

    {{-- Intro --}}
    @if(!empty($block['content']['intro']))
        <p class="text-gray-700 leading-relaxed mb-6 text-[15px]">
            {{ $block['content']['intro'] }}
        </p>
    @endif

    {{-- Lista de Myths --}}
    @if(!empty($block['content']['myths']) && is_array($block['content']['myths']))
        <div class="space-y-6">
            @foreach($block['content']['myths'] as $index => $item)
                @php
                    // Normalizar campo reality (aceita variações textuais)
                    $realityRaw = strtoupper(trim($item['reality'] ?? 'PARCIALMENTE VERDADEIRO'));
                    
                    // Determinar tipo baseado no texto
                    $reality = match(true) {
                        $realityRaw === 'VERDADEIRO' => 'true',
                        $realityRaw === 'VERDADE' => 'true',
                        str_contains($realityRaw, 'VERDADEIRO') && !str_contains($realityRaw, 'PARCIAL') => 'true',
                        $realityRaw === 'FALSO' => 'false',
                        $realityRaw === 'MITO' => 'false',
                        str_contains($realityRaw, 'FALSO') => 'false',
                        default => 'partial'
                    };
                    
                    // Configuração visual por tipo
                    $config = match($reality) {
                        'true' => [
                            'bg' => 'bg-green-50',
                            'border' => 'border-green-500',
                            'badge_bg' => 'bg-green-100',
                            'badge_text' => 'text-green-800',
                            'badge_border' => 'border-green-500',
                            'icon' => '✅',
                            'label' => 'Verdadeiro'
                        ],
                        'false' => [
                            'bg' => 'bg-red-50',
                            'border' => 'border-red-500',
                            'badge_bg' => 'bg-red-100',
                            'badge_text' => 'text-red-800',
                            'badge_border' => 'border-red-500',
                            'icon' => '❌',
                            'label' => 'Mito'
                        ],
                        default => [
                            'bg' => 'bg-yellow-50',
                            'border' => 'border-yellow-500',
                            'badge_bg' => 'bg-yellow-100',
                            'badge_text' => 'text-yellow-800',
                            'badge_border' => 'border-yellow-500',
                            'icon' => '⚠️',
                            'label' => 'Parcialmente Verdadeiro'
                        ]
                    };
                @endphp

                <div class="relative {{ $config['bg'] }} border-l-4 {{ $config['border'] }} rounded-r-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
                    
                    {{-- Badge de número --}}
                    <div class="absolute -left-3 top-6 w-8 h-8 bg-white {{ $config['border'] }} border-2 rounded-full flex items-center justify-center font-bold text-sm text-gray-700 shadow-sm">
                        {{ $index + 1 }}
                    </div>

                    {{-- Afirmação Popular --}}
                    <div class="mb-5">
                        <div class="flex items-start">
                            <span class="text-2xl mr-3 flex-shrink-0">💭</span>
                            <div class="flex-1">
                                <p class="text-xs text-gray-600 font-bold uppercase tracking-wide mb-2">
                                    Afirmação Popular:
                                </p>
                                <p class="text-gray-900 font-semibold leading-relaxed text-base">
                                    "{{ $item['myth'] }}"
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Badge de Veredicto --}}
                    <div class="mb-5 pb-5 border-b-2 {{ $config['border'] }}">
                        <div class="inline-flex items-center px-4 py-2 rounded-full {{ $config['badge_bg'] }} border-2 {{ $config['badge_border'] }} shadow-sm">
                            <span class="text-lg mr-2">{{ $config['icon'] }}</span>
                            <span class="text-sm font-bold {{ $config['badge_text'] }} uppercase tracking-wide">
                                {{ $config['label'] }}
                            </span>
                        </div>
                    </div>

                    {{-- Explicação --}}
                    <div class="mb-4">
                        <p class="text-xs text-gray-600 font-bold uppercase tracking-wide mb-2">
                            💡 A Verdade:
                        </p>
                        <p class="text-gray-800 leading-relaxed text-[15px]">
                            {{ $item['explanation'] }}
                        </p>
                    </div>

                    {{-- Evidências do Teste --}}
                    @if(!empty($item['evidence']))
                        <div class="mt-4 pt-4 border-t border-gray-300">
                            <div class="flex items-start">
                                <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <div class="flex-1">
                                    <p class="text-xs text-blue-700 font-bold uppercase tracking-wide mb-1">
                                        🔬 Evidências do Teste:
                                    </p>
                                    <p class="text-sm text-gray-700 leading-relaxed italic">
                                        {{ $item['evidence'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            @endforeach
        </div>
    @else
        {{-- Fallback caso não haja myths --}}
        <div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-8 text-center">
            <svg class="h-12 w-12 text-gray-400 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-gray-500 text-sm font-medium">
                Nenhum mito ou verdade disponível para exibição.
            </p>
        </div>
    @endif
</div>