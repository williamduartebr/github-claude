@if(!empty($article->getData()['faq']))
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        Perguntas Frequentes
    </h2>

    <div class="space-y-4">
        @foreach($article->getData()['faq'] as $faq)
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <button
                class="flex justify-between items-center w-full px-5 py-4 text-left text-gray-900 font-medium focus:outline-none hover:bg-gray-50 faq-toggle">
                <span>{{ $faq['pergunta'] }}</span>
                <svg class="h-5 w-5 text-[#0E368A] faq-icon transition-transform" xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
            <div class="px-5 py-4 border-t border-gray-200 bg-gray-50 faq-content hidden">
                <p class="text-gray-700">{{ $faq['resposta'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif