{{--
Include: vehicle-data-pickup.blade.php
Componente específico para exibição de dados de veículos pickup/utilitários
Localização: auto-info-center::article.partials.vehicle-data-pickup
--}}

<!-- Especificações dos Pneus e Localização da Etiqueta - PICKUP -->
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-0.5 pb-2 border-b-2 border-[#0E368A]/30">
        🔧 Especificações Técnicas - Pickup
    </h2>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 -mt-4">
        
        <!-- Especificações dos Pneus por Versão -->
        @if(!empty($contentData['especificacoes_por_versao']))
        <div class="bg-white rounded-xl border-2 border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center mb-6">
                <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-[#0E368A]/10 to-[#0E368A]/20 flex items-center justify-center mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
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
                                {{ $spec['versao'] }}
                                @if(!empty($spec['motor']))
                                <div class="text-xs text-gray-500 mt-1">{{ $spec['motor'] }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-sm font-mono font-semibold text-[#0E368A]">
                                {{ $spec['medida_pneus'] }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
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
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm text-blue-800">
                        <strong>Importante para Pickups:</strong> Índices de carga são fundamentais devido à capacidade de transporte na caçamba. Sempre respeite os valores indicados.
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Localização da Etiqueta de Pressão -->
        @if(!empty($contentData['localizacao_etiqueta']))
        <div class="bg-white rounded-xl border-2 border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center mb-6">
                <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-[#E06600]/10 to-[#E06600]/20 flex items-center justify-center mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#E06600]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Localização da Etiqueta</h3>
                    <p class="text-sm text-gray-600">Onde encontrar as pressões oficiais</p>
                </div>
            </div>

            @php $location = $contentData['localizacao_etiqueta'] @endphp
            
            <!-- Localização Principal -->
            <div class="mb-6 bg-gradient-to-r from-[#E06600]/5 to-[#E06600]/10 rounded-lg p-4 border-l-4 border-[#E06600]">
                <div class="flex items-center mb-3">
                    <div class="h-8 w-8 rounded-full bg-[#E06600] text-white flex items-center justify-center mr-3 text-sm font-bold">
                        1
                    </div>
                    <h4 class="font-semibold text-[#E06600]">Local Principal</h4>
                </div>
                <p class="text-sm text-gray-700 ml-11">
                    {{ $location['localizacao_principal'] ?? $location['descricao'] }}
                </p>
            </div>

            <!-- Localizações Alternativas -->
            @if(!empty($location['localizacoes_alternativas']) || !empty($location['locais_alternativos']))
            <div class="space-y-3">
                <h4 class="font-semibold text-gray-800 text-sm">Localizações Alternativas:</h4>
                
                @php 
                $alternativeLocations = $location['localizacoes_alternativas'] ?? $location['locais_alternativos'] ?? [];
                @endphp
                
                @foreach($alternativeLocations as $index => $altLocation)
                <div class="flex items-start">
                    <div class="h-6 w-6 rounded-full bg-gray-200 flex items-center justify-center mr-3 flex-shrink-0 mt-0.5">
                        <span class="text-xs font-semibold text-gray-600">{{ $index + 2 }}</span>
                    </div>
                    <p class="text-sm text-gray-700">{{ $altLocation }}</p>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Observação Especial -->
            @if(!empty($location['observacao']))
            <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-yellow-800 mb-1">💡 Dica Especial:</p>
                        <p class="text-sm text-yellow-700">{{ $location['observacao'] }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>
</section>

<!-- Informações Adicionais do Veículo Pickup -->
@if(!empty($vehicleData['vehicle_info']))
<section class="mb-12">
    <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl p-6 border border-gray-200">
        <h3 class="text-xl font-semibold text-[#151C25] mb-4 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A] mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            🚛 Informações do Veículo
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Informações Básicas -->
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <h4 class="font-semibold text-gray-800 mb-3 text-sm">Identificação</h4>
                <div class="space-y-2 text-sm text-gray-700">
                    @if(!empty($vehicleData['vehicle_info']['full_name']))
                    <p><strong>Modelo:</strong> {{ $vehicleData['vehicle_info']['full_name'] }}</p>
                    @endif
                    
                    @if(!empty($vehicleData['vehicle_info']['category']))
                    <p><strong>Categoria:</strong> {{ $vehicleData['vehicle_info']['category'] }}</p>
                    @endif
                    
                    @if(!empty($vehicleData['tire_size']))
                    <p><strong>Pneus:</strong> <span class="font-mono">{{ $vehicleData['tire_size'] }}</span></p>
                    @endif
                </div>
            </div>

            <!-- Pressões Padrão -->
            @if(!empty($vehicleData['pressure_specifications']))
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <h4 class="font-semibold text-gray-800 mb-3 text-sm">Pressões Padrão</h4>
                <div class="space-y-2 text-sm text-gray-700">
                    @if(!empty($vehicleData['pressure_specifications']['pressure_empty_front']))
                    <p><strong>Diant. Normal:</strong> 
                        <span class="text-[#0E368A] font-bold">{{ $vehicleData['pressure_specifications']['pressure_empty_front'] }} PSI</span>
                    </p>
                    @endif
                    
                    @if(!empty($vehicleData['pressure_specifications']['pressure_empty_rear']))
                    <p><strong>Tras. Normal:</strong> 
                        <span class="text-[#0E368A] font-bold">{{ $vehicleData['pressure_specifications']['pressure_empty_rear'] }} PSI</span>
                    </p>
                    @endif
                    
                    @if(!empty($vehicleData['pressure_specifications']['pressure_spare']))
                    <p><strong>Estepe:</strong> 
                        <span class="text-green-600 font-bold">{{ $vehicleData['pressure_specifications']['pressure_spare'] }} PSI</span>
                    </p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Pressões com Carga -->
            @if(!empty($vehicleData['pressure_specifications']))
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <h4 class="font-semibold text-gray-800 mb-3 text-sm">Pressões c/ Carga</h4>
                <div class="space-y-2 text-sm text-gray-700">
                    @if(!empty($vehicleData['pressure_specifications']['pressure_max_front']))
                    <p><strong>Diant. Carregado:</strong> 
                        <span class="text-[#E06600] font-bold">{{ $vehicleData['pressure_specifications']['pressure_max_front'] }} PSI</span>
                    </p>
                    @endif
                    
                    @if(!empty($vehicleData['pressure_specifications']['pressure_max_rear']))
                    <p><strong>Tras. Carregado:</strong> 
                        <span class="text-[#E06600] font-bold">{{ $vehicleData['pressure_specifications']['pressure_max_rear'] }} PSI</span>
                    </p>
                    @endif
                    
                    <p class="text-xs text-gray-500 mt-2 pt-2 border-t border-gray-200">
                        * Para uso com carga na caçamba
                    </p>
                </div>
            </div>
            @endif
        </div>

        <!-- Alerta Especial para Pickup -->
        <div class="mt-6 bg-[#E06600]/10 border-l-4 border-[#E06600] rounded-r-lg p-4">
            <div class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#E06600] mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <h4 class="font-semibold text-[#E06600] mb-2">⚠️ Atenção Especial para Pickups</h4>
                    <p class="text-sm text-gray-700">
                        Pickups são projetadas para transportar cargas, por isso as pressões traseiras são naturalmente mais altas. 
                        <strong>Sempre ajuste conforme o peso na caçamba</strong> para manter estabilidade, segurança e economia de combustível.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endif