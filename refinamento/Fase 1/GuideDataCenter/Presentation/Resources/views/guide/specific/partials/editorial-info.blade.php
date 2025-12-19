{{-- partials/editorial-info.blade.php --}}

@if(!empty($editorialInfo))
<section class="container mx-auto px-4 sm:px-6 lg:px-8 mb-16">
    <div class="bg-blue-50 rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-blue-900 rounded-full flex items-center justify-center mr-3 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 font-montserrat">{{ $editorialInfo['title'] ?? 'Equipe Editorial' }}</h3>
        </div>

        <div class="text-gray-700 space-y-2 pl-3 ml-10 border-l-2 border-blue-900">
            <p class="text-sm leading-relaxed font-roboto">{{ $editorialInfo['description'] ?? '' }}</p>
            <p class="text-sm leading-relaxed font-roboto">{{ $editorialInfo['methodology'] ?? '' }}</p>
        </div>

        @if(!empty($editorialInfo['link_url']))
        <div class="flex items-center justify-end mt-5 pt-4 border-t border-gray-200">
            <a href="{{ $editorialInfo['link_url'] }}"
                class="text-blue-900 text-sm hover:text-blue-700 hover:underline flex items-center font-medium">
                {{ $editorialInfo['link_text'] ?? 'Saiba mais' }}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </a>
        </div>
        @endif
    </div>
</section>
@endif
