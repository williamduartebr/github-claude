@if(!empty($article->warranty_info) && is_array($article->warranty_info))
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        🛡️ Garantia e Cuidados Especiais
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Informações de Garantia -->
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-center mb-4">
                <div
                    class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                    <span class="text-2xl">🛡️</span>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Prazo de Garantia</h3>
            </div>

            <div class="space-y-4">
                @if(!empty($article->warranty_info['prazo_garantia']))
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-700">
                        <span class="font-semibold">Garantia Contratual:</span> {{
                        $article->warranty_info['prazo_garantia'] }}
                    </p>
                </div>
                @endif

                @if(!empty($article->warranty_info['garantia_itens_desgaste']))
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-700">
                        <span class="font-semibold">Garantia para Itens de Desgaste:</span> {{
                        $article->warranty_info['garantia_itens_desgaste'] }}
                    </p>
                </div>
                @endif

                @if(!empty($article->warranty_info['garantia_anticorrosao']))
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-700">
                        <span class="font-semibold">Garantia Anticorrosão:</span> {{
                        $article->warranty_info['garantia_anticorrosao'] }}
                    </p>
                </div>
                @endif
            </div>

            @if(!empty($article->warranty_info['observacoes_importantes']))
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-sm text-gray-700">
                    <span class="font-semibold">💡 Importante:</span> {{
                    $article->warranty_info['observacoes_importantes'] }}
                </p>
            </div>
            @endif
        </div>

        <!-- Dicas para Prolongar a Vida Útil -->
        @if(!empty($article->warranty_info['dicas_vida_util']) &&
        is_array($article->warranty_info['dicas_vida_util']) &&
        count($article->warranty_info['dicas_vida_util']) > 0)
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-center mb-4">
                <div
                    class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                    <span class="text-2xl">🏍️</span>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Dicas para Motociclistas</h3>
            </div>

            <ul class="space-y-3">
                @foreach($article->warranty_info['dicas_vida_util'] as $dica)
                @if(!empty($dica))
                <li class="flex items-start">
                    <div
                        class="h-5 w-5 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-[#0E368A]" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-700">{{ $dica }}</p>
                </li>
                @endif
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <!-- Alerta de Importância -->
    @if(!empty($article->vehicle_full_name))
    <div class="bg-[#E06600]/5 border border-[#E06600]/20 rounded-lg p-5">
        <div class="flex">
            <div class="flex-shrink-0 mr-4">
                <span class="text-3xl">⚠️</span>
            </div>
            <div>
                <h3 class="text-lg font-medium text-[#E06600] mb-2">🏍️ Atenção Especial para Motocicletas
                </h3>
                <p class="text-gray-700 mb-3">
                    Motocicletas como a {{ $article->vehicle_full_name }} requerem cuidados específicos
                    devido à maior exposição aos elementos.
                    A corrente de transmissão, em particular, exige atenção constante com lubrificação e
                    ajuste de tensão.
                </p>
                <p class="text-gray-700">
                    ⚡ <strong>Dica importante:</strong> Sempre verifique a corrente antes de viagens longas
                    e mantenha-a sempre limpa e lubrificada.
                    A negligência com este componente pode causar acidentes graves.
                </p>
            </div>
        </div>
    </div>
    @endif
</section>
@endif