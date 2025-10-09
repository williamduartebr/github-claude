@if(!empty($article->overview_schedule) && is_array($article->overview_schedule) &&
count($article->overview_schedule) > 0)
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        🔄 Cronograma de Revisões para Veículos Híbridos
    </h2>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-gradient-to-r from-[#0E368A] to-[#10b981] text-white">
                        <th class="py-3 px-4 text-left font-medium text-sm">Revisão</th>
                        <th class="py-3 px-4 text-left font-medium text-sm">Quilometragem / Tempo</th>
                        <th class="py-3 px-4 text-left font-medium text-sm">Principais Serviços</th>
                        <th class="py-3 px-4 text-left font-medium text-sm">Estimativa de Custo*</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($article->overview_schedule as $index => $schedule)
                    <tr class="border-b border-gray-200 {{ $index % 2 == 0 ? 'bg-white' : 'bg-green-50' }}">
                        <td class="py-3 px-4 text-sm font-medium">{{ $schedule['revisao'] ?? '-' }}</td>
                        <td class="py-3 px-4 text-sm">{{ $schedule['intervalo'] ?? '-' }}</td>
                        <td class="py-3 px-4 text-sm">{{ $schedule['principais_servicos'] ?? '-' }}</td>
                        <td class="py-3 px-4 text-sm">{{ $schedule['estimativa_custo'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-green-50 text-sm text-gray-700 border-l-4 border-green-500">
            <span class="font-medium">*Custos estimados para veículos híbridos:</span> Valores de referência
            em {{ \Carbon\Carbon::now()->locale('pt_BR')->isoFormat('MMMM [de] YYYY') }} para
            concessionárias especializadas em capitais brasileiras. Veículos híbridos podem ter custos
            ligeiramente superiores
            devido à complexidade dos sistemas duais.
        </div>
    </div>
</section>
@endif