@php
$unitConversion = $article->getData()['unit_conversion'] ?? $contentData['conversao_unidades'] ?? [];
@endphp

@if(!empty($unitConversion) || true)
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b-2 border-[#0E368A]/30">
        🔄 Conversão de Unidades
    </h2>

    <div class="bg-gradient-to-br from-green-50 to-emerald-100 border border-green-200 rounded-2xl p-8">
        <h3 class="text-center text-xl font-bold text-green-800 mb-6">Tabela de Conversão PSI</h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
            @if(!empty($unitConversion['conversion_table']) || !empty($unitConversion['tabela_conversao']))
            @php $conversionTable = $unitConversion['conversion_table'] ??
            $unitConversion['tabela_conversao'] ?? []; @endphp
            @foreach(array_slice($conversionTable, 0, 4) as $conversion)
            <div class="bg-white rounded-xl border border-green-200 p-4 text-center shadow-sm">
                <div class="text-xs font-semibold text-green-600 mb-1">{{ $conversion['psi'] }} PSI</div>
                <div class="text-sm text-green-800 font-medium">{{ $conversion['kgf_cm2'] }} kgf/cm²</div>
                <div class="text-xs text-green-700">{{ $conversion['bar'] }} Bar</div>
            </div>
            @endforeach
            @else
            <!-- Conversões baseadas nos dados de pressão -->
            <div class="bg-white rounded-xl border border-green-200 p-4 text-center shadow-sm">
                <div class="text-xs font-semibold text-green-600 mb-1">{{
                    $pressureSpecs['pressure_empty_front'] ?? '35' }} PSI</div>
                <div class="text-sm text-green-800 font-medium">{{
                    number_format(($pressureSpecs['pressure_empty_front'] ?? 35) / 14.22, 1) }} kgf/cm²
                </div>
                <div class="text-xs text-green-700">{{ number_format(($pressureSpecs['pressure_empty_front']
                    ?? 35) / 14.5, 1) }} Bar</div>
            </div>

            <div class="bg-white rounded-xl border border-green-200 p-4 text-center shadow-sm">
                <div class="text-xs font-semibold text-green-600 mb-1">{{
                    $pressureSpecs['pressure_empty_rear'] ?? '40' }} PSI</div>
                <div class="text-sm text-green-800 font-medium">{{
                    number_format(($pressureSpecs['pressure_empty_rear'] ?? 40) / 14.22, 1) }} kgf/cm²</div>
                <div class="text-xs text-green-700">{{ number_format(($pressureSpecs['pressure_empty_rear']
                    ?? 40) / 14.5, 1) }} Bar</div>
            </div>

            <div class="bg-white rounded-xl border border-green-200 p-4 text-center shadow-sm">
                <div class="text-xs font-semibold text-green-600 mb-1">{{
                    $pressureSpecs['pressure_max_front'] ?? '38' }} PSI</div>
                <div class="text-sm text-green-800 font-medium">{{
                    number_format(($pressureSpecs['pressure_max_front'] ?? 38) / 14.22, 1) }} kgf/cm²</div>
                <div class="text-xs text-green-700">{{ number_format(($pressureSpecs['pressure_max_front']
                    ?? 38) / 14.5, 1) }} Bar</div>
            </div>

            <div class="bg-white rounded-xl border border-green-200 p-4 text-center shadow-sm">
                <div class="text-xs font-semibold text-green-600 mb-1">{{
                    $pressureSpecs['pressure_max_rear'] ?? '45' }} PSI</div>
                <div class="text-sm text-green-800 font-medium">{{
                    number_format(($pressureSpecs['pressure_max_rear'] ?? 45) / 14.22, 1) }} kgf/cm²</div>
                <div class="text-xs text-green-700">{{ number_format(($pressureSpecs['pressure_max_rear'] ??
                    45) / 14.5, 1) }} Bar</div>
            </div>
            @endif
        </div>

        <div class="mt-6 text-center">
            <p class="text-sm text-green-800 font-medium">
                <strong>Fórmulas:</strong> PSI ÷ 14,22 = kgf/cm² • PSI ÷ 14,5 = Bar
            </p>
        </div>
    </div>
</section>
@endif