{{--
Partial: ideal-tire-pressure/car/special-conditions.blade.php
Seção sobre condições especiais de uso e ajustes necessários
--}}

@php
$specialConditions = $article->getData()['special_conditions'] ?? [];
$vehicleInfo = $article->getData()['vehicle_info'] ?? [];
@endphp

@if(!empty($specialConditions))
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200 flex items-center">
        <span class="text-2xl mr-3">⚙️</span>
        Ajustes para Condições Especiais
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($specialConditions as $condition)
        <div class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-lg transition-shadow">
            <!-- Cabeçalho da Condição -->
            <div class="flex items-center mb-4">
                <div
                    class="w-12 h-12 rounded-full bg-gradient-to-br from-[#0E368A]/10 to-[#0E368A]/20 flex items-center justify-center mr-4">
                    @switch($condition['icon_class'] ?? 'default')
                    @case('trending-up')
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    @break
                    @case('package')
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    @break
                    @case('dollar-sign')
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    @break
                    @case('road')
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    @break
                    @case('sun')
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    @break
                    @case('alert-triangle')
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    @break
                    @default
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    @endswitch
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $condition['title'] ?? $condition['condition'] ??
                        'Condição Especial' }}</h3>
                    @if(!empty($condition['subtitle']))
                    <p class="text-sm text-gray-600">{{ $condition['subtitle'] }}</p>
                    @endif
                </div>
            </div>

            <!-- Descrição da Condição -->
            @if(!empty($condition['description']))
            <div class="mb-4">
                <p class="text-gray-700 text-sm leading-relaxed">{{ $condition['description'] }}</p>
            </div>
            @endif

            <!-- Pressões Recomendadas -->
            <div class="mb-4">
                @if(!empty($condition['front_pressure']) || !empty($condition['rear_pressure']))
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 text-sm mb-3">🎯 Pressões Recomendadas:</h4>
                    <div class="grid grid-cols-2 gap-3">
                        @if(!empty($condition['front_pressure']))
                        <div class="text-center">
                            <div class="text-xs text-gray-600 mb-1">🔄 Dianteiros</div>
                            <div class="text-lg font-bold text-green-600">{{ $condition['front_pressure'] }}</div>
                        </div>
                        @endif
                        @if(!empty($condition['rear_pressure']))
                        <div class="text-center">
                            <div class="text-xs text-gray-600 mb-1">🔙 Traseiros</div>
                            <div class="text-lg font-bold text-blue-600">{{ $condition['rear_pressure'] }}</div>
                        </div>
                        @endif
                    </div>
                </div>
                @elseif(!empty($condition['recommended_adjustment']))
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-800 text-sm mb-2">💡 Ajuste Recomendado:</h4>
                    @if(str_contains(strtolower($condition['recommended_adjustment']), 'tabela'))
                    <p class="text-blue-700 text-sm">
                        <span class="underline cursor-pointer">{{ $condition['recommended_adjustment'] }}</span>
                    </p>
                    <div class="mt-2 text-xs text-blue-600">
                        📊 Consulte a tabela de carga completa no início do artigo
                    </div>
                    @else
                    <p class="text-blue-700 font-medium">{{ $condition['recommended_adjustment'] }}</p>
                    @endif
                </div>
                @endif
            </div>

            <!-- Aplicação -->
            @if(!empty($condition['application']))
            <div class="mb-4">
                <h4 class="font-semibold text-gray-800 text-sm mb-2">📋 Quando Usar:</h4>
                <p class="text-gray-700 text-sm">{{ $condition['application'] }}</p>
            </div>
            @endif

            <!-- Justificativa -->
            @if(!empty($condition['justification']))
            <div class="mb-4">
                <h4 class="font-semibold text-gray-800 text-sm mb-2">🔬 Por que Funciona:</h4>
                <p class="text-gray-600 text-sm">{{ $condition['justification'] }}</p>
            </div>
            @endif

            <!-- Cuidados Especiais -->
            @if(!empty($condition['precautions']))
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <h4 class="font-semibold text-yellow-800 text-sm mb-2">⚠️ Cuidados:</h4>
                @if(is_array($condition['precautions']))
                <ul class="space-y-1">
                    @foreach($condition['precautions'] as $precaution)
                    <li class="text-yellow-700 text-xs flex items-start">
                        <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2 mt-1.5 flex-shrink-0"></span>
                        <span>{{ $precaution }}</span>
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="text-yellow-700 text-xs">{{ $condition['precautions'] }}</p>
                @endif
            </div>
            @endif

            <!-- Duração/Frequência -->
            @if(!empty($condition['frequency']) || !empty($condition['duration']))
            <div class="mt-4 flex items-center justify-between text-xs text-gray-500">
                @if(!empty($condition['frequency']))
                <div class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>{{ $condition['frequency'] }}</span>
                </div>
                @endif
                @if(!empty($condition['duration']))
                <div class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>{{ $condition['duration'] }}</span>
                </div>
                @endif
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Resumo de Condições Mais Comuns -->
    <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Resumo Rápido - Condições Mais Comuns
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Viagem Longa -->
            <div class="bg-white rounded-lg p-4 border border-blue-200">
                <div class="flex items-center mb-2">
                    <span class="text-lg mr-2">🛣️</span>
                    <h4 class="font-semibold text-gray-800 text-sm">Viagem Longa</h4>
                </div>
                <p class="text-xs text-gray-600 mb-2">Acima de 300km</p>
                <p class="text-sm font-bold text-blue-600">+2 PSI em todos</p>
            </div>

            <!-- Carga Pesada -->
            <div class="bg-white rounded-lg p-4 border border-orange-200">
                <div class="flex items-center mb-2">
                    <span class="text-lg mr-2">📦</span>
                    <h4 class="font-semibold text-gray-800 text-sm">Carga Pesada</h4>
                </div>
                <p class="text-xs text-gray-600 mb-2">Porta-malas cheio</p>
                <p class="text-sm font-bold text-orange-600">Ver tabela de carga</p>
            </div>

            <!-- Calor Extremo -->
            <div class="bg-white rounded-lg p-4 border border-red-200">
                <div class="flex items-center mb-2">
                    <span class="text-lg mr-2">🔥</span>
                    <h4 class="font-semibold text-gray-800 text-sm">Calor Extremo</h4>
                </div>
                <p class="text-xs text-gray-600 mb-2">Acima de 35°C</p>
                <p class="text-sm font-bold text-red-600">-2 PSI em todos</p>
            </div>

            <!-- Uso Urbano -->
            <div class="bg-white rounded-lg p-4 border border-green-200">
                <div class="flex items-center mb-2">
                    <span class="text-lg mr-2">🏙️</span>
                    <h4 class="font-semibold text-gray-800 text-sm">Uso Urbano</h4>
                </div>
                <p class="text-xs text-gray-600 mb-2">Trânsito pesado</p>
                <p class="text-sm font-bold text-green-600">Pressão normal</p>
            </div>
        </div>
    </div>

    <!-- Matriz de Decisão -->
    <div class="mt-6 bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Como Escolher a Pressão Certa
        </h3>

        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse bg-white rounded-lg overflow-hidden">
                <thead>
                    <tr class="bg-gray-600 text-white">
                        <th class="py-2 px-3 text-left text-xs font-medium">Situação</th>
                        <th class="py-2 px-3 text-center text-xs font-medium">Ocupantes</th>
                        <th class="py-2 px-3 text-center text-xs font-medium">Bagagem</th>
                        <th class="py-2 px-3 text-center text-xs font-medium">Distância</th>
                        <th class="py-2 px-3 text-center text-xs font-medium">Clima</th>
                        <th class="py-2 px-3 text-center text-xs font-medium">Ação</th>
                    </tr>
                </thead>
                <tbody class="text-xs">
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="py-2 px-3 font-medium">Uso diário urbano</td>
                        <td class="py-2 px-3 text-center">1-2</td>
                        <td class="py-2 px-3 text-center">Pouca</td>
                        <td class="py-2 px-3 text-center">&lt; 50km</td>
                        <td class="py-2 px-3 text-center">Normal</td>
                        <td class="py-2 px-3 text-center text-green-600 font-bold">Pressão padrão</td>
                    </tr>
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="py-2 px-3 font-medium">Viagem de férias</td>
                        <td class="py-2 px-3 text-center">4-5</td>
                        <td class="py-2 px-3 text-center">Muita</td>
                        <td class="py-2 px-3 text-center">&gt; 300km</td>
                        <td class="py-2 px-3 text-center">Variado</td>
                        <td class="py-2 px-3 text-center text-orange-600 font-bold">Tabela de carga + ajuste climático
                        </td>
                    </tr>
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="py-2 px-3 font-medium">Final de semana na praia</td>
                        <td class="py-2 px-3 text-center">2-3</td>
                        <td class="py-2 px-3 text-center">Média</td>
                        <td class="py-2 px-3 text-center">100-200km</td>
                        <td class="py-2 px-3 text-center">Calor</td>
                        <td class="py-2 px-3 text-center text-red-600 font-bold">Padrão -1 PSI</td>
                    </tr>
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="py-2 px-3 font-medium">Mudança de casa</td>
                        <td class="py-2 px-3 text-center">2</td>
                        <td class="py-2 px-3 text-center">Máxima</td>
                        <td class="py-2 px-3 text-center">50-100km</td>
                        <td class="py-2 px-3 text-center">Variado</td>
                        <td class="py-2 px-3 text-center text-purple-600 font-bold">Carga máxima da tabela</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Dicas Importantes -->
    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd" />
            </svg>
            <div class="text-sm">
                <p class="font-medium text-yellow-800 mb-1">💡 Lembre-se:</p>
                <p class="text-yellow-700">
                    Estas são orientações gerais baseadas nas especificações do {{ $vehicleInfo['full_name'] ??
                    'veículo' }}.
                    Condições extremas ou especiais podem exigir ajustes adicionais. Sempre monitore o comportamento
                    do veículo e ajuste conforme necessário. Em caso de dúvida, consulte a concessionária.
                </p>
            </div>
        </div>
    </div>
</section>
@endif