@if(!empty($article->benefits) && is_array($article->benefits) && count($article->benefits) > 0)
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Benefícios do Óleo
        Correto</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($article->benefits as $benefit)
        @if(!empty($benefit['titulo']))
        <div class="flex items-start">
            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#0E368A]" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">{{ $benefit['titulo'] }}</h3>
                @if(!empty($benefit['descricao']))
                <p class="text-gray-700">{{ $benefit['descricao'] }}</p>
                @endif
            </div>
        </div>
        @endif
        @endforeach
    </div>
</section>
@endif