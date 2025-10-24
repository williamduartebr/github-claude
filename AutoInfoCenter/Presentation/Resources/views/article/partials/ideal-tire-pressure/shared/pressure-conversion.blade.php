{{--
Partial: ideal-tire-pressure/shared/pressure-conversion.blade.php
Componente reutilizável para conversão de unidades de pressão
Usado tanto em templates de carros quanto motos
--}}

@php
$unitConversion = $article->getData()['unit_conversion'] ?? [];
$mainTireSpec = $article->getData()['tire_specifications_by_version'] ?? null;
$vehicleInfo = $article->getData()['vehicle_info'] ?? [];

// Valores de exemplo para conversão (usa a primeira versão disponível)
$frontPressurePSI = $mainTireSpec['front_normal'] ?? 
    ($vehicleInfo['pressure_specifications']['pressure_empty_front'] ?? 30);
$rearPressurePSI = $mainTireSpec['rear_normal'] ?? 
    ($vehicleInfo['pressure_specifications']['pressure_empty_rear'] ?? 28);

// Converter para inteiro caso venha como string
$frontPressurePSI = (int) str_replace([' PSI', ' psi'], '', $frontPressurePSI);
$rearPressurePSI = (int) str_replace([' PSI', ' psi'], '', $rearPressurePSI);

// Gerar range dinâmico baseado nas pressões do veículo
$minPressure = min(22, $frontPressurePSI - 6, $rearPressurePSI - 6);
$maxPressure = max(50, $frontPressurePSI + 10, $rearPressurePSI + 10);

// Garantir que as pressões do veículo estejam sempre incluídas
$pressureRange = range($minPressure, $maxPressure, 2);
if (!in_array($frontPressurePSI, $pressureRange)) {
    $pressureRange[] = $frontPressurePSI;
}
if (!in_array($rearPressurePSI, $pressureRange)) {
    $pressureRange[] = $rearPressurePSI;
}
sort($pressureRange);

// Função helper para categorizar uso
function getPressureCategory($psi) {
    if ($psi <= 28) return ['text' => 'Carros pequenos', 'color' => 'text-blue-600'];
    if ($psi <= 32) return ['text' => 'Carros médios', 'color' => 'text-green-600'];
    if ($psi <= 38) return ['text' => 'SUVs/Sedãs grandes', 'color' => 'text-orange-600'];
    if ($psi <= 44) return ['text' => 'Carga/Van', 'color' => 'text-red-600'];
    return ['text' => 'Carga pesada', 'color' => 'text-purple-600'];
}

// Fatores de conversão
$conversionFactors = [
    'psi_to_bar' => 0.0689476,
    'psi_to_kpa' => 6.89476,
    'psi_to_kgf' => 0.0703070
];
@endphp

<section class="mb-12">
    <div class="bg-gradient-to-br from-emerald-600 to-green-700 text-white rounded-lg p-8 relative overflow-hidden">
        <!-- Ícone decorativo -->
        <div class="absolute top-4 right-4 text-6xl opacity-20">
            🔄
        </div>

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

            <!-- Conversor Interativo -->
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 border border-white/20">
                <h3 class="font-semibold text-lg mb-4">🎯 Pressões do {{ $vehicleInfo['full_name'] ?? 'Seu Veículo' }}:
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Pneus Dianteiros -->
                    <div class="space-y-4">
                        <h4 class="font-semibold flex items-center">
                            <span class="text-xl mr-2">🔄</span>
                            Pneus Dianteiros
                        </h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-white/20 rounded-lg p-3 text-center">
                                <div class="text-xs text-green-200 mb-1">PSI (Brasil)</div>
                                <div class="text-xl font-bold">{{ $frontPressurePSI }}</div>
                            </div>
                            <div class="bg-white/20 rounded-lg p-3 text-center">
                                <div class="text-xs text-green-200 mb-1">Bar (Europa)</div>
                                <div class="text-xl font-bold">{{ number_format($frontPressurePSI * $conversionFactors['psi_to_bar'], 1) }}</div>
                            </div>
                            <div class="bg-white/20 rounded-lg p-3 text-center">
                                <div class="text-xs text-green-200 mb-1">kPa (Técnico)</div>
                                <div class="text-xl font-bold">{{ round($frontPressurePSI * $conversionFactors['psi_to_kpa']) }}</div>
                            </div>
                            <div class="bg-white/20 rounded-lg p-3 text-center">
                                <div class="text-xs text-green-200 mb-1">kgf/cm² (Antigo)</div>
                                <div class="text-xl font-bold">{{ number_format($frontPressurePSI * $conversionFactors['psi_to_kgf'], 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Pneus Traseiros -->
                    <div class="space-y-4">
                        <h4 class="font-semibold flex items-center">
                            <span class="text-xl mr-2">🔙</span>
                            Pneus Traseiros
                        </h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-white/20 rounded-lg p-3 text-center">
                                <div class="text-xs text-green-200 mb-1">PSI (Brasil)</div>
                                <div class="text-xl font-bold">{{ $rearPressurePSI }}</div>
                            </div>
                            <div class="bg-white/20 rounded-lg p-3 text-center">
                                <div class="text-xs text-green-200 mb-1">Bar (Europa)</div>
                                <div class="text-xl font-bold">{{ number_format($rearPressurePSI * $conversionFactors['psi_to_bar'], 1) }}</div>
                            </div>
                            <div class="bg-white/20 rounded-lg p-3 text-center">
                                <div class="text-xs text-green-200 mb-1">kPa (Técnico)</div>
                                <div class="text-xl font-bold">{{ round($rearPressurePSI * $conversionFactors['psi_to_kpa']) }}</div>
                            </div>
                            <div class="bg-white/20 rounded-lg p-3 text-center">
                                <div class="text-xs text-green-200 mb-1">kgf/cm² (Antigo)</div>
                                <div class="text-xl font-bold">{{ number_format($rearPressurePSI * $conversionFactors['psi_to_kgf'], 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Conversão Geral -->
    <div class="mt-6 bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2 2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Tabela de Conversão Completa
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-green-600 text-white">
                        <th class="py-3 px-4 text-left font-medium text-sm">PSI<br><span
                                class="text-xs opacity-75">(Brasil)</span></th>
                        <th class="py-3 px-4 text-center font-medium text-sm">Bar<br><span
                                class="text-xs opacity-75">(Europa)</span></th>
                        <th class="py-3 px-4 text-center font-medium text-sm">kPa<br><span
                                class="text-xs opacity-75">(Técnico)</span></th>
                        <th class="py-3 px-4 text-center font-medium text-sm">kgf/cm²<br><span
                                class="text-xs opacity-75">(Antigo)</span></th>
                        <th class="py-3 px-4 text-center font-medium text-sm">Uso Comum</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pressureRange as $psi)
                    @php
                        $isVehiclePressure = ($psi == $frontPressurePSI || $psi == $rearPressurePSI);
                        $category = getPressureCategory($psi);
                    @endphp
                    <tr
                        class="border-b border-gray-200 hover:bg-gray-50 transition-colors {{ $isVehiclePressure ? 'bg-green-50 border-green-200' : '' }}">
                        <td
                            class="py-3 px-4 text-sm font-semibold {{ $isVehiclePressure ? 'text-green-800' : 'text-gray-900' }}">
                            {{ $psi }} PSI
                            @if($isVehiclePressure)
                            <span class="ml-2 text-xs bg-green-600 text-white px-2 py-1 rounded">Seu carro</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-sm text-center text-gray-700">{{ number_format($psi * $conversionFactors['psi_to_bar'], 1) }}</td>
                        <td class="py-3 px-4 text-sm text-center text-gray-700">{{ round($psi * $conversionFactors['psi_to_kpa']) }}</td>
                        <td class="py-3 px-4 text-sm text-center text-gray-700">{{ number_format($psi * $conversionFactors['psi_to_kgf'], 2) }}</td>
                        <td class="py-3 px-4 text-sm text-center {{ $category['color'] }}">
                            {{ $category['text'] }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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

    <!-- Calculadora Rápida -->
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

    <!-- Dica Prática -->
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
                    No Brasil, a maioria dos calibradores usa PSI. Se encontrar um equipamento em Bar ou kPa,
                    use a tabela acima para converter. Lembre-se: <strong>sempre calibre com pneus frios</strong>
                    para medições precisas, independente da unidade usada.
                </p>
            </div>
        </div>
    </div>
</section>