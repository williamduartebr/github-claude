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

<main id="main-content" class="bg-gray-50">

  <!-- ===================== -->
  <!-- CONTEÚDO ABERTO -->
  <!-- ===================== -->

  <section class="bg-white border-b border-gray-200">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12 max-w-4xl">

      <h1 class="text-3xl md:text-4xl font-bold text-gray-900 font-montserrat mb-6">
        Óleo do Motor Honda Civic 2024: até onde dá para ir sem risco?
      </h1>

      <p class="text-lg text-gray-700 leading-relaxed mb-6 font-roboto">
        Este guia não existe para repetir o manual. Ele existe para responder
        uma pergunta prática: <strong>quando o uso real começa a cobrar um preço invisível do motor</strong>.
      </p>

      <p class="text-gray-700 leading-relaxed mb-6 font-roboto">
        O Honda Civic 2024 EX 2.0 CVT utiliza um motor com comando variável e
        tolerâncias projetadas para trabalhar com óleo limpo e estável.
        No papel, o intervalo de troca chega a 10.000 km.
      </p>

      <p class="text-gray-700 leading-relaxed mb-6 font-roboto">
        O problema é que esse intervalo pressupõe um uso ideal, pouco comum
        no Brasil: trajetos longos, poucas partidas a frio e baixa diluição
        de combustível.
      </p>

      <div class="bg-blue-50 border-l-4 border-blue-600 p-4 mb-8">
        <p class="text-sm text-blue-900 font-roboto">
          ✔ Tudo até aqui é conteúdo aberto, técnico e indexável.
        </p>
      </div>

    </div>
  </section>

  <!-- ===================== -->
  <!-- TRANSIÇÃO DE VALOR -->
  <!-- ===================== -->

  <section class="bg-gray-100 border-b border-gray-300">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10 max-w-4xl">

      <h2 class="text-xl font-semibold text-gray-900 mb-4 font-montserrat">
        Onde a maioria dos donos erra
      </h2>

      <p class="text-gray-700 leading-relaxed font-roboto">
        A partir daqui, não falamos mais de especificação genérica.
        Entramos em <strong>decisão técnica baseada em perfil de uso</strong>,
        análise de desgaste progressivo e custo oculto ao longo dos anos.
      </p>

    </div>
  </section>

  <!-- ===================== -->
  <!-- BLOQUEIO POR VALOR -->
  <!-- ===================== -->

  <section class="bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-16 max-w-4xl">

      <div class="relative overflow-hidden rounded-2xl border border-gray-300 shadow-lg">

        <!-- Fundo desfocado -->
        <div class="absolute inset-0 bg-gradient-to-b from-white/40 to-white blur-sm"></div>

        <!-- Conteúdo bloqueado -->
        <div class="relative p-10 text-center">

          <!-- SVG LOCK -->
          <div class="flex justify-center mb-6">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none"
                 xmlns="http://www.w3.org/2000/svg" class="text-blue-900">
              <rect x="4" y="10" width="16" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
              <path d="M8 10V7a4 4 0 018 0v3" stroke="currentColor" stroke-width="2"/>
            </svg>
          </div>

          <h3 class="text-2xl font-bold text-gray-900 mb-4 font-montserrat">
            Conteúdo técnico avançado
          </h3>

          <p class="text-gray-700 mb-6 font-roboto leading-relaxed">
            A partir deste ponto, o conteúdo aprofunda:
          </p>

          <ul class="text-left max-w-md mx-auto text-gray-700 text-sm space-y-2 mb-8 font-roboto">
            <li>• Intervalos ideais por perfil de uso real</li>
            <li>• Envelhecimento químico do óleo</li>
            <li>• Consequências silenciosas no motor R20</li>
            <li>• Decisão técnica: custo x desgaste</li>
          </ul>

          <p class="text-sm text-gray-600 mb-6 font-roboto">
            Não é paywall. É conteúdo de decisão.
          </p>

          <a href="/area-de-membros"
             class="inline-flex items-center justify-center px-6 py-3
                    bg-blue-900 text-white font-semibold rounded-lg
                    hover:bg-blue-800 transition-all font-montserrat">
            Desbloquear análise completa
          </a>

          <p class="text-xs text-gray-500 mt-4 font-roboto">
            Acesso imediato • Sem anúncios • Conteúdo técnico
          </p>

        </div>
      </div>

    </div>
  </section>

  <!-- ===================== -->
  <!-- CONTEÚDO VISUAL “CORTADO” -->
  <!-- ===================== -->

  <section class="bg-gray-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 max-w-4xl">

      <div class="opacity-40 select-none">
        <h4 class="text-lg font-semibold text-gray-700 mb-2 font-montserrat">
          Análise por perfil de uso (prévia)
        </h4>
        <p class="text-gray-600 text-sm font-roboto">
          Urbano intenso → risco elevado após 6–7 mil km<br>
          Uso misto → equilíbrio aceitável<br>
          Rodoviário → maior tolerância
        </p>
      </div>

    </div>
  </section>

</main>


</main>




@endsection