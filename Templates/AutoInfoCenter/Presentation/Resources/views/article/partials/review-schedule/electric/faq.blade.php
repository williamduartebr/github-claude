@if(!empty($article->faq) && is_array($article->faq) && count($article->faq) > 0)
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        ❓ Perguntas Frequentes sobre Veículos Elétricos
    </h2>

    <div class="space-y-4">
        @foreach($article->faq as $question)
        @if(!empty($question['pergunta']) && !empty($question['resposta']))
        <div class="bg-white rounded-lg border shadow-sm overflow-hidden border-l-4 border-blue-400">
            <div class="p-5">
                <h3 class="text-lg font-medium text-gray-900 mb-2">🔋 {{ $question['pergunta'] }}</h3>
                <p class="text-gray-700">{{ $question['resposta'] }}</p>
            </div>
        </div>
        @endif
        @endforeach
    </div>
</section>
@endif