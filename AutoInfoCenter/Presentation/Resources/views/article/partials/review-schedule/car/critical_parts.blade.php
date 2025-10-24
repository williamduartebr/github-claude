<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        Peças que Exigem Atenção Especial
    </h2>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($article->critical_parts as $part)
            <div class="flex items-start">
                <div
                    class="h-10 w-10 rounded-full bg-gradient-to-br from-[#E06600]/5 to-[#E06600]/15 flex items-center justify-center mr-3 flex-shrink-0 mt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#E06600]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-medium text-gray-900 mb-1">{{ $part['componente'] }}</h3>
                    <p class="text-sm text-gray-700 mb-2">
                        <span class="font-medium">Recomendação de troca:</span> {{
                        $part['intervalo_recomendado'] }}
                    </p>
                    <p class="text-sm text-gray-600">{{ $part['observacao'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>