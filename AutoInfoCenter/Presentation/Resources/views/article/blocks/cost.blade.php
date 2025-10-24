{{--
Componente: COST (Análise de Custo)

Uso: Comparação financeira com 2 formatos distintos

FORMATO 1: Análise Detalhada (Lubrax)
cost_items = [{item, cost, notes}]
savings = [{description, amount, calculation}]

FORMATO 2: Scenarios Comparativos (Velas)
cost_items = [{scenario, items: [strings]}]
savings = string única

@author Claude Sonnet 4.5
@version 2.1 - Suporta ambos os formatos reais dos JSONs
--}}

@if(!empty($block['heading']))
    <hr class="my-8 border-t border-gray-200" />
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

    @php
        $costItems = $block['content']['cost_items'] ?? [];
        $firstItem = $costItems[0] ?? [];
        
        // Detectar formato baseado na estrutura do primeiro item
        $isDetailedFormat = isset($firstItem['item']) && isset($firstItem['cost']); // Lubrax
        $isScenarioFormat = isset($firstItem['scenario']) && isset($firstItem['items']); // Velas
    @endphp

    @if($isDetailedFormat)
        {{-- ============================================
            FORMATO 1: ANÁLISE DETALHADA (Lubrax)
        ============================================ --}}
        
        {{-- Itens de Custo --}}
        @if(!empty($costItems))
            <div class="bg-red-50 border-l-4 border-red-500 rounded-r-lg p-5 mb-6">
                <h3 class="text-lg font-bold text-red-800 mb-4 flex items-center">
                    <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                    </svg>
                    Investimento Necessário
                </h3>
                <div class="space-y-3">
                    @foreach($costItems as $costItem)
                        <div class="flex justify-between items-start bg-white rounded-lg p-3 border border-red-200">
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $costItem['item'] }}
                                </p>
                                @if(!empty($costItem['notes']))
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $costItem['notes'] }}
                                    </p>
                                @endif
                            </div>
                            <div class="text-right ml-4">
                                <p class="text-lg font-bold text-red-600">
                                    {{ $costItem['cost'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                    
                    @if(!empty($block['content']['total_investment']))
                        <div class="pt-3 border-t-2 border-red-300">
                            <div class="flex justify-between items-center">
                                <p class="text-base font-bold text-red-900">Total Investido:</p>
                                <p class="text-xl font-bold text-red-700">{{ $block['content']['total_investment'] }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Economias (Array) --}}
        @if(!empty($block['content']['savings']) && is_array($block['content']['savings']))
            <div class="bg-green-50 border-l-4 border-green-500 rounded-r-lg p-5 mb-6">
                <h3 class="text-lg font-bold text-green-800 mb-4 flex items-center">
                    <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Retorno do Investimento
                </h3>
                <div class="space-y-3">
                    @foreach($block['content']['savings'] as $saving)
                        <div class="bg-white rounded-lg p-3 border border-green-200">
                            <div class="flex justify-between items-start mb-2">
                                <p class="text-sm font-semibold text-gray-800 flex-1">
                                    {{ $saving['description'] }}
                                </p>
                                <p class="text-lg font-bold text-green-600 ml-4">
                                    {{ $saving['amount'] }}
                                </p>
                            </div>
                            @if(!empty($saving['calculation']))
                                <p class="text-xs text-gray-600 italic">
                                    {{ $saving['calculation'] }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                    
                    @if(!empty($block['content']['total_savings']))
                        <div class="pt-3 border-t-2 border-green-300">
                            <div class="flex justify-between items-center">
                                <p class="text-base font-bold text-green-900">Economia Total:</p>
                                <p class="text-xl font-bold text-green-700">{{ $block['content']['total_savings'] }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Métricas Financeiras --}}
        @if(!empty($block['content']['roi']) || !empty($block['content']['payback_period']) || !empty($block['content']['break_even']))
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                @if(!empty($block['content']['roi']))
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border-2 border-blue-300 text-center">
                        <p class="text-xs text-blue-700 font-semibold uppercase tracking-wide mb-1">ROI</p>
                        <p class="text-xl font-bold text-blue-900">{{ $block['content']['roi'] }}</p>
                    </div>
                @endif
                
                @if(!empty($block['content']['payback_period']))
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border-2 border-purple-300 text-center">
                        <p class="text-xs text-purple-700 font-semibold uppercase tracking-wide mb-1">Payback</p>
                        <p class="text-xl font-bold text-purple-900">{{ $block['content']['payback_period'] }}</p>
                    </div>
                @endif
                
                @if(!empty($block['content']['break_even']))
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border-2 border-orange-300 text-center">
                        <p class="text-xs text-orange-700 font-semibold uppercase tracking-wide mb-1">Break-Even</p>
                        <p class="text-xl font-bold text-orange-900">{{ $block['content']['break_even'] }}</p>
                    </div>
                @endif
            </div>
        @endif

    @elseif($isScenarioFormat)
        {{-- ============================================
            FORMATO 2: SCENARIOS COMPARATIVOS (Velas)
        ============================================ --}}
        
        <div class="space-y-6">
            @foreach($costItems as $costItem)
                <div class="bg-white border-2 border-gray-200 rounded-lg p-6 hover:border-blue-400 transition-colors shadow-sm">
                    {{-- Título do Scenario --}}
                    @if(!empty($costItem['scenario']))
                        <h3 class="text-lg font-bold text-[#151C25] mb-4 pb-2 border-b-2 border-blue-500">
                            {{ $costItem['scenario'] }}
                        </h3>
                    @endif

                    {{-- Lista de Items (strings) --}}
                    @if(!empty($costItem['items']) && is_array($costItem['items']))
                        <div class="space-y-2">
                            @foreach($costItem['items'] as $item)
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    <p class="text-sm text-gray-700 leading-relaxed">
                                        {{ $item }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Savings e Break-Even (strings únicas) --}}
        @if(!empty($block['content']['savings']) || !empty($block['content']['break_even']))
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                @if(!empty($block['content']['savings']) && is_string($block['content']['savings']))
                    <div class="bg-green-50 rounded-lg p-4 border-2 border-green-300">
                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-green-600 mr-2 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <p class="text-xs text-green-700 font-semibold uppercase tracking-wide mb-1">💰 Economia</p>
                                <p class="text-sm text-gray-800 font-medium">{{ $block['content']['savings'] }}</p>
                            </div>
                        </div>
                    </div>
                @endif
                
                @if(!empty($block['content']['break_even']) && is_string($block['content']['break_even']))
                    <div class="bg-blue-50 rounded-lg p-4 border-2 border-blue-300">
                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-blue-600 mr-2 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <p class="text-xs text-blue-700 font-semibold uppercase tracking-wide mb-1">⚖️ Break-Even</p>
                                <p class="text-sm text-gray-800 font-medium">{{ $block['content']['break_even'] }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    @endif

    {{-- Conclusion (comum para ambos formatos) --}}
    @if(!empty($block['content']['conclusion']))
        <div class="mt-6 p-5 bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 rounded-r-lg shadow-sm">
            <div class="flex items-start">
                <svg class="h-6 w-6 text-blue-600 mr-3 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <div>
                    <p class="text-xs text-blue-700 font-bold uppercase tracking-wide mb-1">💰 Conclusão Financeira</p>
                    <p class="text-gray-800 leading-relaxed">{{ $block['content']['conclusion'] }}</p>
                </div>
            </div>
        </div>
    @endif
</div>