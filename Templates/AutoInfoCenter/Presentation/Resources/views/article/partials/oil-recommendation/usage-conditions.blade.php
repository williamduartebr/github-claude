@if(!empty($article->usage_conditions) && is_array($article->usage_conditions))
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Condições Especiais
        de Uso</h2>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <p class="text-gray-800 mb-6">Dependendo das condições de uso do seu {{
            $article->vehicle_info['make'] ?? 'veículo' }} {{ $article->vehicle_info['model'] ?? '' }}, ajustes nas
            recomendações podem ser necessários:</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            @if(!empty($article->usage_conditions['severo']) && is_array($article->usage_conditions['severo']))
            <div class="bg-gray-50 rounded-lg p-5 border border-gray-100">
                <h3 class="text-lg font-medium text-gray-900 mb-3 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 mr-2" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Uso Severo
                </h3>
                @if(!empty($article->usage_conditions['severo']['condicoes']) &&
                is_array($article->usage_conditions['severo']['condicoes']))
                <ul class="space-y-2 text-gray-700">
                    @foreach($article->usage_conditions['severo']['condicoes'] as $condition)
                    @if(!empty($condition))
                    <li class="flex items-baseline">
                        <span class="text-[#0E368A] mr-2">•</span>
                        <span>{{ $condition }}</span>
                    </li>
                    @endif
                    @endforeach
                </ul>
                @endif
                @if(!empty($article->usage_conditions['severo']['recomendacao']))
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-sm font-medium text-gray-900">Recomendação:</p>
                    <p class="text-sm text-gray-700">{{ $article->usage_conditions['severo']['recomendacao'] }}</p>
                </div>
                @endif
            </div>
            @endif

            @if(!empty($article->usage_conditions['normal']) && is_array($article->usage_conditions['normal']))
            <div class="bg-gray-50 rounded-lg p-5 border border-gray-100">
                <h3 class="text-lg font-medium text-gray-900 mb-3 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mr-2" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Uso Normal
                </h3>
                @if(!empty($article->usage_conditions['normal']['condicoes']) &&
                is_array($article->usage_conditions['normal']['condicoes']))
                <ul class="space-y-2 text-gray-700">
                    @foreach($article->usage_conditions['normal']['condicoes'] as $condition)
                    @if(!empty($condition))
                    <li class="flex items-baseline">
                        <span class="text-[#0E368A] mr-2">•</span>
                        <span>{{ $condition }}</span>
                    </li>
                    @endif
                    @endforeach
                </ul>
                @endif
                @if(!empty($article->usage_conditions['normal']['recomendacao']))
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-sm font-medium text-gray-900">Recomendação:</p>
                    <p class="text-sm text-gray-700">{{ $article->usage_conditions['normal']['recomendacao'] }}</p>
                </div>
                @endif
            </div>
            @endif
        </div>

        @if(!empty($article->usage_conditions['dica_adicional']))
        <div class="bg-yellow-50 p-4 rounded-md border-l-4 border-yellow-400">
            <p class="text-sm text-yellow-800">
                <span class="font-bold">Dica importante:</span> {{ $article->usage_conditions['dica_adicional'] }}
            </p>
        </div>
        @endif
    </div>
</section>
@endif