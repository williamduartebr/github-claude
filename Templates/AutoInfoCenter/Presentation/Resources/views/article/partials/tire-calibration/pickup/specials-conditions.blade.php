@php
$specialConditions = $article->getData()['special_conditions'] ?? $contentData['condicoes_especiais'] ?? [];
@endphp

@if(!empty($specialConditions))
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b-2 border-[#0E368A]/30">
        ⚖️ Condições Especiais para Pickups
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($specialConditions as $condition)
        @php
        $conditionName = $condition['condition'] ?? $condition['condicao'] ?? '';
        $cardClass = 'from-blue-50 to-blue-100 border-blue-200';
        $iconClass = 'bg-blue-500';

        if(str_contains(strtolower($conditionName), 'off-road') || str_contains(strtolower($conditionName),
        'off')) {
        $cardClass = 'from-green-50 to-green-100 border-green-200';
        $iconClass = 'bg-green-500';
        } elseif(str_contains(strtolower($conditionName), 'carga') ||
        str_contains(strtolower($conditionName), 'reboque')) {
        $cardClass = 'from-red-50 to-red-100 border-red-200';
        $iconClass = 'bg-red-500';
        } elseif(str_contains(strtolower($conditionName), 'viagem') ||
        str_contains(strtolower($conditionName), 'rodovia')) {
        $cardClass = 'from-purple-50 to-purple-100 border-purple-200';
        $iconClass = 'bg-purple-500';
        }
        @endphp

        <div
            class="bg-gradient-to-br {{ $cardClass }} border rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
                <div class="h-10 w-10 {{ $iconClass }} rounded-full flex items-center justify-center mr-3">
                    <span class="text-white text-sm">⚙️</span>
                </div>
                <h3 class="font-semibold text-gray-800">{{ $conditionName }}</h3>
            </div>

            <div class="bg-white/70 rounded-lg p-4 mb-4">
                <div class="text-center">
                    <div class="text-xs text-gray-600 mb-1">Ajuste recomendado: consulte o manual</div>
                    <div class="text-lg font-bold text-gray-800">
                        {{ $condition['recommended_adjustment'] ?? $condition['ajuste_recomendado'] ?? '' }}
                    </div>
                </div>
            </div>

            @if(!empty($condition['application']) || !empty($condition['aplicacao']))
            <div class="mb-3">
                <h4 class="text-sm font-semibold text-gray-700 mb-1">Aplicação:</h4>
                <p class="text-sm text-gray-600">{{ $condition['application'] ?? $condition['aplicacao'] }}
                </p>
            </div>
            @endif

            @if(!empty($condition['justification']) || !empty($condition['justificativa']))
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-1">Justificativa:</h4>
                <p class="text-sm text-gray-600">{{ $condition['justification'] ??
                    $condition['justificativa'] }}</p>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</section>
@endif