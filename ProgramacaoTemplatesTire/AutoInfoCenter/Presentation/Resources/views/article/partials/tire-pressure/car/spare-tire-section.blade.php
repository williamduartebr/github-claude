{{-- 
Partial: partials/tire-pressure/car/spare-tire-section.blade.php
Seção específica para veículos COM estepe (pressure_spare > 0)
--}}

@php
    $emergencyEquipment = $article->getData()['emergency_equipment'] ?? [];
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $isHybrid = $vehicleInfo['is_hybrid'] ?? false;
    $isPremium = $vehicleInfo['is_premium'] ?? false;
@endphp

<section class="mb-12" id="pneu-estepe">
    <div class="bg-gradient-to-br from-indigo-600 to-purple-700 text-white rounded-lg p-8 relative overflow-hidden">
        <div class="absolute top-4 right-4 text-6xl opacity-20">🛞</div>
        
        <div class="relative z-10">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold">Pneu Estepe</h2>
                    <p class="text-indigo-100 text-sm">
                        @if($isHybrid)
                            🔄 Estepe para sistema híbrido - {{ $vehicleInfo['full_name'] ?? 'veículo' }}
                        @else
                            Informações sobre o pneu sobressalente do {{ $vehicleInfo['full_name'] ?? 'veículo' }}
                        @endif
                    </p>
                </div>
            </div>

            <!-- Dados Principais do Estepe -->
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 border border-white/20">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Pressão -->
                    <div class="text-center">
                        <h3 class="font-semibold text-lg mb-3 flex items-center justify-center">
                            <span class="text-2xl mr-2">🎯</span>
                            Pressão do Estepe
                        </h3>
                        <div class="bg-white/20 rounded-lg p-4">
                            <div class="text-4xl font-bold mb-2">{{ $emergencyEquipment['pressure'] ?? 60 }}</div>
                            <div class="text-indigo-200 text-sm">PSI (libras por pol²)</div>
                        </div>
                    </div>

                    <!-- Tipo -->
                    <div class="text-center">
                        <h3 class="font-semibold text-lg mb-3 flex items-center justify-center">
                            <span class="text-2xl mr-2">⚙️</span>
                            Tipo de Estepe
                        </h3>
                        <div class="bg-white/20 rounded-lg p-4">
                            <div class="text-xl font-bold mb-2">
                                {{ $emergencyEquipment['spare_type_name'] ?? 'Estepe Temporário' }}
                            </div>
                            <div class="text-indigo-200 text-sm">
                                Máx. {{ $emergencyEquipment['max_speed'] ?? 80 }} km/h
                            </div>
                        </div>
                    </div>

                    <!-- Limitações -->
                    <div class="text-center">
                        <h3 class="font-semibold text-lg mb-3 flex items-center justify-center">
                            <span class="text-2xl mr-2">📏</span>
                            Distância Máxima
                        </h3>
                        <div class="bg-white/20 rounded-lg p-4">
                            <div class="text-xl font-bold mb-2">
                                @if(($emergencyEquipment['max_distance'] ?? 80) > 500)
                                    Sem Limite
                                @else
                                    {{ $emergencyEquipment['max_distance'] ?? 80 }} km
                                @endif
                            </div>
                            <div class="text-indigo-200 text-sm">
                                @if($isHybrid)
                                    ⚡ Afeta eficiência híbrida
                                @else
                                    Uso temporário apenas
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($isHybrid)
                <!-- Alerta Especial para Híbridos -->
                <div class="mt-6 bg-yellow-500/20 border border-yellow-400/30 rounded-lg p-4">
                    <h4 class="font-semibold text-yellow-100 mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        🔄 Atenção - Sistema Híbrido:
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-yellow-100">
                        <div>• Estepe temporário afeta distribuição de peso</div>
                        <div>• Sistema híbrido pode ter eficiência reduzida</div>
                        <div>• Calibrar exatamente em {{ $emergencyEquipment['pressure'] ?? 60 }} PSI</div>
                        <div>• Usar apenas para chegar à borracharia</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Procedimentos de Uso do Estepe -->
    <div class="mt-6 bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-6 h-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v6a2 2 0 01-2 2H9V5z"/>
            </svg>
            Verificação e Manutenção do Estepe
        </h3>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Verificação Regular -->
            <div>
                <h4 class="font-semibold text-indigo-800 mb-4 flex items-center">
                    <span class="text-lg mr-2">🔍</span>
                    Verificação {{ $emergencyEquipment['verification_frequency'] ?? 'Mensal' }}:
                </h4>
                <ul class="space-y-3">
                    @foreach($emergencyEquipment['recommendations'] ?? [
                        'Verificar pressão mensalmente',
                        'Inspecionar visualmente a cada 3 meses', 
                        'Verificar fixação e ferramentas',
                        'Limpar área de armazenamento',
                        'Testar macaco e chaves'
                    ] as $index => $recommendation)
                    <li class="flex items-start">
                        <span class="bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-0.5">
                            {{ $index + 1 }}
                        </span>
                        <span class="text-sm text-gray-700">{{ $recommendation }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            <!-- Cuidados de Armazenamento -->
            <div>
                <h4 class="font-semibold text-indigo-800 mb-4 flex items-center">
                    <span class="text-lg mr-2">📦</span>
                    Armazenamento e Cuidados:
                </h4>
                <ul class="space-y-2">
                    @foreach($emergencyEquipment['storage_tips'] ?? [
                        'Evitar exposição ao sol direto',
                        'Não colocar objetos pesados sobre ele',
                        'Manter área seca e ventilada',
                        'Verificar se está bem fixado',
                        'Proteger de produtos químicos'
                    ] as $tip)
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-indigo-500 rounded-full mr-3 mt-2"></span>
                        <span class="text-sm text-gray-700">{{ $tip }}</span>
                    </li>
                    @endforeach
                </ul>

                <div class="mt-6 bg-blue-100 border border-blue-300 rounded-lg p-4">
                    <h5 class="font-semibold text-blue-800 mb-2 flex items-center">
                        <span class="text-lg mr-2">🔄</span>
                        Substituição:
                    </h5>
                    <p class="text-sm text-blue-700">
                        <strong>Intervalo:</strong> {{ $emergencyEquipment['replacement_interval'] ?? '6-8 anos' }}<br>
                        @if($isHybrid)
                        <strong>Atenção Híbrida:</strong> Estepe desbalanceado pode afetar sistemas eletrônicos.
                        @else
                        <strong>Lembrete:</strong> Mesmo sem uso, o pneu envelhece e perde propriedades.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Conversão para Estepe -->
    <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            Conversão de Pressão para o Estepe
        </h3>

        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-900">PSI</th>
                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-900">kgf/cm²</th>
                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-900">Bar</th>
                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-900">Uso</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-yellow-50 border-yellow-200">
                        <td class="border border-gray-300 px-4 py-2 font-bold text-yellow-800">{{ $emergencyEquipment['pressure'] ?? 60 }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ round(($emergencyEquipment['pressure'] ?? 60) * 0.070307, 2) }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ round(($emergencyEquipment['pressure'] ?? 60) * 0.0689476, 2) }}</td>
                        <td class="border border-gray-300 px-4 py-2 text-yellow-800 font-medium">🎯 Pressão do Estepe</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-xs text-gray-600 mt-3">
            <strong>Importante:</strong> A pressão do estepe é sempre maior que dos pneus normais. 
            @if($isHybrid)
            Em veículos híbridos, a pressão correta é ainda mais crítica para não afetar os sistemas eletrônicos.
            @endif
        </p>
    </div>
</section>