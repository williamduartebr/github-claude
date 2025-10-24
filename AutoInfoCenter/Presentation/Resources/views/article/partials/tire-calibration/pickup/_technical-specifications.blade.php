<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-0.5 pb-2 border-b-2 border-[#0E368A]/30">
        🔧 Especificações Técnicas - Pickup
    </h2>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 -mt-4">
        <!-- Especificações dos Pneus por Versão -->
        @if(!empty($contentData['especificacoes_por_versao']))
        <div class="bg-white rounded-xl border-2 border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center mb-6">
                <div
                    class="h-12 w-12 rounded-xl bg-gradient-to-br from-[#0E368A]/10 to-[#0E368A]/20 flex items-center justify-center mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Medidas dos Pneus</h3>
                    <p class="text-sm text-gray-600">Especificações por versão</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-gradient-to-r from-[#0E368A] to-[#0E368A]/80 text-white">
                            <th class="py-3 px-4 text-left font-semibold text-sm rounded-tl-lg">Versão</th>
                            <th class="py-3 px-4 text-left font-semibold text-sm">Medidas</th>
                            <th class="py-3 px-4 text-center font-semibold text-sm rounded-tr-lg">Índice C/V</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contentData['especificacoes_por_versao'] as $index => $spec)
                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4 text-sm font-medium text-gray-900">
                                {{ \Str::upper($spec['versao']) }}
                                @if(!empty($spec['motor']))
                                <div class="text-xs text-gray-500 mt-1">{{ $spec['motor'] }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-sm font-mono font-semibold text-[#0E368A]">
                                {{ $spec['medida_pneus'] }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $spec['indice_carga_velocidade'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-200 bg-blue-50 rounded-lg p-3">
                <div class="flex items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2 flex-shrink-0 mt-0.5"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm text-blue-800">
                        <strong>Importante para Pickups:</strong> Índices de carga são fundamentais devido à capacidade
                        de transporte na caçamba. Sempre respeite os valores indicados.
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
