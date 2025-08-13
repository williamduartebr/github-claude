{{-- 
Partial: tire-pressure/motorcycle/tire-specifications.blade.php
Especificações dos pneus por versão da motocicleta
Otimizado para características específicas de motos
--}}

@php
    $tireSpecs = $article->getData()['tire_specifications'] ?? [];
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $motorcycleCategory = $vehicleInfo['category'] ?? 'standard';
@endphp

@if(!empty($tireSpecs))
<section class="mb-12" id="tire-specifications">
    <div class="bg-gradient-to-r from-gray-800 to-gray-900 text-white rounded-t-lg p-6">
        <div class="flex items-center">
            <span class="text-3xl mr-4">🏍️</span>
            <div>
                <h2 class="text-2xl font-bold mb-2">
                    Especificações dos Pneus
                </h2>
                <p class="text-gray-300 text-sm">
                    Medidas e pressões recomendadas por versão da {{ $vehicleInfo['full_name'] ?? 'motocicleta' }}
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-b-lg border-l border-r border-b border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-4 px-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Versão
                        </th>
                        <th class="py-4 px-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Pneu Dianteiro
                        </th>
                        <th class="py-4 px-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Pneu Traseiro
                        </th>
                        <th class="py-4 px-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Solo/Normal
                        </th>
                        <th class="py-4 px-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Com Garupa
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @if(empty($tireSpecs))
                    <!-- Linha padrão caso não tenha dados específicos -->
                    <tr class="hover:bg-gray-50 transition-colors duration-200 bg-blue-50">
                        <td class="py-4 px-4 text-sm font-semibold text-gray-900">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-[#DC2626] rounded-full mr-2" title="Versão principal"></div>
                                Padrão
                                @if($motorcycleCategory === 'sport')
                                <span class="ml-2 inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                    Sport
                                </span>
                                @elseif($motorcycleCategory === 'touring')
                                <span class="ml-2 inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    Touring
                                </span>
                                @elseif($motorcycleCategory === 'naked')
                                <span class="ml-2 inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                    Naked
                                </span>
                                @endif
                            </div>
                            <div class="text-xs text-[#DC2626] font-medium mt-1">Versão principal</div>
                        </td>
                        <td class="py-4 px-4 text-sm text-center">
                            <div class="space-y-2">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    Consulte manual
                                </span>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-sm text-center">
                            <div class="space-y-2">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    Consulte manual
                                </span>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-sm text-center">
                            <div class="space-y-1">
                                <div class="text-sm font-bold text-green-700">
                                    D: {{ $vehicleInfo['pressure_front'] ?? 'N/A' }}
                                </div>
                                <div class="text-sm font-bold text-blue-700">
                                    T: {{ $vehicleInfo['pressure_rear'] ?? 'N/A' }}
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-sm text-center">
                            <div class="space-y-1">
                                <div class="text-sm font-bold text-orange-700">
                                    D: {{ $vehicleInfo['pressure_front_loaded'] ?? 'N/A' }}
                                </div>
                                <div class="text-sm font-bold text-red-700">
                                    T: {{ $vehicleInfo['pressure_rear_loaded'] ?? 'N/A' }}
                                </div>
                            </div>
                        </td>
                    </tr>
                    @else
                    <!-- Dados específicos da ViewModel -->
                    @foreach($tireSpecs as $index => $spec)
                    <tr class="hover:bg-gray-50 transition-colors duration-200 {{ $index === 0 ? 'bg-blue-50' : '' }}">
                        <!-- Versão -->
                        <td class="py-4 px-4 text-sm font-semibold text-gray-900">
                            <div class="flex items-center">
                                @if($index === 0)
                                <div class="w-3 h-3 bg-[#DC2626] rounded-full mr-2" title="Versão principal"></div>
                                @else
                                <div class="w-3 h-3 bg-gray-300 rounded-full mr-2"></div>
                                @endif
                                {{ $spec['version'] ?? 'Padrão' }}
                                
                                <!-- Badge de categoria -->
                                @if($motorcycleCategory === 'sport')
                                <span class="ml-2 inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                    Sport
                                </span>
                                @elseif($motorcycleCategory === 'touring')
                                <span class="ml-2 inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    Touring
                                </span>
                                @elseif($motorcycleCategory === 'naked')
                                <span class="ml-2 inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                    Naked
                                </span>
                                @endif
                            </div>
                            @if($index === 0)
                            <div class="text-xs text-[#DC2626] font-medium mt-1">Versão principal</div>
                            @endif
                        </td>

                        <!-- Pneu Dianteiro -->
                        <td class="py-4 px-4 text-sm text-center">
                            <div class="space-y-2">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $spec['front_tire_size'] ?? 'N/A' }}
                                </span>
                                @if(!empty($spec['front_tire_type']))
                                <div class="text-xs text-gray-500">{{ $spec['front_tire_type'] }}</div>
                                @endif
                            </div>
                        </td>

                        <!-- Pneu Traseiro -->
                        <td class="py-4 px-4 text-sm text-center">
                            <div class="space-y-2">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $spec['rear_tire_size'] ?? 'N/A' }}
                                </span>
                                @if(!empty($spec['rear_tire_type']))
                                <div class="text-xs text-gray-500">{{ $spec['rear_tire_type'] }}</div>
                                @endif
                            </div>
                        </td>

                        <!-- Pressão Solo/Normal -->
                        <td class="py-4 px-4 text-sm text-center">
                            <div class="space-y-1">
                                <div class="text-sm font-bold text-green-700">
                                    D: {{ $spec['front_solo'] ?? 'Consulte manual' }}
                                </div>
                                <div class="text-sm font-bold text-blue-700">
                                    T: {{ $spec['rear_solo'] ?? 'Consulte manual' }}
                                </div>
                            </div>
                        </td>

                        <!-- Pressão Com Garupa -->
                        <td class="py-4 px-4 text-sm text-center">
                            <div class="space-y-1">
                                <div class="text-sm font-bold text-orange-700">
                                    D: {{ $spec['front_passenger'] ?? 'Consulte manual' }}
                                </div>
                                <div class="text-sm font-bold text-red-700">
                                    T: {{ $spec['rear_passenger'] ?? 'Consulte manual' }}
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Legendas específicas para motos -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                    <span class="text-gray-700"><strong>D:</strong> Dianteiro</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                    <span class="text-gray-700"><strong>T:</strong> Traseiro</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-[#DC2626] rounded-full mr-2"></div>
                    <span class="text-gray-700">Pressões em PSI</span>
                </div>
            </div>
        </div>

        <!-- Dicas específicas por categoria de moto -->
        @if($motorcycleCategory === 'sport')
        <div class="bg-red-50 border-t border-red-200 px-6 py-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 bg-red-100 rounded-full flex items-center justify-center">
                        <span class="text-sm text-red-600">⚡</span>
                    </div>
                </div>
                <div class="ml-3">
                    <h4 class="text-sm font-semibold text-red-800">Dica para Motos Esportivas</h4>
                    <p class="text-sm text-red-700 mt-1">
                        Para track days ou uso esportivo, considere aumentar 2-3 PSI. Sempre aqueça os pneus gradualmente.
                    </p>
                </div>
            </div>
        </div>
        @elseif($motorcycleCategory === 'touring')
        <div class="bg-blue-50 border-t border-blue-200 px-6 py-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-sm text-blue-600">🛣️</span>
                    </div>
                </div>
                <div class="ml-3">
                    <h4 class="text-sm font-semibold text-blue-800">Dica para Motos Touring</h4>
                    <p class="text-sm text-blue-700 mt-1">
                        Para viagens longas com bagagem, use sempre as pressões "com garupa" mesmo viajando sozinho.
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
@endif