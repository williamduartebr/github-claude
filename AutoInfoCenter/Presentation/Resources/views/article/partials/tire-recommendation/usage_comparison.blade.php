@if(!empty($article->usage_comparison) && is_array($article->usage_comparison) && count($article->usage_comparison) > 0)
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Comparativo por
        Tipo de Uso</h2>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse bg-white rounded-lg overflow-hidden shadow-sm">
            <thead>
                <tr class="bg-[#0E368A] text-white">
                    <th class="py-3 px-4 text-left text-sm font-medium">Tipo de Uso</th>
                    <th class="py-3 px-4 text-left text-sm font-medium">Melhor Pneu Dianteiro</th>
                    <th class="py-3 px-4 text-left text-sm font-medium">Melhor Pneu Traseiro</th>
                    <th class="py-3 px-4 text-left text-sm font-medium">Características</th>
                </tr>
            </thead>
            <tbody>
                @foreach($article->usage_comparison as $index => $usage)
                @if(!empty($usage['tipo_uso']))
                <tr class="border-b border-gray-200 {{ $index % 2 == 0 ? '' : 'bg-gray-50' }}">
                    <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ $usage['tipo_uso'] }}</td>
                    <td class="py-3 px-4 text-sm text-gray-700">{{ $usage['melhor_dianteiro'] ?? 'N/A' }}</td>
                    <td class="py-3 px-4 text-sm text-gray-700">{{ $usage['melhor_traseiro'] ?? 'N/A' }}</td>
                    <td class="py-3 px-4 text-sm text-gray-700">{{ $usage['caracteristicas'] ?? 'N/A' }}</td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif