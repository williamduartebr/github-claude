{{-- 
Partial: tire-pressure/motorcycle/sport-motorcycle-warning.blade.php
Avisos específicos para motocicletas esportivas
Só exibido quando $article->isSportMotorcycle() retorna true
--}}

@php
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $sportWarnings = $article->getData()['sport_warnings'] ?? [];
    $trackDayTips = $article->getData()['track_day_tips'] ?? [];
@endphp

<section class="mb-12" id="sport-motorcycle-warnings">
    <div class="bg-gradient-to-r from-orange-600 to-red-600 text-white rounded-t-lg p-6">
        <div class="flex items-center">
            <span class="text-3xl mr-4">🏁</span>
            <div>
                <h2 class="text-2xl font-bold mb-2">
                    Atenção: Motocicleta Esportiva
                </h2>
                <p class="text-orange-100 text-sm">
                    Sua {{ $vehicleInfo['full_name'] ?? 'moto esportiva' }} exige cuidados especiais na calibragem
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-b-lg border-l border-r border-b border-gray-200">
        <!-- Características de Motos Esportivas -->
        <div class="p-6 bg-gradient-to-br from-orange-50 to-red-50">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <span class="text-orange-500 mr-2">⚡</span>
                Por que Motos Esportivas são Diferentes?
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                            <span class="text-sm text-orange-600">🎯</span>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Maior Potência</h4>
                            <p class="text-sm text-gray-700">Aceleração e velocidades mais altas exigem pressões precisas</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                            <span class="text-sm text-red-600">🌪️</span>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Ângulos Extremos</h4>
                            <p class="text-sm text-gray-700">Inclinações em curvas demandam aderência máxima</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                            <span class="text-sm text-yellow-600">🔥</span>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Aquecimento Rápido</h4>
                            <p class="text-sm text-gray-700">Pneus aquecem mais e pressão sobe rapidamente</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                            <span class="text-sm text-purple-600">⚖️</span>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Tolerância Zero</h4>
                            <p class="text-sm text-gray-700">Margem de erro muito menor que motos convencionais</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pressões Diferenciadas -->
        <div class="p-6 border-t border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <span class="text-red-500 mr-2">🎛️</span>
                Pressões por Tipo de Uso
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Uso Urbano -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="text-center mb-3">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <span class="text-lg text-green-600">🏙️</span>
                        </div>
                        <h4 class="font-semibold text-green-800">Uso Urbano</h4>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-green-700">Dianteiro:</span>
                            <span class="font-bold text-green-800">Padrão</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-green-700">Traseiro:</span>
                            <span class="font-bold text-green-800">Padrão</span>
                        </div>
                        <p class="text-xs text-green-600 mt-2">
                            Use as pressões normais para conforto no trânsito
                        </p>
                    </div>
                </div>

                <!-- Uso Esportivo -->
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <div class="text-center mb-3">
                        <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <span class="text-lg text-orange-600">🛣️</span>
                        </div>
                        <h4 class="font-semibold text-orange-800">Estrada Esportiva</h4>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-orange-700">Dianteiro:</span>
                            <span class="font-bold text-orange-800">+1 a +2 PSI</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-orange-700">Traseiro:</span>
                            <span class="font-bold text-orange-800">+2 a +3 PSI</span>
                        </div>
                        <p class="text-xs text-orange-600 mt-2">
                            Para pilotagem mais agressiva em estrada
                        </p>
                    </div>
                </div>

                <!-- Track Day -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="text-center mb-3">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <span class="text-lg text-red-600">🏁</span>
                        </div>
                        <h4 class="font-semibold text-red-800">Track Day</h4>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-red-700">Dianteiro:</span>
                            <span class="font-bold text-red-800">-2 a -3 PSI</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-red-700">Traseiro:</span>
                            <span class="font-bold text-red-800">-1 a -2 PSI</span>
                        </div>
                        <p class="text-xs text-red-600 mt-2">
                            Pressão inicial baixa para aquecer e expandir
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dicas Cruciais para Track Day -->
        @if(!empty($trackDayTips))
        <div class="p-6 bg-gray-900 text-white border-t border-gray-200">
            <h3 class="text-lg font-bold mb-4 flex items-center">
                <span class="text-yellow-400 mr-2">🏎️</span>
                Protocolo Track Day
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($trackDayTips as $tip)
                <div class="bg-gray-800 rounded-lg p-4">
                    <h4 class="font-semibold text-yellow-400 mb-2 flex items-center">
                        <span class="mr-2">{{ $tip['icon'] ?? '🎯' }}</span>
                        {{ $tip['title'] }}
                    </h4>
                    <p class="text-gray-300 text-sm">{{ $tip['description'] }}</p>
                    @if(!empty($tip['steps']))
                    <ul class="mt-3 space-y-1">
                        @foreach($tip['steps'] as $step)
                        <li class="text-xs text-gray-400 flex items-start">
                            <span class="text-yellow-400 mr-2">•</span>
                            {{ $step }}
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Aviso Legal -->
        <div class="p-6 bg-red-600 text-white border-t border-red-700">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                        <span class="text-lg text-red-600">⚖️</span>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-bold mb-2">Aviso Legal e de Segurança</h3>
                    <div class="text-red-100 text-sm space-y-2">
                        <p>
                            <strong>Track Days:</strong> Sempre consulte os instrutores da pista e fabricante dos pneus para pressões específicas.
                        </p>
                        <p>
                            <strong>Segurança:</strong> Pilotagem esportiva deve ser praticada apenas em ambientes controlados (pistas fechadas).
                        </p>
                        <p>
                            <strong>Responsabilidade:</strong> O uso dessas informações é por sua conta e risco. Sempre priorize a segurança.
                        </p>
                    </div>
                    <div class="mt-4">
                        <button id="sport-calibration-guide" class="bg-white text-red-600 px-4 py-2 rounded font-semibold hover:bg-red-50 transition-colors">
                            Ver Guia de Calibragem Específico →
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>