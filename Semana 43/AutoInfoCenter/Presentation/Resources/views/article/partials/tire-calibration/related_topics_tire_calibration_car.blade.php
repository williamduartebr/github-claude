<!-- Artigos Relacionados -->
@if(!empty($article->getData()['related_topics']))
<section class="mb-8">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        Conteúdo Relacionado
    </h2>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($article->getData()['related_topics'] as $topic)
        <a href="{{ $topic['url'] }}" class="group">
            <div
                class="h-32 rounded-lg bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/10 border border-gray-200 flex items-center justify-center mb-2 hover:shadow-md transition-all">
                <div class="text-center px-4">
                    <div class="mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-[#0E368A]"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            @if(str_contains($topic['title'], 'Pneus'))
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            @elseif(str_contains($topic['title'], 'Rodízio'))
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            @elseif(str_contains($topic['title'], 'Consumo'))
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            @endif
                        </svg>
                    </div>
                    <span class="font-medium text-[#151C25]">{{ explode(' ', $topic['title'])[0] }}</span>
                </div>
            </div>
            <h3
                class="font-medium text-[#0E368A] group-hover:text-[#0A2868] group-hover:underline transition-colors">
                {{ $topic['title'] }}
            </h3>
        </a>
        @endforeach
    </div>
</section>
@endif