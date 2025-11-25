{{-- 
    View: Guide Index
    Lista guias com paginação, busca e filtros
    Estilizado com Tailwind CSS
--}}
@extends('auto-info-center::layouts.app')

@section('title', $title ?? 'Guias Automotivos')

@section('meta')
    <meta name="description" content="{{ $title ?? 'Encontre guias completos para seu veículo' }}">
    <meta name="robots" content="index, follow">
@endsection

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <header class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                {{ $title ?? 'Guias Automotivos' }}
            </h1>
            
            @if(isset($make) && isset($model))
                <p class="text-lg text-gray-600">
                    Todos os guias disponíveis para 
                    <span class="font-semibold text-gray-800">
                        {{ ucfirst(str_replace('-', ' ', $make)) }} {{ ucfirst(str_replace('-', ' ', $model)) }}
                    </span>
                    @if(isset($year) && $year)
                        <span class="text-blue-600">({{ $year }})</span>
                    @endif
                </p>
            @endif
        </header>

        {{-- Search Form --}}
        <div class="mb-8">
            <form action="{{ route('guide.search') }}" method="GET" class="flex gap-2 max-w-xl">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input 
                        type="search" 
                        name="q" 
                        value="{{ $query ?? '' }}"
                        placeholder="Buscar guias por marca, modelo ou tema..."
                        class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150"
                        minlength="2"
                        required
                    >
                </div>
                <button 
                    type="submit" 
                    class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150"
                >
                    Buscar
                </button>
            </form>
        </div>

        {{-- Message --}}
        @if(isset($message) && $message)
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-blue-500 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <p class="text-blue-700">{{ $message }}</p>
                </div>
            </div>
        @endif

        {{-- Results --}}
        @if($guides->isNotEmpty())
            {{-- Results Count --}}
            <div class="mb-4 text-sm text-gray-500">
                {{ $guides->count() }} guia(s) encontrado(s)
            </div>

            {{-- Guide Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($guides as $guide)
                
                    <article class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
                        {{-- Featured Image --}}
                        @if($guide->featuredImage)
                            <a href="{{ $guide->url }}" class="block aspect-video overflow-hidden">
                                <img 
                                    src="{{ $guide->featuredImage }}" 
                                    alt="{{ $guide->title }}"
                                    loading="lazy"
                                    class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                                >
                            </a>
                        @else
                            {{-- Placeholder quando não há imagem --}}
                            <a href="{{ $guide->url }}" class="block aspect-video bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                </svg>
                            </a>
                        @endif

                        <div class="p-5">
                            {{-- Template Badge --}}
                            @if($guide->template)
                                <span class="inline-block px-2.5 py-1 text-xs font-medium rounded-full mb-3
                                    @switch($guide->template)
                                        @case('calibragem')
                                            bg-green-100 text-green-700
                                            @break
                                        @case('pneus')
                                            bg-purple-100 text-purple-700
                                            @break
                                        @case('oleo-motor')
                                            bg-amber-100 text-amber-700
                                            @break
                                        @default
                                            bg-gray-100 text-gray-700
                                    @endswitch
                                ">
                                    {{ ucfirst(str_replace('-', ' ', $guide->template)) }}
                                </span>
                            @endif

                            {{-- Title --}}
                            <h2 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                <a href="{{ $guide->url }}" class="hover:text-blue-600 transition-colors">
                                    {{ $guide->title }}
                                </a>
                            </h2>

                            {{-- Vehicle Info --}}
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                <a 
                                    href="{{ $guide->vehicleUrl }}" 
                                    class="inline-flex items-center text-sm text-gray-600 hover:text-blue-600 transition-colors"
                                >
                                    <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                    {{ $guide->make }} {{ $guide->model }}
                                    @if($guide->version)
                                        <span class="text-gray-400 ml-1">{{ $guide->version }}</span>
                                    @endif
                                </a>
                                
                                @if($guide->yearRange)
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded">
                                        {{ $guide->yearRange }}
                                    </span>
                                @endif
                            </div>

                            {{-- Excerpt --}}
                            @if($guide->excerpt)
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                    {{ $guide->excerpt }}
                                </p>
                            @endif

                            {{-- Footer --}}
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <span class="text-xs text-gray-400">
                                    {{ $guide->createdAt }}
                                </span>
                                <a 
                                    href="{{ $guide->url }}" 
                                    class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors"
                                >
                                    Ler guia
                                    <svg class="w-4 h-4 ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            {{-- @if(isset($pagination) && $pagination)
                <nav class="mt-10 flex justify-center" aria-label="Paginação">
                    <div class="inline-flex items-center gap-1">
                        {{ $pagination->withQueryString()->links('vendor.pagination.tailwind') }}
                    </div>
                </nav>
            @endif --}}

        @else
            {{-- Empty State --}}
            <div class="text-center py-16">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 mb-6">
                    <svg class="w-10 h-10 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-gray-900 mb-2">
                    Nenhum guia encontrado
                </h2>
                <p class="text-gray-500 mb-8 max-w-md mx-auto">
                    Não encontramos guias que correspondam aos critérios de busca. Tente outros termos ou navegue por todos os guias disponíveis.
                </p>
                
                <a 
                    href="{{ route('guide.index') }}" 
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150"
                >
                    <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    Ver todos os guias
                </a>
            </div>
        @endif

    </div>
</div>
@endsection