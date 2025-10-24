@if(!empty($article->durability_factors) && is_array($article->durability_factors) &&
count($article->durability_factors) > 0)
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        Fatores que Afetam a Durabilidade dos Pneus
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($article->durability_factors as $factor)
        @if(!empty($factor['title']))
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ $factor['title'] }}</h3>

            @if(!empty($factor['impact']))
            <div class="flex items-center mb-3">
                <span class="text-sm font-medium text-gray-700 w-32">Impacto na vida útil:</span>
                <div class="flex-1 bg-gray-200 rounded-full h-2 mx-3">
                    @php
                    $impactValue = (int) filter_var($factor['impact'], FILTER_SANITIZE_NUMBER_INT);
                    $width = abs($impactValue);
                    $color = $factor['is_positive'] ? 'bg-green-500' : 'bg-red-500';
                    @endphp
                    <div class="{{ $color }} h-2 rounded-full" style="width: {{ min($width, 100) }}%"></div>
                </div>
                <span class="text-sm font-medium {{ $factor['is_positive'] ? 'text-green-600' : 'text-red-600' }}">
                    {{ $factor['impact'] }}
                </span>
            </div>
            @endif

            <p class="text-gray-600 mb-3">{{ $factor['description'] ?? '' }}</p>

            @if(!empty($factor['recommendation']))
            <div class="bg-blue-50 p-3 rounded-lg">
                <p class="text-sm text-blue-800"><strong>Recomendação:</strong> {{ $factor['recommendation'] }}</p>
            </div>
            @endif

            @if(!empty($factor['pressure_recommendation']))
            <div class="mt-2 text-sm text-gray-600">
                <strong>Pressão recomendada:</strong> {{ $factor['pressure_recommendation'] }}
            </div>
            @endif
        </div>
        @endif
        @endforeach
    </div>
</section>
@endif