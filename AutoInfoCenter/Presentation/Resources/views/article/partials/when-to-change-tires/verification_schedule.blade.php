@if(!empty($article->verification_schedule) && is_array($article->verification_schedule) &&
count($article->verification_schedule) > 0)
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        Cronograma de Verificação e Manutenção
    </h2>

    <div class="space-y-4">
        @foreach($article->verification_schedule as $index => $schedule)
        {{-- @if(!empty($schedule['title'])) --}}
        <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
                <div
                    class="w-8 h-8 rounded-full bg-[#0E368A] flex items-center justify-center text-white text-sm font-medium">
                    {{ $index + 1 }}
                </div>
            </div>
            <div class="flex-1 bg-white rounded-lg border border-gray-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $schedule['title'] }}</h3>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $schedule['importance'] === 'alta' || $schedule['importance'] === 'essencial' || $schedule['importance'] === 'obrigatória' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ ucfirst($schedule['importance']) }}
                    </span>
                </div>
                <p class="text-gray-600">{{ $schedule['description'] ?? '' }}</p>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif