<div class="bg-gray-100 border-b border-gray-300">
    <div class="container mx-auto px-4 py-2 overflow-x-auto whitespace-nowrap">
        <nav class="text-xs md:text-sm font-roboto" aria-label="Breadcrumb">
            <ol class="list-none p-0 inline-flex" itemscope itemtype="https://schema.org/BreadcrumbList">
                <li class="flex items-center" itemprop="itemListElement" itemscope
                    itemtype="https://schema.org/ListItem">
                    <a href="{{ url('/') }}" class="text-blue-600 hover:underline" itemprop="item">
                        <span itemprop="name">Início</span>
                    </a>
                    <meta itemprop="position" content="1" />
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mx-1 text-gray-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </li>
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <span class="text-gray-700" itemprop="name">{{ MetaTag::get('title') }}</span>
                    <meta itemprop="position" content="2" />                    
                </li>
            </ol>
        </nav>
    </div>
</div>