@php
$fullLoadTable = $article->getData()['full_load_table'] ?? [];
@endphp

@if(!empty($fullLoadTable['conditions']) || !empty($fullLoadTable['condicoes']))
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b-2 border-[#0E368A]/30">
        📊 {{ $fullLoadTable['title'] ?? $fullLoadTable['titulo'] ?? 'Pressões para Carga na Caçamba' }}
    </h2>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <p class="text-gray-700">
                {{ $fullLoadTable['description'] ?? $fullLoadTable['descricao'] ?? 'Pressões para uso com
                diferentes cargas na caçamba.' }}
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-[#0E368A] text-white">
                        <th class="py-3 px-4 text-left font-semibold text-sm">Versão</th>
                        <th class="py-3 px-4 text-left font-semibold text-sm">Ocupantes</th>
                        <th class="py-3 px-4 text-left font-semibold text-sm">Carga na Caçamba</th>
                        <th class="py-3 px-4 text-center font-semibold text-sm">Dianteiros</th>
                        <th class="py-3 px-4 text-center font-semibold text-sm">Traseiros</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fullLoadTable['conditions'] ?? $fullLoadTable['condicoes'] as $index =>
                    $condition)
                    <tr class="border-b border-gray-200 {{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                        <td class="py-3 px-4 text-sm font-medium text-gray-900">
                            {{ $condition['version'] ?? $condition['versao'] ?? 'Pickup' }}
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-700">
                            {{ $condition['occupants'] ?? $condition['ocupantes'] ?? '2-5' }}
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-700">
                            {{ $condition['luggage'] ?? $condition['bagagem'] ?? 'Normal' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-800">
                                {{ $condition['front_pressure'] ?? $condition['pressao_dianteira'] ?? '38'
                                }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-800">
                                {{ $condition['rear_pressure'] ?? $condition['pressao_traseira'] ?? '45' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-blue-50 border-t border-blue-200">
            <div class="flex items-start">
                <div class="flex-shrink-0 mr-3">
                    <div class="h-5 w-5 bg-blue-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-xs">💡</span>
                    </div>
                </div>
                <p class="text-sm text-blue-800">
                    <strong>Dica:</strong> Use pressões "Normal" para uso urbano sem carga.
                    Use pressões "c/ Carga" quando transportar peso na caçamba ou rebocar.
                </p>
            </div>
        </div>
    </div>
</section>
@endif