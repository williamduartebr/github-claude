{{-- 
Partial: tire-pressure/motorcycle/maintenance-tips.blade.php
Dicas de manutenção específicas para motocicletas
Focado em inspeção visual, desgaste e cuidados preventivos
--}}

@php
    $maintenanceTips = $article->getData()['maintenance_tips'] ?? [];
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $motorcycleCategory = $vehicleInfo['category'] ?? 'standard';
@endphp

@if(!empty($maintenanceTips))
<section class="mb-12" id="maintenance-tips">
    <div class="bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-t-lg p-6">
        <div class="flex items-center">
            <span class="text-3xl mr-4">🔧</span>
            <div>
                <h2 class="text-2xl font-bold mb-2">
                    Manutenção Preventiva dos Pneus
                </h2>
                <p class="text-green-100 text-sm">
                    Dicas específicas para maximizar segurança e durabilidade em motocicletas
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-b-lg border-l border-r border-b border-gray-200">
        <!-- Inspeção Visual Diária -->
        <div class="p-6 bg-gradient-to-br from-green-50 to-emerald-50">
            <h3 class="text-lg font-bold text-green-900 mb-4 flex items-center">
                <span class="text-green-600 mr-2">👁️</span>
                Inspeção Visual Diária (30 segundos)
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h4 class="font-semibold text-green-800">O que Verificar:</h4>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                <span class="text-sm text-green-600">💨</span>
                            </div>
                            <div>
                                <h5 class="font-medium text-green-800">Aspecto Geral</h5>
                                <p class="text-sm text-green-700">Pneu "murcho" ou muito rígido? Deformações visíveis?</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                <span class="text-sm text-blue-600">🔍</span>
                            </div>
                            <div>
                                <h5 class="font-medium text-green-800">Objetos Estranhos</h5>
                                <p class="text-sm text-green-700">Pregos, parafusos, pedras ou vidros cravados</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                <span class="text-sm text-red-600">⚡</span>
                            </div>
                            <div>
                                <h5 class="font-medium text-green-800">Cortes e Bolhas</h5>
                                <p class="text-sm text-green-700">Danos na banda de rodagem ou lateral</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="space-y-4">
                    <h4 class="font-semibold text-green-800">Como Verificar:</h4>
                    <div class="bg-white rounded-lg p-4 border border-green-200">
                        <ol class="space-y-2 text-sm text-green-700">
                            <li class="flex items-start">
                                <span class="font-bold text-green-600 mr-2">1.</span>
                                <span>Agache ao lado da moto</span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold text-green-600 mr-2">2.</span>
                                <span>Observe toda a circunferência do pneu</span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold text-green-600 mr-2">3.</span>
                                <span>Gire a roda para ver a parte de trás</span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold text-green-600 mr-2">4.</span>
                                <span>Repita no outro pneu</span>
                            </li>
                        </ol>
                        <div class="mt-3 p-2 bg-green-50 rounded text-xs text-green-600">
                            <strong>Tempo total:</strong> Menos de 30 segundos, pode salvar sua vida!
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sinais de Desgaste -->
        <div class="p-6 border-t border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <span class="text-orange-600 mr-2">📊</span>
                Padrões de Desgaste e Significados
            </h3>
            <!-- Seção de Padrões de Desgaste Padrão (caso não tenha dados específicos) -->
            @if(empty($maintenanceTips))
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Desgaste Central -->
                <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-lg p-5 hover:shadow-lg transition-all duration-300">
                    <div class="text-center mb-3">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <span class="text-lg text-red-600">🎯</span>
                        </div>
                        <h4 class="font-semibold text-gray-900">Desgaste Central</h4>
                    </div>
                    <p class="text-sm text-gray-700 mb-3 text-center">Desgaste excessivo no centro da banda de rodagem</p>
                    <div class="space-y-2">
                        <h5 class="text-xs font-semibold text-gray-800 uppercase">Possíveis Causas:</h5>
                        <div class="flex items-start text-xs text-gray-600">
                            <div class="w-1 h-1 bg-gray-400 rounded-full mr-2 flex-shrink-0 mt-2"></div>
                            <span>Pressão excessiva nos pneus</span>
                        </div>
                        <div class="flex items-start text-xs text-gray-600">
                            <div class="w-1 h-1 bg-gray-400 rounded-full mr-2 flex-shrink-0 mt-2"></div>
                            <span>Calibragem acima do recomendado</span>
                        </div>
                    </div>
                    <div class="mt-3 p-2 bg-red-50 rounded border border-red-200">
                        <p class="text-xs text-red-700">
                            <strong>Ação:</strong> Reduza a pressão para valores recomendados
                        </p>
                    </div>
                </div>

                <!-- Desgaste nas Bordas -->
                <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-lg p-5 hover:shadow-lg transition-all duration-300">
                    <div class="text-center mb-3">
                        <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <span class="text-lg text-orange-600">↔️</span>
                        </div>
                        <h4 class="font-semibold text-gray-900">Desgaste nas Bordas</h4>
                    </div>
                    <p class="text-sm text-gray-700 mb-3 text-center">Desgaste excessivo nas laterais do pneu</p>
                    <div class="space-y-2">
                        <h5 class="text-xs font-semibold text-gray-800 uppercase">Possíveis Causas:</h5>
                        <div class="flex items-start text-xs text-gray-600">
                            <div class="w-1 h-1 bg-gray-400 rounded-full mr-2 flex-shrink-0 mt-2"></div>
                            <span>Pressão insuficiente</span>
                        </div>
                        <div class="flex items-start text-xs text-gray-600">
                            <div class="w-1 h-1 bg-gray-400 rounded-full mr-2 flex-shrink-0 mt-2"></div>
                            <span>Curvas muito inclinadas</span>
                        </div>
                    </div>
                    <div class="mt-3 p-2 bg-orange-50 rounded border border-orange-200">
                        <p class="text-xs text-orange-700">
                            <strong>Ação:</strong> Aumente a pressão e verifique técnica de pilotagem
                        </p>
                    </div>
                </div>

                <!-- Desgaste Irregular -->
                <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-lg p-5 hover:shadow-lg transition-all duration-300">
                    <div class="text-center mb-3">
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <span class="text-lg text-purple-600">🌊</span>
                        </div>
                        <h4 class="font-semibold text-gray-900">Desgaste Irregular</h4>
                    </div>
                    <p class="text-sm text-gray-700 mb-3 text-center">Padrão ondulado ou em degraus no pneu</p>
                    <div class="space-y-2">
                        <h5 class="text-xs font-semibold text-gray-800 uppercase">Possíveis Causas:</h5>
                        <div class="flex items-start text-xs text-gray-600">
                            <div class="w-1 h-1 bg-gray-400 rounded-full mr-2 flex-shrink-0 mt-2"></div>
                            <span>Desbalanceamento das rodas</span>
                        </div>
                        <div class="flex items-start text-xs text-gray-600">
                            <div class="w-1 h-1 bg-gray-400 rounded-full mr-2 flex-shrink-0 mt-2"></div>
                            <span>Problemas na suspensão</span>
                        </div>
                    </div>
                    <div class="mt-3 p-2 bg-purple-50 rounded border border-purple-200">
                        <p class="text-xs text-purple-700">
                            <strong>Ação:</strong> Balanceamento e inspeção da suspensão
                        </p>
                    </div>
                </div>
            </div>
            @else
            <!-- Seção com dados específicos da ViewModel -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($maintenanceTips as $tip)
                <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-lg p-5 hover:shadow-lg transition-all duration-300">
                    <div class="text-center mb-3">
                        <div class="w-12 h-12 bg-{{ $tip['color'] ?? 'gray' }}-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <span class="text-lg text-{{ $tip['color'] ?? 'gray' }}-600">{{ $tip['icon'] ?? '🔧' }}</span>
                        </div>
                        <h4 class="font-semibold text-gray-900">{{ $tip['title'] ?? 'Dica de Manutenção' }}</h4>
                    </div>
                    
                    <p class="text-sm text-gray-700 mb-3 text-center">{{ $tip['description'] ?? 'Descrição não disponível' }}</p>
                    
                    @if(!empty($tip['causes']))
                    <div class="space-y-2">
                        <h5 class="text-xs font-semibold text-gray-800 uppercase">Possíveis Causas:</h5>
                        @foreach($tip['causes'] as $cause)
                        <div class="flex items-start text-xs text-gray-600">
                            <div class="w-1 h-1 bg-gray-400 rounded-full mr-2 flex-shrink-0 mt-2"></div>
                            <span>{{ $cause }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if(!empty($tip['action']))
                    <div class="mt-3 p-2 bg-{{ $tip['color'] ?? 'gray' }}-50 rounded border border-{{ $tip['color'] ?? 'gray' }}-200">
                        <p class="text-xs text-{{ $tip['color'] ?? 'gray' }}-700">
                            <strong>Ação:</strong> {{ $tip['action'] }}
                        </p>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Cuidados por Categoria de Moto -->
        <div class="p-6 bg-gray-50 border-t border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <span class="text-purple-600 mr-2">🎯</span>
                Cuidados Específicos por Categoria
            </h3>
            
            @if($motorcycleCategory === 'sport')
            <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                <h4 class="font-bold text-red-800 mb-3 flex items-center">
                    <span class="mr-2">🏁</span>
                    Motocicleta Esportiva
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h5 class="font-semibold text-red-700 mb-2">Atenção Especial:</h5>
                        <ul class="space-y-1 text-sm text-red-600">
                            <li>• Desgaste acelerado nas bordas dos pneus</li>
                            <li>• Aquecimento rápido em pilotagem agressiva</li>
                            <li>• Verificação após track days obrigatória</li>
                            <li>• Inspeção das camadas internas após quedas</li>
                        </ul>
                    </div>
                    <div>
                        <h5 class="font-semibold text-red-700 mb-2">Frequência Recomendada:</h5>
                        <ul class="space-y-1 text-sm text-red-600">
                            <li>• Inspeção visual: Diária</li>
                            <li>• Calibragem: A cada 7 dias</li>
                            <li>• Verificação completa: Semanal</li>
                            <li>• Balanceamento: A cada 5.000 km</li>
                        </ul>
                    </div>
                </div>
            </div>
            @elseif($motorcycleCategory === 'touring')
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h4 class="font-bold text-blue-800 mb-3 flex items-center">
                    <span class="mr-2">🛣️</span>
                    Motocicleta Touring
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h5 class="font-semibold text-blue-700 mb-2">Foco Principal:</h5>
                        <ul class="space-y-1 text-sm text-blue-600">
                            <li>• Desgaste uniforme para máxima quilometragem</li>
                            <li>• Verificação antes de viagens longas</li>
                            <li>• Atenção ao peso da bagagem</li>
                            <li>• Monitoramento da profundidade dos sulcos</li>
                        </ul>
                    </div>
                    <div>
                        <h5 class="font-semibold text-blue-700 mb-2">Cronograma:</h5>
                        <ul class="space-y-1 text-sm text-blue-600">
                            <li>• Inspeção visual: A cada 3 dias</li>
                            <li>• Calibragem: A cada 15 dias</li>
                            <li>• Verificação profunda: Quinzenal</li>
                            <li>• Rodízio: A cada 8.000 km</li>
                        </ul>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <h4 class="font-bold text-green-800 mb-3 flex items-center">
                    <span class="mr-2">🏍️</span>
                    Motocicleta Urbana/Naked
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h5 class="font-semibold text-green-700 mb-2">Características de Uso:</h5>
                        <ul class="space-y-1 text-sm text-green-600">
                            <li>• Uso frequente em trânsito urbano</li>
                            <li>• Freadas e acelerações constantes</li>
                            <li>• Exposição a detritos urbanos</li>
                            <li>• Variação grande de cargas</li>
                        </ul>
                    </div>
                    <div>
                        <h5 class="font-semibold text-green-700 mb-2">Manutenção Sugerida:</h5>
                        <ul class="space-y-1 text-sm text-green-600">
                            <li>• Inspeção visual: Diária</li>
                            <li>• Calibragem: A cada 10 dias</li>
                            <li>• Limpeza dos sulcos: Semanal</li>
                            <li>• Verificação geral: Quinzenal</li>
                        </ul>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Dicas Essenciais -->
        <div class="p-6 bg-gradient-to-r from-gray-800 to-gray-900 text-white border-t border-gray-700">
            <h3 class="text-lg font-bold mb-4 flex items-center">
                <span class="text-yellow-400 mr-2">💡</span>
                Dicas Essenciais para Motociclistas
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="space-y-3">
                    <h4 class="font-semibold text-yellow-400">Economize na Manutenção:</h4>
                    <ul class="space-y-2 text-sm text-gray-300">
                        <li class="flex items-start">
                            <span class="text-yellow-400 mr-2">•</span>
                            <span>Calibragem correta aumenta vida útil em 30%</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-yellow-400 mr-2">•</span>
                            <span>Rodízio adequado duplica a quilometragem</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-yellow-400 mr-2">•</span>
                            <span>Limpeza regular evita danos por objetos</span>
                        </li>
                    </ul>
                </div>
                <div class="space-y-3">
                    <h4 class="font-semibold text-yellow-400">Maximize a Segurança:</h4>
                    <ul class="space-y-2 text-sm text-gray-300">
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2">•</span>
                            <span>Nunca ignore pequenas anormalidades</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2">•</span>
                            <span>Substitua em pares sempre que possível</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-400 mr-2">•</span>
                            <span>Teste a estabilidade após qualquer serviço</span>
                        </li>
                    </ul>
                </div>
                <div class="space-y-3">
                    <h4 class="font-semibold text-yellow-400">Sinais de Alerta:</h4>
                    <ul class="space-y-2 text-sm text-gray-300">
                        <li class="flex items-start">
                            <span class="text-red-400 mr-2">•</span>
                            <span>Vibração anormal no guidão</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-red-400 mr-2">•</span>
                            <span>Moto "puxa" para um lado</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-red-400 mr-2">•</span>
                            <span>Ruídos estranhos durante curvas</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
@endif