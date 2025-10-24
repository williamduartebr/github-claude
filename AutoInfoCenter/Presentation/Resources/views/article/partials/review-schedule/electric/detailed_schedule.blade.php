@if(!empty($article->detailed_schedule) && is_array($article->detailed_schedule) &&
count($article->detailed_schedule) > 0)
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        ⚡ Detalhamento das Revisões Elétricas
    </h2>

    <div class="relative">
        <!-- Linha vertical da timeline -->
        <div class="absolute left-8 md:left-12 top-0 bottom-0 w-0.5 bg-blue-300"></div>

        @foreach($article->detailed_schedule as $index => $revision)
        <div class="relative mb-10 pl-20 md:pl-28">
            <div
                class="absolute left-0 top-0 h-16 w-16 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 border border-gray-200 flex items-center justify-center z-10">
                <div
                    class="h-10 w-10 rounded-full bg-[#0E368A] flex items-center justify-center text-white font-semibold">
                    {{ $revision['km'] ?? '?' }}
                </div>
            </div>

            <div class="bg-white rounded-lg border p-5 shadow-sm">
                <h3 class="text-lg font-medium text-[#151C25] mb-3">
                    🔋 {{ $revision['numero_revisao'] ?? '?' }}ª Revisão ({{ $revision['intervalo'] ?? 'N/A'
                    }})
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    @if(!empty($revision['servicos_principais']) &&
                    is_array($revision['servicos_principais']) && count($revision['servicos_principais']) >
                    0)
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">⚡ Procedimentos Principais:
                        </h4>
                        <ul class="space-y-1">
                            @foreach($revision['servicos_principais'] as $servico)
                            @if(!empty($servico))
                            <li class="flex items-center text-sm text-gray-700">
                                <div
                                    class="h-4 w-4 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-2 flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5 text-[#0E368A]"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span>{{ $servico }}</span>
                            </li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @if(!empty($revision['verificacoes_complementares']) &&
                    is_array($revision['verificacoes_complementares']) &&
                    count($revision['verificacoes_complementares']) > 0)
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">🔍 Verificações Complementares:
                        </h4>
                        <ul class="space-y-1">
                            @foreach($revision['verificacoes_complementares'] as $verificacao)
                            @if(!empty($verificacao))
                            <li class="flex items-center text-sm text-gray-700">
                                <div
                                    class="h-4 w-4 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-2 flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5 text-[#0E368A]"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span>{{ $verificacao }}</span>
                            </li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>

                @if(!empty($revision['observacoes']))
                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                    <div class="flex-shrink-0 mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#E06600]" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-700">
                        <span class="font-semibold">⚠️ Importante:</span> {{ $revision['observacoes'] }}
                    </p>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif