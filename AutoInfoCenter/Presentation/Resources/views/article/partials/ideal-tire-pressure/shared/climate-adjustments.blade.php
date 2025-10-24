{{--
Partial: ideal-tire-pressure/shared/climate-adjustments.blade.php
Componente para ajustes de pressão conforme clima e temperatura
Usado tanto em templates de carros quanto motos
--}}

@php
$vehicleInfo = $article->getData()['vehicle_info'] ?? [];
$climateInfo = $article->getData()['climate_adjustments'] ?? [];
@endphp

<section class="mb-12">
    <div class="bg-gradient-to-br from-sky-600 to-blue-700 text-white rounded-lg p-8 relative overflow-hidden">
        <!-- Ícone decorativo -->
        <div class="absolute top-4 right-4 text-6xl opacity-20">
            🌡️
        </div>

        <div class="relative z-10">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold">Ajustes para o Clima Brasileiro</h2>
                    <p class="text-sky-100 text-sm">
                        Como adaptar as pressões às diferentes condições climáticas
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <!-- Calor Intenso -->
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                    <div class="flex items-center mb-3">
                        <span class="text-2xl mr-3">🔥</span>
                        <h3 class="font-semibold">Calor Intenso</h3>
                    </div>
                    <p class="text-sky-100 text-sm">
                        Reduza 2-3 PSI da pressão recomendada
                    </p>
                </div>

                <!-- Frio Extremo -->
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                    <div class="flex items-center mb-3">
                        <span class="text-2xl mr-3">❄️</span>
                        <h3 class="font-semibold">Frio Extremo</h3>
                    </div>
                    <p class="text-sky-100 text-sm">
                        Aumente 2-3 PSI da pressão recomendada
                    </p>
                </div>

                <!-- Chuva -->
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                    <div class="flex items-center mb-3">
                        <span class="text-2xl mr-3">🌧️</span>
                        <h3 class="font-semibold">Época de Chuvas</h3>
                    </div>
                    <p class="text-sky-100 text-sm">
                        Mantenha pressão ideal para melhor aderência
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Ajustes por Temperatura -->
    <div class="mt-6 bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white px-6 py-4">
            <h3 class="text-lg font-bold flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z"
                        clip-rule="evenodd" />
                </svg>
                Ajustes por Temperatura Ambiente
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="py-3 px-4 text-left font-medium text-sm text-gray-700">Temperatura</th>
                        <th class="py-3 px-4 text-center font-medium text-sm text-gray-700">Condição</th>
                        <th class="py-3 px-4 text-center font-medium text-sm text-gray-700">Ajuste Recomendado</th>
                        <th class="py-3 px-4 text-center font-medium text-sm text-gray-700">Explicação</th>
                        <th class="py-3 px-4 text-center font-medium text-sm text-gray-700">Regiões Típicas</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-200 hover:bg-blue-50 transition-colors">
                        <td class="py-4 px-4 text-sm font-semibold text-gray-900">
                            <div class="flex items-center">
                                <span class="text-xl mr-2">🔥</span>
                                Acima de 35°C
                            </div>
                        </td>
                        <td class="py-4 px-4 text-sm text-center">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Calor Extremo
                            </span>
                        </td>
                        <td class="py-4 px-4 text-sm text-center font-bold text-red-600">
                            -3 PSI
                        </td>
                        <td class="py-4 px-4 text-sm text-gray-600">
                            Pneu aquece muito durante condução, pressão sobe naturalmente
                        </td>
                        <td class="py-4 px-4 text-sm text-center text-gray-600">
                            Interior do Nordeste, Centro-Oeste
                        </td>
                    </tr>
                    <tr class="border-b border-gray-200 hover:bg-orange-50 transition-colors">
                        <td class="py-4 px-4 text-sm font-semibold text-gray-900">
                            <div class="flex items-center">
                                <span class="text-xl mr-2">☀️</span>
                                25°C a 35°C
                            </div>
                        </td>
                        <td class="py-4 px-4 text-sm text-center">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                Calor Moderado
                            </span>
                        </td>
                        <td class="py-4 px-4 text-sm text-center font-bold text-orange-600">
                            -1 a -2 PSI
                        </td>
                        <td class="py-4 px-4 text-sm text-gray-600">
                            Ajuste leve para compensar aquecimento do asfalto
                        </td>
                        <td class="py-4 px-4 text-sm text-center text-gray-600">
                            Litoral Nordeste, Rio de Janeiro
                        </td>
                    </tr>
                    <tr class="border-b border-gray-200 hover:bg-green-50 transition-colors">
                        <td class="py-4 px-4 text-sm font-semibold text-gray-900">
                            <div class="flex items-center">
                                <span class="text-xl mr-2">🌤️</span>
                                15°C a 25°C
                            </div>
                        </td>
                        <td class="py-4 px-4 text-sm text-center">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Temperatura Ideal
                            </span>
                        </td>
                        <td class="py-4 px-4 text-sm text-center font-bold text-green-600">
                            Pressão Normal
                        </td>
                        <td class="py-4 px-4 text-sm text-gray-600">
                            Use exatamente as pressões recomendadas da tabela
                        </td>
                        <td class="py-4 px-4 text-sm text-center text-gray-600">
                            São Paulo, Sul do país
                        </td>
                    </tr>
                    <tr class="border-b border-gray-200 hover:bg-blue-50 transition-colors">
                        <td class="py-4 px-4 text-sm font-semibold text-gray-900">
                            <div class="flex items-center">
                                <span class="text-xl mr-2">🌥️</span>
                                5°C a 15°C
                            </div>
                        </td>
                        <td class="py-4 px-4 text-sm text-center">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Frio Moderado
                            </span>
                        </td>
                        <td class="py-4 px-4 text-sm text-center font-bold text-blue-600">
                            +1 a +2 PSI
                        </td>
                        <td class="py-4 px-4 text-sm text-gray-600">
                            Ar frio contrai, pressão diminui naturalmente
                        </td>
                        <td class="py-4 px-4 text-sm text-center text-gray-600">
                            Sul, Inverno no Sudeste
                        </td>
                    </tr>
                    <tr class="border-b border-gray-200 hover:bg-indigo-50 transition-colors">
                        <td class="py-4 px-4 text-sm font-semibold text-gray-900">
                            <div class="flex items-center">
                                <span class="text-xl mr-2">❄️</span>
                                Abaixo de 5°C
                            </div>
                        </td>
                        <td class="py-4 px-4 text-sm text-center">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                Frio Extremo
                            </span>
                        </td>
                        <td class="py-4 px-4 text-sm text-center font-bold text-indigo-600">
                            +3 PSI
                        </td>
                        <td class="py-4 px-4 text-sm text-gray-600">
                            Contração máxima do ar, compensação necessária
                        </td>
                        <td class="py-4 px-4 text-sm text-center text-gray-600">
                            Serra Gaúcha, Campos do Jordão
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Condições Específicas do Brasil -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Época de Chuvas -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                    <span class="text-white text-xl">🌧️</span>
                </div>
                <h3 class="text-lg font-semibold text-blue-900">Época de Chuvas</h3>
            </div>

            <div class="space-y-3">
                <div class="bg-white rounded-lg p-3">
                    <h4 class="font-semibold text-blue-800 text-sm mb-2">✅ Recomendações:</h4>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                            <span>Mantenha pressão exata da tabela</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                            <span>Verifique desenho dos pneus (sulcos)</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                            <span>Pressão baixa = aquaplanagem</span>
                        </li>
                    </ul>
                </div>

                <div class="bg-red-50 rounded-lg p-3 border border-red-200">
                    <h4 class="font-semibold text-red-800 text-sm mb-2">❌ Evite:</h4>
                    <ul class="text-sm text-red-700 space-y-1">
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                            <span>Pressões abaixo do recomendado</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                            <span>Calibrar com pneu molhado</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Viagens Longas -->
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center mr-3">
                    <span class="text-white text-xl">🛣️</span>
                </div>
                <h3 class="text-lg font-semibold text-purple-900">Viagens Longas</h3>
            </div>

            <div class="space-y-3">
                <div class="bg-white rounded-lg p-3">
                    <h4 class="font-semibold text-purple-800 text-sm mb-2">🎯 Estratégia:</h4>
                    <ul class="text-sm text-purple-700 space-y-1">
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-purple-500 rounded-full mr-2 mt-2"></span>
                            <span>Adicione +2 PSI se viagem > 300km</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-purple-500 rounded-full mr-2 mt-2"></span>
                            <span>Considere temperatura do destino</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-purple-500 rounded-full mr-2 mt-2"></span>
                            <span>Pare a cada 2h para verificar</span>
                        </li>
                    </ul>
                </div>

                <div class="bg-yellow-50 rounded-lg p-3 border border-yellow-200">
                    <h4 class="font-semibold text-yellow-800 text-sm mb-2">⚠️ Atenção:</h4>
                    <p class="text-sm text-yellow-700">
                        Em viagens longas pelo Nordeste, reduza 1-2 PSI devido ao calor do asfalto.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerta Importante -->
    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd" />
            </svg>
            <div class="text-sm">
                <p class="font-medium text-yellow-800 mb-1">🇧🇷 Lembre-se:</p>
                <p class="text-yellow-700">
                    O Brasil tem dimensões continentais com climas muito variados. Se você vai viajar entre regiões,
                    ajuste as pressões conforme o destino. Uma viagem de São Paulo para Fortaleza pode exigir
                    redução de 2-3 PSI para o clima nordestino.
                </p>
            </div>
        </div>
    </div>
</section>