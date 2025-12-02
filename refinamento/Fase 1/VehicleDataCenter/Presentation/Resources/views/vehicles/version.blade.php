@extends('vehicle-data-center::layouts.app')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])

{{-- SEO: Canonical e Open Graph --}}
@push('head')
    <link rel="canonical" href="{{ $seo['canonical'] }}" />
    <link rel="alternate" hreflang="pt-BR" href="{{ $seo['canonical'] }}" />
    
    <meta property="og:type" content="website" />
    <meta property="og:title" content="{{ $seo['title'] }}" />
    <meta property="og:description" content="{{ $seo['description'] }}" />
    <meta property="og:image" content="{{ $seo['og_image'] }}" />
    <meta property="og:url" content="{{ $seo['canonical'] }}" />
    <meta property="og:site_name" content="Mercado Veículos" />
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo['title'] }}">
    <meta name="twitter:description" content="{{ $seo['description'] }}">
    <meta name="twitter:image" content="{{ $seo['og_image'] }}">
    
    <style>
        .kv {
            min-width: 140px;
        }
    </style>
@endpush

@section('content')

{{-- BREADCRUMBS --}}
@if(isset($breadcrumbs))
    @section('breadcrumbs')
        <div class="bg-gray-100 border-b border-gray-200">
            <div class="container mx-auto px-4 py-2 whitespace-nowrap overflow-x-auto">
                <nav class="text-xs md:text-sm font-roboto">
                    <ol class="inline-flex" itemscope itemtype="https://schema.org/BreadcrumbList">
                        @foreach($breadcrumbs as $index => $crumb)
                            <li class="flex items-center" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                @if($crumb['url'])
                                    <a href="{{ $crumb['url'] }}" class="text-blue-600 hover:underline" itemprop="item">
                                        <span itemprop="name">{{ $crumb['name'] }}</span>
                                    </a>
                                    <meta itemprop="position" content="{{ $index + 1 }}" />
                                    @if(!$loop->last)
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mx-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

{{-- HERO DA VERSÃO --}}
<section class="bg-white border-b border-gray-200">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="md:flex md:items-start md:justify-between gap-8">
            <div class="flex-1">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3 font-montserrat">
                    {{ $version['full_name'] }}
                </h1>

                <p class="text-sm text-gray-600 mb-4 max-w-2xl font-roboto">
                    {{ $version['description'] }}
                </p>

                {{-- BADGES DE QUALIDADE --}}
                <div class="flex flex-wrap gap-2 mb-5">
                    @foreach($badges as $badge)
                        <span class="inline-flex items-center px-3 py-1 bg-{{ $badge['color'] }}-50 text-{{ $badge['color'] }}-700 text-xs font-medium rounded-full border border-{{ $badge['color'] }}-200">
                            @if($badge['icon'] === 'check')
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                            {{ $badge['text'] }}
                        </span>
                    @endforeach
                </div>

                {{-- QUICK FACTS --}}
                <div class="flex flex-wrap gap-3">
                    @foreach($quickFacts as $fact)
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm flex gap-3 font-roboto">
                            <strong class="kv text-gray-700">{{ $fact['label'] }}</strong>
                            {{ $fact['value'] }}
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="md:w-72 mt-6 md:mt-0">

                <img src="/images/placeholder/corolla-full-hero.jpeg" 
                     alt="{{ $version['full_name'] }}" 
                     class="w-full rounded shadow">
{{-- 
                <img src="{{ $version['image'] }}" 
                     alt="{{ $version['full_name'] }}" 
                     class="w-full rounded shadow"> --}}
                <p class="text-xs text-gray-500 mt-2 font-roboto">Imagem ilustrativa</p>
            </div>
        </div>
    </div>
</section>

{{-- DISCLAIMER CRÍTICO --}}
<div class="container mx-auto px-4 sm:px-6 lg:px-8 mt-6">
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg shadow-sm">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-800 font-medium">
                    <strong>⚠️ Importante:</strong> As informações técnicas são baseadas em especificações oficiais da fabricante e servem para fins informativos. Consulte sempre o manual do proprietário do seu veículo e um profissional qualificado antes de realizar qualquer manutenção ou modificação.
                </p>
            </div>
        </div>
    </div>
</div>

{{-- BANNER RESPONSIVO (MOCK) --}}
<div class="container mx-auto px-4 my-6">
    <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
        <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
    </div>
