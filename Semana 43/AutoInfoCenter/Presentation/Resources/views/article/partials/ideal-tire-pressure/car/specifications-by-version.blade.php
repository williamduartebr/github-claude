{{--
Partial: ideal-tire-pressure/car/specifications-by-version.blade.php
Exibe especificações detalhadas por versão usando dados embarcados
--}}

@php
$tireSpecs = $article->getData()['tire_specifications_by_version'] ?? [];
$vehicleInfo = $article->getData()['vehicle_info'] ?? [];
@endphp

<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200 flex items-center">
        <span class="text-2xl mr-3">🚗</span>
        Tabela de Pressão dos Pneus (PSI - Padrão Brasileiro)
    </h2>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-[#0E368A] text-white">
                        <th class="py-3 px-4 text-left font-medium text-sm">Versão</th>
                        <th class="py-3 px-4 text-center font-medium text-sm">Medida dos Pneus</th>
                        <th class="py-3 px-4 text-center font-medium text-sm">Dianteiros<br>(uso normal)</th>
                        <th class="py-3 px-4 text-center font-medium text-sm">Traseiros<br>(uso normal)</th>
                        <th class="py-3 px-4 text-center font-medium text-sm">Dianteiros<br>(carga completa)</th>
                        <th class="py-3 px-4 text-center font-medium text-sm">Traseiros<br>(carga completa)</th>
                        @if(collect($tireSpecs)->where('load_speed_index', '!=', '')->isNotEmpty())
                        <th class="py-3 px-4 text-center font-medium text-sm">Índice<br>Carga/Vel.</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($tireSpecs as $index => $spec)
                    <tr
                        class="border-b border-gray-200 hover:bg-gray-50 transition-colors {{ $index === 0 ? 'bg-blue-50' : '' }}">
                        <!-- Versão -->
                        <td class="py-4 px-4 text-sm font-semibold text-gray-900">
                            <div class="flex items-center">
                                @if($index === 0)
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-2" title="Versão principal"></div>
                                @else
                                <div class="w-3 h-3 bg-gray-300 rounded-full mr-2"></div>
                                @endif
                                {{ \Str::upper($spec['version']) ?? 'Padrão' }}
                            </div>
                            @if($index === 0)
                            <div class="text-xs text-blue-600 font-medium mt-1">Versão principal</div>
                            @endif
                        </td>

                        <!-- Medida dos Pneus -->
                        <td class="py-4 px-4 text-sm text-center">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $spec['tire_size'] ?? 'N/A' }}
                            </span>
                        </td>

                        <!-- Pressão Dianteira Normal -->
                        <td class="py-4 px-4 text-sm text-center">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800">
                                {{ $spec['front_normal'] ?? 'N/A' }} PSI
                            </span>
                        </td>

                        <!-- Pressão Traseira Normal -->
                        <td class="py-4 px-4 text-sm text-center">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                                {{ $spec['rear_normal'] ?? 'N/A' }} PSI
                            </span>
                        </td>

                        <!-- Pressão Dianteira Carregado -->
                        <td class="py-4 px-4 text-sm text-center">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-orange-100 text-orange-800">
                                {{ $spec['front_loaded'] ?? 'N/A' }} PSI
                            </span>
                        </td>

                        <!-- Pressão Traseira Carregado -->
                        <td class="py-4 px-4 text-sm text-center">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-800">
                                {{ $spec['rear_loaded'] ?? 'N/A' }} PSI
                            </span>
                        </td>

                        <!-- Índice de Carga/Velocidade (condicional) -->
                        @if(collect($tireSpecs)->where('load_speed_index', '!=', '')->isNotEmpty())
                        <td class="py-4 px-4 text-sm text-center">
                            @if(!empty($spec['load_speed_index']))
                            <span
                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                {{ $spec['load_speed_index'] }}
                            </span>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Legenda das Cores -->
    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
        <h4 class="text-sm font-semibold text-gray-700 mb-3">Legenda das Pressões:</h4>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
            <div class="flex items-center">
                <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                <span>Dianteiros (normal)</span>
            </div>
            <div class="flex items-center">
                <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                <span>Traseiros (normal)</span>
            </div>
            <div class="flex items-center">
                <span class="w-3 h-3 bg-orange-500 rounded-full mr-2"></span>
                <span>Dianteiros (carregado)</span>
            </div>
            <div class="flex items-center">
                <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                <span>Traseiros (carregado)</span>
            </div>
        </div>
    </div>

    <!-- Notas Importantes -->
    <div class="mt-6 space-y-4">
        <!-- Nota sobre uso normal vs carregado -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
                <div class="text-sm">
                    <p class="font-medium text-blue-800 mb-1">📌 Quando usar cada pressão:</p>
                    <ul class="text-blue-700 space-y-1">
                        <li><strong>Uso Normal:</strong> Para 1-3 pessoas sem bagagem pesada</li>
                        <li><strong>Carga Completa:</strong> Para 4-5 pessoas ou com bagagem no porta-malas</li>
                    </ul>
                </div>
            </div>
        </div>

        @if($vehicleInfo['is_premium'] ?? false)
        <!-- Nota para veículos premium -->
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-purple-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"
                        clip-rule="evenodd" />
                </svg>
                <div class="text-sm">
                    <p class="font-medium text-purple-800 mb-1">👑 Dica Premium:</p>
                    <p class="text-purple-700">
                        Este {{ $vehicleInfo['full_name'] ?? 'veículo' }} pode ter diferentes modos de condução que
                        ajustam automaticamente
                        as pressões via sistema TPMS. Consulte o manual para configurações específicas.
                    </p>
                </div>
            </div>
        </div>
        @endif

        @if($vehicleInfo['has_tpms'] ?? false)
        <!-- Nota sobre TPMS -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <div class="text-sm">
                    <p class="font-medium text-green-800 mb-1">✅ Sistema TPMS Disponível:</p>
                    <p class="text-green-700">
                        Seu veículo possui monitoramento automático da pressão dos pneus. O sistema alertará no painel
                        quando houver variações significativas.
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>