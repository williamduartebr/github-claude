@extends('auto-info-center::layouts.app')

@section('content')
<!-- Hero Banner -->
@include('auto-info-center::home.partials.hero')

<section class="container mx-auto">
    [ADSENSE-1]
</section>

<!-- Conteúdo Principal -->
<main id="main-content" class="container mx-auto px-4 md:px-0 py-8" itemscope itemtype="https://schema.org/WebPage">

    <!-- Centro de Informações Automotivas -->
    @include('auto-info-center::home.partials.info-center', ['categories' => $infoCenterCategories])

    <!-- Informações de Blog -->
    @include('auto-info-center::home.partials.featured-blog')

    <!-- Seção CTA Newsletter -->
    <x-info::newsletter />

    <!-- Grade de Categorias -->
    @include('auto-info-center::home.partials.popular-categories', ['categories' => $popularCategories])

</main>

<div class="container mx-auto px-4 md:px-0 mb-12">
    [ADSENSE-3]
</div>
@endsection