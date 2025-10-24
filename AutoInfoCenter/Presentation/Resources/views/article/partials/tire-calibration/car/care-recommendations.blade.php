@if(!empty($article->getData()['care_recommendations']))
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        Cuidados e Recomendações para o Brasil
    </h2>

    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @php $recommendations = $article->getData()['care_recommendations'] @endphp
            @php $chunkedRecommendations = array_chunk($recommendations, ceil(count($recommendations) / 2))
            @endphp

            @foreach($chunkedRecommendations as $column)
            <div class="space-y-5">
                @foreach($column as $recommendation)
                <div class="flex items-start">
                    <div
                        class="h-8 w-8 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3 flex-shrink-0 mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#0E368A]" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            @switch($recommendation['icon_class'])
                            @case('clock')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            @break
                            @case('thermometer')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            @break
                            @case('tool')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            @break
                            @case('sun')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            @break
                            @case('cloud-rain')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M4 14l4-4 4 4m-4-4v12m8-8l-4-4m4 4h-4" />
                            @break
                            @case('rotate-cw')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            @break
                            @default
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            @endswitch
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-md font-medium text-gray-900 mb-1">{{ $recommendation['category'] }}
                        </h3>
                        <p class="text-sm text-gray-700">{{ $recommendation['description'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>

        <!-- Alerta -->
        <div class="mt-6 bg-[#E06600]/5 border border-[#E06600]/20 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0 mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#E06600]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-md font-medium text-[#E06600] mb-1">Pressão e Segurança</h3>
                    <p class="text-sm text-gray-700">
                        Pneus com 5 PSI abaixo do recomendado aumentam o consumo em 10% e reduzem a vida
                        útil em até 30%. No calor brasileiro, pneus subcalibrados têm maior risco de
                        estouro.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endif