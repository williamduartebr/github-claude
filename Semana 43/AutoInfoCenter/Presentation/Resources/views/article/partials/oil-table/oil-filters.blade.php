@if(!empty($article->oil_filters) && is_array($article->oil_filters) && count($article->oil_filters) > 0)
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        Filtros de Óleo Recomendados
    </h2>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-[#0E368A] text-white">
                        <th class="py-3 px-4 text-left text-sm font-medium">Geração</th>
                        <th class="py-3 px-4 text-left text-sm font-medium">Motor</th>
                        <th class="py-3 px-4 text-left text-sm font-medium">Código Original</th>
                        <th class="py-3 px-4 text-left text-sm font-medium">Equivalentes Aftermarket</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($article->oil_filters as $index => $filter)
                    <tr class="border-b border-gray-200 {{ $index % 2 == 0 ? 'bg-gray-50' : 'bg-white' }}">
                        <td class="py-3 px-4 text-sm text-gray-700">{{ $filter['geracao'] ?? 'N/A' }}</td>
                        <td class="py-3 px-4 text-sm text-gray-700">{{ $filter['motor'] ?? 'N/A' }}</td>
                        <td class="py-3 px-4 text-sm text-gray-700 font-medium">{{
                            $filter['codigo_original'] ?? 'N/A' }}</td>
                        <td class="py-3 px-4 text-sm text-gray-700">
                            @if(!empty($filter['equivalentes_aftermarket']))
                            @if(is_array($filter['equivalentes_aftermarket']))
                            {{ implode(', ', $filter['equivalentes_aftermarket']) }}
                            @else
                            {{ $filter['equivalentes_aftermarket'] }}
                            @endif
                            @else
                            N/A
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 bg-[#0E368A]/5 p-4 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#0E368A]" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <p class="ml-3 text-sm text-gray-700">
                Recomenda-se a substituição do filtro de óleo a cada troca de óleo para garantir a máxima
                proteção do motor. Filtros originais oferecem a melhor compatibilidade, mas as
                alternativas aftermarket listadas apresentam qualidade equivalente.
            </p>
        </div>
    </div>
</section>
@endif