{{--
Partial: partials/ideal-tire-pressure/car/repair-kit-section.blade.php
Seção específica para veículos SEM estepe (pressure_spare = 0)
--}}

@php
$emergencyEquipment = $article->getData()['emergency_equipment'] ?? [];
$vehicleInfo = $article->getData()['vehicle_info'] ?? [];
$isElectric = $vehicleInfo['is_electric'] ?? false;
$isHybrid = $vehicleInfo['is_hybrid'] ?? false;
$isPremium = $vehicleInfo['is_premium'] ?? false;
@endphp

<section class="mb-12" id="kit-reparo">
    <div class="bg-gradient-to-br from-orange-600 to-red-700 text-white rounded-lg p-8 relative overflow-hidden">
        <div class="absolute top-4 right-4 text-6xl opacity-20">🧰</div>

        <div class="relative z-10">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a1 1 0 01-1-1V9a1 1 0 011-1h1a2 2 0 100-4H4a1 1 0 01-1-1V4a1 1 0 011-1h3a1 1 0 001-1z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold">Kit de Reparo + Compressor</h2>
                    <p class="text-orange-100 text-sm">
                        @if($isElectric)
                        ⚡ Solução para veículos elétricos - Sem estepe para maximizar espaço da bateria
                        @elseif($isHybrid)
                        🔄 Sistema para híbridos - Otimização de peso e espaço
                        @else
                        🛠️ Sistema de reparo temporário para emergências
                        @endif
                    </p>
                </div>
            </div>

            <!-- Componentes do Kit -->
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 border border-white/20">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Selante -->
                    <div class="text-center">
                        <h3 class="font-semibold text-lg mb-3 flex items-center justify-center">
                            <span class="text-2xl mr-2">🧴</span>
                            {{ $emergencyEquipment['kit_components']['sealant']['name'] ?? 'Selante' }}
                        </h3>
                        <div class="bg-white/20 rounded-lg p-4">
                            <div class="text-lg font-bold mb-2">
                                {{ $emergencyEquipment['kit_components']['sealant']['description'] ?? 'Reparo
                                Temporário' }}
                            </div>
                            <div class="text-orange-200 text-sm">
                                {{ $emergencyEquipment['kit_components']['sealant']['limitations'] ?? 'Para furos até
                                4mm' }}
                            </div>
                        </div>
                    </div>

                    <!-- Compressor -->
                    <div class="text-center">
                        <h3 class="font-semibold text-lg mb-3 flex items-center justify-center">
                            <span class="text-2xl mr-2">💨</span>
                            {{ $emergencyEquipment['kit_components']['compressor']['name'] ?? 'Compressor' }}
                        </h3>
                        <div class="bg-white/20 rounded-lg p-4">
                            <div class="text-lg font-bold mb-2">
                                {{ $emergencyEquipment['kit_components']['compressor']['description'] ?? '12V Portátil'
                                }}
                            </div>
                            <div class="text-orange-200 text-sm">
                                {{ $emergencyEquipment['kit_components']['compressor']['power_source'] ?? 'Tomada 12V do
                                veículo' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Limitações Importantes -->
                <div class="mt-6 bg-red-500/20 border border-red-400/30 rounded-lg p-4">
                    <h4 class="font-semibold text-red-100 mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                        ⚠️ Limitações do Kit:
                    </h4>
                    <div class="grid grid-cols-2 gap-4 text-sm text-red-100">
                        <div>• Máx. {{ $emergencyEquipment['max_speed'] ?? 80 }} km/h</div>
                        <div>• Máx. {{ $emergencyEquipment['max_distance'] ?? 150 }} km</div>
                        <div>• Reparo temporário apenas</div>
                        <div>• Pneu deve ser substituído</div>
                    </div>
                </div>

                <!-- Benefícios por Tipo de Veículo -->
                @if($isElectric && !empty($emergencyEquipment['electric_benefits']))
                <div class="mt-6 bg-green-500/20 border border-green-400/30 rounded-lg p-4">
                    <h4 class="font-semibold text-green-100 mb-2 flex items-center">
                        <span class="text-lg mr-2">🔋</span>
                        Vantagens para Veículos Elétricos:
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-green-100">
                        @foreach($emergencyEquipment['electric_benefits'] as $benefit)
                        <div class="flex items-center">
                            <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-2"></span>
                            {{ $benefit }}
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($isHybrid && !empty($emergencyEquipment['hybrid_benefits']))
                <div class="mt-6 bg-blue-500/20 border border-blue-400/30 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-100 mb-2 flex items-center">
                        <span class="text-lg mr-2">🔄</span>
                        Vantagens para Veículos Híbridos:
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-blue-100">
                        @foreach($emergencyEquipment['hybrid_benefits'] as $benefit)
                        <div class="flex items-center">
                            <span class="w-1.5 h-1.5 bg-blue-400 rounded-full mr-2"></span>
                            {{ $benefit }}
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Procedimento de Uso do Kit -->
    <div class="mt-6 bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-6 h-6 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v6a2 2 0 01-2 2H9V5z" />
            </svg>
            Como Usar o Kit de Reparo
        </h3>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Procedimento Passo a Passo -->
            <div>
                <h4 class="font-semibold text-orange-800 mb-4 flex items-center">
                    <span class="text-lg mr-2">📋</span>
                    Procedimento Detalhado:
                </h4>
                <ol class="space-y-3">
                    @foreach($emergencyEquipment['procedure'] ?? [
                    'Pare em local seguro e sinalize o veículo',
                    'Localize o furo e remova objeto (se visível)',
                    'Conecte o tubo do selante à válvula do pneu',
                    'Injete todo o conteúdo do selante',
                    'Conecte o compressor à tomada 12V',
                    'Infle até pressão normal (' . ($emergencyEquipment['normal_pressure'] ?? 35) . ' PSI)',
                    'Dirija por 5km para distribuir o selante',
                    'Verifique pressão novamente',
                    'Dirija até borracharia (máx. 80km/h, 150km)'
                    ] as $index => $step)
                    <li class="flex items-start">
                        <span
                            class="bg-orange-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-0.5">
                            {{ $index + 1 }}
                        </span>
                        <span class="text-sm text-gray-700">{{ $step }}</span>
                    </li>
                    @endforeach
                </ol>
            </div>

            <!-- Limitações e Avisos de Segurança -->
            <div>
                <h4 class="font-semibold text-red-800 mb-4 flex items-center">
                    <span class="text-lg mr-2">⚠️</span>
                    Avisos de Segurança:
                </h4>
                <ul class="space-y-2">
                    @foreach($emergencyEquipment['safety_warnings'] ?? $emergencyEquipment['limitations'] ?? [
                    'Não usar em pneus run-flat danificados',
                    'Não funciona com furos maiores que 4mm',
                    'Não reparar furos na lateral do pneu',
                    'Não exceder 80 km/h após reparo',
                    'Informar borracheiro sobre uso do selante',
                    'Substituir pneu o mais rápido possível'
                    ] as $warning)
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-3 mt-2"></span>
                        <span class="text-sm text-red-700">{{ $warning }}</span>
                    </li>
                    @endforeach
                </ul>

                @if(!empty($emergencyEquipment['why_no_spare']))
                <div class="mt-6 bg-gray-100 border border-gray-300 rounded-lg p-4">
                    <h5 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <span class="text-lg mr-2">❓</span>
                        Por que este veículo não tem estepe?
                    </h5>
                    <div class="space-y-2">
                        @foreach($emergencyEquipment['why_no_spare'] as $reason)
                        <div class="text-sm text-gray-700">
                            <strong>{{ $reason['title'] }}:</strong> {{ $reason['description'] }}
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Tabela de Pressão para Kit -->
    <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            Pressão para Usar com Kit de Reparo
        </h3>

        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-900">Situação</th>
                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-900">PSI</th>
                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-900">kgf/cm²</th>
                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-900">Observação
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-green-50">
                        <td class="border border-gray-300 px-4 py-2 font-medium">Após usar o kit</td>
                        <td class="border border-gray-300 px-4 py-2 font-bold text-green-700">{{
                            $emergencyEquipment['normal_pressure'] ?? 35 }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ round(($emergencyEquipment['normal_pressure'] ??
                            35) * 0.070307, 2) }}</td>
                        <td class="border border-gray-300 px-4 py-2 text-green-700">Pressão normal do veículo</td>
                    </tr>
                    <tr class="bg-yellow-50">
                        <td class="border border-gray-300 px-4 py-2 font-medium">Emergencial</td>
                        <td class="border border-gray-300 px-4 py-2 font-bold text-yellow-700">25-30</td>
                        <td class="border border-gray-300 px-4 py-2">1,76-2,11</td>
                        <td class="border border-gray-300 px-4 py-2 text-yellow-700">Apenas para chegar à borracharia
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-xs text-gray-600 mt-3">
            <strong>Importante:</strong> Sempre tente atingir a pressão normal do veículo após usar o kit.
            @if($isElectric)
            Em veículos elétricos, pressão baixa reduz drasticamente a autonomia.
            @elseif($isHybrid)
            Em híbridos, pressão incorreta afeta a eficiência do sistema.
            @endif
        </p>
    </div>

    <!-- Assistência Premium (se aplicável) -->
    @if($isPremium && !empty($emergencyEquipment['emergency_contacts']))
    <div class="mt-6 bg-purple-50 border border-purple-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-purple-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
            {{ $emergencyEquipment['emergency_contacts']['service_name'] ?? 'Assistência 24h' }}
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-purple-700 text-sm mb-3">
                    {{ $emergencyEquipment['emergency_contacts']['coverage'] ?? 'Assistência completa em caso de pane.'
                    }}
                </p>
                <p class="text-purple-600 text-xs">
                    <strong>Telefone:</strong> {{ $emergencyEquipment['emergency_contacts']['phone'] ?? 'Consulte manual
                    do proprietário' }}<br>
                    <strong>Disponibilidade:</strong> {{ $emergencyEquipment['emergency_contacts']['availability'] ??
                    '24h' }}
                </p>
            </div>

            @if(!empty($emergencyEquipment['emergency_contacts']['included_services']))
            <div>
                <h4 class="font-semibold text-purple-800 text-sm mb-2">Serviços inclusos:</h4>
                <ul class="text-purple-700 text-sm space-y-1">
                    @foreach($emergencyEquipment['emergency_contacts']['included_services'] as $service)
                    <li class="flex items-center">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full mr-2"></span>
                        {{ $service }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        @if(!empty($emergencyEquipment['emergency_contacts']['app_support']))
        <div class="mt-4 bg-purple-100 border border-purple-300 rounded-lg p-3">
            <p class="text-purple-800 text-xs">
                <strong>📱 App Disponível:</strong> {{ $emergencyEquipment['emergency_contacts']['app_support'] }}
            </p>
        </div>
        @endif
    </div>
    @endif

    <!-- Alerta Final -->
    <div class="mt-6 bg-amber-50 border border-amber-200 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-amber-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd" />
            </svg>
            <div class="text-sm">
                <p class="font-medium text-amber-800 mb-1">🚨 Importante:</p>
                <p class="text-amber-700">
                    O kit de reparo é uma solução temporária para chegar com segurança à borracharia.
                    @if($isElectric)
                    Como este é um veículo elétrico sem estepe, mantenha sempre o kit em perfeitas condições
                    e considere levar um compressor adicional em viagens longas.
                    @elseif($isHybrid)
                    Em veículos híbridos, a ausência do estepe otimiza peso e distribuição para o sistema
                    elétrico/combustão.
                    @else
                    Sempre substitua o pneu reparado o mais rápido possível por um novo.
                    @endif
                </p>
            </div>
        </div>
    </div>
</section>