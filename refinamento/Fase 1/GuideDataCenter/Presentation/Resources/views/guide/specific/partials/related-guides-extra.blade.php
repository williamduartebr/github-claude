{{-- partials/related-guides-extra.blade.php --}}

@if(isset($relatedGuidesExtra) && $relatedGuidesExtra->count() > 0)
<section class="container mx-auto px-4 sm:px-6 lg:px-8 mb-12">
    <h2 class="text-2xl font-bold text-gray-900 font-montserrat mb-6">
        Outros Guias deste Veículo
    </h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($relatedGuidesExtra as $related)

            @if ($related->version_slug == $version)              

                <a href="{{ $related->url }}" 
                class="group bg-white rounded-lg border border-gray-200 hover:border-blue-500 hover:shadow-md transition-all p-4">
                    <div class="flex items-center gap-2">
                        <div class="p-3 rounded-full {{ $related->category['icon_bg_color'] ?? 'bg-blue-50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" 
                                    class="w-6 h-6 {{ $related->category['icon_text_color'] ?? 'text-blue-600' }}" 
                                    fill="none" 
                                    viewBox="0 0 24 24" 
                                    stroke="currentColor" 
                                    aria-hidden="true">
                                {!! $related->category['icon_svg'] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />' !!}
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 transition-colors mb-1">
                                {{ $related->category->name ?? 'Guia' }}
                            </h3>
                            <p class="text-xs text-gray-600">
                                {{ $related->make }} {{ $related->model }} {{ $related->year_start }} {{ $related->version }}
                            </p>
                        </div>
                    </div>
                </a>

            @endif
        @endforeach
    </div>
</section>
@endif
