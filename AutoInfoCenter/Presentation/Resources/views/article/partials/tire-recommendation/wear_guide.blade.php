@if(!empty($article->wear_guide) && is_array($article->wear_guide))
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Guia de Desgaste e
        Substituição</h2>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Indicadores de Desgaste -->
            @if(!empty($article->wear_guide['indicadores_desgaste']) &&
            is_array($article->wear_guide['indicadores_desgaste']) &&
            count($article->wear_guide['indicadores_desgaste']) > 0)
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <div class="bg-[#0E368A]/10 w-8 h-8 rounded-full flex items-center justify-center mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#0E368A]" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    Indicadores de Desgaste
                </h3>

                <ul class="space-y-3 text-gray-700 ml-2">
                    @foreach($article->wear_guide['indicadores_desgaste'] as $indicator)
                    @if(!empty($indicator['indicador']))
                    <li class="flex items-baseline">
                        <span class="text-[#0E368A] mr-2">•</span>
                        <span><span class="font-medium">{{ $indicator['indicador'] }}:</span> {{ $indicator['descricao']
                            ?? '' }}</span>
                    </li>
                    @endif
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Quando Substituir -->
            @if(!empty($article->wear_guide['quando_substituir']) && is_array($article->wear_guide['quando_substituir'])
            && count($article->wear_guide['quando_substituir']) > 0)
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <div class="bg-[#0E368A]/10 w-8 h-8 rounded-full flex items-center justify-center mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#0E368A]" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    Quando Substituir
                </h3>

                <ul class="space-y-3 text-gray-700 ml-2">
                    @foreach($article->wear_guide['quando_substituir'] as $situation)
                    @if(!empty($situation['situacao']))
                    <li class="flex items-baseline">
                        <span class="text-[#0E368A] mr-2">•</span>
                        <span><span class="font-medium">{{ $situation['situacao'] }}:</span> {{ $situation['descricao']
                            ?? '' }}</span>
                    </li>
                    @endif
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
</section>
@endif