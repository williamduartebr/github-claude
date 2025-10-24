@if(!empty($article->getData()['final_considerations']))
<section class="mb-12 bg-gray-50 rounded-lg p-6">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-4">Considerações Finais</h2>
    <div class="prose prose-lg max-w-none text-gray-800">
        {!! nl2br(e($article->getData()['final_considerations'])) !!}
    </div>
</section>
@endif