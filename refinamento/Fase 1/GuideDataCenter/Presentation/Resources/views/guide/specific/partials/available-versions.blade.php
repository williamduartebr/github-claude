{{-- partials/available-versions.blade.php --}}

@if(isset($availableVersionsExtra) && $availableVersionsExtra->count() > 1)
<section class="container mx-auto px-4 sm:px-6 lg:px-8 mb-12">
    <h2 class="text-2xl font-bold text-gray-900 font-montserrat mb-6">
        Outras Versões de {{ $year }}
    </h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($availableVersionsExtra as $versionItem)
        <a href="{{ $versionItem->url ?? '#' }}" 
           class="group bg-white rounded-lg border {{ $versionItem->version_slug == $version ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-blue-500' }} hover:shadow-md transition-all p-4">
            <h3 class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 transition-colors mb-1">
                {{ $versionItem->version ?? 'Versão' }}
            </h3>
            @if(!empty($versionItem->motor))
            <p class="text-xs text-gray-600">
                Motor: {{ $versionItem->motor }}
            </p>
            @endif
        </a>
        @endforeach
    </div>
</section>
@endif
