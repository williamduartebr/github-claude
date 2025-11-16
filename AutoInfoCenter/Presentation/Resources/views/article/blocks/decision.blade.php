{{--
Componente: DECISION (Matriz de Decisão)

Uso: Árvore de decisão - quando fazer o quê

Estrutura esperada (baseada nos JSONs reais):
{
  "block_type": "decision",
  "heading": "Quando Vale a Pena Investir?",
  "content": {
    "intro": "Nem sempre a opção mais cara é a melhor escolha...",
    "scenarios": [
      {
        "title": "Vale a pena para você se:",
        "points": [
          "Roda mais de 20.000km por ano",
          "Planeja manter o veículo por pelo menos 3 anos"
        ]
      },
      {
        "title": "Pode não compensar se:",
        "points": [
          "Roda menos de 10.000km por ano",
          "Pretende vender o veículo em breve"
        ]
      }
    ],
    "conclusion": "O maior benefício é observado em veículos..."
  }
}

ESTRUTURA ALTERNATIVA (não usada nos JSONs, mas suportada):
{
  "scenarios": [
    {
      "condition": "Se X acontecer",
      "action": "Faça Y",
      "urgency": "low | medium | high | critical",
      "reason": "Por que fazer isso"
    }
  ]
}

@author Claude Sonnet 4.5
@version 2.0 - Refatorado para JSONs reais com suporte dual
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

    {{-- Intro --}}
    @if(!empty($block['content']['intro']))
        <p class="text-gray-700 leading-relaxed mb-6">
            {{ $block['content']['intro'] }}
        </p>
    @endif

    {{-- Scenarios --}}
    @if(!empty($block['content']['scenarios']) && is_array($block['content']['scenarios']))
        @php
            $scenarios = $block['content']['scenarios'];
            $firstScenario = $scenarios[0] ?? [];
            
            // Detectar formato: title+points OU condition+action+urgency
            $isTitlePoints = !empty($firstScenario['title']) && !empty($firstScenario['points']);
            $isConditionAction = !empty($firstScenario['condition']) && !empty($firstScenario['action']);
        @endphp

        @if($isTitlePoints)
            {{-- FORMATO 1: Title + Points (Formato usado nos JSONs reais) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($scenarios as $scenario)
                    @php
                    // Detectar se é "vale a pena" (positivo) ou "não compensa" (negativo)
                    $title_lower = \Str::lower($scenario['title'] ?? '');
                    
                    // PALAVRAS POSITIVAS (verde com checkmark)
                    $positive_keywords = [
                        'escolha',
                        'recomendado',
                        'ideal',
                        'vale a pena',
                        'melhor para',
                        'adequado',
                        'indicado',
                        'perfeito',
                        'ótimo',
                        'excelente',
                        'compensa',
                        'beneficia',
                        'vantajoso'
                    ];
                    
                    // PALAVRAS NEGATIVAS (vermelho com X)
                    $negative_keywords = [
                        'não compensa',
                        'menos prioritário',
                        'evite',
                        'não recomendado',
                        'inadequado',
                        'desaconselhado',
                        'cuidado',
                        'atenção',
                        'não vale',
                        'não escolha',
                        'não use',
                        'contraindicado',
                        'nunca use'
                    ];
                    
                    // Verificar primeiro as negativas (prioridade)
                    $isNegative = false;
                    foreach ($negative_keywords as $keyword) {
                        if (str_contains($title_lower, $keyword)) {
                            $isNegative = true;
                            break;
                        }
                    }
                    
                    // Se não for negativo, verificar se é positivo
                    $isPositive = false;
                    if (!$isNegative) {
                        foreach ($positive_keywords as $keyword) {
                            if (str_contains($title_lower, $keyword)) {
                                $isPositive = true;
                                break;
                            }
                        }
                    }
                    
                    // Se não encontrou nenhuma palavra-chave, assumir POSITIVO por padrão
                    // (já que a maioria dos cenários são recomendações)
                    if (!$isNegative && !$isPositive) {
                        $isPositive = true;
                    }
                    
                    $config = $isPositive ? [
                        'bg' => 'bg-green-50',
                        'border' => 'border-green-500',
                        'icon_color' => 'text-green-600',
                        'icon' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />'
                    ] : [
                        'bg' => 'bg-red-50',
                        'border' => 'border-red-500',
                        'icon_color' => 'text-red-600',
                        'icon' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />'
                    ];
                @endphp

                    <div class="{{ $config['bg'] }} border-l-4 {{ $config['border'] }} rounded-r-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
                        {{-- Título com ícone --}}
                        @if(!empty($scenario['title']))
                            <div class="flex items-start mb-4">
                                <div class="flex-shrink-0 mr-3 mt-1">
                                    <svg class="h-6 w-6 {{ $config['icon_color'] }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        {!! $config['icon'] !!}
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">
                                    {{ $scenario['title'] }}
                                </h3>
                            </div>
                        @endif

                        {{-- Points (lista de critérios) --}}
                        @if(!empty($scenario['points']) && is_array($scenario['points']))
                            <ul class="space-y-3">
                                @foreach($scenario['points'] as $point)
                                    <li class="flex items-start">
                                        <span class="flex-shrink-0 w-1.5 h-1.5 {{ $isPositive ? 'bg-green-500' : 'bg-red-500' }} rounded-full mr-3 mt-2"></span>
                                        <span class="text-sm text-gray-800 leading-relaxed">{{ $point }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>

        @elseif($isConditionAction)
            {{-- FORMATO 2: Condition + Action + Urgency (Suporte alternativo) --}}
            <div class="space-y-4">
                @foreach($scenarios as $scenario)
                    @php
                        $urgency = $scenario['urgency'] ?? 'medium';
                        
                        $urgencyConfig = [
                            'low' => [
                                'bg' => 'bg-blue-50',
                                'border' => 'border-blue-400',
                                'badge_bg' => 'bg-blue-100',
                                'badge_text' => 'text-blue-800',
                                'icon_color' => 'text-blue-500',
                                'icon' => 'ℹ️'
                            ],
                            'medium' => [
                                'bg' => 'bg-yellow-50',
                                'border' => 'border-yellow-400',
                                'badge_bg' => 'bg-yellow-100',
                                'badge_text' => 'text-yellow-800',
                                'icon_color' => 'text-yellow-500',
                                'icon' => '⏰'
                            ],
                            'high' => [
                                'bg' => 'bg-orange-50',
                                'border' => 'border-orange-400',
                                'badge_bg' => 'bg-orange-100',
                                'badge_text' => 'text-orange-800',
                                'icon_color' => 'text-orange-500',
                                'icon' => '⚠️'
                            ],
                            'critical' => [
                                'bg' => 'bg-red-50',
                                'border' => 'border-red-500',
                                'badge_bg' => 'bg-red-100',
                                'badge_text' => 'text-red-800',
                                'icon_color' => 'text-red-500',
                                'icon' => '🚨'
                            ]
                        ];
                        
                        $config = $urgencyConfig[$urgency] ?? $urgencyConfig['medium'];
                    @endphp

                    <div class="{{ $config['bg'] }} border-l-4 {{ $config['border'] }} rounded-r-lg p-5 shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-start gap-4">
                            {{-- Condição + Urgência --}}
                            <div class="md:w-2/5">
                                @if(!empty($scenario['urgency_label']))
                                    <div class="mb-3">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $config['badge_bg'] }} {{ $config['badge_text'] }}">
                                            {{ $config['icon'] }} {{ strtoupper($scenario['urgency_label']) }}
                                        </span>
                                    </div>
                                @endif

                                @if(!empty($scenario['condition']))
                                    <div>
                                        <p class="text-xs text-gray-600 font-semibold uppercase tracking-wide mb-1">Condição:</p>
                                        <p class="text-gray-900 font-semibold leading-relaxed">{{ $scenario['condition'] }}</p>
                                    </div>
                                @endif
                            </div>

                            {{-- Ação + Razão --}}
                            <div class="md:w-3/5">
                                @if(!empty($scenario['action']))
                                    <div class="mb-3">
                                        <div class="flex items-start">
                                            <svg class="h-5 w-5 {{ $config['icon_color'] }} mr-2 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd" />
                                            </svg>
                                            <div>
                                                <p class="text-xs text-gray-600 font-semibold uppercase tracking-wide mb-1">Ação Recomendada:</p>
                                                <p class="text-gray-900 font-medium leading-relaxed">{{ $scenario['action'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

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
    @endif

    {{-- Conclusion --}}
    @if(!empty($block['content']['conclusion']))
        <div class="mt-6 p-5 bg-gradient-to-r from-blue-50 to-blue-100 border-l-4 border-blue-500 rounded-r-lg shadow-sm">
            <div class="flex items-start">
                <svg class="h-6 w-6 text-blue-600 mr-3 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <div>
                    <p class="text-xs text-blue-700 font-bold uppercase tracking-wide mb-1">💡 Conclusão</p>
                    <p class="text-gray-800 leading-relaxed">{{ $block['content']['conclusion'] }}</p>
                </div>
            </div>
        </div>
    @endif
</div>