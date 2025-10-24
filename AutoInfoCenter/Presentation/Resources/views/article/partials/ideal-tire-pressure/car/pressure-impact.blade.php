{{--
Partial: ideal-tire-pressure/car/pressure-impact.blade.php
Seção sobre como a pressão dos pneus impacta diferentes aspectos do desempenho
--}}

@php
$pressureImpact = $article->getData()['pressure_impact'] ?? [];
$vehicleInfo = $article->getData()['vehicle_info'] ?? [];
@endphp

@if(!empty($pressureImpact))
<section class="mb-12">
    <div class="bg-gradient-to-br from-red-600 to-pink-700 text-white rounded-lg p-8 relative overflow-hidden">
        <!-- Ícone decorativo -->
        <div class="absolute top-4 right-4 text-6xl opacity-20">
            📊
        </div>

        <div class="relative z-10">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold">Impacto no Desempenho</h2>
                    <p class="text-red-100 text-sm">
                        Como a pressão dos pneus afeta segurança, economia e dirigibilidade
                    </p>
                </div>
            </div>

            <!-- Resumo dos Impactos -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20 text-center">
                    <div class="text-3xl mb-2">🛡️</div>
                    <h3 class="font-semibold">Segurança</h3>
                    <p class="text-red-100 text-sm">Aderência e frenagem</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20 text-center">
                    <div class="text-3xl mb-2">⛽</div>
                    <h3 class="font-semibold">Economia</h3>
                    <p class="text-red-100 text-sm">Consumo de combustível</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20 text-center">
                    <div class="text-3xl mb-2">🏁</div>
                    <h3 class="font-semibold">Performance</h3>
                    <p class="text-red-100 text-sm">Dirigibilidade e estabilidade</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20 text-center">
                    <div class="text-3xl mb-2">😌</div>
                    <h3 class="font-semibold">Conforto</h3>
                    <p class="text-red-100 text-sm">Suavidade e ruído</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparação de Pressões -->
    <div class="mt-6 bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                Comparação: Pressão Baixa vs Ideal vs Alta
            </h3>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Pressão Baixa -->
                <div class="border border-red-200 rounded-lg p-6 bg-red-50">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center mr-3">
                            <span class="text-white font-bold">↓</span>
                        </div>
                        <h4 class="font-semibold text-red-800">Pressão Baixa</h4>
                    </div>

                    @foreach($pressureImpact as $category => $impacts)
                    @if($category === 'safety')
                    <div class="mb-4">
                        <h5 class="font-semibold text-red-700 text-sm mb-2">🛡️ Segurança:</h5>
                        <ul class="space-y-1 text-xs text-red-600">
                            <li>• Distância de frenagem aumenta {{ $impacts['low_pressure']['braking_distance'] ??
                                '+15%' }}</li>
                            <li>• Aderência reduzida {{ $impacts['low_pressure']['grip'] ?? '-25%' }}</li>
                            <li>• Risco de aquaplanagem maior</li>
                            <li>• Possibilidade de estouro do pneu</li>
                        </ul>
                    </div>
                    @endif

                    @if($category === 'economy')
                    <div class="mb-4">
                        <h5 class="font-semibold text-red-700 text-sm mb-2">⛽ Economia:</h5>
                        <ul class="space-y-1 text-xs text-red-600">
                            <li>• Consumo aumenta {{ $impacts['low_pressure']['fuel_consumption'] ?? '+10%' }}</li>
                            <li>• Vida útil do pneu reduz {{ $impacts['low_pressure']['tire_life'] ?? '-30%' }}</li>
                            <li>• Desgaste irregular nas bordas</li>
                        </ul>
                    </div>
                    @endif

                    @if($category === 'performance')
                    <div class="mb-4">
                        <h5 class="font-semibold text-red-700 text-sm mb-2">🏁 Performance:</h5>
                        <ul class="space-y-1 text-xs text-red-600">
                            <li>• Dirigibilidade comprometida</li>
                            <li>• Resposta lenta da direção</li>
                            <li>• Instabilidade em curvas</li>
                        </ul>
                    </div>
                    @endif

                    @if($category === 'comfort')
                    <div>
                        <h5 class="font-semibold text-red-700 text-sm mb-2">😌 Conforto:</h5>
                        <ul class="space-y-1 text-xs text-red-600">
                            <li>• Mais ruído de rodagem</li>
                            <li>• Sensação de "pneu mole"</li>
                            <li>• Maior vibração</li>
                        </ul>
                    </div>
                    @endif
                    @endforeach
                </div>

                <!-- Pressão Ideal -->
                <div class="border border-green-200 rounded-lg p-6 bg-green-50">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mr-3">
                            <span class="text-white font-bold">✓</span>
                        </div>
                        <h4 class="font-semibold text-green-800">Pressão Ideal</h4>
                    </div>

                    <div class="mb-4">
                        <h5 class="font-semibold text-green-700 text-sm mb-2">🛡️ Segurança:</h5>
                        <ul class="space-y-1 text-xs text-green-600">
                            <li>• Máxima aderência ao solo</li>
                            <li>• Frenagem otimizada</li>
                            <li>• Estabilidade em curvas</li>
                            <li>• Controle total do veículo</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h5 class="font-semibold text-green-700 text-sm mb-2">⛽ Economia:</h5>
                        <ul class="space-y-1 text-xs text-green-600">
                            <li>• Menor consumo de combustível</li>
                            <li>• Vida útil máxima dos pneus</li>
                            <li>• Desgaste uniforme</li>
                            <li>• Menor resistência ao rolamento</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h5 class="font-semibold text-green-700 text-sm mb-2">🏁 Performance:</h5>
                        <ul class="space-y-1 text-xs text-green-600">
                            <li>• Dirigibilidade precisa</li>
                            <li>• Resposta imediata</li>
                            <li>• Estabilidade perfeita</li>
                        </ul>
                    </div>

                    <div>
                        <h5 class="font-semibold text-green-700 text-sm mb-2">😌 Conforto:</h5>
                        <ul class="space-y-1 text-xs text-green-600">
                            <li>• Ruído mínimo</li>
                            <li>• Conforto balanceado</li>
                            <li>• Absorção adequada</li>
                        </ul>
                    </div>
                </div>

                <!-- Pressão Alta -->
                <div class="border border-yellow-200 rounded-lg p-6 bg-yellow-50">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center mr-3">
                            <span class="text-white font-bold">↑</span>
                        </div>
                        <h4 class="font-semibold text-yellow-800">Pressão Alta</h4>
                    </div>

                    @foreach($pressureImpact as $category => $impacts)
                    @if($category === 'safety')
                    <div class="mb-4">
                        <h5 class="font-semibold text-yellow-700 text-sm mb-2">🛡️ Segurança:</h5>
                        <ul class="space-y-1 text-xs text-yellow-600">
                            <li>• Área de contato reduzida {{ $impacts['high_pressure']['contact_area'] ?? '-20%' }}
                            </li>
                            <li>• Aderência comprometida em piso molhado</li>
                            <li>• Maior rigidez lateral</li>
                            <li>• Sensibilidade a irregularidades</li>
                        </ul>
                    </div>
                    @endif

                    @if($category === 'economy')
                    <div class="mb-4">
                        <h5 class="font-semibold text-yellow-700 text-sm mb-2">⛽ Economia:</h5>
                        <ul class="space-y-1 text-xs text-yellow-600">
                            <li>• Consumo ligeiramente menor {{ $impacts['high_pressure']['fuel_consumption'] ?? '-2%'
                                }}</li>
                            <li>• Desgaste no centro do pneu</li>
                            <li>• Vida útil reduzida {{ $impacts['high_pressure']['tire_life'] ?? '-15%' }}</li>
                        </ul>
                    </div>
                    @endif

                    @if($category === 'performance')
                    <div class="mb-4">
                        <h5 class="font-semibold text-yellow-700 text-sm mb-2">🏁 Performance:</h5>
                        <ul class="space-y-1 text-xs text-yellow-600">
                            <li>• Direção mais "nervosa"</li>
                            <li>• Menor aderência lateral</li>
                            <li>• Resposta mais abrupta</li>
                        </ul>
                    </div>
                    @endif

                    @if($category === 'comfort')
                    <div>
                        <h5 class="font-semibold text-yellow-700 text-sm mb-2">😌 Conforto:</h5>
                        <ul class="space-y-1 text-xs text-yellow-600">
                            <li>• Conforto reduzido</li>
                            <li>• Mais impactos sentidos</li>
                            <li>• Suspensão mais rígida</li>
                        </ul>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos de Impacto -->
    <div class="mt-6 bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Impactos Quantificados
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Consumo de Combustível -->
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <h4 class="font-semibold text-gray-800 mb-3 text-center">⛽ Consumo de Combustível</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-red-600">-5 PSI</span>
                        <div class="flex-1 mx-2 bg-gray-200 rounded-full h-2">
                            <div class="bg-red-500 h-2 rounded-full" style="width: 110%"></div>
                        </div>
                        <span class="text-xs text-red-600">+10%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-green-600">Ideal</span>
                        <div class="flex-1 mx-2 bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                        <span class="text-xs text-green-600">100%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-yellow-600">+5 PSI</span>
                        <div class="flex-1 mx-2 bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: 98%"></div>
                        </div>
                        <span class="text-xs text-yellow-600">-2%</span>
                    </div>
                </div>
            </div>

            <!-- Vida Útil dos Pneus -->
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <h4 class="font-semibold text-gray-800 mb-3 text-center">🛞 Vida Útil dos Pneus</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-red-600">-5 PSI</span>
                        <div class="flex-1 mx-2 bg-gray-200 rounded-full h-2">
                            <div class="bg-red-500 h-2 rounded-full" style="width: 70%"></div>
                        </div>
                        <span class="text-xs text-red-600">-30%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-green-600">Ideal</span>
                        <div class="flex-1 mx-2 bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                        <span class="text-xs text-green-600">100%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-yellow-600">+5 PSI</span>
                        <div class="flex-1 mx-2 bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: 85%"></div>
                        </div>
                        <span class="text-xs text-yellow-600">-15%</span>
                    </div>
                </div>
            </div>

            <!-- Distância de Frenagem -->
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <h4 class="font-semibold text-gray-800 mb-3 text-center">🛑 Distância de Frenagem</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-red-600">-5 PSI</span>
                        <div class="flex-1 mx-2 bg-gray-200 rounded-full h-2">
                            <div class="bg-red-500 h-2 rounded-full" style="width: 115%"></div>
                        </div>
                        <span class="text-xs text-red-600">+15%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-green-600">Ideal</span>
                        <div class="flex-1 mx-2 bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                        <span class="text-xs text-green-600">100%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-yellow-600">+5 PSI</span>
                        <div class="flex-1 mx-2 bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: 108%"></div>
                        </div>
                        <span class="text-xs text-yellow-600">+8%</span>
                    </div>
                </div>
            </div>

            <!-- Conforto de Rodagem -->
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <h4 class="font-semibold text-gray-800 mb-3 text-center">😌 Conforto de Rodagem</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-red-600">-5 PSI</span>
                        <div class="flex-1 mx-2 bg-gray-200 rounded-full h-2">
                            <div class="bg-red-500 h-2 rounded-full" style="width: 85%"></div>
                        </div>
                        <span class="text-xs text-red-600">-15%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-green-600">Ideal</span>
                        <div class="flex-1 mx-2 bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                        <span class="text-xs text-green-600">100%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-yellow-600">+5 PSI</span>
                        <div class="flex-1 mx-2 bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: 75%"></div>
                        </div>
                        <span class="text-xs text-yellow-600">-25%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Situações Específicas -->
    <div class="mt-6 bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Impactos em Situações Específicas
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Condições de Chuva -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-semibold text-blue-800 mb-3 flex items-center">
                    <span class="text-lg mr-2">🌧️</span>
                    Condições de Chuva
                </h4>
                <div class="space-y-3">
                    <div class="bg-white rounded-lg p-3">
                        <h5 class="font-semibold text-blue-700 text-sm mb-2">Pressão Baixa:</h5>
                        <ul class="text-xs text-blue-600 space-y-1">
                            <li>• Maior risco de aquaplanagem</li>
                            <li>• Dificuldade para drenar água</li>
                            <li>• Aderência lateral comprometida</li>
                        </ul>
                    </div>
                    <div class="bg-white rounded-lg p-3">
                        <h5 class="font-semibold text-blue-700 text-sm mb-2">Pressão Ideal:</h5>
                        <ul class="text-xs text-blue-600 space-y-1">
                            <li>• Máxima eficiência dos sulcos</li>
                            <li>• Contato otimizado com o solo</li>
                            <li>• Melhor controle em curvas</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Viagens Longas -->
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                <h4 class="font-semibold text-orange-800 mb-3 flex items-center">
                    <span class="text-lg mr-2">🛣️</span>
                    Viagens Longas
                </h4>
                <div class="space-y-3">
                    <div class="bg-white rounded-lg p-3">
                        <h5 class="font-semibold text-orange-700 text-sm mb-2">Pressão Baixa:</h5>
                        <ul class="text-xs text-orange-600 space-y-1">
                            <li>• Aquecimento excessivo do pneu</li>
                            <li>• Risco de estouro aumentado</li>
                            <li>• Consumo elevado de combustível</li>
                        </ul>
                    </div>
                    <div class="bg-white rounded-lg p-3">
                        <h5 class="font-semibold text-orange-700 text-sm mb-2">Pressão +2 PSI:</h5>
                        <ul class="text-xs text-orange-600 space-y-1">
                            <li>• Menor aquecimento</li>
                            <li>• Melhor estabilidade em alta velocidade</li>
                            <li>• Economia de combustível otimizada</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($vehicleInfo['is_electric'] ?? false)
    <!-- Impacto Específico para Veículos Elétricos -->
    <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-green-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.477.859h4z" />
            </svg>
            Impacto na Autonomia Elétrica
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg p-4 border border-red-200">
                <h4 class="font-semibold text-red-800 text-sm mb-2">Pressão -5 PSI:</h4>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600 mb-1">-8%</div>
                    <div class="text-xs text-red-600">Autonomia reduzida</div>
                    <div class="text-xs text-gray-600 mt-2">400km → 368km</div>
                </div>
            </div>
            <div class="bg-white rounded-lg p-4 border border-green-200">
                <h4 class="font-semibold text-green-800 text-sm mb-2">Pressão Ideal:</h4>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600 mb-1">100%</div>
                    <div class="text-xs text-green-600">Autonomia máxima</div>
                    <div class="text-xs text-gray-600 mt-2">400km completos</div>
                </div>
            </div>
            <div class="bg-white rounded-lg p-4 border border-blue-200">
                <h4 class="font-semibold text-blue-800 text-sm mb-2">Pressão +3 PSI:</h4>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600 mb-1">+3%</div>
                    <div class="text-xs text-blue-600">Ligeiro ganho</div>
                    <div class="text-xs text-gray-600 mt-2">400km → 412km</div>
                </div>
            </div>
        </div>

        <div class="mt-4 bg-yellow-100 border border-yellow-300 rounded-lg p-3">
            <p class="text-yellow-800 text-sm">
                <strong>⚠️ Atenção:</strong> Em veículos elétricos, cada quilômetro de autonomia é valioso.
                Manter pressões ideais pode significar a diferença entre chegar ao destino ou precisar recarregar.
            </p>
        </div>
    </div>
    @endif

    <!-- Resumo e Recomendações -->
    <div class="mt-6 bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-indigo-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Resumo e Recomendações
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-semibold text-indigo-800 mb-3">✅ Para Máximo Benefício:</h4>
                <ul class="space-y-2 text-sm text-indigo-700">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-indigo-500 rounded-full mr-2 mt-2"></span>
                        <span>Mantenha sempre a pressão recomendada pelo fabricante</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-indigo-500 rounded-full mr-2 mt-2"></span>
                        <span>Verifique mensalmente com pneus frios</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-indigo-500 rounded-full mr-2 mt-2"></span>
                        <span>Ajuste conforme carga e condições climáticas</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-indigo-500 rounded-full mr-2 mt-2"></span>
                        <span>Use calibrador de qualidade certificado</span>
                    </li>
                </ul>
            </div>

            <div>
                <h4 class="font-semibold text-indigo-800 mb-3">📊 Economia Anual Estimada:</h4>
                <div class="bg-white rounded-lg p-4 border border-indigo-200">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Economia de combustível:</span>
                            <span class="font-semibold text-indigo-600">R$ 600-900/ano</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Vida útil dos pneus:</span>
                            <span class="font-semibold text-indigo-600">+25% duração</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Manutenção evitada:</span>
                            <span class="font-semibold text-indigo-600">R$ 200-400/ano</span>
                        </div>
                        <hr class="border-indigo-200">
                        <div class="flex justify-between text-base font-bold">
                            <span class="text-gray-800">Total estimado:</span>
                            <span class="text-indigo-600">R$ 800-1.300/ano</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif