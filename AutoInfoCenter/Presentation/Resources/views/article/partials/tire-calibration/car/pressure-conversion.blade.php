{{--
Partial: ideal-tire-pressure/shared/pressure-conversion.blade.php
Componente reutilizável para conversão de unidades de pressão
Usado tanto em templates de carros quanto motos
--}}

@php
$unitConversion = $article->getData()['unit_conversion'] ?? [];
$vehicleInfo = $article->getData()['vehicle_info'] ?? [];
$pressureSpecs = $article->getData()['pressure_specifications'] ?? [];

$conversionTable = $unitConversion['conversion_table'] ?? [];
$referenceObservation = $unitConversion['observation'] ?? 'No Brasil, PSI é o padrão usado nos postos de combustível.';

$frontPressurePSI = $pressureSpecs['pressure_empty_front'] ?? 30;
$rearPressurePSI = $pressureSpecs['pressure_empty_rear'] ?? 28;
$frontMaxPressurePSI = $pressureSpecs['pressure_max_front'] ?? $frontPressurePSI;
$rearMaxPressurePSI = $pressureSpecs['pressure_max_rear'] ?? $rearPressurePSI;
$sparePressurePSI = $pressureSpecs['pressure_spare'] ?? 60;

$hasDifferentPressures = $frontPressurePSI !== $rearPressurePSI || $frontMaxPressurePSI !== $rearMaxPressurePSI;

$vehiclePressures = array_unique(array_filter([
    $frontPressurePSI,
    $rearPressurePSI,
    $frontMaxPressurePSI,
    $rearMaxPressurePSI,
    $sparePressurePSI
]));

$allTablePressures = collect($conversionTable)->map(fn($item) => (int) str_replace([' PSI', ' psi'], '', $item['psi']))->toArray();
$carPressures = [$frontPressurePSI, $rearPressurePSI, $frontMaxPressurePSI, $rearMaxPressurePSI];
$allPressures = array_merge($carPressures, $allTablePressures);

$minPressure = !empty($allPressures) ? min($allPressures) - 4 : 22;
$maxPressure = min(38, !empty($allPressures) ? max($allPressures) + 6 : 38);

$pressureRange = range($minPressure, $maxPressure, 2);

foreach ($carPressures as $pressure) {
    if ($pressure <= 38 && !in_array($pressure, $pressureRange)) {
        $pressureRange[] = $pressure;
    }
}
sort($pressureRange);

// Obter categoria do veículo
$vehicleMainCategory = $vehicleInfo['main_category'] ?? 'sedan';

// Mapeamento de categorias para texto legível
$categoryLabels = [
    'hatch' => 'Hatchbacks',
    'sedan' => 'Sedans',
    'suv' => 'SUVs',
    'car_electric' => 'Carros Elétricos',
    'car_sedan' => 'Sedans',
    'car_sports' => 'Esportivos',
    'car_hybrid' => 'Híbridos',
    'car_hatchback' => 'Hatchbacks',
    'van' => 'Vans/Utilitários',
    'minivan' => 'Minivans',
    'pickup' => 'Picapes',
    'suv_hybrid' => 'SUVs Híbridos',
    'suv_electric' => 'SUVs Elétricos',
    'hatch_electric' => 'Hatchbacks Elétricos',
    'sedan_electric' => 'Sedans Elétricos',
];

// Cores por tipo de veículo
$categoryColors = [
    'hatch' => 'text-blue-600',
    'sedan' => 'text-green-600',
    'suv' => 'text-orange-600',
    'car_electric' => 'text-purple-600',
    'car_sedan' => 'text-green-600',
    'car_sports' => 'text-red-600',
    'car_hybrid' => 'text-teal-600',
    'car_hatchback' => 'text-blue-600',
    'van' => 'text-gray-700',
    'minivan' => 'text-gray-600',
    'pickup' => 'text-amber-700',
    'suv_hybrid' => 'text-teal-600',
    'suv_electric' => 'text-purple-600',
    'hatch_electric' => 'text-purple-600',
    'sedan_electric' => 'text-purple-600',
];

// Função para categorizar uso baseado na categoria real do veículo
function getVehicleCategoryDisplay($mainCategory, $categoryLabels, $categoryColors) {
    return [
        'text' => $categoryLabels[$mainCategory] ?? ucfirst(str_replace('_', ' ', $mainCategory)),
        'color' => $categoryColors[$mainCategory] ?? 'text-gray-600'
    ];
}

$conversionFactors = [
    'psi_to_bar' => 0.0689476,
    'psi_to_kpa' => 6.89476,
    'psi_to_kgf' => 0.0703070
];

function getConversionData($psi, $conversionTable, $conversionFactors) {
    $tableData = collect($conversionTable)->firstWhere('psi', $psi) ?? 
                 collect($conversionTable)->firstWhere('psi', $psi . ' PSI');
    
    if ($tableData) {
        return [
            'bar' => str_replace(',', '.', $tableData['bar']),
            'kgf_cm2' => str_replace(',', '.', $tableData['kgf_cm2']),
            'kpa' => round($psi * $conversionFactors['psi_to_kpa']),
            'is_recommended' => $tableData['is_recommended'] ?? false,
            'highlight_class' => $tableData['highlight_class'] ?? ''
        ];
    }
    
    return [
        'bar' => number_format($psi * $conversionFactors['psi_to_bar'], 1),
        'kgf_cm2' => number_format($psi * $conversionFactors['psi_to_kgf'], 2),
        'kpa' => round($psi * $conversionFactors['psi_to_kpa']),
        'is_recommended' => false,
        'highlight_class' => ''
    ];
}
@endphp

<section class="mb-12 hidden md:block">
    <div class="bg-gradient-to-br from-emerald-600 to-green-700 text-white rounded-lg p-8 relative overflow-hidden">
        <!-- Ícone decorativo -->
        <div class="absolute top-4 right-4 text-6xl opacity-20">🔄</div>

        <div class="relative z-10">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold">Conversão de Unidades</h2>
                    <p class="text-green-100 text-sm">
                        Tabela de conversão para diferentes padrões de medida
                    </p>
                </div>
            </div>

            <!-- Pressões do Veículo -->
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 border border-white/20">
                <h3 class="font-semibold text-lg mb-4">🎯 Pressões do {{ $vehicleInfo['full_name'] ?? 'Seu Veículo' }}:</h3>

                @if($hasDifferentPressures)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Pneus Dianteiros -->
                        <div class="space-y-4">
                            <h4 class="font-semibold flex items-center">
                                <span class="text-xl mr-2">🔄</span>Pneus Dianteiros
                            </h4>
                            @php $frontData = getConversionData($frontPressurePSI, $conversionTable, $conversionFactors); @endphp
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-white/20 rounded-lg p-3 text-center">
                                    <div class="text-xs text-green-200 mb-1">PSI (Brasil)</div>
                                    <div class="text-xl font-bold">{{ $frontPressurePSI }}</div>
                                </div>
                                <div class="bg-white/20 rounded-lg p-3 text-center">
                                    <div class="text-xs text-green-200 mb-1">Bar (Europa)</div>
                                    <div class="text-xl font-bold">{{ $frontData['bar'] }}</div>
                                </div>
                                <div class="bg-white/20 rounded-lg p-3 text-center">
                                    <div class="text-xs text-green-200 mb-1">kPa (Técnico)</div>
                                    <div class="text-xl font-bold">{{ $frontData['kpa'] }}</div>
                                </div>
                                <div class="bg-white/20 rounded-lg p-3 text-center">
                                    <div class="text-xs text-green-200 mb-1">kgf/cm² (Antigo)</div>
                                    <div class="text-xl font-bold">{{ $frontData['kgf_cm2'] }}</div>
                                </div>
                            </div>
                            
                            @if($frontMaxPressurePSI !== $frontPressurePSI)
                                <div class="text-sm text-green-200">
                                    Máxima carga: {{ $frontMaxPressurePSI }} PSI ({{ number_format($frontMaxPressurePSI * $conversionFactors['psi_to_bar'], 1) }} Bar)
                                </div>
                            @endif
                        </div>

                        <!-- Pneus Traseiros -->
                        <div class="space-y-4">
                            <h4 class="font-semibold flex items-center">
                                <span class="text-xl mr-2">🔙</span>Pneus Traseiros
                            </h4>
                            @php $rearData = getConversionData($rearPressurePSI, $conversionTable, $conversionFactors); @endphp
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-white/20 rounded-lg p-3 text-center">
                                    <div class="text-xs text-green-200 mb-1">PSI (Brasil)</div>
                                    <div class="text-xl font-bold">{{ $rearPressurePSI }}</div>
                                </div>
                                <div class="bg-white/20 rounded-lg p-3 text-center">
                                    <div class="text-xs text-green-200 mb-1">Bar (Europa)</div>
                                    <div class="text-xl font-bold">{{ $rearData['bar'] }}</div>
                                </div>
                                <div class="bg-white/20 rounded-lg p-3 text-center">
                                    <div class="text-xs text-green-200 mb-1">kPa (Técnico)</div>
                                    <div class="text-xl font-bold">{{ $rearData['kpa'] }}</div>
                                </div>
                                <div class="bg-white/20 rounded-lg p-3 text-center">
                                    <div class="text-xs text-green-200 mb-1">kgf/cm² (Antigo)</div>
                                    <div class="text-xl font-bold">{{ $rearData['kgf_cm2'] }}</div>
                                </div>
                            </div>
                            
                            @if($rearMaxPressurePSI !== $rearPressurePSI)
                                <div class="text-sm text-green-200">
                                    Máxima carga: {{ $rearMaxPressurePSI }} PSI ({{ number_format($rearMaxPressurePSI * $conversionFactors['psi_to_bar'], 1) }} Bar)
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <!-- Pressão Única -->
                    <div class="space-y-4">
                        <h4 class="font-semibold flex items-center mb-4">
                            <span class="text-xl mr-2">🎯</span>Pressão Recomendada
                        </h4>
                        @php $singleData = getConversionData($frontPressurePSI, $conversionTable, $conversionFactors); @endphp
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div class="bg-white/20 rounded-lg p-3 text-center">
                                <div class="text-xs text-green-200 mb-1">PSI (Brasil)</div>
                                <div class="text-xl font-bold">{{ $frontPressurePSI }}</div>
                            </div>
                            <div class="bg-white/20 rounded-lg p-3 text-center">
                                <div class="text-xs text-green-200 mb-1">Bar (Europa)</div>
                                <div class="text-xl font-bold">{{ $singleData['bar'] }}</div>
                            </div>
                            <div class="bg-white/20 rounded-lg p-3 text-center">
                                <div class="text-xs text-green-200 mb-1">kPa (Técnico)</div>
                                <div class="text-xl font-bold">{{ $singleData['kpa'] }}</div>
                            </div>
                            <div class="bg-white/20 rounded-lg p-3 text-center">
                                <div class="text-xs text-green-200 mb-1">kgf/cm² (Antigo)</div>
                                <div class="text-xl font-bold">{{ $singleData['kgf_cm2'] }}</div>
                            </div>
                        </div>
                        
                        @if($frontMaxPressurePSI !== $frontPressurePSI)
                            <div class="text-sm text-green-200 text-center">
                                Máxima carga: {{ $frontMaxPressurePSI }} PSI ({{ number_format($frontMaxPressurePSI * $conversionFactors['psi_to_bar'], 1) }} Bar)
                            </div>
                        @endif
                    </div>
                @endif

                @if($pressureSpecs['pressure_spare'] ?? false)
                    <div class="mt-4 pt-4 border-t border-white/20">
                        <div class="text-sm text-green-200">
                            <strong>Pneu Estepe:</strong> {{ $sparePressurePSI }} PSI ({{ number_format($sparePressurePSI * $conversionFactors['psi_to_bar'], 1) }} Bar)
                        </div>
                    </div>
                @endif

                @if($pressureSpecs['pressure_display'] ?? false)
                    <div class="mt-4 pt-4 border-t border-white/20">
                        <div class="text-sm text-green-200">
                            <strong>Resumo:</strong> {{ $pressureSpecs['pressure_display'] }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Tabela de Conversão Completa -->
    <div class="mt-6 bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2-2z" />
                </svg>
                Tabela de Conversão Completa
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-green-600 text-white">
                        <th class="py-3 px-4 text-left font-medium text-sm">PSI<br><span class="text-xs opacity-75">(Brasil)</span></th>
                        <th class="py-3 px-4 text-center font-medium text-sm">Bar<br><span class="text-xs opacity-75">(Europa)</span></th>
                        <th class="py-3 px-4 text-center font-medium text-sm">kPa<br><span class="text-xs opacity-75">(Técnico)</span></th>
                        <th class="py-3 px-4 text-center font-medium text-sm">kgf/cm²<br><span class="text-xs opacity-75">(Antigo)</span></th>
                        <th class="py-3 px-4 text-center font-medium text-sm">Uso Comum</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($pressureRange as $psi)
                    @php
                        $convData = getConversionData($psi, $conversionTable, $conversionFactors);
                        $isVehiclePressure = in_array($psi, $vehiclePressures);
                        $isRecommended = $convData['is_recommended'];
                        $category = getVehicleCategoryDisplay($vehicleMainCategory, $categoryLabels, $categoryColors);
                        
                        $rowClass = '';
                        $labelText = '';
                        $labelClass = '';
                        
                        if ($psi == $frontPressurePSI || $psi == $rearPressurePSI) {
                            $rowClass = 'bg-green-50 border-green-200';
                            $labelText = 'Recomendado';
                            $labelClass = 'bg-green-600 text-white';
                        } elseif ($psi == $frontMaxPressurePSI || $psi == $rearMaxPressurePSI) {
                            $rowClass = 'bg-purple-50 border-purple-200';
                            $labelText = 'Carga máx';
                            $labelClass = 'bg-purple-600 text-white';
                        } elseif ($isRecommended && $convData['highlight_class']) {
                            $rowClass = 'bg-blue-50 border-blue-200';
                            $labelText = 'Tabela';
                            $labelClass = 'bg-blue-600 text-white';
                        }
                    @endphp
                    <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors {{ $rowClass }}">
                        <td class="py-3 px-4 text-sm font-semibold {{ $isVehiclePressure || $isRecommended ? 'text-gray-800' : 'text-gray-900' }}">
                            {{ $psi }} PSI
                            @if($labelText)
                                <span class="ml-2 text-xs {{ $labelClass }} px-2 py-1 rounded">{{ $labelText }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-sm text-center text-gray-700">{{ $convData['bar'] }}</td>
                        <td class="py-3 px-4 text-sm text-center text-gray-700">{{ $convData['kpa'] }}</td>
                        <td class="py-3 px-4 text-sm text-center text-gray-700">{{ $convData['kgf_cm2'] }}</td>
                        <td class="py-3 px-4 text-sm text-center {{ $category['color'] }}">
                            {{ $category['text'] }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            </table>
        </div>
    </div>

    <!-- Calculadora Rápida -->
    <div class="p-6 bg-gray-50 border rounded-md border-gray-200 mt-4">
        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
            <span class="text-blue-600 mr-2">🧮</span>
            Calculadora de Conversão
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">PSI (Brasil)</label>
                <input type="number"
                    class="pressure-conversion-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    data-from="psi" placeholder="Ex: 30">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">BAR (Europa)</label>
                <div id="bar-result"
                    class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-900">
                    -
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">kgf/cm² (Antigo)</label>
                <div id="kgf-result"
                    class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-900">
                    -
                </div>
            </div>
        </div>
    </div>

    <!-- Informações sobre Unidades -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- PSI -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center mb-2">
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                    <span class="text-white font-bold text-xs">PSI</span>
                </div>
                <h4 class="font-semibold text-blue-800">PSI (Brasil)</h4>
            </div>
            <p class="text-blue-700 text-sm">
                <strong>Pounds per Square Inch</strong><br>
                Padrão brasileiro e americano. Mais comum nos calibradores.
            </p>
        </div>

        <!-- Bar -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center mb-2">
                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3">
                    <span class="text-white font-bold text-xs">Bar</span>
                </div>
                <h4 class="font-semibold text-green-800">Bar (Europa)</h4>
            </div>
            <p class="text-green-700 text-sm">
                <strong>Unidade métrica</strong><br>
                Usado na Europa. 1 Bar ≈ pressão atmosférica ao nível do mar.
            </p>
        </div>

        <!-- kPa -->
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
            <div class="flex items-center mb-2">
                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center mr-3">
                    <span class="text-white font-bold text-xs">kPa</span>
                </div>
                <h4 class="font-semibold text-purple-800">kPa (Técnico)</h4>
            </div>
            <p class="text-purple-700 text-sm">
                <strong>Kilopascal</strong><br>
                Unidade internacional (SI). Usado em manuais técnicos.
            </p>
        </div>

        <!-- kgf/cm² -->
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
            <div class="flex items-center mb-2">
                <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center mr-3">
                    <span class="text-white font-bold text-xs">kg</span>
                </div>
                <h4 class="font-semibold text-orange-800">kgf/cm² (Antigo)</h4>
            </div>
            <p class="text-orange-700 text-sm">
                <strong>Quilograma-força</strong><br>
                Sistema antigo, ainda encontrado em alguns equipamentos.
            </p>
        </div>
    </div>

    <!-- Fórmulas de Conversão -->
    <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 002 2z" />
            </svg>
            Fórmulas de Conversão Rápida
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-semibold text-gray-800 mb-3">🧮 De PSI para outras unidades:</h4>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex justify-between items-center bg-white p-2 rounded">
                        <span>PSI → Bar:</span>
                        <code class="bg-gray-100 px-2 py-1 rounded text-xs">PSI × 0.069</code>
                    </li>
                    <li class="flex justify-between items-center bg-white p-2 rounded">
                        <span>PSI → kPa:</span>
                        <code class="bg-gray-100 px-2 py-1 rounded text-xs">PSI × 6.895</code>
                    </li>
                    <li class="flex justify-between items-center bg-white p-2 rounded">
                        <span>PSI → kgf/cm²:</span>
                        <code class="bg-gray-100 px-2 py-1 rounded text-xs">PSI × 0.0703</code>
                    </li>
                </ul>
            </div>

            <div>
                <h4 class="font-semibold text-gray-800 mb-3">🔄 Para PSI de outras unidades:</h4>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex justify-between items-center bg-white p-2 rounded">
                        <span>Bar → PSI:</span>
                        <code class="bg-gray-100 px-2 py-1 rounded text-xs">Bar × 14.5</code>
                    </li>
                    <li class="flex justify-between items-center bg-white p-2 rounded">
                        <span>kPa → PSI:</span>
                        <code class="bg-gray-100 px-2 py-1 rounded text-xs">kPa × 0.145</code>
                    </li>
                    <li class="flex justify-between items-center bg-white p-2 rounded">
                        <span>kgf/cm² → PSI:</span>
                        <code class="bg-gray-100 px-2 py-1 rounded text-xs">kgf/cm² × 14.22</code>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Observação -->
    @if($referenceObservation)
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                </svg>
                <div class="text-sm">
                    <p class="font-medium text-yellow-800 mb-1">💡 Dica Prática:</p>
                    <p class="text-yellow-700">
                        {{ $referenceObservation }} Lembre-se: <strong>sempre calibre com pneus frios</strong>
                        para medições precisas, independente da unidade usada.
                    </p>
                </div>
            </div>
        </div>
    @endif
</section>