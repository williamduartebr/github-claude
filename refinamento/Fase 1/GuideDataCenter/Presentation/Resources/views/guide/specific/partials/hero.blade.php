{{-- partials/hero.blade.php --}}

<section class="bg-white border-b border-gray-200">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 pt-8">
         {{-- ✅ H1 - USA $seo['h1'] ao invés de montar manualmente --}}
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 font-montserrat mb-4">
            {{ $seo['h1'] ?? $guide['title'] ?? 'Guia' }}
        </h1>

        {{-- INTRO / DESCRIÇÃO --}}
        @if(!empty($guide['intro']) || !empty($guide['description']))
        <div class="text-lg text-gray-700 leading-relaxed mb-6 font-roboto md:w-8/12">
            <p>{{ $guide['intro'] ?? $guide['description'] }}</p>
        </div>
        @endif

        {{-- BADGES --}}
        @if(!empty($badges))
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach($badges as $badge)
            <span
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $badge['class'] ?? 'bg-blue-100 text-blue-800' }}">
                @if(!empty($badge['icon']))
                <span class="mr-1">{{ $badge['icon'] }}</span>
                @endif
                {{ $badge['text'] }}
            </span>
            @endforeach
        </div>
        @endif

        {{-- DISCLAIMER --}}
        @if(!empty($disclaimer))
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">{{ $disclaimer }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>

</section>