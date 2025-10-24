@if(!empty($article->vehicle_data) && is_array($article->vehicle_data))
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        Especificações do {{ $article->vehicle_full_name ?? 'Veículo' }}
    </h2>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-200">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações Básicas</h3>
                <div class="space-y-3">
                    @if(!empty($article->vehicle_data['tire_size']))
                    <div class="flex justify-between">
                        <span class="text-gray-600">Medida dos Pneus:</span>
                        <span class="font-medium text-gray-900">{{ $article->vehicle_data['tire_size'] }}</span>
                    </div>
                    @endif
                    @if(!empty($article->vehicle_data['vehicle_category']))
                    <div class="flex justify-between">
                        <span class="text-gray-600">Categoria:</span>
                        <span class="font-medium text-gray-900">{{
                            translate_vehicle_category($article->vehicle_data['vehicle_category']) }}</span>
                    </div>
                    @endif

                    @if(!empty($article->vehicle_data['vehicle_type']))
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tipo:</span>
                        <span class="font-medium text-gray-900">{{
                            translate_vehicle_type($article->vehicle_data['vehicle_type']) }}</span>
                    </div>
                    @endif
                </div>
            </div>
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Pressões Recomendadas</h3>
                <div class="space-y-3">
                    @if(!empty($article->vehicle_data['pressure_display']))
                    <div class="bg-[#0E368A]/5 p-3 rounded-lg text-center">
                        <span class="text-sm text-gray-600">Veículo Vazio</span>
                        <div class="text-2xl font-bold text-[#0E368A]">{{ $article->vehicle_data['pressure_display'] }}
                        </div>
                    </div>
                    @endif
                    @if(!empty($article->vehicle_data['pressure_loaded_display']))
                    <div class="bg-gray-100 p-3 rounded-lg text-center">
                        <span class="text-sm text-gray-600">Com Carga</span>
                        <div class="text-xl font-semibold text-gray-700">{{
                            $article->vehicle_data['pressure_loaded_display'] }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endif