{{-- 
Partial: tire-pressure/car/spare-tire.blade.php
Seção específica sobre pneu estepe e kit de reparo
--}}

@php
    $spareTireInfo = $article->getData()['spare_tire_info'] ?? [];
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $mainTireSpec = $article->getData()['tire_specifications_by_version'][0] ?? null;
    
    // Pressão padrão do estepe (geralmente maior que os pneus normais)
    $sparePressure = $spareTireInfo['pressure'] ?? '60 PSI';
    $spareType = $spareTireInfo['type'] ?? 'temporário';
@endphp

<section class="mb-12">
    <div class="bg-gradient-to-br from-indigo-600 to-purple-700 text-white rounded-lg p-8 relative overflow-hidden">
        <!-- Ícone decorativo -->
        <div class="absolute top-4 right-4 text-6xl opacity-20">
            🛞
        </div>

        <div class="relative z-10">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold">Pneu Estepe e Kit de Reparo</h2>
                    <p class="text-indigo-100 text-sm">
                        Informações essenciais sobre o pneu sobressalente do {{ $vehicleInfo['full_name'] ?? 'seu veículo' }}
                    </p>
                </div>
            </div>

            <!-- Pressão do Estepe -->
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 border border-white/20">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Pressão Recomendada -->
                    <div class="text-center">
                        <h3 class="font-semibold text-lg mb-3 flex items-center justify-center">
                            <span class="text-2xl mr-2">🎯</span>
                            Pressão do Estepe
                        </h3>
                        <div class="bg-white/20 rounded-lg p-4">
                            <div class="text-4xl font-bold mb-2">{{ str_replace([' PSI', ' psi'], '', $sparePressure) }}</div>
                            <div class="text-indigo-200 text-sm">PSI (libras por pol²)</div>
                        </div>
                        <p class="text-indigo-100 text-sm mt-2">Pressão sempre maior que pneus normais</p>
                    </div>

                    <!-- Tipo do Estepe -->
                    <div class="text-center">
                        <h3 class="font-semibold text-lg mb-3 flex items-center justify-center">
                            <span class="text-2xl mr-2">⚙️</span>
                            Tipo de Estepe
                        </h3>
                        <div class="bg-white/20 rounded-lg p-4">
                            <div class="text-2xl font-bold mb-2 capitalize">{{ $spareType }}</div>
                            <div class="text-indigo-200 text-sm">
                                @if($spareType === 'temporário')
                                Uso limitado - máx. 80 km/h
                                @elseif($spareType === 'compacto')
                                Menor que pneus normais
                                @else
                                Mesmo tamanho dos originais
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tipos de Estepe -->
    <div class="mt-6 bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Tipos de Pneu Estepe
            </h3>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Estepe Temporário -->
                <div class="border border-orange-200 rounded-lg p-4 {{ $spareType === 'temporário' ? 'bg-orange-50 border-orange-300' : 'bg-gray-50' }}">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center mr-3">
                            <span class="text-white text-xl">🚧</span>
                        </div>
                        <h4 class="font-semibold text-orange-800">Temporário (Donut)</h4>
                        @if($spareType === 'temporário')
                        <span class="ml-auto bg-orange-600 text-white text-xs px-2 py-1 rounded">Seu modelo</span>
                        @endif
                    </div>
                    <ul class="space-y-2 text-sm text-orange-700">
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 mt-2"></span>
                            <span>Pressão: 60 PSI (padrão)</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 mt-2"></span>
                            <span>Velocidade máxima: 80 km/h</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 mt-2"></span>
                            <span>Distância máxima: 80 km</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 mt-2"></span>
                            <span>Menor e mais leve</span>
                        </li>
                    </ul>
                </div>

                <!-- Estepe Compacto -->
                <div class="border border-blue-200 rounded-lg p-4 {{ $spareType === 'compacto' ? 'bg-blue-50 border-blue-300' : 'bg-gray-50' }}">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                            <span class="text-white text-xl">🔷</span>
                        </div>
                        <h4 class="font-semibold text-blue-800">Compacto</h4>
                        @if($spareType === 'compacto')
                        <span class="ml-auto bg-blue-600 text-white text-xs px-2 py-1 rounded">Seu modelo</span>
                        @endif
                    </div>
                    <ul class="space-y-2 text-sm text-blue-700">
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                            <span>Pressão: 40-50 PSI</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                            <span>Velocidade máxima: 100 km/h</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                            <span>Distância: até 150 km</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                            <span>Largura menor que originais</span>
                        </li>
                    </ul>
                </div>

                <!-- Estepe Full Size -->
                <div class="border border-green-200 rounded-lg p-4 {{ $spareType === 'full_size' ? 'bg-green-50 border-green-300' : 'bg-gray-50' }}">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mr-3">
                            <span class="text-white text-xl">⭕</span>
                        </div>
                        <h4 class="font-semibold text-green-800">Tamanho Original</h4>
                        @if($spareType === 'full_size')
                        <span class="ml-auto bg-green-600 text-white text-xs px-2 py-1 rounded">Seu modelo</span>
                        @endif
                    </div>
                    <ul class="space-y-2 text-sm text-green-700">
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2"></span>
                            <span>Pressão: igual aos originais</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2"></span>
                            <span>Sem limite de velocidade</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2"></span>
                            <span>Sem limite de distância</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2"></span>
                            <span>Mesmo tamanho dos originais</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Procedimento de Verificação -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Como Verificar o Estepe
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Verificação Mensal -->
            <div>
                <h4 class="font-semibold text-blue-800 mb-3">📅 Verificação Mensal:</h4>
                <ol class="space-y-2 text-sm text-blue-700">
                    <li class="flex items-start">
                        <span class="bg-blue-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs mr-2 mt-0.5">1</span>
                        <span>Retire o estepe do compartimento</span>
                    </li>
                    <li class="flex items-start">
                        <span class="bg-blue-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs mr-2 mt-0.5">2</span>
                        <span>Verifique pressão com calibrador</span>
                    </li>
                    <li class="flex items-start">
                        <span class="bg-blue-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs mr-2 mt-0.5">3</span>
                        <span>Inspecione visualmente (rachaduras, desgaste)</span>
                    </li>
                    <li class="flex items-start">
                        <span class="bg-blue-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs mr-2 mt-0.5">4</span>
                        <span>Calibre se necessário ({{ $sparePressure }})</span>
                    </li>
                    <li class="flex items-start">
                        <span class="bg-blue-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs mr-2 mt-0.5">5</span>
                        <span>Recoloque no compartimento</span>
                    </li>
                </ol>
            </div>

            <!-- Sinais de Problemas -->
            <div>
                <h4 class="font-semibold text-blue-800 mb-3">⚠️ Sinais de Problemas:</h4>
                <ul class="space-y-2 text-sm text-blue-700">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Pressão abaixo do recomendado</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Rachaduras na borracha</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Desgaste irregular da banda</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Ressecamento excessivo</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Idade superior a 6 anos</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Kit de Reparo (para veículos sem estepe) -->
    @if($vehicleInfo['has_repair_kit'] ?? false)
    <div class="mt-6 bg-purple-50 border border-purple-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-purple-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.78 0-2.678-2.153-1.415-3.414l5-5A2 2 0 009 9.172V5L8 4z"/>
            </svg>
            Kit de Reparo de Pneu
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Componentes do Kit -->
            <div>
                <h4 class="font-semibold text-purple-800 mb-3">🧰 Componentes do Kit:</h4>
                <ul class="space-y-2 text-sm text-purple-700">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-purple-500 rounded-full mr-2 mt-2"></span>
                        <span>Compressor de ar 12V</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-purple-500 rounded-full mr-2 mt-2"></span>
                        <span>Frasco de selante líquido</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-purple-500 rounded-full mr-2 mt-2"></span>
                        <span>Mangueiras e conectores</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-purple-500 rounded-full mr-2 mt-2"></span>
                        <span>Manômetro integrado</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-purple-500 rounded-full mr-2 mt-2"></span>
                        <span>Manual de instruções</span>
                    </li>
                </ul>
            </div>

            <!-- Limitações do Kit -->
            <div>
                <h4 class="font-semibold text-purple-800 mb-3">⚠️ Limitações:</h4>
                <ul class="space-y-2 text-sm text-purple-700">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Só repara furos até 6mm</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Não funciona em lateral do pneu</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Velocidade máxima: 80 km/h</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Distância máxima: 200 km</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Pneu deve ser substituído após uso</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Procedimento de Uso -->
        <div class="mt-4 bg-white rounded-lg p-4">
            <h4 class="font-semibold text-purple-800 mb-3">🔧 Como Usar o Kit:</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <ol class="space-y-2 text-sm text-purple-700">
                    <li class="flex items-start">
                        <span class="bg-purple-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs mr-2 mt-0.5">1</span>
                        <span>Retire objeto que causou o furo (se visível)</span>
                    </li>
                    <li class="flex items-start">
                        <span class="bg-purple-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs mr-2 mt-0.5">2</span>
                        <span>Conecte o frasco de selante ao compressor</span>
                    </li>
                    <li class="flex items-start">
                        <span class="bg-purple-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs mr-2 mt-0.5">3</span>
                        <span>Conecte mangueira à válvula do pneu</span>
                    </li>
                    <li class="flex items-start">
                        <span class="bg-purple-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs mr-2 mt-0.5">4</span>
                        <span>Ligue compressor na tomada 12V</span>
                    </li>
                </ol>
                <ol start="5" class="space-y-2 text-sm text-purple-700">
                    <li class="flex items-start">
                        <span class="bg-purple-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs mr-2 mt-0.5">5</span>
                        <span>Injete selante e ar até pressão correta</span>
                    </li>
                    <li class="flex items-start">
                        <span class="bg-purple-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs mr-2 mt-0.5">6</span>
                        <span>Dirija imediatamente por 10 km</span>
                    </li>
                    <li class="flex items-start">
                        <span class="bg-purple-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs mr-2 mt-0.5">7</span>
                        <span>Verifique pressão novamente</span>
                    </li>
                    <li class="flex items-start">
                        <span class="bg-purple-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs mr-2 mt-0.5">8</span>
                        <span>Procure borracharia para reparo definitivo</span>
                    </li>
                </ol>
            </div>
        </div>
    </div>
    @endif

    <!-- Dicas de Manutenção -->
    <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-green-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            Dicas de Manutenção do Estepe
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Frequência -->
            <div>
                <h4 class="font-semibold text-green-800 mb-3">📅 Frequência de Verificação:</h4>
                <ul class="space-y-2 text-sm text-green-700">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2"></span>
                        <span>Pressão: mensalmente</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2"></span>
                        <span>Inspeção visual: a cada 3 meses</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2"></span>
                        <span>Troca: a cada 6-8 anos</span>
                    </li>
                </ul>
            </div>

            <!-- Cuidados -->
            <div>
                <h4 class="font-semibold text-green-800 mb-3">🛡️ Cuidados Importantes:</h4>
                <ul class="space-y-2 text-sm text-green-700">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2"></span>
                        <span>Evite exposição ao sol direto</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2"></span>
                        <span>Mantenha longe de produtos químicos</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2"></span>
                        <span>Não deixe objetos pesados sobre ele</span>
                    </li>
                </ul>
            </div>

            <!-- Ferramentas -->
            <div>
                <h4 class="font-semibold text-green-800 mb-3">🔧 Ferramentas Necessárias:</h4>
                <ul class="space-y-2 text-sm text-green-700">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2"></span>
                        <span>Macaco hidráulico</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2"></span>
                        <span>Chave de roda</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2"></span>
                        <span>Triângulo de segurança</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Situações de Emergência -->
    <div class="mt-6 bg-red-50 border border-red-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-red-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Situações de Emergência
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Quando NÃO usar o estepe -->
            <div>
                <h4 class="font-semibold text-red-800 mb-3">🚫 Quando NÃO usar o estepe:</h4>
                <ul class="space-y-2 text-sm text-red-700">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Estepe também furado ou danificado</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Pressão muito baixa (menos de 40 PSI)</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Rachaduras visíveis na borracha</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Separação da banda de rodagem</span>
                    </li>
                </ul>
            </div>

            <!-- Alternativas de Emergência -->
            <div>
                <h4 class="font-semibold text-red-800 mb-3">🆘 Alternativas de Emergência:</h4>
                <ul class="space-y-2 text-sm text-red-700">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Ligue para seguro ou assistência 24h</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Procure borracharia móvel</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Use aplicativos de reboque</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Contate concessionária autorizada</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Nota Final -->
    <div class="mt-6 bg-amber-50 border border-amber-200 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-amber-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div class="text-sm">
                <p class="font-medium text-amber-800 mb-1">🛞 Lembre-se:</p>
                <p class="text-amber-700">
                    O estepe é uma solução temporária para chegar com segurança ao destino ou borracharia. 
                    Sempre que usar o estepe {{ $spareType === 'temporário' ? 'temporário' : '' }}, dirija com cautela, 
                    respeite os limites de velocidade e distância, e substitua o pneu principal o mais rápido possível.
                </p>
            </div>
        </div>
    </div>
</section>