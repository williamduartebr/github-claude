{{--
Template: guide.specific.blade.php

Sistema de blocos modulares para guias de veículos.
Suporta 13 categorias com blocos reutilizáveis.

@author Claude Sonnet 4.5
@version 2.0 - Sistema de blocos
--}}

@extends('guide-data-center::layouts.app')

@section('title', $seo['title'] ?? '')
@section('meta_description', $seo['description'] ?? '')

{{-- SEO: Canonical e Open Graph --}}
@push('head')
<link rel="canonical" href="{{ $seo['canonical'] ?? '' }}" />
<link rel="alternate" hreflang="pt-BR" href="{{ $seo['canonical'] ?? '' }}" />

<meta property="og:type" content="{{ $seo['og_type'] ?? 'article' }}" />
<meta property="og:title" content="{{ $seo['title'] ?? '' }}" />
<meta property="og:description" content="{{ $seo['description'] ?? '' }}" />
<meta property="og:image" content="{{ $seo['og_image'] ?? '' }}" />
<meta property="og:url" content="{{ $seo['canonical'] ?? '' }}" />
<meta property="og:site_name" content="Mercado Veículos" />

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo['title'] ?? '' }}">
<meta name="twitter:description" content="{{ $seo['description'] ?? '' }}">
<meta name="twitter:image" content="{{ $seo['og_image'] ?? '' }}">
@endpush

@section('content')

{{-- BREADCRUMBS --}}
@if(isset($breadcrumbs))
@section('breadcrumbs')
<div class="bg-gray-100 border-b border-gray-200">
    <div class="container mx-auto px-4 py-2 overflow-x-auto whitespace-nowrap">
        <nav class="text-xs md:text-sm font-roboto" aria-label="Breadcrumb">
            <ol class="list-none p-0 inline-flex" itemscope itemtype="https://schema.org/BreadcrumbList">
                @foreach($breadcrumbs as $index => $crumb)
                <li class="flex items-center" itemprop="itemListElement" itemscope
                    itemtype="https://schema.org/ListItem">
                    @if($crumb['url'])
                    <a href="{{ $crumb['url'] }}" class="text-blue-600 hover:underline" itemprop="item">
                        <span itemprop="name">{{ $crumb['name'] }}</span>
                    </a>
                    <meta itemprop="position" content="{{ $index + 1 }}" />
                    @if(!$loop->last)
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mx-1 text-gray-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    @endif
                    @else
                    <span class="text-gray-700" itemprop="name">{{ $crumb['name'] }}</span>
                    <meta itemprop="position" content="{{ $index + 1 }}" />
                    @endif
                </li>
                @endforeach
            </ol>
        </nav>
    </div>
</div>
@endsection
@endif

{{-- ============================================
SISTEMA DE BLOCOS DINÂMICOS
============================================ --}}

@if(!empty($guide['content_blocks']) && is_array($guide['content_blocks']))

@foreach($guide['content_blocks'] as $block)
    @if(!empty($block['type']))
        @php
        $componentPath = "guide-data-center::blocks.{$block['type']}";
        @endphp

        {{-- Renderizar bloco se componente existir --}}
        @if(view()->exists($componentPath))
            @include($componentPath, ['block' => $block])
        @else
            {{-- Fallback para desenvolvimento
            <div class="container mx-auto px-4 py-4">
                <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                    <p class="text-sm text-yellow-800">
                        ⚠️ Bloco não encontrado: <strong>{{ $block['type'] }}</strong>
                    </p>
                </div>
            </div> --}}
        @endif
    @endif
@endforeach
@else
{{-- FALLBACK: Conteúdo hardcoded antigo (temporário) --}}
<div class="container mx-auto px-4 py-8">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <p class="text-blue-800">
            📝 <strong>Sistema de blocos não configurado para este guia.</strong>
        </p>
        <p class="text-sm text-blue-600 mt-2">
            Configure o campo <code>content_blocks</code> no MongoDB para habilitar o novo sistema.
        </p>
    </div>
</div>
@endif

{{-- BANNER --}}
<div class="container mx-auto px-4 sm:px-6 lg:px-8 mt-2 mb-10">
    <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
        <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
    </div>
</div>


{{-- CRÉDITOS EQUIPE EDITORIAL --}}
@if(!empty($editorialInfo))
<section class="container mx-auto px-4 sm:px-6 lg:px-8 mb-16">
    <div class="bg-blue-50 rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-blue-900 rounded-full flex items-center justify-center mr-3 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 font-montserrat">{{ $editorialInfo['title'] ?? 'Equipe
                Editorial' }}</h3>
        </div>

        <div class="text-gray-700 space-y-2 pl-3 ml-10 border-l-2 border-blue-900">
            <p class="text-sm leading-relaxed font-roboto">{{ $editorialInfo['description'] ?? '' }}</p>
            <p class="text-sm leading-relaxed font-roboto">{{ $editorialInfo['methodology'] ?? '' }}</p>
        </div>

        @if(!empty($editorialInfo['link_url']))
        <div class="flex items-center justify-end mt-5 pt-4 border-t border-gray-200">
            <a href="{{ $editorialInfo['link_url'] }}"
                class="text-blue-900 text-sm hover:text-blue-700 hover:underline flex items-center font-medium">
                {{ $editorialInfo['link_text'] ?? 'Saiba mais' }}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </a>
        </div>
        @endif
    </div>
</section>
@endif

@endsection