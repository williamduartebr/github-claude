@if(!empty($article->front_tires) && is_array($article->front_tires) && count($article->front_tires) > 0)
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Melhores Pneus
        Dianteiros para {{ $article->vehicle_info['make'] ?? 'Veículo' }} {{ $article->vehicle_info['model']
        ?? '' }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($article->front_tires as $tire)
        @if(!empty($tire['nome_pneu']))
        <div
            class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="bg-[#0E368A] text-white px-4 py-3">
                <h3 class="font-medium">{{ $tire['categoria'] ?? 'Recomendado' }}</h3>
            </div>
            <div class="p-5">
                <div class="flex items-center justify-center mb-4">
                    <span class="inline-block bg-[#E06600] text-white text-xs font-medium px-2.5 py-1 rounded">
                        {{ $tire['categoria'] === 'Melhor Custo-Benefício' ? 'MAIS VENDIDO' : 'PREMIUM' }}
                    </span>
                </div>
                <h4 class="text-xl font-semibold text-center mb-3">{{ $tire['nome_pneu'] }}</h4>
                <div class="flex justify-between text-sm mb-4">
                    <span class="text-gray-600">Medida:</span>
                    <span class="font-medium">{{ $tire['medida'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between text-sm mb-4">
                    <span class="text-gray-600">Índice de Carga:</span>
                    <span class="font-medium">{{ $tire['indice_carga'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between text-sm mb-4">
                    <span class="text-gray-600">Índice de Velocidade:</span>
                    <span class="font-medium">{{ $tire['indice_velocidade'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Tipo:</span>
                    <span class="font-medium">{{ $tire['tipo'] ?? 'N/A' }}</span>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-2 text-sm">
                    <div class="bg-green-50 p-1.5 rounded text-center">
                        <span class="block text-xs text-gray-500">Preço Médio</span>
                        <span class="font-medium text-green-700">{{ $tire['preco_medio'] ?? 'N/A' }}</span>
                    </div>
                    <div class="bg-blue-50 p-1.5 rounded text-center">
                        <span class="block text-xs text-gray-500">Durabilidade</span>
                        <span class="font-medium text-blue-700">{{ $tire['durabilidade'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>
</section>
@endif