
@if(!empty($article->getData()['faq']))
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        Perguntas Frequentes
    </h2>

    <div class="space-y-4">
        @foreach($article->getData()['faq'] as $item)
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">
                {{ $item['question'] ?? $item['pergunta'] ?? 'Pergunta não disponível' }}
            </h3>
            <div class="text-sm text-gray-700 prose prose-sm max-w-none">
                {!! nl2br(e($item['answer'] ?? $item['resposta'] ?? 'Resposta não disponível')) !!}
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif