@php
$extractedEntities = $article->getData()['extracted_entities'] ?? [];
@endphp

<!-- Informações Técnicas do Veículo -->
@if(!empty($extractedEntities))
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
            @if(!empty($extractedEntities['marca']))
            <div>
                <span class="text-gray-600">Marca:</span>
                <div class="font-semibold">{{ $extractedEntities['marca'] }}</div>
            </div>
            @endif

            @if(!empty($extractedEntities['modelo']))
            <div>
                <span class="text-gray-600">Modelo:</span>
                <div class="font-semibold">{{ $extractedEntities['modelo'] }}</div>
            </div>
            @endif

            @if(!empty($extractedEntities['categoria']))
            <div>
                <span class="text-gray-600">Categoria:</span>
                <div class="font-semibold capitalize">{{ $extractedEntities['categoria'] }}</div>
            </div>
            @endif

            @if(!empty($extractedEntities['tipo_veiculo']))
            <div>
                <span class="text-gray-600">Tipo:</span>
                <div class="font-semibold">{{ $extractedEntities['tipo_veiculo'] }}</div>
            </div>
            @endif

            @if(!empty($extractedEntities['pneus']))
            <div class="md:col-span-2">
                <span class="text-gray-600">Medida dos Pneus:</span>
                <div class="font-semibold text-blue-600 bg-blue-50 px-3 py-1 rounded-md inline-block mt-1">
                    {{ $extractedEntities['pneus'] }}
                </div>
            </div>
            @endif
        </div>

        <!-- Resumo do Veículo -->
        <div class="mt-4 pt-4 border-t border-gray-300">
            <p class="text-sm text-gray-700 text-center">
                <strong>{{ $extractedEntities['marca'] ?? '' }} {{ $extractedEntities['modelo'] ?? '' }}</strong>
                - Especificações oficiais para calibragem de pneus
            </p>
        </div>
    </div>
</section>
@endif