</div>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    {{-- FICHA TÉCNICA DA VERSÃO --}}
    <section class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Ficha Técnica – {{ $version['name'] }} {{ $version['year'] }}</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- TABELA PRINCIPAL --}}
            <div class="md:col-span-2">
                <table class="w-full text-sm border-collapse font-roboto">
                    <tbody>
                        @foreach($mainSpecs as $index => $spec)
                            <tr class="border-t border-gray-200 {{ $index % 2 === 1 ? 'bg-gray-50' : '' }}">
                                <td class="py-3 w-40 font-medium text-gray-700">{{ $spec['label'] }}</td>
                                <td class="py-3 text-gray-600">{{ $spec['value'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- CARDS LATERAIS --}}
            <aside class="space-y-3">
                @foreach($sideCards as $card)
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                        <p class="text-xs text-gray-500 font-roboto">{{ $card['title'] }}</p>
                        <p class="font-semibold text-sm font-montserrat">{{ $card['value'] }}</p>
                        @if($card['extra'])
                            <p class="text-xs text-gray-500 mt-1 font-roboto">{{ $card['extra'] }}</p>
                        @endif
                    </div>
                @endforeach
            </aside>
        </div>
    </section>

    {{-- BANNER RESPONSIVO (MOCK) --}}
    <div class="my-6">
        <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
            <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
        </div>
    </div>

    {{-- FLUIDOS E CAPACIDADES --}}
    <section class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Fluidos e Capacidades</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm font-roboto">
            @foreach($fluids as $fluid)
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <p class="font-medium">{{ $fluid['emoji'] }} {{ $fluid['label'] }}</p>
                    <p class="text-xs text-gray-600 mt-1">{{ $fluid['value'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- MANUTENÇÃO RESUMIDA --}}
    <section class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Manutenção – Resumo</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm font-roboto">
            @foreach($maintenanceSummary as $maintenance)
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <p class="font-medium mb-1">🔧 {{ $maintenance['km'] }} km</p>
                    <p class="text-xs text-gray-600">{{ $maintenance['items'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-sm text-blue-800">
                <strong>💡 Dica:</strong> Para cronograma detalhado de manutenção, acesse o guia completo de revisões.
            </p>
        </div>
    </section>

    {{-- BANNER RESPONSIVO (MOCK) --}}
    <div class="my-6">
        <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
            <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
        </div>
    </div>

    {{-- GUIAS TÉCNICOS RELACIONADOS --}}
    <section class="mb-8">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Guias Técnicos Completos</h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            @foreach($guides as $guide)
                <a href="{{ $guide['url'] }}"
                    class="block bg-white border border-gray-200 p-4 rounded-lg hover:shadow-md hover:border-blue-500 transition-all text-center">
                    <div class="text-2xl mb-2">{{ $guide['emoji'] }}</div>
                    <p class="text-sm font-medium font-roboto">{{ $guide['name'] }}</p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- BANNER RESPONSIVO (MOCK) --}}
    <div class="my-6">
        <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
            <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
        </div>
    </div>

    {{-- ARTIGOS RELACIONADOS (OPCIONAL) --}}
    {{-- TODO: Implementar se houver artigos relacionados --}}
    {{--
    <section class="mb-12">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Artigos relacionados</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Cards de artigos aqui -->
        </div>
    </section>
    --}}

</div>

{{-- CRÉDITOS EQUIPE EDITORIAL --}}
<div class="bg-blue-50 border-t border-blue-100">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <section class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-sm p-6 border border-blue-100">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2 font-montserrat">Equipe Editorial Mercado Veículos</h3>
                        <p class="text-sm text-gray-700 leading-relaxed font-roboto mb-3">
                            Esta ficha técnica foi desenvolvida com base em especificações oficiais da fabricante {{ $make['name'] }}, manuais do proprietário e dados técnicos verificados. Nossa equipe editorial segue um rigoroso processo de verificação para garantir a precisão das informações apresentadas.
                        </p>
                        <p class="text-sm text-gray-700 leading-relaxed font-roboto mb-4">
                            <strong>Processo editorial:</strong> Coleta de dados oficiais → Verificação cruzada → Revisão técnica → Publicação → Atualizações periódicas
                        </p>
                        <a href="/sobre/metodologia-editorial" class="inline-flex items-center text-blue-700 hover:text-blue-800 font-medium text-sm font-roboto">
                            Conheça nossa metodologia editorial completa
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

@endsection