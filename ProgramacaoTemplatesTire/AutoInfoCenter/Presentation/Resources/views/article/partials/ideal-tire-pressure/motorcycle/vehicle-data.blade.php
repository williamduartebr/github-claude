{{--
Partial: ideal-tire-pressure/motorcycle/vehicle-data.blade.php
Dados principais do veículo específicos para motocicletas
Formatação adequada para categorias e tipos de motos
--}}

@php
$vehicleInfo = $article->getData()['vehicle_info'] ?? [];
$pressureSpecs = $article->getData()['pressure_specifications'] ?? [];

// Formatação específica para categoria de moto
$categoryDisplayMap = [
'motorcycle_street' => 'Motocicleta Street',
'motorcycle_naked' => 'Motocicleta Naked',
'motorcycle_sport' => 'Motocicleta Esportiva',
'motorcycle_touring' => 'Motocicleta Touring',
'motorcycle_adventure' => 'Motocicleta Adventure',
'motorcycle_cruiser' => 'Motocicleta Cruiser',
'motorcycle_scooter' => 'Scooter',
'naked' => 'Naked',
'sport' => 'Esportiva',
'street' => 'Street',
'touring' => 'Touring',
'adventure' => 'Adventure',
'cruiser' => 'Cruiser',
'scooter' => 'Scooter'
];

$categoryRaw = $vehicleInfo['category'] ?? $vehicleInfo['main_category'] ?? 'street';
$categoryDisplay = $categoryDisplayMap[$categoryRaw] ?? ucfirst(str_replace(['_', 'motorcycle'], [' ', ''],
$categoryRaw));
@endphp

<!-- Dados Técnicos do Veículo -->
<section class="mb-10">
    <div class="bg-gradient-to-r from-gray-600 to-gray-800 text-white rounded-t-lg p-6">
        <div class="flex items-center">
            <span class="text-3xl mr-4">ℹ️</span>
            <div>
                <h2 class="text-2xl font-bold mb-2">
                    Dados Técnicos do Veículo
                </h2>
                <p class="text-gray-300 text-sm">
                    Informações específicas da {{ $vehicleInfo['full_name'] ?? 'motocicleta' }}
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-b-lg border-l border-r border-b border-gray-200 p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Marca -->
            <div class="text-center">
                <h3 class="text-sm font-semibold text-gray-600 mb-2">Marca:</h3>
                <p class="text-lg font-bold text-gray-900">{{ $vehicleInfo['make'] ?? 'N/D' }}</p>
            </div>

            <!-- Modelo -->
            <div class="text-center">
                <h3 class="text-sm font-semibold text-gray-600 mb-2">Modelo:</h3>
                <p class="text-lg font-bold text-gray-900">{{ $vehicleInfo['model'] ?? 'N/D' }}</p>
            </div>

            <!-- Categoria -->
            <div class="text-center">
                <h3 class="text-sm font-semibold text-gray-600 mb-2">Categoria:</h3>
                <p class="text-lg font-bold text-gray-900">Motocicletas</p>
            </div>
        </div>
    </div>
</section>

<!-- Destaque Principal da Pressão Ideal -->
<section class="mb-10">
    <div class="bg-gradient-to-br from-[#DC2626] to-red-700 text-white rounded-xl p-8 relative overflow-hidden">
        <!-- Ícone decorativo -->
        <div class="absolute top-4 right-4 text-6xl opacity-20">
            🎯
        </div>

        <!-- Título Principal -->
        <div class="relative z-10">
            <h2 class="text-2xl lg:text-3xl font-bold mb-2">
                Pressão Ideal para {{ $vehicleInfo['full_name'] ?? 'Sua Motocicleta' }}
            </h2>
            <p class="text-red-100 text-sm mb-6">
                Valores oficiais da montadora em PSI (padrão brasileiro)
            </p>
        </div>

        <!-- Grid de Pressões para Motos -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
            <!-- Piloto Solo -->
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 border border-white/20">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                        <span class="text-2xl">🏍️</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg">Piloto Solo</h3>
                        <p class="text-red-100 text-sm">Uso urbano normal</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="text-center">
                        <div class="text-sm text-red-200 mb-1">Dianteiro</div>
                        <div class="text-2xl font-bold">
                            {{ str_replace([' PSI', ' psi'], '', $pressureSpecs['front_solo'] ??
                            $pressureSpecs['pressure_empty_front'] ?? 'Consulte manual') }}
                        </div>
                        <div class="text-xs text-red-200">PSI</div>
                    </div>
                    <div class="text-center">
                        <div class="text-sm text-red-200 mb-1">Traseiro</div>
                        <div class="text-2xl font-bold">
                            {{ str_replace([' PSI', ' psi'], '', $pressureSpecs['rear_solo'] ??
                            $pressureSpecs['pressure_empty_rear'] ?? 'Consulte manual') }}
                        </div>
                        <div class="text-xs text-red-200">PSI</div>
                    </div>
                </div>
            </div>

            <!-- Com Garupa -->
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 border border-white/20">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                        <span class="text-2xl">👥</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg">Com Garupa</h3>
                        <p class="text-red-100 text-sm">Dois ocupantes</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="text-center">
                        <div class="text-sm text-red-200 mb-1">Dianteiro</div>
                        <div class="text-2xl font-bold">
                            {{ str_replace([' PSI', ' psi'], '', $pressureSpecs['front_passenger'] ??
                            $pressureSpecs['pressure_max_front'] ?? 'Consulte manual') }}
                        </div>
                        <div class="text-xs text-red-200">PSI</div>
                    </div>
                    <div class="text-center">
                        <div class="text-sm text-red-200 mb-1">Traseiro</div>
                        <div class="text-2xl font-bold">
                            {{ str_replace([' PSI', ' psi'], '', $pressureSpecs['rear_passenger'] ??
                            $pressureSpecs['pressure_max_rear'] ?? 'Consulte manual') }}
                        </div>
                        <div class="text-xs text-red-200">PSI</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerta Importante -->
        <div class="mt-6 bg-yellow-500/20 border border-yellow-400/30 rounded-lg p-4 relative z-10">
            <div class="flex items-center">
                <span class="text-yellow-300 mr-3 text-xl">⚠️</span>
                <p class="text-yellow-100 text-sm">
                    <strong>Importante:</strong> Sempre calibre com pneus frios. Em motocicletas, pressões incorretas
                    podem ser fatais.
                </p>
            </div>
        </div>
    </div>
</section>