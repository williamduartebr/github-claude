<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        Especificações dos Pneus Originais
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Especificações Técnicas -->
        @if(!empty($article->getData()['tire_specifications_by_version']))

        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-center mb-4">
                <div
                    class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Medidas de Pneus por Versão</h3>
            </div>

            @php
            $tireSpecs = $article->getData()['tire_specifications_by_version'] ?? [];
            $hasLoadSpeedIndex = collect($tireSpecs)->some(fn($spec) => !empty($spec['load_speed_index']));
            @endphp

            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-[#0E368A] text-white">
                            <th class="py-2 px-3 text-left font-medium text-xs">Versão</th>
                            <th class="py-2 px-3 text-left font-medium text-xs">Medidas de Pneus</th>
                            @if($hasLoadSpeedIndex)
                            <th class="py-2 px-3 text-left font-medium text-xs">Índice de Carga/Vel.</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tireSpecs as $spec)
                        <tr class="border-b border-gray-200 {{ $spec['css_class'] }}">
                            <td class="py-2 px-3 text-xs">{{ \Str::upper($spec['version']) }}</td>
                            <td class="py-2 px-3 text-xs">{{ $spec['tire_size'] }}</td>
                            @if($hasLoadSpeedIndex)
                            <td class="py-2 px-3 text-xs">{{ $spec['load_speed_index'] ?: '----' }}</td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-xs text-gray-700">
                    <span class="font-medium">Observação:</span> Para veículos equipados com rodas
                    diferentes das originais, consulte o manual do proprietário ou a etiqueta de calibragem
                    na coluna da porta do motorista.
                </p>
            </div>
        </div>
        @endif

        <!-- Localização da Etiqueta - USANDO PARTIAL MODULAR INLINE -->
        @if(!empty($article->getData()['label_location']))
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-center mb-4">
                <div
                    class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Localização da Etiqueta de Pressão</h3>
            </div>

            @php $location = $article->getData()['label_location'] @endphp
            <div class="space-y-4">
                <div class="flex items-start">
                    <div
                        class="h-6 w-6 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-3 flex-shrink-0 mt-0.5">
                        <span class="text-xs font-semibold text-[#0E368A]">1</span>
                    </div>
                    <p class="text-sm text-gray-700">{{ $location['description'] }}</p>
                </div>

                @foreach($location['alternative_locations'] ?? [] as $index => $altLocation)
                <div class="flex items-start">
                    <div
                        class="h-6 w-6 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-3 flex-shrink-0 mt-0.5">
                        <span class="text-xs font-semibold text-[#0E368A]">{{ $index + 2 }}</span>
                    </div>
                    <p class="text-sm text-gray-700">{{ $altLocation }}</p>
                </div>
                @endforeach
            </div>

            @if($location['note'])
            <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#E06600] mr-2" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-medium text-gray-800">{{ $location['note'] }}</p>
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>
</section>