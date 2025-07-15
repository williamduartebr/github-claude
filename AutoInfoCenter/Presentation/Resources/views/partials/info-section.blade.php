<!-- Seção Informativa -->
@if(isset($category->info_sections) && !empty($category->info_sections))
    @php
        $infoSections = is_array($category->info_sections) ? $category->info_sections : json_decode($category->info_sections, true);
    @endphp
    <section class="bg-blue-50 rounded-lg p-4 md:p-6 mt-10" aria-labelledby="por-que-{{\Str::slug( $infoSections['title'])}}">
        <h2 id="por-que-{{\Str::slug( $infoSections['title'])}}" class="text-xl md:text-2xl font-bold text-gray-800 mb-4 font-montserrat">
            {{ $infoSections['title'] ?? '' }}
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
            @if(isset($infoSections['sections']) && is_array($infoSections['sections']))
                @foreach($infoSections['sections'] as $section)
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-2 font-montserrat">
                            {{ $section['title'] ?? '' }}
                        </h3>
                        <p class="text-sm md:text-base text-gray-700 font-roboto">
                            {{ $section['content'] ?? '' }}
                        </p>
                    </div>
                @endforeach

            @endif
        </div>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 p-3 md:p-4 mt-5 md:mt-6" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm md:text-base text-yellow-700 font-roboto">
                        <strong>Dica importante:</strong> {{ $infoSections['alert'] ?? ''  }}
                    </p>
                </div>
            </div>
        </div>
    </section>
@endif