{{--
Partial: ideal-tire-pressure/car/tpms-section.blade.php
Seção específica sobre Sistema TPMS (Tire Pressure Monitoring System)
Só é exibida quando o veículo possui TPMS
--}}

@php
$vehicleInfo = $article->getData()['vehicle_info'] ?? [];
$tpmsInfo = $article->getData()['tpms_info'] ?? [];
@endphp

<section class="mb-12">
    <div class="bg-gradient-to-br from-green-600 to-emerald-700 text-white rounded-lg p-8 relative overflow-hidden">
        <!-- Ícone decorativo -->
        <div class="absolute top-4 right-4 text-6xl opacity-20">
            📡
        </div>

        <div class="relative z-10">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold">Sistema TPMS Disponível</h2>
                    <p class="text-green-100 text-sm">
                        Seu {{ $vehicleInfo['full_name'] ?? 'veículo' }} possui monitoramento automático da pressão dos
                        pneus
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <!-- Monitoramento em Tempo Real -->
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                    <div class="flex items-center mb-3">
                        <span class="text-2xl mr-3">📊</span>
                        <h3 class="font-semibold">Monitoramento</h3>
                    </div>
                    <p class="text-green-100 text-sm">
                        Pressões verificadas automaticamente durante a condução
                    </p>
                </div>

                <!-- Alertas no Painel -->
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                    <div class="flex items-center mb-3">
                        <span class="text-2xl mr-3">⚠️</span>
                        <h3 class="font-semibold">Alertas</h3>
                    </div>
                    <p class="text-green-100 text-sm">
                        Avisos no painel quando há variações significativas
                    </p>
                </div>

                <!-- Calibragem Facilitada -->
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                    <div class="flex items-center mb-3">
                        <span class="text-2xl mr-3">🔧</span>
                        <h3 class="font-semibold">Facilidade</h3>
                    </div>
                    <p class="text-green-100 text-sm">
                        Calibragem mais precisa com feedback digital
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Como Funciona o TPMS -->
    <div class="mt-6 bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
            Como Funciona o Sistema TPMS
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Funcionamento -->
            <div>
                <h4 class="font-semibold text-gray-800 mb-3">🔍 Funcionamento:</h4>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2"></span>
                        <span>Sensores em cada pneu monitoram pressão e temperatura</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2"></span>
                        <span>Dados transmitidos via radiofrequência para a central</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2"></span>
                        <span>Sistema alerta quando pressão varia mais que 25%</span>
                    </li>
                </ul>
            </div>

            <!-- Vantagens -->
            <div>
                <h4 class="font-semibold text-gray-800 mb-3">✅ Vantagens:</h4>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                        <span>Maior segurança na condução</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                        <span>Economia de combustível otimizada</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                        <span>Vida útil prolongada dos pneus</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Interpretando os Alertas TPMS -->
    <div class="mt-6 bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd" />
            </svg>
            Interpretando os Alertas do TPMS
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Alerta Amarelo -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center mb-2">
                    <div class="w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white font-bold text-sm">!</span>
                    </div>
                    <h4 class="font-semibold text-yellow-800">Alerta Amarelo</h4>
                </div>
                <p class="text-yellow-700 text-sm mb-2">
                    <strong>Pressão baixa detectada</strong>
                </p>
                <p class="text-yellow-600 text-xs">
                    Verificar e calibrar pneus assim que possível
                </p>
            </div>

            <!-- Alerta Vermelho -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center mb-2">
                    <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white font-bold text-sm">⚠</span>
                    </div>
                    <h4 class="font-semibold text-red-800">Alerta Vermelho</h4>
                </div>
                <p class="text-red-700 text-sm mb-2">
                    <strong>Pressão criticamente baixa</strong>
                </p>
                <p class="text-red-600 text-xs">
                    Parar imediatamente e verificar pneu
                </p>
            </div>

            <!-- Sistema Inativo -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex items-center mb-2">
                    <div class="w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white font-bold text-sm">—</span>
                    </div>
                    <h4 class="font-semibold text-gray-800">Sistema Inativo</h4>
                </div>
                <p class="text-gray-700 text-sm mb-2">
                    <strong>TPMS desligado ou com falha</strong>
                </p>
                <p class="text-gray-600 text-xs">
                    Verificar sistema em oficina autorizada
                </p>
            </div>
        </div>
    </div>

    <!-- Procedimento de Reset TPMS -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Como Resetar o Sistema TPMS
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Procedimento Padrão -->
            <div>
                <h4 class="font-semibold text-blue-800 mb-3">📋 Procedimento Padrão:</h4>
                <ol class="space-y-2 text-sm text-blue-700">
                    <li class="flex items-start">
                        <span
                            class="bg-blue-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs mr-2 mt-0.5">1</span>
                        <span>Calibre todos os pneus nas pressões corretas</span>
                    </li>
                    <li class="flex items-start">
                        <span
                            class="bg-blue-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs mr-2 mt-0.5">2</span>
                        <span>Ligue o veículo (sem dar partida no motor)</span>
                    </li>
                    <li class="flex items-start">
                        <span
                            class="bg-blue-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs mr-2 mt-0.5">3</span>
                        <span>Procure botão "TPMS Reset" ou acesse pelo menu</span>
                    </li>
                    <li class="flex items-start">
                        <span
                            class="bg-blue-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs mr-2 mt-0.5">4</span>
                        <span>Mantenha pressionado por 3 segundos até piscar</span>
                    </li>
                    <li class="flex items-start">
                        <span
                            class="bg-blue-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs mr-2 mt-0.5">5</span>
                        <span>Dirija por 10-15 minutos para calibração</span>
                    </li>
                </ol>
            </div>

            <!-- Importante -->
            <div>
                <h4 class="font-semibold text-blue-800 mb-3">⚠️ Importante:</h4>
                <ul class="space-y-2 text-sm text-blue-700">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                        <span>Reset necessário após calibragem ou troca de pneus</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                        <span>Temperatura dos pneus deve estar fria</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                        <span>Consulte manual para procedimento específico</span>
                    </li>
                </ul>

                @if($vehicleInfo['is_premium'] ?? false)
                <div class="mt-4 bg-purple-100 border border-purple-300 rounded-lg p-3">
                    <p class="text-purple-800 text-xs">
                        <strong>💎 Recurso Premium:</strong> Alguns modelos possuem reset automático
                        ou podem ser configurados via central multimídia.
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Manutenção do Sistema -->
    <div class="mt-6 bg-amber-50 border border-amber-200 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-amber-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd" />
            </svg>
            <div class="text-sm">
                <p class="font-medium text-amber-800 mb-1">🔧 Manutenção do TPMS:</p>
                <p class="text-amber-700">
                    Os sensores TPMS têm bateria com vida útil de 5-10 anos. Quando a bateria acabar,
                    será necessária substituição em oficina especializada. O custo varia entre R$ 150-300 por sensor.
                </p>
            </div>
        </div>
    </div>
</section>