{{-- partials/available-years.blade.php --}}

@if(isset($availableYearsExtra) && $availableYearsExtra->count() > 1)
<section class="container mx-auto px-4 sm:px-6 lg:px-8 mb-12">
    <h2 class="text-2xl font-bold text-gray-900 font-montserrat mb-6">
        Outros Anos Disponíveis
    </h2>
    
    <div class="flex flex-wrap gap-2">
        @foreach($availableYearsExtra as $yearItem)
        <a href="{{ $yearItem->url ?? '#' }}" 
           class="px-4 py-2 rounded-lg border {{ $yearItem->year == $year ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:border-blue-500 hover:bg-blue-50' }} transition-colors">
            {{ $yearItem->year }}
        </a>
        @endforeach
    </div>
</section>
@endif
