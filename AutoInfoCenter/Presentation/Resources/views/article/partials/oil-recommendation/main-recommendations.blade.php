<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Óleos Recomendados
        para {{ $article->vehicle_info['make'] ?? 'Veículo' }} {{ $article->vehicle_info['model'] ?? '' }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Óleo Recomendado pela Fabricante -->
        @if(!empty($article->manufacturer_recommendation) && is_array($article->manufacturer_recommendation))
        <div
            class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="bg-[#0E368A] text-white px-4 py-3">
                <h3 class="font-medium">Recomendação Oficial</h3>
            </div>
            <div class="p-5">
                <div class="flex items-center justify-center mb-4">
                    <span
                        class="inline-block bg-[#E06600] text-white text-xs font-medium px-2.5 py-1 rounded">PREFERENCIAL</span>
                </div>
                <h4 class="text-xl font-semibold text-center mb-3">{{
                    $article->manufacturer_recommendation['nome_oleo'] ?? 'N/A' }}</h4>
                @if(!empty($article->manufacturer_recommendation['classificacao']))
                <div class="flex justify-between text-sm mb-4">
                    <span class="text-gray-600">Classificação:</span>
                    <span class="font-medium">{{ $article->manufacturer_recommendation['classificacao'] }}</span>
                </div>
                @endif
                @if(!empty($article->manufacturer_recommendation['viscosidade']))
                <div class="flex justify-between text-sm mb-4">
                    <span class="text-gray-600">Viscosidade:</span>
                    <span class="font-medium">{{ $article->manufacturer_recommendation['viscosidade'] }}</span>
                </div>
                @endif
                @if(!empty($article->manufacturer_recommendation['especificacao']))
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Especificação:</span>
                    <span class="font-medium">{{ $article->manufacturer_recommendation['especificacao'] }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Alternativa Premium -->
        @if(!empty($article->premium_alternative) && is_array($article->premium_alternative))
        <div
            class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="bg-gray-700 text-white px-4 py-3">
                <h3 class="font-medium">Alternativa Premium</h3>
            </div>
            <div class="p-5">
                <div class="flex items-center justify-center mb-4">
                    <span
                        class="inline-block bg-gray-200 text-gray-800 text-xs font-medium px-2.5 py-1 rounded">COMPATÍVEL</span>
                </div>
                <h4 class="text-xl font-semibold text-center mb-3">{{
                    $article->premium_alternative['nome_oleo'] ?? 'N/A' }}</h4>
                @if(!empty($article->premium_alternative['classificacao']))
                <div class="flex justify-between text-sm mb-4">
                    <span class="text-gray-600">Classificação:</span>
                    <span class="font-medium">{{ $article->premium_alternative['classificacao'] }}</span>
                </div>
                @endif
                @if(!empty($article->premium_alternative['viscosidade']))
                <div class="flex justify-between text-sm mb-4">
                    <span class="text-gray-600">Viscosidade:</span>
                    <span class="font-medium">{{ $article->premium_alternative['viscosidade'] }}</span>
                </div>
                @endif
                @if(!empty($article->premium_alternative['especificacao']))
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Especificação:</span>
                    <span class="font-medium">{{ $article->premium_alternative['especificacao'] }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Opção Econômica -->
        @if(!empty($article->economic_option) && is_array($article->economic_option))
        <div
            class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="bg-gray-700 text-white px-4 py-3">
                <h3 class="font-medium">Opção Econômica</h3>
            </div>
            <div class="p-5">
                <div class="flex items-center justify-center mb-4">
                    <span
                        class="inline-block bg-gray-200 text-gray-800 text-xs font-medium px-2.5 py-1 rounded">ALTERNATIVA</span>
                </div>
                <h4 class="text-xl font-semibold text-center mb-3">{{ $article->economic_option['nome_oleo'] ?? 'N/A' }}
                </h4>
                @if(!empty($article->economic_option['classificacao']))
                <div class="flex justify-between text-sm mb-4">
                    <span class="text-gray-600">Classificação:</span>
                    <span class="font-medium">{{ $article->economic_option['classificacao'] }}</span>
                </div>
                @endif
                @if(!empty($article->economic_option['viscosidade']))
                <div class="flex justify-between text-sm mb-4">
                    <span class="text-gray-600">Viscosidade:</span>
                    <span class="font-medium">{{ $article->economic_option['viscosidade'] }}</span>
                </div>
                @endif
                @if(!empty($article->economic_option['especificacao']))
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Especificação:</span>
                    <span class="font-medium">{{ $article->economic_option['especificacao'] }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    @if(!empty($article->specifications['especificacao_minima']) && !empty($article->vehicle_info))
    <div class="mt-6 bg-[#0E368A]/5 p-4 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#0E368A]" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <p class="ml-3 text-sm text-gray-700">
                Para o motor {{ $article->vehicle_info['engine'] ?? 'do veículo' }} do {{ $article->vehicle_info['make']
                ?? '' }}
                {{ $article->vehicle_info['model'] ?? '' }}, é fundamental utilizar óleos que atendam às
                especificações {{ $article->specifications['especificacao_minima'] }} para garantir a
                proteção ideal do motor.
            </p>
        </div>
    </div>
    @endif
</section>