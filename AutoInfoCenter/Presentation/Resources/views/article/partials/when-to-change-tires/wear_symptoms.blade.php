@if(!empty($article->wear_symptoms) && is_array($article->wear_symptoms) && count($article->wear_symptoms) > 0)
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        Sintomas de Pneus que Precisam de Substituição
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($article->wear_symptoms as $symptom)
        @if(!empty($symptom['title']))
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 h-12 w-12 rounded-full bg-red-100 flex items-center justify-center mr-4">
                        @if($symptom['severity'] === 'alta')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $symptom['title'] }}</h3>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $symptom['severity'] === 'alta' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                            Severidade {{ ucfirst($symptom['severity']) }}
                        </span>
                    </div>
                </div>
                <p class="text-gray-600 mb-3">{{ $symptom['description'] ?? '' }}</p>
                @if(!empty($symptom['action']))
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-700"><strong>Ação recomendada:</strong> {{ $symptom['action'] }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
        @endforeach
    </div>
</section>
@endif