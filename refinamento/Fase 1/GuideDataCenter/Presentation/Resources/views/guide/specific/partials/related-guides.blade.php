{{-- partials/related-guides.blade.php --}}

@if(!empty($relatedGuides) && count($relatedGuides) > 0)
<section class="container mx-auto px-4 sm:px-6 lg:px-8 mb-12">
    <h2 class="text-2xl font-bold text-gray-900 font-montserrat mb-6">
        Guias Relacionados
    </h2>
    
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($relatedGuides as $related)
        <a href="{{ $related['url'] }}" 
           class="group bg-white rounded-lg border border-gray-200 hover:border-blue-500 hover:shadow-md transition-all p-4">
            <div class="flex flex-col items-center text-center">
                <div class="text-3xl mb-2">{{ $related['icon'] }}</div>
                <h3 class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                    {{ is_object($related['name']) ? $related['name']->name : $related['name'] }}
                </h3>
            </div>
        </a>
        @endforeach
    </div>
</section>
@endif
