<!-- Localização da Etiqueta -->
@if(!empty($labelLocation))
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b-2 border-[#0E368A]/30">
        📍 Localização da Etiqueta de Pressão
    </h2>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
        <p class="text-gray-700 mb-6 leading-relaxed">
            {{ $labelLocation['description'] ?? $labelLocation['descricao'] ?? 'Localizações mais comuns para encontrar
            as informações oficiais de pressão dos pneus.' }}
        </p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-6 text-center">
                <div
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-500 text-white mb-3">
                    Principal
                </div>
                <div class="text-gray-800 font-medium">
                    {{ $labelLocation['main_location'] ?? $labelLocation['local_principal'] ?? 'Porta do motorista' }}
                </div>
            </div>

            @php
            $alternativeLocations = $labelLocation['alternative_locations'] ?? $labelLocation['locais_alternativos'] ??
            ['Manual do proprietário', 'Porta-luvas'];
            @endphp
            @foreach($alternativeLocations as $index => $location)
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 rounded-xl p-6 text-center">
                <div
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-500 text-white mb-3">
                    {{ $index === 0 ? 'Alternativo' : 'Outro' }}
                </div>
                <div class="text-gray-800 font-medium">{{ $location }}</div>
            </div>
            @endforeach
        </div>

        @if(!empty($labelLocation['note']) || !empty($labelLocation['observacao']))
        <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 rounded-lg p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0 mr-3">
                    <div class="h-6 w-6 bg-blue-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-xs">💡</span>
                    </div>
                </div>
                <div>
                    <p class="text-blue-800 font-medium">Dica:</p>
                    <p class="text-blue-700 text-sm mt-1">{{ $labelLocation['note'] ?? $labelLocation['observacao'] }}
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
@endif