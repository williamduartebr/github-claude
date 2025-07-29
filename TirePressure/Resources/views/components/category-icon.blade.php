<a href="{{ $url }}" @if (!$toFollow) rel="nofollow" @endif class="block p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
    <div class="flex flex-col items-center text-center">
        <div class="mb-3 p-3 rounded-full {{ $bgColor }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 {{ $textColor }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                {!! $iconSvg !!}
            </svg>
        </div>
        <h3 class="text-sm font-medium text-gray-800">{{ $title }}</h3>
    </div>
</a>