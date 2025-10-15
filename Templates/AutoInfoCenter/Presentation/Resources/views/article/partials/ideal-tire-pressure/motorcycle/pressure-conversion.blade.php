{{--
Partial: ideal-tire-pressure/motorcycle/pressure-conversion.blade.php
Conversão de unidades específica para motocicletas
Com valores e exemplos adequados para motos
--}}

@php
$conversionData = $article->getData()['unit_conversion'] ?? [];
$vehicleInfo = $article->getData()['vehicle_info'] ?? [];
@endphp

<section class="mb-12" id="pressure-conversion">
    <div class="bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-t-lg p-6">
        <div class="flex items-center">
            <span class="text-3xl mr-4">🔄</span>
            <div>
                <h2 class="text-2xl font-bold mb-2">
                    Conversão de Unidades para Motos
                </h2>
                <p class="text-green-100 text-sm">
                    Converta pressões entre PSI, BAR e kgf/cm² com valores específicos para motocicletas
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-b-lg border-l border-r border-b border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-4 px-4 text-left text-sm font-semibold text-gray-700">
                            PSI<br><span class="text-xs font-normal">(Brasil)</span>
                        </th>
                        <th class="py-4 px-4 text-center text-sm font-semibold text-gray-700">
                            BAR<br><span class="text-xs font-normal">(Europa)</span>
                        </th>
                        <th class="py-4 px-4 text-center text-sm font-semibold text-gray-700">
                            kPa<br><span class="text-xs font-normal">(Técnico)</span>
                        </th>
                        <th class="py-4 px-4 text-center text-sm font-semibold text-gray-700">
                            kgf/cm²<br><span class="text-xs font-normal">(Antigo)</span>
                        </th>
                        <th class="py-4 px-4 text-center text-sm font-semibold text-gray-700">
                            Uso Comum em Motos
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <!-- 26 PSI -->
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 font-semibold text-gray-900">26 PSI</td>
                        <td class="py-3 px-4 text-center">1.8</td>
                        <td class="py-3 px-4 text-center">179</td>
                        <td class="py-3 px-4 text-center">1.8</td>
                        <td class="py-3 px-4 text-center">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                Motos pequenas (125cc)
                            </span>
                        </td>
                    </tr>

                    <!-- 28 PSI -->
                    <tr class="hover:bg-green-50 bg-green-25">
                        <td class="py-3 px-4 font-semibold text-gray-900">
                            28 PSI
                            <span
                                class="ml-2 inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                Comum
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center font-semibold">1.9</td>
                        <td class="py-3 px-4 text-center font-semibold">193</td>
                        <td class="py-3 px-4 text-center font-semibold">2.0</td>
                        <td class="py-3 px-4 text-center">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                Motos urbanas (150-250cc)
                            </span>
                        </td>
                    </tr>

                    <!-- 30 PSI -->
                    <tr class="hover:bg-green-50 bg-green-25">
                        <td class="py-3 px-4 font-semibold text-gray-900">
                            30 PSI
                            <span
                                class="ml-2 inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                Comum
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center font-semibold">2.1</td>
                        <td class="py-3 px-4 text-center font-semibold">207</td>
                        <td class="py-3 px-4 text-center font-semibold">2.1</td>
                        <td class="py-3 px-4 text-center">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                Motos médias (300-500cc)
                            </span>
                        </td>
                    </tr>

                    <!-- 32 PSI -->
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 font-semibold text-gray-900">32 PSI</td>
                        <td class="py-3 px-4 text-center">2.2</td>
                        <td class="py-3 px-4 text-center">221</td>
                        <td class="py-3 px-4 text-center">2.2</td>
                        <td class="py-3 px-4 text-center">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                Motos médias/grandes
                            </span>
                        </td>
                    </tr>

                    <!-- 34 PSI -->
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 font-semibold text-gray-900">34 PSI</td>
                        <td class="py-3 px-4 text-center">2.3</td>
                        <td class="py-3 px-4 text-center">234</td>
                        <td class="py-3 px-4 text-center">2.4</td>
                        <td class="py-3 px-4 text-center">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                Motos grandes (600cc+)
                            </span>
                        </td>
                    </tr>

                    <!-- 36 PSI -->
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 font-semibold text-gray-900">36 PSI</td>
                        <td class="py-3 px-4 text-center">2.5</td>
                        <td class="py-3 px-4 text-center">248</td>
                        <td class="py-3 px-4 text-center">2.5</td>
                        <td class="py-3 px-4 text-center">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                Touring/Esportivas
                            </span>
                        </td>
                    </tr>

                    <!-- 38 PSI -->
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 font-semibold text-gray-900">38 PSI</td>
                        <td class="py-3 px-4 text-center">2.6</td>
                        <td class="py-3 px-4 text-center">262</td>
                        <td class="py-3 px-4 text-center">2.7</td>
                        <td class="py-3 px-4 text-center">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                Com garupa/bagagem
                            </span>
                        </td>
                    </tr>

                    <!-- 40 PSI -->
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 font-semibold text-gray-900">40 PSI</td>
                        <td class="py-3 px-4 text-center">2.8</td>
                        <td class="py-3 px-4 text-center">276</td>
                        <td class="py-3 px-4 text-center">2.8</td>
                        <td class="py-3 px-4 text-center">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                Carga máxima
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Calculadora Rápida -->
        <div class="p-6 bg-gray-50 border-t border-gray-200">
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">kgf/cm² (Antigo)</label>
                    <div id="kgf-result"
                        class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-900">
                        -
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">BAR (Europa)</label>
                    <div id="bar-result"
                        class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-900">
                        -
                    </div>
                </div>
            </div>
        </div>

        <!-- Dicas Específicas para Motos -->
        <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-t border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <span class="text-blue-600 mr-2">💡</span>
                Dicas de Conversão para Motocicletas
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <h4 class="font-semibold text-blue-800">Fórmulas Rápidas:</h4>
                    <ul class="space-y-2 text-sm text-blue-700">
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2 mt-1">•</span>
                            <span><strong>PSI → BAR:</strong> PSI × 0.069 = BAR</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2 mt-1">•</span>
                            <span><strong>PSI → kgf/cm²:</strong> PSI × 0.070 = kgf/cm²</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2 mt-1">•</span>
                            <span><strong>BAR → PSI:</strong> BAR × 14.5 = PSI</span>
                        </li>
                    </ul>
                </div>
                <div class="space-y-3">
                    <h4 class="font-semibold text-blue-800">Lembre-se:</h4>
                    <ul class="space-y-2 text-sm text-blue-700">
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2 mt-1">✓</span>
                            <span>Motos usam pressões diferentes de carros</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2 mt-1">✓</span>
                            <span>Dianteiro e traseiro têm valores distintos</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2 mt-1">✓</span>
                            <span>No Brasil, PSI é o padrão oficial</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>