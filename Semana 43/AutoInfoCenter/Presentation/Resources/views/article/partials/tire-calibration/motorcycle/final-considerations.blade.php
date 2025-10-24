@if(!empty($article->getData()['final_considerations']))
<section class="mb-12">
    <div class="bg-gradient-to-r from-[#DC2626] to-red-700 text-white rounded-lg p-8">
        <div class="flex items-center mb-4">
            <span class="text-3xl mr-4">🏁</span>
            <h2 class="text-2xl font-bold">Considerações Finais</h2>
        </div>
        <div class="text-red-100 leading-relaxed">
            {!! nl2br(e($article->getData()['final_considerations'])) !!}
        </div>
    </div>
</section>
@endif