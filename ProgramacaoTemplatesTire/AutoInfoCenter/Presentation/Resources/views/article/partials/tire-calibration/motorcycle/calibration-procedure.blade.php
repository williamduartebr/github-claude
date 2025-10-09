{{--
Partial: ideal-tire-pressure/motorcycle/calibration-procedure.blade.php
Procedimento específico de calibragem para motocicletas
Considerando características únicas de estabilidade e centro de gravidade
--}}

@php
$calibrationProcedure = $article->getData()['calibration_procedure'] ?? [];
$vehicleInfo = $article->getData()['vehicle_info'] ?? [];
@endphp

<section class="mb-12" id="calibration-procedure">
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-t-lg p-6">
        <div class="flex items-center">
            <span class="text-3xl mr-4">🔧</span>
            <div>
                <h2 class="text-2xl font-bold mb-2">
                    Procedimento de Calibragem para Motos
                </h2>
                <p class="text-blue-100 text-sm">
                    Passo a passo específico considerando estabilidade e centro de gravidade
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-b-lg border-l border-r border-b border-gray-200">
        <!-- Preparação Inicial -->
        <div class="p-6 bg-blue-50 border-b border-blue-200">
            <h3 class="text-lg font-bold text-blue-900 mb-4 flex items-center">
                <span class="text-blue-600 mr-2">📋</span>
                Antes de Começar
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <h4 class="font-semibold text-blue-800">Condições Necessárias:</h4>
                    <ul class="space-y-2 text-sm text-blue-700">
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2 mt-1">✓</span>
                            <span>Moto parada há pelo menos 3 horas (pneus frios)</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2 mt-1">✓</span>
                            <span>Superfície plana e nivelada</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2 mt-1">✓</span>
                            <span>Moto na posição vertical (com ou sem cavalete central)</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2 mt-1">✓</span>
                            <span>Temperatura ambiente entre 15°C e 25°C (ideal)</span>
                        </li>
                    </ul>
                </div>
                <div class="space-y-3">
                    <h4 class="font-semibold text-blue-800">Equipamentos:</h4>
                    <ul class="space-y-2 text-sm text-blue-700">
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2 mt-1">🔧</span>
                            <span>Manômetro digital de qualidade</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2 mt-1">💨</span>
                            <span>Compressor ou bomba manual</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2 mt-1">📱</span>
                            <span>Tabela de pressões (esta página)</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2 mt-1">🧤</span>
                            <span>Luvas para proteção</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Procedimento Passo a Passo -->
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                <span class="text-indigo-600 mr-2">📝</span>
                Procedimento Passo a Passo
            </h3>

            @if(empty($calibrationProcedure))
            <!-- Procedimento padrão caso não tenha dados específicos -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Passo 1 -->
                <div
                    class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-all duration-300">
                    <div class="text-center mb-4">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-3">
                            <span class="text-2xl font-bold text-white">1</span>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900">Preparação</h4>
                    </div>
                    <p class="text-sm text-gray-700 mb-4 text-center">Posicione a moto em local plano com pneus frios
                        (3h parada)</p>
                    <div class="space-y-2">
                        <div class="flex items-start text-xs text-gray-600 bg-gray-50 rounded p-2">
                            <div class="w-2 h-2 bg-indigo-500 rounded-full mr-2 flex-shrink-0 mt-1"></div>
                            <span>Superfície nivelada e firme</span>
                        </div>
                        <div class="flex items-start text-xs text-gray-600 bg-gray-50 rounded p-2">
                            <div class="w-2 h-2 bg-indigo-500 rounded-full mr-2 flex-shrink-0 mt-1"></div>
                            <span>Moto na posição vertical</span>
                        </div>
                    </div>
                </div>

                <!-- Passo 2 -->
                <div
                    class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-all duration-300">
                    <div class="text-center mb-4">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-3">
                            <span class="text-2xl font-bold text-white">2</span>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900">Medição Atual</h4>
                    </div>
                    <p class="text-sm text-gray-700 mb-4 text-center">Verifique a pressão atual com manômetro digital
                    </p>
                    <div class="space-y-2">
                        <div class="flex items-start text-xs text-gray-600 bg-gray-50 rounded p-2">
                            <div class="w-2 h-2 bg-indigo-500 rounded-full mr-2 flex-shrink-0 mt-1"></div>
                            <span>Comece sempre pelo dianteiro</span>
                        </div>
                        <div class="flex items-start text-xs text-gray-600 bg-gray-50 rounded p-2">
                            <div class="w-2 h-2 bg-indigo-500 rounded-full mr-2 flex-shrink-0 mt-1"></div>
                            <span>Anote os valores atuais</span>
                        </div>
                    </div>
                </div>

                <!-- Passo 3 -->
                <div
                    class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-all duration-300">
                    <div class="text-center mb-4">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-3">
                            <span class="text-2xl font-bold text-white">3</span>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900">Ajuste Correto</h4>
                    </div>
                    <p class="text-sm text-gray-700 mb-4 text-center">Calibre para os valores recomendados conforme uso
                    </p>
                    <div class="space-y-2">
                        <div class="flex items-start text-xs text-gray-600 bg-gray-50 rounded p-2">
                            <div class="w-2 h-2 bg-indigo-500 rounded-full mr-2 flex-shrink-0 mt-1"></div>
                            <span>Solo: pressões normais</span>
                        </div>
                        <div class="flex items-start text-xs text-gray-600 bg-gray-50 rounded p-2">
                            <div class="w-2 h-2 bg-indigo-500 rounded-full mr-2 flex-shrink-0 mt-1"></div>
                            <span>Garupa: pressões elevadas</span>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <!-- Procedimento específico da ViewModel -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($calibrationProcedure as $step)
                <div
                    class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-all duration-300">
                    <div class="text-center mb-4">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-3">
                            <span class="text-2xl font-bold text-white">{{ $step['number'] ?? $loop->iteration }}</span>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900">{{ $step['title'] ?? 'Passo ' . $loop->iteration
                            }}</h4>
                    </div>

                    <p class="text-sm text-gray-700 mb-4 text-center">{{ $step['description'] ?? 'Descrição do
                        procedimento' }}</p>

                    @if(!empty($step['details']))
                    <div class="space-y-2">
                        @foreach($step['details'] as $detail)
                        <div class="flex items-start text-xs text-gray-600 bg-gray-50 rounded p-2">
                            <div class="w-2 h-2 bg-indigo-500 rounded-full mr-2 flex-shrink-0 mt-1"></div>
                            <span>{{ $detail }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if(!empty($step['warning']))
                    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                        <div class="flex items-start">
                            <span class="text-yellow-600 mr-2">⚠️</span>
                            <p class="text-xs text-yellow-700">{{ $step['warning'] }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Diferenças Específicas para Motos -->
        <div class="p-6 bg-gradient-to-r from-orange-50 to-red-50 border-t border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <span class="text-orange-600 mr-2">🏍️</span>
                Particularidades das Motocicletas
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div class="bg-white rounded-lg p-4 border border-orange-200">
                        <h4 class="font-semibold text-orange-800 mb-2 flex items-center">
                            <span class="mr-2">⚖️</span>
                            Centro de Gravidade
                        </h4>
                        <p class="text-sm text-orange-700">
                            Diferente dos carros, o centro de gravidade alto das motos torna a calibragem ainda mais
                            crítica.
                            Pressões incorretas afetam diretamente o equilíbrio e a dirigibilidade.
                        </p>
                    </div>
                    <div class="bg-white rounded-lg p-4 border border-red-200">
                        <h4 class="font-semibold text-red-800 mb-2 flex items-center">
                            <span class="mr-2">🔄</span>
                            Sequência de Calibragem
                        </h4>
                        <p class="text-sm text-red-700">
                            Sempre calibre primeiro o pneu dianteiro, depois o traseiro. Isso evita alterações no centro
                            de gravidade
                            durante o processo que poderiam afetar a leitura.
                        </p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="bg-white rounded-lg p-4 border border-blue-200">
                        <h4 class="font-semibold text-blue-800 mb-2 flex items-center">
                            <span class="mr-2">🌡️</span>
                            Sensibilidade Térmica
                        </h4>
                        <p class="text-sm text-blue-700">
                            Pneus de moto aquecem mais rápido que de carro. Uma diferença de 10°C pode alterar a pressão
                            em 1-2 PSI.
                            Sempre calibre com pneus completamente frios.
                        </p>
                    </div>
                    <div class="bg-white rounded-lg p-4 border border-purple-200">
                        <h4 class="font-semibold text-purple-800 mb-2 flex items-center">
                            <span class="mr-2">📏</span>
                            Precisão Necessária
                        </h4>
                        <p class="text-sm text-purple-700">
                            A margem de erro em motos é muito menor. Uma diferença de 3-4 PSI pode ser a diferença entre
                            segurança e um acidente grave.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Verificação Final -->
        <div class="p-6 bg-green-50 border-t border-green-200">
            <h3 class="text-lg font-bold text-green-900 mb-4 flex items-center">
                <span class="text-green-600 mr-2">✅</span>
                Verificação Final Obrigatória
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-semibold text-green-800 mb-3">Checklist Pós-Calibragem:</h4>
                    <ul class="space-y-2 text-sm text-green-700">
                        <li class="flex items-center">
                            <input type="checkbox" class="mr-2 text-green-600" disabled>
                            <span>Pressões conferidas com manômetro</span>
                        </li>
                        <li class="flex items-center">
                            <input type="checkbox" class="mr-2 text-green-600" disabled>
                            <span>Bicos dos pneus bem fechados</span>
                        </li>
                        <li class="flex items-center">
                            <input type="checkbox" class="mr-2 text-green-600" disabled>
                            <span>Inspeção visual dos pneus</span>
                        </li>
                        <li class="flex items-center">
                            <input type="checkbox" class="mr-2 text-green-600" disabled>
                            <span>Teste de estabilidade em baixa velocidade</span>
                        </li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-green-800 mb-3">Teste de Estabilidade:</h4>
                    <div class="bg-white rounded-lg p-4 border border-green-200">
                        <p class="text-sm text-green-700 mb-3">
                            Após calibrar, faça um teste em local seguro:
                        </p>
                        <ol class="space-y-1 text-xs text-green-600">
                            <li><strong>1.</strong> Ande devagar (10-20 km/h) em linha reta</li>
                            <li><strong>2.</strong> Solte levemente o guidão</li>
                            <li><strong>3.</strong> A moto deve manter a trajetória</li>
                            <li><strong>4.</strong> Oscilações = recalibrar</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Frequência de Verificação -->
        <div class="p-6 bg-gray-900 text-white border-t border-gray-700">
            <h3 class="text-lg font-bold mb-4 flex items-center">
                <span class="text-yellow-400 mr-2">📅</span>
                Cronograma de Verificação
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="w-16 h-16 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-3">
                        <span class="text-xl font-bold text-gray-900">7</span>
                    </div>
                    <h4 class="font-semibold text-yellow-400 mb-1">A Cada 7 Dias</h4>
                    <p class="text-sm text-gray-300">Verificação visual rápida</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-orange-500 rounded-full flex items-center justify-center mx-auto mb-3">
                        <span class="text-xl font-bold text-gray-900">15</span>
                    </div>
                    <h4 class="font-semibold text-orange-400 mb-1">A Cada 15 Dias</h4>
                    <p class="text-sm text-gray-300">Calibragem completa</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-3">
                        <span class="text-xl font-bold text-gray-900">🛣️</span>
                    </div>
                    <h4 class="font-semibold text-red-400 mb-1">Antes de Viagens</h4>
                    <p class="text-sm text-gray-300">Sempre verificar</p>
                </div>
            </div>
        </div>
    </div>
</section>