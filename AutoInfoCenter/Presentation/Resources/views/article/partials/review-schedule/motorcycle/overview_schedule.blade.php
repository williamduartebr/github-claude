@if(!empty($article->overview_schedule) && is_array($article->overview_schedule) &&
count($article->overview_schedule) > 0)
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        🏍️ Cronograma de Revisões Programadas
    </h2>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-[#0E368A] text-white">
                        <th class="py-3 px-4 text-left font-medium text-sm">Revisão</th>
                        <th class="py-3 px-4 text-left font-medium text-sm">Quilometragem / Tempo</th>
                        <th class="py-3 px-4 text-left font-medium text-sm">Principais Serviços</th>
                        <th class="py-3 px-4 text-left font-medium text-sm">Estimativa de Custo*</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($article->overview_schedule as $index => $schedule)
                    <tr class="border-b border-gray-200 {{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                        <td class="py-3 px-4 text-sm font-medium">{{ $schedule['revisao'] ?? '-' }}</td>
                        <td class="py-3 px-4 text-sm">{{ $schedule['intervalo'] ?? '-' }}</td>
                        <td class="py-3 px-4 text-sm">{{ $schedule['principais_servicos'] ?? '-' }}</td>
                        <td class="py-3 px-4 text-sm">{{ $schedule['estimativa_custo'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-gray-50 text-sm text-gray-700">
            <span class="font-medium">*Custos estimados para motocicletas:</span> Valores de referência em
            {{ \Carbon\Carbon::now()->locale('pt_BR')->isoFormat('MMMM [de] YYYY') }} para
            concessionárias e oficinas especializadas em capitais brasileiras. Os valores podem variar
            conforme a região,
            inflação e promoções.
        </div>
    </div>
</section>
@endif