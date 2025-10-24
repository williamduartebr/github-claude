@php
$careRecommendations = $article->getData()['care_recommendations'] ?? $contentData['cuidados_recomendacoes']
?? [];
@endphp

@if(!empty($careRecommendations) || !empty($contentData['cuidados_recomendacoes']))
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b-2 border-[#0E368A]/30">
        🛠️ Cuidados Específicos para Pickups
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($careRecommendations ?? $contentData['cuidados_recomendacoes'] ?? [] as $dica)
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-6 shadow-sm">
            <div class="flex items-center mb-4">
                <div class="h-10 w-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                    <span class="text-white text-lg">🔧</span>
                </div>
                <h3 class="font-semibold text-blue-800">
                    {{ $dica['categoria'] ?? $dica['category'] ?? $dica['title'] }}
                </h3>
            </div>

            <p class="text-blue-700 mb-4 leading-relaxed">
                {{ $dica['descricao'] ?? $dica['description'] }}
            </p>

            @if(!empty($dica['procedures']) && is_array($dica['procedures']))
            <div class="space-y-2">
                @foreach($dica['procedures'] as $procedure)
                <div class="flex items-start">
                    <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2 flex-shrink-0"></span>
                    <p class="text-sm text-blue-700">{{ $procedure }}</p>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Alerta especial para pickups -->
    <div class="mt-8 bg-gradient-to-r from-amber-50 to-orange-50 border-l-4 border-amber-400 rounded-lg p-6">
        <div class="flex items-start">
            <div class="flex-shrink-0 mr-4">
                <div class="h-8 w-8 bg-amber-400 rounded-full flex items-center justify-center">
                    <span class="text-white font-bold">!</span>
                </div>
            </div>
            <div>
                <h3 class="font-semibold text-amber-800 mb-2">Atenção Especial para Pickups</h3>
                <p class="text-amber-700 leading-relaxed">
                    Pickups sofrem variações maiores de carga que carros comuns. Variações de peso de
                    300-1000kg na caçamba exigem ajustes frequentes na pressão dos pneus para manter
                    segurança e economia. Verifique sempre antes de carregar ou descarregar peso
                    significativo.
                </p>
            </div>
        </div>
    </div>
</section>
@endif