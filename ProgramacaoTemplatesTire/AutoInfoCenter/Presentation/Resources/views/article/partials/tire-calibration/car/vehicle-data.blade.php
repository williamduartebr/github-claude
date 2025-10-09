{{--
Partial: ideal-tire-pressure/shared/vehicle-data.blade.php
Componente reutilizável para exibir dados principais do veículo e pressão ideal
Usa dados embarcados das ViewModels
--}}

@php
$vehicleInfo = $article->getData()['vehicle_info'] ?? [];
$pressureSpecs = $article->getData()['pressure_specifications'] ?? [];
$mainTireSpec = $article->getData()['tire_specifications_by_version'] ?? null;
@endphp

<!-- Destaque Principal da Pressão Ideal -->
<section class="mb-10">
    <div class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white rounded-xl p-8 relative overflow-hidden">
        <!-- Ícone decorativo -->
        <div class="absolute top-4 right-4 text-6xl opacity-20">
            🎯
        </div>

        <!-- Título Principal -->
        <div class="relative z-10">
            <h2 class="text-2xl lg:text-3xl font-bold mb-2">
                Pressão Ideal para {{ $vehicleInfo['full_name'] ?? 'Seu Veículo' }}
            </h2>
            <p class="text-blue-100 text-sm mb-6">
                Valores oficiais da montadora em PSI (padrão brasileiro)
            </p>
        </div>

        <!-- Grid de Pressões -->
        @if($mainTireSpec)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
            <!-- Pneus Dianteiros -->
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 border border-white/20">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                        <span class="text-2xl">🔄</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg">Pneus Dianteiros</h3>
                        <p class="text-blue-100 text-sm">Uso normal (1-3 pessoas)</p>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold mb-1">
                        {{ str_replace([' PSI', ' psi'], '', $mainTireSpec['front_normal'] ?? '30') }}
                    </div>
                    <div class="text-blue-100 text-sm">PSI (libras por pol²)</div>
                </div>
            </div>

            <!-- Pneus Traseiros -->
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 border border-white/20">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                        <span class="text-2xl">🔙</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg">Pneus Traseiros</h3>
                        <p class="text-blue-100 text-sm">Uso normal (1-3 pessoas)</p>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold mb-1">
                        {{ str_replace([' PSI', ' psi'], '', $mainTireSpec['rear_normal'] ?? '28') }}
                    </div>
                    <div class="text-blue-100 text-sm">PSI (libras por pol²)</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Informações Adicionais -->
        <div class="mt-6 pt-6 border-t border-white/20 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                @if(!empty($mainTireSpec['tire_size']))
                <div class="flex items-center">
                    <span class="w-2 h-2 bg-white/60 rounded-full mr-2"></span>
                    <span>Medida: <strong>{{ $mainTireSpec['tire_size'] }}</strong></span>
                </div>
                @endif

                @if($vehicleInfo['has_tpms'] ?? false)
                <div class="flex items-center">
                    <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                    <span>Sistema <strong>TPMS</strong> disponível</span>
                </div>
                @endif

                @if($vehicleInfo['is_electric'] ?? false)
                <div class="flex items-center">
                    <span class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></span>
                    <span>Veículo <strong>Elétrico</strong></span>
                </div>
                @elseif($vehicleInfo['is_hybrid'] ?? false)
                <div class="flex items-center">
                    <span class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></span>
                    <span>Veículo <strong>Híbrido</strong></span>
                </div>
                @endif
            </div>
        </div>

        <!-- Nota Importante -->
        <div class="mt-6 bg-yellow-400/20 border border-yellow-400/30 rounded-lg p-4 relative z-10">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-300 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                </svg>
                <div class="text-sm">
                    <p class="font-medium text-yellow-100 mb-1">⚡ Lembrete Importante</p>
                    <p class="text-yellow-200">
                        Para uso com carga completa ou viagens longas, ajuste conforme a tabela específica abaixo.
                        Pressões incorretas comprometem segurança e economia.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Informações Técnicas do Veículo -->
@if(!empty($vehicleInfo))
<section class="mb-8">
    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Dados Técnicos do Veículo
        </h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            @if(!empty($vehicleInfo['make']))
            <div>
                <span class="text-gray-600">Marca:</span>
                <div class="font-semibold">{{ $vehicleInfo['make'] }}</div>
            </div>
            @endif

            @if(!empty($vehicleInfo['model']))
            <div>
                <span class="text-gray-600">Modelo:</span>
                <div class="font-semibold">{{ $vehicleInfo['model'] }}</div>
            </div>
            @endif

            @if(!empty($vehicleInfo['year']))
            <div>
                <span class="text-gray-600">Ano:</span>
                <div class="font-semibold">{{ $vehicleInfo['year'] }}</div>
            </div>
            @endif

            @if(!empty($vehicleInfo['category']))
            <div>
                <span class="text-gray-600">Categoria:</span>
                <div class="font-semibold capitalize">{{ $vehicleInfo['category'] }}</div>
            </div>
            @endif
        </div>
    </div>
</section>
@endif