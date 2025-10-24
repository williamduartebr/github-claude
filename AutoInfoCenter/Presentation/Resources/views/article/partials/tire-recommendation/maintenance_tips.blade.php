@if(!empty($article->maintenance_tips) && is_array($article->maintenance_tips) && count($article->maintenance_tips) > 0)
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Dicas de Manutenção
        para Prolongar a Vida dos Pneus</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($article->maintenance_tips as $tipCategory)
        @if(!empty($tipCategory['categoria']))
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#0E368A]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">{{ $tipCategory['categoria'] }}</h3>
            </div>

            @if(!empty($tipCategory['dicas']) && is_array($tipCategory['dicas']) && count($tipCategory['dicas']) > 0)
            <ul class="space-y-2 text-gray-700 ml-2">
                @foreach($tipCategory['dicas'] as $tip)
                @if(!empty($tip))
                <li class="flex items-baseline">
                    <span class="text-[#0E368A] mr-2">•</span>
                    <span>{{ $tip }}</span>
                </li>
                @endif
                @endforeach
            </ul>
            @endif
        </div>
        @endif
        @endforeach
    </div>
</section>
@endif