<section class="mb-12">
    <div
        class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl border-2 border-orange-200 p-8 shadow-lg">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-orange-800 mb-2 flex items-center justify-center">
                🚛 Pressões Ideais para {{ $vehicleName }}
            </h2>
            <p class="text-orange-700 font-medium">
                Verificação {{ $isPremium ? 'semanal' : 'quinzenal' }} recomendada • Sempre com pneus frios
            </p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 max-w-4xl mx-auto">
            <!-- Dianteiros Normal -->
            <div class="bg-white rounded-xl border border-blue-200 p-4 text-center shadow-sm">
                <div class="text-xs font-semibold text-blue-600 mb-1">DIANTEIROS (Normal)</div>
                <div class="text-2xl font-bold text-blue-800 mb-1">{{ $pressureSpecs['pressure_empty_front']
                    ?? '35' }}</div>
                <div class="text-xs text-blue-600 font-medium mb-2">PSI</div>
                <div class="text-xs text-gray-500">Sem carga</div>
            </div>

            <!-- Traseiros Normal -->
            <div class="bg-white rounded-xl border border-blue-200 p-4 text-center shadow-sm">
                <div class="text-xs font-semibold text-blue-600 mb-1">TRASEIROS (Normal)</div>
                <div class="text-2xl font-bold text-blue-800 mb-1">{{ $pressureSpecs['pressure_empty_rear']
                    ?? '40' }}</div>
                <div class="text-xs text-blue-600 font-medium mb-2">PSI</div>
                <div class="text-xs text-gray-500">Sem carga</div>
            </div>

            <!-- Com Carga -->
            <div
                class="bg-white rounded-xl border border-orange-200 p-4 text-center shadow-sm col-span-2 md:col-span-1">
                <div class="text-xs font-semibold text-orange-600 mb-1">COM CARGA</div>
                <div class="text-2xl font-bold text-orange-800 mb-1">{{
                    $pressureSpecs['loaded_pressure_display'] ?? '38/45' }}</div>
                <div class="text-xs text-orange-600 font-medium mb-2">PSI</div>
                <div class="text-xs text-gray-500">Caçamba carregada</div>
            </div>
        </div>

        @if(!empty($pressureSpecs['pressure_spare']))
        <div class="mt-6 flex justify-center">
            <div
                class="bg-white rounded-xl border border-green-200 p-4 text-center shadow-sm min-w-[140px]">
                <div class="text-xs font-semibold text-green-600 mb-1">PNEU ESTEPE</div>
                <div class="text-2xl font-bold text-green-800 mb-1">{{ $pressureSpecs['pressure_spare'] }}
                </div>
                <div class="text-xs text-green-600 font-medium">PSI</div>
            </div>
        </div>
        @endif
    </div>
</section>