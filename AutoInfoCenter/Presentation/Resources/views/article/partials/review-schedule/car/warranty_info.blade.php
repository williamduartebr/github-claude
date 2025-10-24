<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        Garantia e Recomendações Adicionais
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Informações de Garantia -->
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-center mb-4">
                <div
                    class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Prazo de Garantia</h3>
            </div>

            <div class="space-y-4">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-700">
                        <span class="font-semibold">Garantia Contratual:</span> {{
                        $article->warranty_info['prazo_garantia'] }}
                    </p>
                </div>

                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-700">
                        <span class="font-semibold">Garantia para Itens de Desgaste:</span> {{
                        $article->warranty_info['garantia_itens_desgaste'] }}
                    </p>
                </div>

                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-700">
                        <span class="font-semibold">Garantia Anticorrosão:</span> {{
                        $article->warranty_info['garantia_anticorrosao'] }}
                    </p>
                </div>
            </div>

            @if($article->warranty_info['observacoes_importantes'])
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-sm text-gray-700">
                    <span class="font-semibold">Importante:</span> {{
                    $article->warranty_info['observacoes_importantes'] }}
                </p>
            </div>
            @endif
        </div>

        <!-- Dicas para Prolongar a Vida Útil -->
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-center mb-4">
                <div
                    class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Dicas para Prolongar a Vida Útil</h3>
            </div>

            <ul class="space-y-3">
                @foreach($article->warranty_info['dicas_vida_util'] as $dica)
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
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Alerta de Importância -->
    <div class="bg-[#E06600]/5 border border-[#E06600]/20 rounded-lg p-5">
        <div class="flex">
            <div class="flex-shrink-0 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#E06600]" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-medium text-[#E06600] mb-2">Atenção às Revisões Críticas</h3>
                <p class="text-gray-700 mb-3">
                    As revisões de 20.000 km e 60.000 km são consideradas críticas para o {{
                    $article->vehicle_full_name }},
                    pois incluem a verificação e/ou substituição de componentes fundamentais para a
                    longevidade do motor.
                </p>
                <p class="text-gray-700">
                    A revisão de 60.000 km, em particular, inclui a troca da correia dentada, componente
                    crítico cuja falha pode causar sérios danos ao motor. Não postergue esta revisão e
                    sempre utilize peças originais ou homologadas pela fabricante.
                </p>
            </div>
        </div>
    </div>
</section>