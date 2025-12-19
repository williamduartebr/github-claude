{{--
Template: guide.specific.blade.php
Renderiza guia completo com includes organizados

@version 4.0 - Arquitetura de includes
--}}

@extends('shared::layouts.app')

@section('title', $seo['title'] ?? '')
@section('meta_description', $seo['description'] ?? '')

{{-- SEO --}}
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
@include('guide-data-center::guide.partials.breadcrumbs')

{{-- HERO / TÍTULO --}}
@include('guide-data-center::guide.partials.hero')

{{-- CONTEÚDO DO GUIA --}}
@include('guide-data-center::guide.partials.content')

{{-- GUIAS RELACIONADOS (Original ViewModel) --}}
@include('guide-data-center::guide.partials.related-guides')

{{-- GUIAS RELACIONADOS EXTRAS (Service) --}}
@include('guide-data-center::guide.partials.related-guides-extra')

{{-- CONTEÚDOS ESSENCIAIS (Original ViewModel) --}}
@include('guide-data-center::guide.partials.essential-cluster')

{{-- CONTEÚDOS ESSENCIAIS EXTRAS (Service) --}}
@include('guide-data-center::guide.partials.essential-contents')

{{-- ANOS DISPONÍVEIS (Service) --}}
@include('guide-data-center::guide.partials.available-years')

{{-- VERSÕES DISPONÍVEIS (Service) --}}
@include('guide-data-center::guide.partials.available-versions')

{{-- BANNER --}}
@include('guide-data-center::guide.partials.banner')

{{-- CRÉDITOS EQUIPE EDITORIAL --}}
@include('guide-data-center::guide.partials.editorial-info')

@endsection