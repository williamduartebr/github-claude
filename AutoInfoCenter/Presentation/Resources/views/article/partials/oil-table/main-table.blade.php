@if(!empty($article->oil_table) && is_array($article->oil_table) && count($article->oil_table) > 0)
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        Tabela de Óleo por Geração e Motor
    </h2>

    <div class="overflow-x-auto bg-white rounded-lg border border-gray-200 shadow-sm">
        <table class="min-w-full">
            <thead>
                <tr class="bg-[#0E368A] text-white">
                    <th class="py-3 px-4 text-left text-sm font-medium">Geração</th>
                    <th class="py-3 px-4 text-left text-sm font-medium">Período</th>
                    <th class="py-3 px-4 text-left text-sm font-medium">Motor</th>
                    <th class="py-3 px-4 text-left text-sm font-medium">Óleo Recomendado</th>
                    <th class="py-3 px-4 text-left text-sm font-medium">Capacidade</th>
                    <th class="py-3 px-4 text-left text-sm font-medium">Intervalo de Troca</th>
                </tr>
            </thead>
            <tbody>
                @foreach($article->oil_table as $index => $oilEntry)
                <tr class="border-b border-gray-200 {{ $index % 2 == 0 ? 'bg-gray-50' : 'bg-white' }}">
                    <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ $oilEntry['geracao'] ?? 'N/A'
                        }}</td>
                    <td class="py-3 px-4 text-sm text-gray-700">{{ $oilEntry['periodo'] ?? 'N/A' }}</td>
                    <td class="py-3 px-4 text-sm text-gray-700">{{ $oilEntry['motor'] ?? 'N/A' }}</td>
                    <td class="py-3 px-4 text-sm text-gray-700 font-medium">{{ $oilEntry['oleo_recomendado']
                        ?? 'N/A' }}</td>
                    <td class="py-3 px-4 text-sm text-gray-700">{{ $oilEntry['capacidade'] ?? 'N/A' }}</td>
                    <td class="py-3 px-4 text-sm text-gray-700">{{ $oilEntry['intervalo_troca'] ?? 'N/A' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 bg-yellow-50 p-4 rounded-md border-l-4 border-yellow-400">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <p class="ml-3 text-sm text-yellow-800">
                <span class="font-bold">Importante:</span> As capacidades listadas incluem a troca do
                filtro de óleo. Para trocas sem substituição do filtro, reduza o volume em aproximadamente
                0,2-0,3 litros para carros ou 0,1 litros para motos.
            </p>
        </div>
    </div>
</section>
@endif