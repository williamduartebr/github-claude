@if(!empty($article->maintenance_intervals) && is_array($article->maintenance_intervals) &&
count($article->maintenance_intervals) > 0)
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        Intervalos de Troca por Condição de Uso
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($article->maintenance_intervals as $interval)
        @if(!empty($interval['tipo_uso']))
        @php
        $badgeColors = [
        'green' => 'bg-green-600',
        'yellow' => 'bg-yellow-600',
        'gray' => 'bg-gray-700',
        'red' => 'bg-red-600'
        ];
        $iconColors = [
        'green' => 'bg-green-100 text-green-600',
        'yellow' => 'bg-yellow-100 text-yellow-600',
        'gray' => 'bg-gray-200 text-gray-700',
        'red' => 'bg-red-100 text-red-600'
        ];
        $bulletColors = [
        'green' => 'text-green-600',
        'yellow' => 'text-yellow-600',
        'gray' => 'text-gray-700',
        'red' => 'text-red-600'
        ];

        $color = $interval['cor_badge'] ?? 'gray';
        $headerClass = $badgeColors[$color] ?? $badgeColors['gray'];
        $iconClass = $iconColors[$color] ?? $iconColors['gray'];
        $bulletClass = $bulletColors[$color] ?? $bulletColors['gray'];
        @endphp

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden h-full">
            <div class="{{ $headerClass }} text-white px-4 py-3">
                <h3 class="font-medium">{{ $interval['tipo_uso'] }}</h3>
            </div>
            <div class="p-5">
                <div class="flex items-center mb-4">
                    <div class="{{ $iconClass }} w-10 h-10 rounded-full flex items-center justify-center mr-3">
                        @if(($interval['icone'] ?? 'check') === 'check')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        @elseif(($interval['icone'] ?? 'check') === 'warning')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        @endif
                    </div>
                    <h4 class="font-semibold text-gray-900">{{ $interval['intervalo'] ?? 'N/A' }}</h4>
                </div>

                @if(!empty($interval['condicoes']) && is_array($interval['condicoes']) && count($interval['condicoes'])
                > 0)
                <h5 class="text-sm font-medium text-gray-900 mb-2">Condições de uso:</h5>
                <ul class="space-y-1 text-sm text-gray-700 mb-4">
                    @foreach($interval['condicoes'] as $condicao)
                    @if(!empty($condicao))
                    <li class="flex items-baseline">
                        <span class="{{ $bulletClass }} mr-2">•</span>
                        <span>{{ $condicao }}</span>
                    </li>
                    @endif
                    @endforeach
                </ul>
                @endif

                @if(!empty($interval['observacoes']))
                <p class="text-xs text-gray-600 mt-2">{{ $interval['observacoes'] }}</p>
                @endif
            </div>
        </div>
        @endif
        @endforeach
    </div>
</section>
@endif