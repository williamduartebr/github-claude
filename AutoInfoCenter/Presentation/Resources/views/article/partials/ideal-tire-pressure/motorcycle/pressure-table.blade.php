{{--
Partial: ideal-tire-pressure/motorcycle/pressure-table.blade.php
Tabela principal de pressões para motocicletas (apenas o piloto vs com garupa)
Focado nas características específicas de duas rodas
--}}

@php
$pressureTable = $article->getData()['pressure_table'] ?? [];
$vehicleInfo = $article->getData()['vehicle_info'] ?? [];
$pressureSpecs = $article->getData()['pressure_specifications'] ?? [];

$officialPressures = $pressureTable['official_pressures'] ?? [];
$conditionalAdjustments = $pressureTable['conditional_adjustments'] ?? [];
$specialConditions = $pressureTable['special_conditions'] ?? [];
@endphp

@if(!empty($officialPressures) || !empty($pressureSpecs) || !empty($specialConditions))
<section class="mb-12" id="pressure-table">
    <div class="bg-gradient-to-r from-[#DC2626] to-red-700 text-white rounded-t-lg p-6">
        <div class="flex items-center">
            <span class="text-3xl mr-4">🎯</span>
            <div>
                <h2 class="text-2xl font-bold mb-2">
                    Tabela de Pressões por Situação
                </h2>
                <p class="text-red-100 text-sm">
                    Apenas o Piloto vs. com garupa - Pressões críticas para sua segurança
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-b-lg border-l border-r border-b border-gray-200">
        <!-- Tabela Principal -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-4 px-6 text-left text-sm font-semibold text-gray-700">
                            Situação de Uso
                        </th>
                        <th class="py-4 px-4 text-center text-sm font-semibold text-gray-700">
                            <div class="flex flex-col items-center">
                                <span class="text-green-600">🔄</span>
                                <span>Pneu Dianteiro</span>
                            </div>
                        </th>
                        <th class="py-4 px-4 text-center text-sm font-semibold text-gray-700">
                            <div class="flex flex-col items-center">
                                <span class="text-blue-600">🔄</span>
                                <span>Pneu Traseiro</span>
                            </div>
                        </th>
                        <th class="py-4 px-4 text-center text-sm font-semibold text-gray-700">
                            Observações
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <!-- Pressões Oficiais -->
                    @foreach($officialPressures as $condition => $pressureData)
                    @if($pressureData['condition'] === 'piloto_solo')
                    <tr class="hover:bg-green-50 transition-colors duration-200">
                        <td class="py-6 px-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                    <span class="text-2xl">🏍️</span>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Apenas o Piloto</h3>
                                    <p class="text-sm text-gray-600">Uso urbano e rodoviário normal</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-6 px-4 text-center">
                            <div
                                class="inline-flex items-center px-4 py-2 rounded-full text-lg font-bold bg-green-100 text-green-800">
                                {{ $pressureData['front'] }}
                            </div>
                        </td>
                        <td class="py-6 px-4 text-center">
                            <div
                                class="inline-flex items-center px-4 py-2 rounded-full text-lg font-bold bg-green-100 text-green-800">
                                {{ $pressureData['rear'] }}
                            </div>
                        </td>
                        <td class="py-6 px-4">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 mb-1 block">
                                Configuração padrão
                            </span>
                            @if(!empty($pressureData['observation']))
                            <p class="text-xs text-gray-600">{{ $pressureData['observation'] }}</p>
                            @endif
                        </td>
                    </tr>
                    @endif

                    @if($pressureData['condition'] === 'piloto_garupa')
                    <tr class="hover:bg-blue-50 transition-colors duration-200 bg-blue-25">
                        <td class="py-6 px-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                    <span class="text-2xl">👥</span>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Com Garupa</h3>
                                    <p class="text-sm text-gray-600">Dois ocupantes + possível bagagem</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-6 px-4 text-center">
                            <div
                                class="inline-flex items-center px-4 py-2 rounded-full text-lg font-bold bg-blue-100 text-blue-800">
                                {{ $pressureData['front'] }}
                            </div>
                        </td>
                        <td class="py-6 px-4 text-center">
                            <div
                                class="inline-flex items-center px-4 py-2 rounded-full text-lg font-bold bg-blue-100 text-blue-800">
                                {{ $pressureData['rear'] }}
                            </div>
                        </td>
                        <td class="py-6 px-4">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-orange-100 text-orange-800 mb-1 ">
                                Pressão elevada no traseiro
                            </span>
                            @if(!empty($pressureData['observation']))
                            <p class="text-xs text-gray-600">{{ $pressureData['observation'] }}</p>
                            @endif
                        </td>
                    </tr>
                    @endif
                    @endforeach

                    <!-- Condições Especiais -->
                    @foreach($specialConditions as $condition)
                    @php
                    $iconMap = [
                    'home' => '🏠',
                    'map' => '🗺️',
                    'user' => '👤',
                    'package' => '📦',
                    'cloud-rain' => '🌧️'
                    ];
                    $icon = $iconMap[$condition['icon_class'] ?? 'gear'] ?? '⚙️';

                    $colorClasses = [
                    'home' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'hover' => 'hover:bg-gray-50'],
                    'map' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'hover' => 'hover:bg-purple-50'],
                    'user' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-800', 'hover' => 'hover:bg-indigo-50'],
                    'package' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'hover' =>
                    'hover:bg-orange-50'],
                    'cloud-rain' => ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-800', 'hover' => 'hover:bg-cyan-50']
                    ];
                    $colors = $colorClasses[$condition['icon_class'] ?? 'gear'] ?? ['bg' => 'bg-gray-100', 'text' =>
                    'text-gray-800', 'hover' => 'hover:bg-gray-50'];
                    @endphp
                    <tr class="{{ $colors['hover'] }} transition-colors duration-200">
                        <td class="py-6 px-6">
                            <div class="flex items-center">
                                <div
                                    class="w-12 h-12 {{ $colors['bg'] }} rounded-lg flex items-center justify-center mr-4">
                                    <span class="text-2xl">{{ $icon }}</span>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $condition['situation'] }}</h3>
                                    <p class="text-sm text-gray-600">{{ $condition['terrain'] }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-6 px-4 text-center">
                            <div
                                class="inline-flex items-center px-4 py-2 rounded-full text-lg font-bold {{ $colors['bg'] }} {{ $colors['text'] }}">
                                {{ $condition['front_pressure'] }}
                            </div>
                        </td>
                        <td class="py-6 px-4 text-center">
                            <div
                                class="inline-flex items-center px-4 py-2 rounded-full text-lg font-bold {{ $colors['bg'] }} {{ $colors['text'] }}">
                                {{ $condition['rear_pressure'] }}
                            </div>
                        </td>
                        <td class="py-6 px-4">
                            @if(!empty($condition['ideal_temperature']))
                            <span
                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $colors['bg'] }} {{ $colors['text'] }} mb-1 block">
                                {{ $condition['ideal_temperature'] }}
                            </span>
                            @endif
                            @if(!empty($condition['observation']))
                            <p class="text-xs text-gray-600">{{ $condition['observation'] }}</p>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Seção de Informações Críticas -->
        <div class="bg-gradient-to-r from-red-50 to-orange-50 border-t border-gray-200 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Diferenças Fundamentais -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <span class="text-red-500 mr-2">⚠️</span>
                        Por que a Diferença?
                    </h3>
                    <div class="space-y-3 text-sm text-gray-700">
                        <div class="flex items-start">
                            <div class="w-1 h-1 bg-red-500 rounded-full mt-2 mr-3 flex-shrink-0"></div>
                            <span><strong>Centro de gravidade:</strong> O garupa altera drasticamente o equilíbrio da
                                moto</span>
                        </div>
                        <div class="flex items-start">
                            <div class="w-1 h-1 bg-red-500 rounded-full mt-2 mr-3 flex-shrink-0"></div>
                            <span><strong>Distribuição de peso:</strong> Mais peso no traseiro exige maior
                                pressão</span>
                        </div>
                        <div class="flex items-start">
                            <div class="w-1 h-1 bg-red-500 rounded-full mt-2 mr-3 flex-shrink-0"></div>
                            <span><strong>Estabilidade:</strong> Pressões incorretas podem causar oscilações
                                perigosas</span>
                        </div>
                    </div>
                </div>

                <!-- Regra de Ouro -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <span class="text-yellow-500 mr-2">🏆</span>
                        Regra de Ouro
                    </h3>
                    <div class="bg-white rounded-lg p-4 border border-yellow-200">
                        <p class="text-sm text-gray-700 font-medium mb-2">
                            "Sempre calibre ANTES de sair, com pneus frios"
                        </p>
                        <div class="space-y-2 text-xs text-gray-600">
                            <div class="flex items-center">
                                <span class="text-green-500 mr-2">✓</span>
                                <span>Verifique a cada 15 dias no mínimo</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-green-500 mr-2">✓</span>
                                <span>Ajuste conforme o peso total (piloto + garupa + bagagem)</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-green-500 mr-2">✓</span>
                                <span>Nunca calibre com pneus quentes (após rodar)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conversão Rápida -->
        <div class="bg-gray-50 border-t border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-semibold text-gray-700">Conversão Rápida:</h4>
                <div class="flex space-x-4 text-xs text-gray-600">
                    <span><strong>PSI × 0.069 = BAR</strong></span>
                    <span><strong>PSI × 0.070 = kgf/cm²</strong></span>
                </div>
            </div>
        </div>
    </div>
</section>
@endif