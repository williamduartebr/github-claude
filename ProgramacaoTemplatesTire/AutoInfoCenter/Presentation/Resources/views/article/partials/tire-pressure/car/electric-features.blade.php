{{-- 
Partial: tire-pressure/car/electric-features.blade.php
Seção específica para veículos elétricos e híbridos
Só é exibida quando o veículo é elétrico ou híbrido
--}}

@php
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $isElectric = $vehicleInfo['is_electric'] ?? false;
    $isHybrid = $vehicleInfo['is_hybrid'] ?? false;
    $electricInfo = $article->getData()['electric_info'] ?? [];
@endphp

<section class="mb-12">
    <div class="bg-gradient-to-br from-green-600 to-emerald-700 text-white rounded-lg p-8 relative overflow-hidden">
        <!-- Ícone decorativo -->
        <div class="absolute top-4 right-4 text-6xl opacity-20">
            @if($isElectric)
                🔋
            @else
                ⚡
            @endif
        </div>

        <div class="relative z-10">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                    @if($isElectric)
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.477.859h4z"/>
                        </svg>
                    @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    @endif
                </div>
                <div>
                    <h2 class="text-2xl font-bold">
                        @if($isElectric)
                            Veículo 100% Elétrico
                        @else
                            Veículo Híbrido
                        @endif
                    </h2>
                    <p class="text-green-100 text-sm">
                        Pressões otimizadas para máxima eficiência energética
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <!-- Autonomia -->
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                    <div class="flex items-center mb-3">
                        <span class="text-2xl mr-3">🏃‍♂️</span>
                        <h3 class="font-semibold">Autonomia</h3>
                    </div>
                    <p class="text-green-100 text-sm">
                        Pressões corretas aumentam autonomia em até 10%
                    </p>
                </div>

                <!-- Eficiência -->
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                    <div class="flex items-center mb-3">
                        <span class="text-2xl mr-3">📊</span>
                        <h3 class="font-semibold">Eficiência</h3>
                    </div>
                    <p class="text-green-100 text-sm">
                        Menor resistência ao rolamento = menos consumo
                    </p>
                </div>

                <!-- Regeneração -->
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                    <div class="flex items-center mb-3">
                        <span class="text-2xl mr-3">🔄</span>
                        <h3 class="font-semibold">Regeneração</h3>
                    </div>
                    <p class="text-green-100 text-sm">
                        Melhora eficiência da frenagem regenerativa
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Impacto das Pressões na Autonomia -->
    <div class="mt-6 bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Impacto das Pressões na Autonomia
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Pressão Baixa -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white font-bold text-sm">↓</span>
                    </div>
                    <h4 class="font-semibold text-red-800">Pressão Baixa</h4>
                </div>
                <div class="space-y-2 text-sm">
                    <p class="text-red-700"><strong>-5 PSI:</strong> Reduz autonomia em 5%</p>
                    <p class="text-red-700"><strong>-10 PSI:</strong> Reduz autonomia em 10%</p>
                    <p class="text-red-600 text-xs">Maior resistência ao rolamento</p>
                </div>
            </div>

            <!-- Pressão Ideal -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white font-bold text-sm">✓</span>
                    </div>
                    <h4 class="font-semibold text-green-800">Pressão Ideal</h4>
                </div>
                <div class="space-y-2 text-sm">
                    <p class="text-green-700"><strong>Conforme tabela:</strong> Autonomia máxima</p>
                    <p class="text-green-700"><strong>Eficiência:</strong> 100% otimizada</p>
                    <p class="text-green-600 text-xs">Resistência mínima ao rolamento</p>
                </div>
            </div>

            <!-- Pressão Alta -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white font-bold text-sm">↑</span>
                    </div>
                    <h4 class="font-semibold text-yellow-800">Pressão Alta</h4>
                </div>
                <div class="space-y-2 text-sm">
                    <p class="text-yellow-700"><strong>+5 PSI:</strong> Autonomia ligeiramente maior</p>
                    <p class="text-yellow-700"><strong>Porém:</strong> Conforto e aderência comprometidos</p>
                    <p class="text-yellow-600 text-xs">Desgaste irregular dos pneus</p>
                </div>
            </div>
        </div>
    </div>

    @if($isElectric)
    <!-- Dicas Específicas para Veículos Elétricos -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            Dicas Premium para Veículos Elétricos
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-semibold text-blue-800 mb-3">🔋 Otimização da Bateria:</h4>
                <ul class="space-y-2 text-sm text-blue-700">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                        <span>Verifique pressões antes de viagens longas</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                        <span>No inverno, pressões ligeiramente maiores ajudam</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                        <span>Pneus de baixa resistência potencializam autonomia</span>
                    </li>
                </ul>
            </div>

            <div>
                <h4 class="font-semibold text-blue-800 mb-3">⚡ Carregamento e Pressão:</h4>
                <ul class="space-y-2 text-sm text-blue-700">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                        <span>Não calibre logo após carregamento rápido</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                        <span>Aguarde pelo menos 2 horas para esfriar</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                        <span>Monitore temperatura dos pneus no verão</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    @endif

    @if($isHybrid)
    <!-- Dicas Específicas para Veículos Híbridos -->
    <div class="mt-6 bg-purple-50 border border-purple-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-purple-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Dicas para Veículos Híbridos
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-semibold text-purple-800 mb-3">🚗 Modo Elétrico:</h4>
                <ul class="space-y-2 text-sm text-purple-700">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-purple-500 rounded-full mr-2 mt-2"></span>
                        <span>Pressões ideais maximizam uso do motor elétrico</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-purple-500 rounded-full mr-2 mt-2"></span>
                        <span>Menor resistência = mais tempo em modo EV</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-purple-500 rounded-full mr-2 mt-2"></span>
                        <span>Economia de combustível otimizada</span>
                    </li>
                </ul>
            </div>

            <div>
                <h4 class="font-semibold text-purple-800 mb-3">⛽ Modo Combustão:</h4>
                <ul class="space-y-2 text-sm text-purple-700">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-purple-500 rounded-full mr-2 mt-2"></span>
                        <span>Frenagem regenerativa mais eficiente</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-purple-500 rounded-full mr-2 mt-2"></span>
                        <span>Transições suaves entre os motores</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-purple-500 rounded-full mr-2 mt-2"></span>
                        <span>Menor consumo geral de combustível</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    @endif

    <!-- Alerta de Temperatura -->
    <div class="mt-6 bg-orange-50 border border-orange-200 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-orange-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div class="text-sm">
                <p class="font-medium text-orange-800 mb-1">🌡️ Atenção à Temperatura:</p>
                <p class="text-orange-700">
                    @if($isElectric)
                    Veículos elétricos geram menos calor que convencionais, mas carregamento rápido e uso intenso
                    @else
                    Veículos híbridos podem ter variações de temperatura devido à alternância entre motores
                    @endif
                    podem aquecer pneus. Sempre calibre com pneus frios para medições precisas.
                </p>
            </div>
        </div>
    </div>
</section>