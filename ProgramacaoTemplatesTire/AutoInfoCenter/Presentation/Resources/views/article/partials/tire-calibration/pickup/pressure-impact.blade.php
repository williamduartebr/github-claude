@php
$pressureImpact = $article->getData()['pressure_impact'] ?? $contentData['impacto_pressao'] ?? [];
@endphp

@if(!empty($pressureImpact) || !empty($contentData['impacto_pressao']))
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b-2 border-[#0E368A]/30">
        📊 Impacto da Pressão no Desempenho
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @php $impacts = $pressureImpact ?? $contentData['impacto_pressao'] ?? []; @endphp
        @foreach($impacts as $key => $impact)
        @php
        $cardClasses = match($key) {
        'subcalibrado' => 'from-red-50 to-red-100 border-red-200',
        'ideal', 'correto' => 'from-green-50 to-green-100 border-green-200',
        'sobrecalibrado' => 'from-amber-50 to-amber-100 border-amber-200',
        default => 'from-gray-50 to-gray-100 border-gray-200'
        };

        $iconClasses = match($key) {
        'subcalibrado' => 'bg-red-500',
        'ideal', 'correto' => 'bg-green-500',
        'sobrecalibrado' => 'bg-amber-500',
        default => 'bg-gray-500'
        };

        $icons = match($key) {
        'subcalibrado' => '⬇️',
        'ideal', 'correto' => '✅',
        'sobrecalibrado' => '⬆️',
        default => '⚖️'
        };
        @endphp

        <div class="bg-gradient-to-br {{ $cardClasses }} border rounded-xl p-6 shadow-sm">
            <div class="flex items-center mb-4">
                <div class="h-10 w-10 {{ $iconClasses }} rounded-full flex items-center justify-center mr-3">
                    <span class="text-white">{{ $icons }}</span>
                </div>
                <h3 class="font-semibold text-gray-800">
                    {{ $impact['titulo'] ?? $impact['title'] ?? ucfirst($key) }}
                </h3>
            </div>

            <div class="space-y-2">
                @if(!empty($impact['problemas']) && is_array($impact['problemas']))
                @foreach($impact['problemas'] as $problema)
                <div class="flex items-start">
                    <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2 flex-shrink-0"></span>
                    <p class="text-sm text-gray-700">{{ $problema }}</p>
                </div>
                @endforeach
                @elseif(!empty($impact['beneficios']) && is_array($impact['beneficios']))
                @foreach($impact['beneficios'] as $beneficio)
                <div class="flex items-start">
                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2 flex-shrink-0"></span>
                    <p class="text-sm text-gray-700">{{ $beneficio }}</p>
                </div>
                @endforeach
                @elseif(!empty($impact['items']) && is_array($impact['items']))
                @foreach($impact['items'] as $item)
                <div class="flex items-start">
                    <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2 flex-shrink-0"></span>
                    <p class="text-sm text-gray-700">{{ $item }}</p>
                </div>
                @endforeach
                @endif
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif