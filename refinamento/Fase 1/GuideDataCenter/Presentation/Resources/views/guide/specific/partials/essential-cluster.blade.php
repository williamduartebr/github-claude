{{-- partials/essential-cluster.blade.php --}}

@if(!empty($essentialCluster) && count($essentialCluster) > 0)
<section class="container mx-auto px-4 sm:px-6 lg:px-8 mb-12">
    <h2 class="text-2xl font-bold text-gray-900 font-montserrat mb-6">
        Conteúdos Essenciais
    </h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($essentialCluster as $item)
        <a href="{{ $item['url'] }}" 
           class="group bg-white rounded-lg border border-gray-200 hover:border-blue-500 hover:shadow-md transition-all p-4">
            <div class="flex items-center">
                <div class="text-2xl mr-3">{{ $item['icon'] }}</div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                        {{ $item['title'] }}
                    </h3>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</section>
@endif
