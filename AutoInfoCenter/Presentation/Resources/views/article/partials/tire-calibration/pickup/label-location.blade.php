@php
$labelLocation = $article->getData()['label_location'] ?? $contentData['localizacao_etiqueta'] ?? [];
@endphp

@if(!empty($labelLocation))

<section class="mb-8">
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6 shadow-sm">
        <div class="flex items-start mb-4">
            <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center mr-4 flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">📍 Localização da Etiqueta de Pressão</h3>
                <p class="text-sm text-gray-600 mb-4">
                    {{ $labelLocation['description'] ?? $labelLocation['descricao'] ?? 'Localizações mais comuns para
                    encontrar as informações oficiais de pressão dos pneus.' }}
                </p>
            </div>
        </div>

        @php
        $mainLocation = $labelLocation['main_location'] ?? $labelLocation['local_principal'] ?? 'Porta do motorista';
        $alternativeLocations = $labelLocation['alternative_locations'] ?? $labelLocation['locais_alternativos'] ??
        ['Manual do proprietário', 'Porta-luvas'];
        $locations = array_merge([$mainLocation], $alternativeLocations);
        $priorities = ['Principal', 'Alternativo', 'Outro', 'Adicional'];
        $colors = ['green', 'blue', 'gray', 'purple'];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-{{ min(count($locations), 3) }} gap-4 mb-4">
            @foreach($locations as $index => $location)
            <div class="bg-white rounded-lg p-4 border border-blue-100 shadow-sm">
                <div class="flex items-center mb-2">
                    <span class="inline-block w-2 h-2 bg-{{ $colors[$index] ?? 'gray' }}-500 rounded-full mr-2"></span>
                    <span class="text-xs font-medium text-{{ $colors[$index] ?? 'gray' }}-700 uppercase tracking-wide">
                        {{ $priorities[$index] ?? 'Outro' }}
                    </span>
                </div>
                <p class="text-sm font-medium text-gray-900">{{ $location }}</p>
                @if($index === 0)
                <p class="text-xs text-gray-500 mt-1">Batente ou marco da porta</p>
                @elseif(str_contains(strtolower($location), 'manual'))
                <p class="text-xs text-gray-500 mt-1">Seção de especificações</p>
                @else
                <p class="text-xs text-gray-500 mt-1">Localização adicional</p>
                @endif
            </div>
            @endforeach
        </div>

        @if(!empty($labelLocation['note']) || !empty($labelLocation['observacao']))
        <div class="bg-blue-100 border border-blue-200 rounded-lg p-3">
            <div class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 mr-2 flex-shrink-0 mt-0.5"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-xs text-blue-800">
                    <strong>💡 Dica:</strong> {{ $labelLocation['note'] ?? $labelLocation['observacao'] }}
                </p>
            </div>
        </div>
        @endif
    </div>
</section>
@endif