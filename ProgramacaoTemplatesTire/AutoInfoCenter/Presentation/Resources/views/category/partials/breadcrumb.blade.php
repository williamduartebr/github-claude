
@props([
    'category' => null,
    'isAllModels' => false
])

<div class="bg-gray-100 border-b border-gray-300">
    <div class="container mx-auto px-4 py-2 overflow-x-auto whitespace-nowrap">
        <nav class="text-xs md:text-sm font-roboto" aria-label="Breadcrumb">
            <ol class="list-none p-0 inline-flex" itemscope itemtype="https://schema.org/BreadcrumbList">
                <!-- Início -->
                <li class="flex items-center" itemprop="itemListElement" itemscope
                    itemtype="https://schema.org/ListItem">
                    <a href="{{ url('/')}}" class="text-blue-600 hover:underline" itemprop="item">
                        <span itemprop="name">Início</span>
                    </a>
                    <meta itemprop="position" content="1" />
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mx-1 text-gray-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </li>
                
                @if ($category)
                    <!-- Informações -->
                    <li class="flex items-center" itemprop="itemListElement" itemscope
                        itemtype="https://schema.org/ListItem">
                        <a href="{{ route('info.category.index') }}" class="text-blue-600 hover:underline" itemprop="item">
                            <span itemprop="name">Informações</span>
                        </a>
                        <meta itemprop="position" content="2" />
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mx-1 text-gray-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </li>
                @else
                    <!-- Informações -->
                    <li class="flex items-center" itemprop="itemListElement" itemscope
                        itemtype="https://schema.org/ListItem">
                         <span class="text-gray-700" itemprop="name">Informações</span>
                        <meta itemprop="position" content="2" />      
                    </li>
                @endif

                @if($category)
                    @if($isAllModels)
                        <!-- Categoria (quando é all-models) -->
                        <li class="flex items-center" itemprop="itemListElement" itemscope
                            itemtype="https://schema.org/ListItem">
                            <a href="{{ route('info.category.show', $category->slug) }}" class="text-blue-600 hover:underline" itemprop="item">
                                <span itemprop="name">{{ $category->name }}</span>
                            </a>
                            <meta itemprop="position" content="3" />
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mx-1 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </li>
                        
                        <!-- Todos os Modelos (final) -->
                        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                            <span class="text-gray-700" itemprop="name">Todos os Modelos</span>
                            <meta itemprop="position" content="4" />
                            <meta itemprop="item" content="{{ route('info.category.all-models', $category->slug) }}" />
                        </li>
                    @else
                        <!-- Categoria (quando é categoria normal) -->
                        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                            <span class="text-gray-700" itemprop="name">{{ $category->name }}</span>
                            <meta itemprop="position" content="3" />
                            <meta itemprop="item" content="{{ route('info.category.show', $category->slug) }}" />
                        </li>
                    @endif
                @endif
            </ol>
        </nav>
    </div>
</div>