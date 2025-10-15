@if(!empty($contentData['consideracoes_finais']) || !empty($article->getData()['final_considerations']))
<section class="mb-12">
    <div class="bg-gradient-to-br from-slate-50 to-blue-50 border-l-4 border-[#0E368A] rounded-lg p-8 shadow-sm">
        <h2 class="text-2xl font-semibold text-[#151C25] mb-4 flex items-center">
            <span class="mr-3">📋</span>
            Considerações Finais
        </h2>
        <div class="prose prose-lg text-gray-800 leading-relaxed">
            {!! nl2br(e($contentData['consideracoes_finais'] ??
            $article->getData()['final_considerations'])) !!}
        </div>
    </div>
</section>
@endif