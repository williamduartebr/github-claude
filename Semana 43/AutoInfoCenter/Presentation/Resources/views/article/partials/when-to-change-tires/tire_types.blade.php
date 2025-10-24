@if(!empty($article->tire_types) && is_array($article->tire_types) && count($article->tire_types) > 0)
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        Tipos de Pneus e Quilometragem Esperada
    </h2>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <thead>
                <tr class="bg-[#0E368A] text-white">
                    <th class="py-3 px-4 text-left text-sm font-medium">Tipo de Pneu</th>
                    <th class="py-3 px-4 text-left text-sm font-medium">Quilometragem Esperada</th>
                    <th class="py-3 px-4 text-left text-sm font-medium">Aplicação</th>
                    <th class="py-3 px-4 text-left text-sm font-medium">Observações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($article->tire_types as $index => $tire)
                @if(!empty($tire['type']))
                <tr class="border-b border-gray-200 {{ $index % 2 == 0 ? 'bg-gray-50' : 'bg-white' }}">
                    <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ $tire['type'] }}</td>
                    <td class="py-3 px-4 text-sm text-gray-700">{{ $tire['expected_mileage'] ?? 'N/A' }}</td>
                    <td class="py-3 px-4 text-sm text-gray-700">{{ $tire['application'] ?? 'N/A' }}</td>
                    <td class="py-3 px-4 text-sm text-gray-700">{{ $tire['observations'] ?? 'N/A' }}</td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif