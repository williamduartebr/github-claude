{{--
Componente: DECISION (Matriz de Decisão)

Uso: Árvore de decisão - quando fazer o quê

Estrutura esperada:
{
  "block_type": "decision",
  "heading": "Quando Fazer Cada Coisa",
  "content": {
    "scenarios": [
      {
        "condition": "Se X acontecer",
        "action": "Faça Y",
        "urgency": "low | medium | high | critical",
        "urgency_label": "Baixa | Média | Alta | Crítica",
        "reason": "Por que fazer isso"
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
                @php
                    $urgency = $scenario['urgency'] ?? 'medium';
                    
                    // Configuração de cores por urgência
                    $urgencyConfig = [
                        'low' => [
                            'bg' => 'bg-blue-50',
                            'border' => 'border-blue-400',
                            'badge_bg' => 'bg-blue-100',
                            'badge_text' => 'text-blue-800',
                            'icon_color' => 'text-blue-500'
                        ],
                        'medium' => [
                            'bg' => 'bg-yellow-50',
                            'border' => 'border-yellow-400',
                            'badge_bg' => 'bg-yellow-100',
                            'badge_text' => 'text-yellow-800',
                            'icon_color' => 'text-yellow-500'
                        ],
                        'high' => [
                            'bg' => 'bg-orange-50',
                            'border' => 'border-orange-400',
                            'badge_bg' => 'bg-orange-100',
                            'badge_text' => 'text-orange-800',
                            'icon_color' => 'text-orange-500'
                        ],
                        'critical' => [
                            'bg' => 'bg-red-50',
                            'border' => 'border-red-500',
                            'badge_bg' => 'bg-red-100',
                            'badge_text' => 'text-red-800',
                            'icon_color' => 'text-red-500'
                        ]
                    ];
                    
                    $config = $urgencyConfig[$urgency] ?? $urgencyConfig['medium'];
                @endphp

                <div class="{{ $config['bg'] }} border-l-4 {{ $config['border'] }} rounded-r-lg p-5 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-start gap-4">
                        {{-- Coluna Esquerda: Condição + Urgência --}}
                        <div class="md:w-2/5">
                            {{-- Urgency Badge --}}
                            @if(!empty($scenario['urgency_label']))
                                <div class="mb-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $config['badge_bg'] }} {{ $config['badge_text'] }}">
                                        @if($urgency === 'critical')
                                            🚨
                                        @elseif($urgency === 'high')
                                            ⚠️
                                        @elseif($urgency === 'medium')
                                            ⏰
                                        @else
                                            ℹ️
                                        @endif
                                        {{ strtoupper($scenario['urgency_label']) }}
                                    </span>
                                </div>
                            @endif

                            {{-- Condition --}}
                            @if(!empty($scenario['condition']))
                                <div>
                                    <p class="text-xs text-gray-600 font-semibold uppercase tracking-wide mb-1">
                                        Condição:
                                    </p>
                                    <p class="text-gray-900 font-semibold leading-relaxed">
                                        {{ $scenario['condition'] }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        {{-- Coluna Direita: Ação + Razão --}}
                        <div class="md:w-3/5">
                            {{-- Action --}}
                            @if(!empty($scenario['action']))
                                <div class="mb-3">
                                    <div class="flex items-start">
                                        <svg class="h-5 w-5 {{ $config['icon_color'] }} mr-2 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd" />
                                        </svg>
                                        <div>
                                            <p class="text-xs text-gray-600 font-semibold uppercase tracking-wide mb-1">
                                                Ação Recomendada:
                                            </p>
                                            <p class="text-gray-900 font-medium leading-relaxed">
                                                {{ $scenario['action'] }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Reason --}}
                            @if(!empty($scenario['reason']))
                                <div class="mt-3 pt-3 border-t {{ $config['border'] }}">
                                    <p class="text-sm text-gray-700 leading-relaxed">
                                        <strong>Por quê:</strong> {{ $scenario['reason'] }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>