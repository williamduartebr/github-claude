{{--
Template Principal: generic_article.blade.php

Template universal para artigos genéricos usando sistema de blocos modulares.
Serve para TODOS os temas: Óleo, Velas, Embreagem, Bateria, etc.

Sistema de Blocos:
- 15 tipos de blocos diferentes
- Ordem dinâmica via display_order
- Processado pelo GenericArticleViewModel
- Componentes reutilizáveis em components/article/blocks/

Compatível com:
- 100+ artigos de Óleo
- 100 artigos de Velas
- 100 artigos de Embreagem
- 100+ artigos de Câmbio
- Qualquer tema futuro

@author Claude Sonnet 4
@version 1.0 - Universal Generic Article System
--}}

@extends('auto-info-center::layouts.app')

@push('head')
    {{-- AdSense --}}
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-5108844086542870"
        crossorigin="anonymous"></script>

    {{-- SEO Meta Tags --}}
    <title>{{ $article->getData()['seo_data']['page_title'] ?? $article->getData()['title'] }}</title>
    <meta name="description" content="{{ $article->getData()['seo_data']['meta_description'] ?? '' }}">
    
    {{-- Keywords --}}
    @if(!empty($article->getData()['seo_data']['secondary_keywords']))
        <meta name="keywords" content="{{ implode(', ', $article->getData()['seo_data']['secondary_keywords']) }}">
    @endif

    {{-- Canonical URL --}}
    <link rel="canonical" href="{{ $article->getData()['seo_data']['canonical_url'] ?? url()->current() }}" />
    <link rel="alternate" hreflang="pt-BR" href="{{ $article->getData()['seo_data']['canonical_url'] ?? url()->current() }}" />

    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="article" />
    <meta property="og:title" content="{{ $article->getData()['seo_data']['og_title'] ?? $article->getData()['title'] }}" />
    <meta property="og:description" content="{{ $article->getData()['seo_data']['og_description'] ?? '' }}" />
    <meta property="og:image" content="{{ $article->getData()['seo_data']['og_image'] ?? '' }}" />
    <meta property="og:url" content="{{ $article->getData()['seo_data']['canonical_url'] ?? url()->current() }}" />
    <meta property="og:site_name" content="Mercado Veículos" />
    <meta property="article:published_time" content="{{ $article->getData()['created_at'] ?? now()->toISOString() }}" />
    <meta property="article:modified_time" content="{{ $article->getData()['updated_at'] ?? now()->toISOString() }}" />

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $article->getData()['seo_data']['og_title'] ?? $article->getData()['title'] }}">
    <meta name="twitter:description" content="{{ $article->getData()['seo_data']['og_description'] ?? '' }}">
    <meta name="twitter:image" content="{{ $article->getData()['seo_data']['og_image'] ?? '' }}">

    {{-- Preload de recursos críticos --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://mercadoveiculos.s3.amazonaws.com/statics/logos/logo-mercadoveiculos-write.svg" as="image">

    {{-- Schema.org Structured Data (Article) --}}
    @if(!empty($article->getData()['structured_data']))
        <script type="application/ld+json">
        {!! json_encode($article->getData()['structured_data'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endif

    {{-- Schema.org FAQPage (se houver FAQs) --}}
    @if(!empty($article->getData()['faq_schema']))
        <script type="application/ld+json">
        {!! json_encode($article->getData()['faq_schema'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endif
@endpush

@section('content')

{{-- Breadcrumb --}}
@include('auto-info-center::article.partials.breadcrumb')

{{-- Conteúdo Principal --}}
<main id="main-content" class="container mx-auto px-4">
    <div itemscope itemtype="https://schema.org/Article">
        {{-- Meta tags invisíveis para Schema.org --}}
        <meta itemprop="headline" content="{{ $article->getData()['title'] ?? '' }}" />
        <meta itemprop="datePublished" content="{{ $article->getData()['created_at'] ?? now()->toISOString() }}" />
        <meta itemprop="dateModified" content="{{ $article->getData()['updated_at'] ?? now()->toISOString() }}" />
        
        {{-- Article Container --}}
        <article class="max-w-4xl mx-auto pt-6 pb-12">
            
            {{-- Header do Artigo --}}
            <header class="mb-8">
                <div class="border-b-2 border-[#0E368A] pb-4">
                    {{-- H1 Principal --}}
                    <h1 class="text-3xl md:text-4xl font-semibold leading-tight text-[#151C25] mb-3">
                        {{ $article->getData()['seo_data']['h1'] ?? $article->getData()['title'] }}
                    </h1>
                    
                    {{-- Metadata do Artigo --}}
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 mt-4">
                        {{-- Data de atualização --}}
                        <div class="flex items-center">
                            <svg class="h-4 w-4 mr-1 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                            </svg>
                            <span>Atualizado em: {{ $article->getData()['formated_updated_at'] ?? now()->format('d/m/Y') }}</span>
                        </div>

                        {{-- Tempo de leitura --}}
                        @if(!empty($article->getData()['reading_time']))
                            <div class="flex items-center">
                                <svg class="h-4 w-4 mr-1 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ $article->getData()['reading_time'] }} min de leitura</span>
                            </div>
                        @endif

                        {{-- Dificuldade --}}
                        @if(!empty($article->getData()['difficulty']))
                            <div class="flex items-center">
                                <svg class="h-4 w-4 mr-1 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                                </svg>
                                <span>Nível: {{ ucfirst($article->getData()['difficulty']) }}</span>
                            </div>
                        @endif

                        {{-- Badge de experiência real --}}
                        @if(!empty($article->getData()['experience_based']) && $article->getData()['experience_based'] === true)
                            <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                ✓ Baseado em Experiência Real
                            </div>
                        @endif
                    </div>
                </div>
            </header>          

            {{-- Blocos de Conteúdo com Ads Estratégicos --}}
            @php
                $contentBlocks = $article->getData()['content_blocks'] ?? [];
                $totalBlocks = count($contentBlocks);
                $adPositions = ['before_tldr' => false, 'mid_content' => false, 'before_faq' => false];
            @endphp

            @if(!empty($contentBlocks) && is_array($contentBlocks))
                <div class="article-content">
                    @foreach($contentBlocks as $index => $block)
                        @if(!empty($block['block_type']))
                            @php
                                $componentPath = "auto-info-center::article.blocks.{$block['block_type']}";
                                $isMiddle = ($index + 1) === (int)ceil($totalBlocks * 0.4);
                            @endphp

                            {{-- 🎯 AD 1: ANTES DO TLDR (Melhor Posição!) --}}
                            @if($block['block_type'] === 'tldr' && !$adPositions['before_tldr'])
                                @php $adPositions['before_tldr'] = true; @endphp
                                
                                <!-- Banner de Anúncio 1 - Antes da Resposta Rápida -->
                                <div class="my-8 flex justify-center">
                                    <div class="w-full max-w-4xl">
                                        [ADSENSE-ARTICLE-1]
                                    </div>
                                </div>
                            @endif

                            {{-- Renderizar Bloco --}}
                            @if(view()->exists($componentPath))
                                @include($componentPath, ['block' => $block])
                            @endif

                            {{-- 🎯 AD 2: MEIO (40%) --}}
                            @if($isMiddle && !$adPositions['mid_content'])
                                @php $adPositions['mid_content'] = true; @endphp
                                
                                <!-- Banner de Anúncio 2 - Meio do Artigo -->
                                <div class="my-8 flex justify-center">
                                    <div class="w-full max-w-4xl">
                                        [ADSENSE-ARTICLE-2]
                                    </div>
                                </div>
                            @endif

                            {{-- 🎯 AD 3: ANTES DO FAQ --}}
                            @if($block['block_type'] === 'faq' && !$adPositions['before_faq'])
                                @php $adPositions['before_faq'] = true; @endphp
                                
                                <!-- Banner de Anúncio 3 - Antes das Perguntas Frequentes -->
                                <div class="my-8 flex justify-center">
                                    <div class="w-full max-w-4xl">
                                        [ADSENSE-ARTICLE-3]
                                    </div>
                                </div>
                            @endif

                        @endif
                    @endforeach
                </div>
            @endif

            {{-- Artigos Relacionados (opcional) --}}
            @if(!empty($article->getData()['related_articles']) && is_array($article->getData()['related_articles']))
                <aside class="mt-12 pt-8 border-t-2 border-gray-200">
                    <h2 class="text-2xl font-semibold text-[#151C25] mb-6">
                        📚 Artigos Relacionados
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($article->getData()['related_articles'] as $relatedSlug)
                            <a href="{{ route('info.article.show', $relatedSlug) }}" 
                               class="block bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg p-4 transition-colors">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-blue-500 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-gray-700 font-medium">{{ ucwords(str_replace('-', ' ', $relatedSlug)) }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </aside>
            @endif

            {{-- Footer do Artigo --}}
            <footer class="mt-12 pt-8 border-t-2 border-gray-200">
                <div class="bg-gray-50 rounded-lg p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-700 leading-relaxed">
                                <strong>Importante:</strong> Este artigo contém informações técnicas baseadas em experiências reais e fontes confiáveis. 
                                Sempre consulte um profissional qualificado para serviços específicos em seu veículo.
                            </p>
                            <p class="text-xs text-gray-500 mt-2">
                                Última verificação: {{ $article->getData()['formated_updated_at'] ?? now()->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                </div>
            </footer>

        </article>
    </div>
</main>

@endsection

@push('scripts')
    {{-- Scripts específicos do artigo (se necessário) --}}
    <script>
        // Smooth scroll para âncoras internas
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Log de leitura (analytics)
        console.log('📊 Article View:', {
            title: '{{ $article->getData()['title'] ?? '' }}',
            topic: '{{ $article->getData()['article_topic'] ?? 'general' }}',
            category: '{{ $article->getData()['article_category'] ?? 'guide' }}',
            blocks: {{ count($article->getData()['content_blocks'] ?? []) }}
        });
    </script>
@endpush