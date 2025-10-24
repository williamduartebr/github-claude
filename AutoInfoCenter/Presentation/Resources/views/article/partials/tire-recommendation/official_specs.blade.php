@if(!empty($article->official_specs) && is_array($article->official_specs))
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Especificações
        Oficiais {{ $article->vehicle_info['make'] ?? 'do Veículo' }}</h2>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-200">
            <!-- Pneu Dianteiro -->
            @if(!empty($article->official_specs['pneu_dianteiro']) &&
            is_array($article->official_specs['pneu_dianteiro']))
            <div class="p-6">
                <div class="flex items-center mb-5">
                    <div
                        class="flex-shrink-0 h-12 w-12 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900">Pneu Dianteiro</h3>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-gray-600">Medida Original:</span>
                        <span class="font-medium text-gray-900">{{
                            $article->official_specs['pneu_dianteiro']['medida_original'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-gray-600">Índice de Carga:</span>
                        <span class="font-medium text-gray-900">{{
                            $article->official_specs['pneu_dianteiro']['indice_carga'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-gray-600">Índice de Velocidade:</span>
                        <span class="font-medium text-gray-900">{{
                            $article->official_specs['pneu_dianteiro']['indice_velocidade'] ?? 'N/A'
                            }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-gray-600">Pressão Recomendada:</span>
                        <span class="font-medium text-gray-900">{{
                            $article->official_specs['pneu_dianteiro']['pressao_recomendada'] ?? 'N/A'
                            }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Capacidade de Carga:</span>
                        <span class="font-medium text-gray-900">{{
                            $article->official_specs['pneu_dianteiro']['capacidade_carga'] ?? 'N/A'
                            }}</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Pneu Traseiro -->
            @if(!empty($article->official_specs['pneu_traseiro']) &&
            is_array($article->official_specs['pneu_traseiro']))
            <div class="p-6">
                <div class="flex items-center mb-5">
                    <div
                        class="flex-shrink-0 h-12 w-12 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900">Pneu Traseiro</h3>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-gray-600">Medida Original:</span>
                        <span class="font-medium text-gray-900">{{
                            $article->official_specs['pneu_traseiro']['medida_original'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-gray-600">Índice de Carga:</span>
                        <span class="font-medium text-gray-900">{{
                            $article->official_specs['pneu_traseiro']['indice_carga'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-gray-600">Índice de Velocidade:</span>
                        <span class="font-medium text-gray-900">{{
                            $article->official_specs['pneu_traseiro']['indice_velocidade'] ?? 'N/A'
                            }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-gray-600">Pressão Recomendada:</span>
                        <span class="font-medium text-gray-900">{{
                            $article->official_specs['pneu_traseiro']['pressao_recomendada'] ?? 'N/A'
                            }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Capacidade de Carga:</span>
                        <span class="font-medium text-gray-900">{{
                            $article->official_specs['pneu_traseiro']['capacidade_carga'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="mt-4 bg-[#0E368A]/5 p-4 rounded-lg">
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
                Os índices de carga e velocidade indicados são os mínimos recomendados pelo fabricante. É
                possível utilizar pneus com índices superiores, mas nunca inferiores aos especificados.
            </p>
        </div>
    </div>
</section>
@endif