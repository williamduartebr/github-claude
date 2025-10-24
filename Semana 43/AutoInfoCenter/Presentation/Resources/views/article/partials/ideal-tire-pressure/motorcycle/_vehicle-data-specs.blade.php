@php
$extractedEntities = $article->getData()['extracted_entities'] ?? [];
@endphp

@if(!empty($extractedEntities))
<section class="mb-8">
    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="mr-1" width="24" height="24" viewBox="0 0 48 48" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M24 44C29.5228 44 34.5228 41.7614 38.1421 38.1421C41.7614 34.5228 44 29.5228 44 24C44 18.4772 41.7614 13.4772 38.1421 9.85786C34.5228 6.23858 29.5228 4 24 4C18.4772 4 13.4772 6.23858 9.85786 9.85786C6.23858 13.4772 4 18.4772 4 24C4 29.5228 6.23858 34.5228 9.85786 38.1421C13.4772 41.7614 18.4772 44 24 44Z"
                    fill="#4a90e2" stroke="#4a90e2" stroke-width="4" stroke-linejoin="round" />
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M24 11C25.3807 11 26.5 12.1193 26.5 13.5C26.5 14.8807 25.3807 16 24 16C22.6193 16 21.5 14.8807 21.5 13.5C21.5 12.1193 22.6193 11 24 11Z"
                    fill="#FFF" />
                <path d="M24.5 34V20H23.5H22.5" stroke="#FFF" stroke-width="4" stroke-linecap="round"
                    stroke-linejoin="round" />
                <path d="M21 34H28" stroke="#FFF" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
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
        </div>

        @if(!empty($extractedEntities['pneus']))
        @php
        $pneus = explode(' ', $extractedEntities['pneus']);
        $dianteiro = '';
        $traseiro = '';

        foreach($pneus as $index => $pneu) {
        if(str_contains($pneu, '(DIANTEIRO)')) {
        $dianteiro = $pneus[$index - 1] ?? '';
        }
        if(str_contains($pneu, '(TRASEIRO)')) {
        $traseiro = $pneus[$index - 1] ?? '';
        }
        }
        @endphp

        <div class="mt-6">
            <span class="text-gray-600 text-sm mb-3 block">Medida dos Pneus:</span>
            <div class="grid grid-cols-2 gap-4">
                @if($dianteiro)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
                    <div class="text-xs text-blue-600 mb-1">DIANTEIRO</div>
                    <div class="text-blue-700 font-semibold">{{ $dianteiro }}</div>
                </div>
                @endif

                @if($traseiro)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
                    <div class="text-xs text-blue-600 mb-1">TRASEIRO</div>
                    <div class="text-blue-700 font-semibold">{{ $traseiro }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif

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