@php
$faq = $article->getData()['faq'] ?? $contentData['perguntas_frequentes'] ?? [];
@endphp

@if(!empty($faq))
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b-2 border-[#0E368A]/30">
        ❓ Perguntas Frequentes sobre {{ $vehicleName }}
    </h2>

    <div class="space-y-4">
        @foreach($faq as $index => $pergunta)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <details class="group">
                <summary
                    class="flex justify-between items-center cursor-pointer p-6 hover:bg-gray-50 transition-colors">
                    <h3 class="text-lg font-semibold text-gray-900 pr-4">
                        {{ $pergunta['pergunta'] ?? $pergunta['question'] }}
                    </h3>
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-gray-400 transition-transform group-open:rotate-180" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </summary>
                <div class="px-6 pb-6">
                    <div class="pt-4 border-t border-gray-100">
                        <p class="text-gray-700 leading-relaxed">
                            {{ $pergunta['resposta'] ?? $pergunta['answer'] }}
                        </p>
                    </div>
                </div>
            </details>
        </div>
        @endforeach
    </div>
</section>
@endif