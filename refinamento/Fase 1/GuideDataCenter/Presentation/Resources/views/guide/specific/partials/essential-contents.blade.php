{{-- partials/essential-contents.blade.php --}}

@if(isset($essentialContents) && $essentialContents->count() > 0)
<section class="container mx-auto px-4 sm:px-6 lg:px-8 mb-12">
    <h2 class="text-2xl font-bold text-gray-900 font-montserrat mb-6">
        Explore Outros Anos e Versões
    </h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($essentialContents as $content)
        <a href="{{ $content->url }}" 
           class="group bg-white rounded-lg border border-gray-200 hover:border-blue-500 hover:shadow-md transition-all p-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 transition-colors mb-1">
                    {{ $content->full_title }}
                </h3>
                <p class="text-xs text-gray-600">
                    {{ $content->make }} {{ $content->model }} {{ $content->year_start }}
                    @if($content->version)
                        - {{ $content->version }}
                    @endif
                </p>
            </div>
        </a>
        @endforeach
    </div>
</section>
@endif
