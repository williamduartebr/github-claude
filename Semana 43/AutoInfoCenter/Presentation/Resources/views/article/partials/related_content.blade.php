<section class="mb-8">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Conteúdo
        Relacionado</h2>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($article->related_topics as $relatedTopic)
        <a href="/{{ $relatedTopic['slug'] }}" class="group">
            <div
                class="h-32 rounded-lg bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/10 border border-gray-200 flex items-center justify-center mb-2 hover:shadow-md transition-all">
                <div class="text-center px-4">
                    <div class="mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-[#0E368A]"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            @if($relatedTopic['icon'] == 'filter')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                            @elseif($relatedTopic['icon'] == 'gas-pump')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0016.5 3c-1.76 0-3.332.763-4.5 2m4.5 4V3m-9 8h-3m0 0H3m3 0v3m0-3V8m0 0h3m-3 0H3m3 0V5" />
                            @elseif($relatedTopic['icon'] == 'oil-can')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                            @endif
                        </svg>
                    </div>
                    <span class="font-medium text-[#151C25]">{{ explode(':', $relatedTopic['title'])[0] ??
                        $relatedTopic['title'] }}</span>
                </div>
            </div>
            <h3
                class="font-medium text-[#0E368A] group-hover:text-[#0A2868] group-hover:underline transition-colors">
                {{ $relatedTopic['title'] }}</h3>
        </a>
        @endforeach
    </div>
</section>