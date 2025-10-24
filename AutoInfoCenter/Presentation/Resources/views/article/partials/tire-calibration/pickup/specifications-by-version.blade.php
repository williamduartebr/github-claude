{{--
Partial: ideal-tire-pressure/pickup/specifications-by-version.blade.php
Exibe especificações detalhadas por versão usando dados embarcados
--}}

@php
$tireSpecs = $article->getData()['tire_specifications_by_version'] ?? [];
@endphp
<!-- Especificações dos Pneus por Versão -->
@if(!empty($tireSpecs))
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b-2 border-[#0E368A]/30">
        🔧 Especificações dos Pneus por Versão
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($tireSpecs as $spec)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow p-6">
            <div class="border-b border-gray-100 pb-4 mb-4">
                <h3 class="text-lg font-bold text-[#0E368A] mb-1">
                    {{ \Str::upper($spec['version']) ?? \Str::upper($spec['versao']) ?? 'Versão Principal'
                    }}
                </h3>
                @if(!empty($spec['tire_size']) || !empty($spec['medida_pneus']))
                <p class="text-sm text-gray-600 font-mono">
                    {{ $spec['tire_size'] ?? $spec['medida_pneus'] }}
                </p>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Uso Normal</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600">Dianteiro:</span>
                            <span class="text-sm font-semibold text-blue-600">
                                {{ $spec['front_normal'] ?? $spec['pressao_dianteiro_normal'] ??
                                $pressureSpecs['pressure_empty_front'] ?? '35' }} PSI
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600">Traseiro:</span>
                            <span class="text-sm font-semibold text-blue-600">
                                {{ $spec['rear_normal'] ?? $spec['pressao_traseiro_normal'] ??
                                $pressureSpecs['pressure_empty_rear'] ?? '40' }} PSI
                            </span>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Com Carga</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600">Dianteiro:</span>
                            <span class="text-sm font-semibold text-orange-600">
                                {{ $spec['front_loaded'] ?? $spec['pressao_dianteiro_carregado'] ??
                                $pressureSpecs['pressure_max_front'] ?? '38' }} PSI
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600">Traseiro:</span>
                            <span class="text-sm font-semibold text-orange-600">
                                {{ $spec['rear_loaded'] ?? $spec['pressao_traseiro_carregado'] ??
                                $pressureSpecs['pressure_max_rear'] ?? '450' }} PSI
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif