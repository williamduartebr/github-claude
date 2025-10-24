{{--
Partial: ideal-tire-pressure/motorcycle/critical-safety-alerts.blade.php
Alertas críticos de segurança específicos para motocicletas
Enfoque na importância da calibragem correta para segurança em duas rodas
--}}

@php
$criticalAlerts = $article->getData()['critical_alerts'] ?? [];
$vehicleInfo = $article->getData()['vehicle_info'] ?? [];
@endphp

@if(!empty($criticalAlerts))
<section class="mb-12" id="critical-safety-alerts">
    <div class="bg-gradient-to-r from-red-600 to-red-800 text-white rounded-t-lg p-6">
        <div class="flex items-center">
            <span class="text-3xl mr-4">🚨</span>
            <div>
                <h2 class="text-2xl font-bold mb-2">
                    Alertas Críticos de Segurança
                </h2>
                <p class="text-red-100 text-sm">
                    Em motocicletas, a pressão incorreta pode ser FATAL. Leia com atenção!
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-b-lg border-l border-r border-b border-gray-200">
        <div class="p-6">
            @if(empty($criticalAlerts))
            <!-- Alertas padrão caso não tenha dados específicos -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Alerta de Pressão Baixa -->
                <div
                    class="critical-alert bg-gradient-to-br from-red-50 to-orange-50 border-l-4 border-red-500 rounded-lg p-6 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                <span class="text-lg text-red-600">⚠️</span>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-lg font-bold text-red-800 mb-2">
                                Pressão Baixa = Risco de Morte
                            </h3>
                            <p class="text-sm text-red-700 mb-3 leading-relaxed">
                                Pressão insuficiente pode causar instabilidade fatal, especialmente em curvas e
                                frenagens de emergência.
                            </p>
                            <div class="space-y-2">
                                <h4 class="text-sm font-semibold text-red-800">Consequências:</h4>
                                <ul class="space-y-1">
                                    <li class="flex items-start text-xs text-red-700">
                                        <span class="text-red-500 mr-2 mt-1">●</span>
                                        <span>Perda de controle em curvas</span>
                                    </li>
                                    <li class="flex items-start text-xs text-red-700">
                                        <span class="text-red-500 mr-2 mt-1">●</span>
                                        <span>Oscilação do guidão</span>
                                    </li>
                                    <li class="flex items-start text-xs text-red-700">
                                        <span class="text-red-500 mr-2 mt-1">●</span>
                                        <span>Estouro do pneu</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="mt-4 p-3 bg-white rounded border border-red-200">
                                <h4 class="text-sm font-semibold text-green-800 mb-2">Como Prevenir:</h4>
                                <p class="text-xs text-green-700">Verificação quinzenal obrigatória com manômetro de
                                    qualidade</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alerta de Pressão Alta -->
                <div
                    class="critical-alert bg-gradient-to-br from-red-50 to-orange-50 border-l-4 border-red-500 rounded-lg p-6 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                <span class="text-lg text-red-600">💥</span>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-lg font-bold text-red-800 mb-2">
                                Pressão Alta = Perda de Aderência
                            </h3>
                            <p class="text-sm text-red-700 mb-3 leading-relaxed">
                                Excesso de pressão reduz drasticamente a área de contato, causando perda de aderência
                                fatal.
                            </p>
                            <div class="space-y-2">
                                <h4 class="text-sm font-semibold text-red-800">Consequências:</h4>
                                <ul class="space-y-1">
                                    <li class="flex items-start text-xs text-red-700">
                                        <span class="text-red-500 mr-2 mt-1">●</span>
                                        <span>Derrapagem em superfícies molhadas</span>
                                    </li>
                                    <li class="flex items-start text-xs text-red-700">
                                        <span class="text-red-500 mr-2 mt-1">●</span>
                                        <span>Frenagem ineficiente</span>
                                    </li>
                                    <li class="flex items-start text-xs text-red-700">
                                        <span class="text-red-500 mr-2 mt-1">●</span>
                                        <span>Moto "saltitante"</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="mt-4 p-3 bg-white rounded border border-red-200">
                                <h4 class="text-sm font-semibold text-green-800 mb-2">Como Prevenir:</h4>
                                <p class="text-xs text-green-700">Nunca exceda as pressões recomendadas pelo fabricante
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <!-- Alertas específicos da ViewModel -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($criticalAlerts as $alert)
                <div
                    class="critical-alert bg-gradient-to-br from-red-50 to-orange-50 border-l-4 border-red-500 rounded-lg p-6 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                @switch($alert['type'] ?? 'danger')
                                @case('death_risk')
                                <span class="text-lg text-red-600">💀</span>
                                @break
                                @case('instability')
                                <span class="text-lg text-red-600">⚡</span>
                                @break
                                @case('tire_failure')
                                <span class="text-lg text-red-600">💥</span>
                                @break
                                @case('handling')
                                <span class="text-lg text-red-600">🌪️</span>
                                @break
                                @default
                                <span class="text-lg text-red-600">⚠️</span>
                                @endswitch
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-lg font-bold text-red-800 mb-2">
                                {{ $alert['title'] ?? 'Alerta de Segurança' }}
                            </h3>
                            <p class="text-sm text-red-700 mb-3 leading-relaxed">
                                {{ $alert['description'] ?? 'Atenção aos riscos específicos de motocicletas.' }}
                            </p>

                            @if(!empty($alert['consequences']))
                            <div class="space-y-2">
                                <h4 class="text-sm font-semibold text-red-800">Consequências:</h4>
                                <ul class="space-y-1">
                                    @foreach($alert['consequences'] as $consequence)
                                    <li class="flex items-start text-xs text-red-700">
                                        <span class="text-red-500 mr-2 mt-1">●</span>
                                        <span>{{ $consequence }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            @if(!empty($alert['prevention']))
                            <div class="mt-4 p-3 bg-white rounded border border-red-200">
                                <h4 class="text-sm font-semibold text-green-800 mb-2">Como Prevenir:</h4>
                                <p class="text-xs text-green-700">{{ $alert['prevention'] }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Seção de Estatísticas de Acidentes (se disponível) -->
        @if(!empty($criticalAlerts[0]['statistics']))
        <div class="bg-gray-900 text-white p-6 border-t border-gray-200">
            <h3 class="text-lg font-bold mb-4 flex items-center">
                <span class="text-yellow-400 mr-2">📊</span>
                Dados de Segurança
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($criticalAlerts[0]['statistics'] as $stat)
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-400 mb-1">{{ $stat['value'] }}</div>
                    <div class="text-sm text-gray-300">{{ $stat['description'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Verificação de Emergência -->
        <div class="bg-yellow-50 border-t border-yellow-200 p-6">
            <h3 class="text-lg font-bold text-yellow-800 mb-4 flex items-center">
                <span class="text-yellow-600 mr-2">🔍</span>
                Verificação de Emergência na Estrada
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-semibold text-yellow-800 mb-3">Sinais de Pressão Baixa:</h4>
                    <ul class="space-y-2 text-sm text-yellow-700">
                        <li class="flex items-start">
                            <span class="text-yellow-600 mr-2 mt-1">●</span>
                            <span>Moto "pesada" nas curvas</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-yellow-600 mr-2 mt-1">●</span>
                            <span>Guidão oscilando em linha reta</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-yellow-600 mr-2 mt-1">●</span>
                            <span>Maior esforço para manter direção</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-yellow-600 mr-2 mt-1">●</span>
                            <span>Pneu com aparência "murcha"</span>
                        </li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-yellow-800 mb-3">Sinais de Pressão Alta:</h4>
                    <ul class="space-y-2 text-sm text-yellow-700">
                        <li class="flex items-start">
                            <span class="text-yellow-600 mr-2 mt-1">●</span>
                            <span>Moto "saltitante" em irregularidades</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-yellow-600 mr-2 mt-1">●</span>
                            <span>Perda de aderência em curvas</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-yellow-600 mr-2 mt-1">●</span>
                            <span>Frenagem menos eficiente</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-yellow-600 mr-2 mt-1">●</span>
                            <span>Desgaste no centro do pneu</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Ação Imediata -->
        <div class="bg-red-600 text-white p-6 border-t border-red-700">
            <div class="text-center">
                <h3 class="text-xl font-bold mb-3">🛑 AÇÃO IMEDIATA</h3>
                <p class="text-lg mb-4">
                    Se você notar QUALQUER um desses sinais:
                </p>
                <div class="bg-white text-red-600 rounded-lg p-4 inline-block">
                    <p class="font-bold text-lg">
                        PARE IMEDIATAMENTE E VERIFIQUE OS PNEUS
                    </p>
                    <p class="text-sm mt-1">
                        Sua vida vale mais que alguns minutos de atraso
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endif