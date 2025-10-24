@if(!empty($article->specifications) && is_array($article->specifications))
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Especificações
        Técnicas</h2>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse">
            <thead>
                <tr class="bg-[#0E368A] text-white">
                    <th class="py-3 px-4 text-left font-medium text-sm">Especificação</th>
                    <th class="py-3 px-4 text-left font-medium text-sm">{{ $article->vehicle_info['make'] ?? 'Veículo'
                        }}
                        {{ $article->vehicle_info['model'] ?? '' }} {{ $article->vehicle_info['engine'] ?? '' }}</th>
                    <th class="py-3 px-4 text-left font-medium text-sm">Observações</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($article->specifications['capacidade_oleo']))
                <tr class="border-b border-gray-200 bg-white">
                    <td class="py-3 px-4 text-sm">Capacidade de Óleo (com filtro)</td>
                    <td class="py-3 px-4 text-sm font-medium">{{ $article->specifications['capacidade_oleo'] }}</td>
                    <td class="py-3 px-4 text-sm">Capacidade total do sistema</td>
                </tr>
                @endif
                @if(!empty($article->specifications['capacidade_sem_filtro']))
                <tr class="border-b border-gray-200 bg-gray-50">
                    <td class="py-3 px-4 text-sm">Capacidade sem Filtro</td>
                    <td class="py-3 px-4 text-sm font-medium">{{ $article->specifications['capacidade_sem_filtro'] }}
                    </td>
                    <td class="py-3 px-4 text-sm">Apenas troca de óleo</td>
                </tr>
                @endif
                @if(!empty($article->specifications['viscosidade']))
                <tr class="border-b border-gray-200 bg-gray-50">
                    <td class="py-3 px-4 text-sm">Viscosidade Recomendada</td>
                    <td class="py-3 px-4 text-sm font-medium">{{ $article->specifications['viscosidade'] }}</td>
                    <td class="py-3 px-4 text-sm">Para todas as temperaturas operacionais</td>
                </tr>
                @endif
                @if(!empty($article->specifications['especificacao_minima']))
                <tr class="border-b border-gray-200 bg-white">
                    <td class="py-3 px-4 text-sm">Especificação Mínima</td>
                    <td class="py-3 px-4 text-sm font-medium">{{ $article->specifications['especificacao_minima'] }}
                    </td>
                    <td class="py-3 px-4 text-sm">Requisito do fabricante</td>
                </tr>
                @endif
                @if(!empty($article->specifications['intervalo_troca']))
                <tr class="border-b border-gray-200 bg-gray-50">
                    <td class="py-3 px-4 text-sm">Intervalo de Troca</td>
                    <td class="py-3 px-4 text-sm font-medium">{{ $article->specifications['intervalo_troca'] }}</td>
                    <td class="py-3 px-4 text-sm">O que ocorrer primeiro</td>
                </tr>
                @endif
                @if(!empty($article->specifications['filtro_oleo']))
                <tr class="border-b border-gray-200 bg-white">
                    <td class="py-3 px-4 text-sm">Filtro de Óleo</td>
                    <td class="py-3 px-4 text-sm font-medium">{{ $article->specifications['filtro_oleo'] }}</td>
                    <td class="py-3 px-4 text-sm">Recomendação do fabricante</td>
                </tr>
                @endif
                @if(!empty($article->specifications['codigo_filtro']))
                <tr class="bg-gray-50">
                    <td class="py-3 px-4 text-sm">Código Filtro Original</td>
                    <td class="py-3 px-4 text-sm font-medium">{{ $article->specifications['codigo_filtro'] }}</td>
                    <td class="py-3 px-4 text-sm">Filtro original</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</section>
@endif