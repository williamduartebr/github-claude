@php
$labelLocation =  $article->getData()['information_location'] ?? [];
@endphp

<!-- Localização da Etiqueta para Motocicletas -->
@if(!empty($labelLocation) || !empty($contentData['motorcycle_label']) || !empty($contentData['owner_manual']))
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b-2 border-[#0E368A]/30">
        📍 Onde Encontrar as Especificações de Pressão
    </h2>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
        <p class="text-gray-700 mb-8 leading-relaxed">
            Em motocicletas, as informações de pressão dos pneus podem estar em diferentes locais.
            Aqui estão os principais pontos onde verificar:
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <!-- Manual do Proprietário -->
            @if(!empty($contentData['owner_manual']) || !empty($labelLocation['owner_manual']))
            @php $ownerManual = $contentData['owner_manual'] ?? $labelLocation['owner_manual'] ?? []; @endphp
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-6">
                <div class="flex items-center mb-4">
                    <div class="h-10 w-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white text-lg">📖</span>
                    </div>
                    <div
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-500 text-white">
                        Principal
                    </div>
                </div>
                <h3 class="font-semibold text-blue-800 mb-3">Manual do Proprietário</h3>
                <div class="space-y-2 text-sm text-blue-700">
                    <div class="flex flex-col">
                        <span>Localização:</span>
                        <span class="font-medium">{{ $ownerManual['location'] ?? 'Especificações Técnicas' }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span>Seção:</span>
                        <span class="font-medium">{{ $ownerManual['section'] ?? 'Rodas e Pneus' }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span>Página:</span>
                        <span class="font-medium">{{ $ownerManual['approximate_page'] ?? 'Consulte índice' }}</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Etiqueta na Motocicleta -->
            @if(!empty($contentData['motorcycle_label']) || !empty($labelLocation['motorcycle_label']))
            @php $motorcycleLabel = $contentData['motorcycle_label'] ?? $labelLocation['motorcycle_label'] ?? [];
            @endphp
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-xl p-6">
                <div class="flex items-center mb-4">
                    <div class="h-10 w-10 bg-orange-500 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white text-lg">🏍️</span>
                    </div>
                    <div
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-orange-500 text-white">
                        Alternativo
                    </div>
                </div>
                <h3 class="font-semibold text-orange-800 mb-3">Etiqueta na Moto</h3>
                <div class="space-y-3 text-sm text-orange-700">
                    @if(!empty($motorcycleLabel['common_locations']) && is_array($motorcycleLabel['common_locations']))
                    @foreach($motorcycleLabel['common_locations'] as $location)
                    <div class="flex items-center">
                        <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 flex-shrink-0"></span>
                        <span>{{ $location }}</span>
                    </div>
                    @endforeach
                    @else
                    <div class="flex items-center">
                        <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 flex-shrink-0"></span>
                        <span>Chassi (lado direito)</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 flex-shrink-0"></span>
                        <span>Balança traseira</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 flex-shrink-0"></span>
                        <span>Garfo dianteiro</span>
                    </div>
                    @endif

                    @if(!empty($motorcycleLabel['note']) || !empty($motorcycleLabel['observacao']))
                    <div class="mt-3 p-3 bg-orange-100/50 rounded-lg">
                        <p class="text-xs font-medium">{{ $motorcycleLabel['note'] ?? $motorcycleLabel['observacao'] }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Outras Fontes -->
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 rounded-xl p-6">
                <div class="flex items-center mb-4">
                    <div class="h-10 w-10 bg-gray-500 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white text-lg">🔍</span>
                    </div>
                    <div
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-500 text-white">
                        Outros
                    </div>
                </div>
                <h3 class="font-semibold text-gray-800 mb-3">Outras Fontes</h3>
                <div class="space-y-3 text-sm text-gray-700">
                    <div class="flex items-center">
                        <span class="w-2 h-2 bg-gray-500 rounded-full mr-2 flex-shrink-0"></span>
                        <span>Concessionária autorizada</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-2 h-2 bg-gray-500 rounded-full mr-2 flex-shrink-0"></span>
                        <span>Site oficial da marca</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-2 h-2 bg-gray-500 rounded-full mr-2 flex-shrink-0"></span>
                        <span>Ficha técnica do veículo</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-2 h-2 bg-gray-500 rounded-full mr-2 flex-shrink-0"></span>
                        <span>Aplicativos da fabricante</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
@endif