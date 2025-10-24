<!-- Breadcrumbs -->
<div class="bg-gray-100 border-b border-gray-300">
    <div class="container mx-auto px-4 py-2 overflow-x-auto whitespace-nowrap">
        <nav class="text-xs md:text-sm font-roboto" aria-label="Breadcrumb">
            <ol class="list-none p-0 inline-flex" itemscope itemtype="https://schema.org/BreadcrumbList">
                @foreach ($article->breadcrumbs as $breadcrumb)
                    <li class="flex items-center" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        @if (!$loop->last && !empty($breadcrumb['url']))
                            {{-- Links para todos exceto o último --}}
                            <a href="{{ $breadcrumb['url'] }}" class="text-blue-600 hover:underline" itemprop="item">
                                <span itemprop="name">{{ $breadcrumb['name'] }}</span>
                            </a>
                        @else
                            {{-- Último item ou sem URL - usa URL válida --}}
                            <div itemprop="item" itemid="{{ $breadcrumb['url'] ?? request()->url() }}">
                                <span class="text-gray-700" itemprop="name">{{ $breadcrumb['name'] }}</span>
                            </div>
                        @endif
                        <meta itemprop="position" content="{{ $breadcrumb['position'] }}" />
                        @if (!$loop->last)
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mx-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    </div>
</div>