{{--
Template Desktop Otimizado: tire_calibration_pickup.blade.php
Usando dados embarcados das ViewModels e includes modulares DENTRO da estrutura existente
--}}

@extends('auto-info-center::layouts.app')

@push('head')
<link rel="amphtml" href="{{ route('info.article.show.amp', $article->slug) }}">
<link rel="canonical" href="{{ route('info.article.show', $article->slug) }}">

<script type="application/ld+json">
    {!! json_encode($article->structured_data) !!}
</script>
@endpush

@section('content')

@include('auto-info-center::article.partials.breadcrumb')

<!-- Conteúdo Principal -->
<main id="main-content" class="container mx-auto px-4">
    <div itemscope itemtype="https://schema.org/Article">
        <meta itemprop="vehicleEngine" content="{{ $article->getData()['vehicle_info']['full_name'] ?? '' }}" />
        <meta itemprop="category" content="Manutenção Automotiva" />

        <!-- Tag Article -->
        <article class="max-w-4xl mx-auto pt-6 pb-12">

            
            <!-- Cabeçalho Minimalista -->
            <div class="mb-8">
                <div class="border-b-2 border-[#0E368A] pb-4">
                    <h1 class="text-3xl md:text-4xl font-semibold leading-tight text-[#151C25]">
                        {{ $article->getData()['seo_data']['h1'] ?? $article->getData()['title'] }}
                    </h1>
                    <p class="text-sm mt-2 text-gray-500">
                        Atualizado em: {{ $article->getData()['formated_updated_at'] ?? '15 de janeiro de 2025' }}
                    </p>
                </div>
            </div>

            <!-- Introdução -->
            @if(!empty($article->getData()['content']['introducao']))
            <div class="mb-10">
                <p class="text-lg text-gray-800 leading-relaxed">
                    {!! nl2br(e($article->getData()['content']['introducao'])) !!}
                </p>
            </div>
            @endif

            <!-- Banner de Anúncio 1 -->
            <div class="my-8">
                [ADSENSE-ARTICLE-1]
            </div>

            <!-- Destaque da Pressão Ideal - PICKUP ESPECÍFICO -->
            <section class="mb-12 bg-gradient-to-r from-[#0E368A]/5 to-[#0E368A]/10 rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Pressão Ideal para Pickup
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Uso Normal -->
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="text-center">
                            <div class="h-16 w-16 rounded-full bg-gradient-to-br from-[#0E368A]/10 to-[#0E368A]/20 flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Uso Normal</h3>
                            <div class="text-2xl font-bold text-[#0E368A] mb-1">
                                {{ $article->getData()['vehicle_data']['pressure_specifications']['empty_pressure_display'] ?? '35/40 PSI' }}
                            </div>
                            <p class="text-sm text-gray-600">Sem carga na caçamba</p>
                        </div>
                    </div>

                    <!-- Com Carga -->
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="text-center">
                            <div class="h-16 w-16 rounded-full bg-gradient-to-br from-[#E06600]/10 to-[#E06600]/20 flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#E06600]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M9 1l8 6v10" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Com Carga</h3>
                            <div class="text-2xl font-bold text-[#E06600] mb-1">
                                {{ $article->getData()['vehicle_data']['pressure_specifications']['loaded_pressure_display'] ?? '38/45 PSI' }}
                            </div>
                            <p class="text-sm text-gray-600">Caçamba carregada</p>
                        </div>
                    </div>

                    <!-- Pneu Estepe -->
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="text-center">
                            <div class="h-16 w-16 rounded-full bg-gradient-to-br from-green-500/10 to-green-500/20 flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Pneu Estepe</h3>
                            <div class="text-2xl font-bold text-green-600 mb-1">
                                {{ $article->getData()['vehicle_data']['pressure_specifications']['pressure_spare'] ?? '42' }} PSI
                            </div>
                            <p class="text-sm text-gray-600">Sempre verificar</p>
                        </div>
                    </div>
                </div>

                <!-- Alerta Pickup -->
                <div class="mt-6 bg-[#E06600]/5 border border-[#E06600]/20 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0 mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#E06600]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-md font-medium text-[#E06600] mb-1">Importante para Pickups</h3>
                            <p class="text-sm text-gray-700">
                                Pickups têm pressões traseiras mais altas devido à capacidade de carga. Sempre ajuste conforme o peso transportado na caçamba para manter estabilidade e segurança.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Especificações dos Pneus Originais e Localização da Etiqueta -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Especificações dos Pneus Originais
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Especificações Técnicas -->
                    @if(!empty($article->getData()['content']['especificacoes_por_versao']))
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Medidas de Pneus por Versão</h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse">
                                <thead>
                                    <tr class="bg-[#0E368A] text-white">
                                        <th class="py-2 px-3 text-left font-medium text-xs">Versão</th>
                                        <th class="py-2 px-3 text-left font-medium text-xs">Medidas de Pneus</th>
                                        <th class="py-2 px-3 text-left font-medium text-xs">Índice de Carga/Vel.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($article->getData()['content']['especificacoes_por_versao'] as $spec)
                                    <tr class="border-b border-gray-200">
                                        <td class="py-2 px-3 text-xs">{{ $spec['versao'] }}</td>
                                        <td class="py-2 px-3 text-xs font-semibold">{{ $spec['medida_pneus'] }}</td>
                                        <td class="py-2 px-3 text-xs">{{ $spec['indice_carga_velocidade'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-xs text-gray-700">
                                <span class="font-medium">Observação:</span> Para pickups, índices de carga são especialmente importantes devido à capacidade de transporte na caçamba.
                            </p>
                        </div>
                    </div>
                    @endif

                    <!-- Localização da Etiqueta -->
                    @if(!empty($article->getData()['content']['localizacao_etiqueta']))
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Localização da Etiqueta de Pressão</h3>
                        </div>

                        @php $location = $article->getData()['content']['localizacao_etiqueta'] @endphp
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="h-6 w-6 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-3 flex-shrink-0 mt-0.5">
                                    <span class="text-xs font-semibold text-[#0E368A]">1</span>
                                </div>
                                <p class="text-sm text-gray-700">{{ $location['descricao'] }}</p>
                            </div>

                            @foreach($location['locais_alternativos'] ?? [] as $index => $altLocation)
                            <div class="flex items-start">
                                <div class="h-6 w-6 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-3 flex-shrink-0 mt-0.5">
                                    <span class="text-xs font-semibold text-[#0E368A]">{{ $index + 2 }}</span>
                                </div>
                                <p class="text-sm text-gray-700">{{ $altLocation }}</p>
                            </div>
                            @endforeach
                        </div>

                        @if($location['observacao'])
                        <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#E06600] mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm font-medium text-gray-800">{{ $location['observacao'] }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </section>

            <!-- Tabela de Pressão por Versão -->
            @if(!empty($article->getData()['content']['especificacoes_por_versao']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Pressão dos Pneus por Versão (PSI)
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-[#0E368A] text-white">
                                <tr>
                                    <th class="py-3 px-4 text-left font-medium text-sm">Versão</th>
                                    <th class="py-3 px-4 text-left font-medium text-sm">Medida dos Pneus</th>
                                    <th class="py-3 px-4 text-center font-medium text-sm">Dianteiro Normal</th>
                                    <th class="py-3 px-4 text-center font-medium text-sm">Traseiro Normal</th>
                                    <th class="py-3 px-4 text-center font-medium text-sm">Dianteiro c/ Carga</th>
                                    <th class="py-3 px-4 text-center font-medium text-sm">Traseiro c/ Carga</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($article->getData()['content']['especificacoes_por_versao'] as $spec)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ $spec['versao'] }}</td>
                                    <td class="py-3 px-4 text-sm text-gray-700">{{ $spec['medida_pneus'] }}</td>
                                    <td class="py-3 px-4 text-sm text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#0E368A] text-white">
                                            {{ $spec['pressao_dianteiro_normal'] }} PSI
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#0E368A] text-white">
                                            {{ $spec['pressao_traseiro_normal'] }} PSI
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#E06600] text-white">
                                            {{ $spec['pressao_dianteiro_carregado'] }} PSI
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#E06600] text-white">
                                            {{ $spec['pressao_traseiro_carregado'] }} PSI
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 text-sm text-gray-700">
                        <div class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#E06600] mr-2 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="font-medium mb-1">Importante para Pickups:</p>
                                <p>Use pressões "Normal" para uso urbano e rodoviário sem carga. Use pressões "c/ Carga" quando transportar peso na caçamba ou rebocar.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Tabela de Carga Completa -->
            @if(!empty($article->getData()['content']['tabela_carga_completa']['condicoes']))
            <section class="mb-12" id="tabela-carga-completa">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    {{ $article->getData()['content']['tabela_carga_completa']['titulo'] }}
                </h2>

                <div class="mb-4">
                    <p class="text-gray-700 leading-relaxed">
                        {{ $article->getData()['content']['tabela_carga_completa']['descricao'] }}
                    </p>
                </div>

                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-[#E06600] text-white">
                                <tr>
                                    <th class="py-3 px-4 text-left font-medium text-sm">Versão</th>
                                    <th class="py-3 px-4 text-left font-medium text-sm">Ocupantes</th>
                                    <th class="py-3 px-4 text-left font-medium text-sm">Carga na Caçamba</th>
                                    <th class="py-3 px-4 text-center font-medium text-sm">Dianteiro</th>
                                    <th class="py-3 px-4 text-center font-medium text-sm">Traseiro</th>
                                    <th class="py-3 px-4 text-left font-medium text-sm">Observação</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($article->getData()['content']['tabela_carga_completa']['condicoes'] as $condition)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ $condition['versao'] }}</td>
                                    <td class="py-3 px-4 text-sm text-gray-700">{{ $condition['ocupantes'] }}</td>
                                    <td class="py-3 px-4 text-sm text-gray-700">{{ $condition['bagagem'] }}</td>
                                    <td class="py-3 px-4 text-sm text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#E06600] text-white">
                                            {{ $condition['pressao_dianteira'] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#E06600] text-white">
                                            {{ $condition['pressao_traseira'] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-700">{{ $condition['observacao'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
            @endif

            <!-- Banner de Anúncio 2 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-2]
            </div>

            <!-- Condições Especiais -->
            @if(!empty($article->getData()['content']['condicoes_especiais']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Ajustes para Condições Especiais
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($article->getData()['content']['condicoes_especiais'] as $condition)
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    @if(str_contains($condition['condicao'], 'Carga'))
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M9 1l8 6v10" />
                                    @elseif(str_contains($condition['condicao'], 'Rodovia'))
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    @elseif(str_contains($condition['condicao'], 'Off-road'))
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                    @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    @endif
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $condition['condicao'] }}</h3>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-medium text-gray-700 mb-1">Ajuste Recomendado:</p>
                                <p class="text-sm text-[#0E368A] font-semibold">{{ $condition['ajuste_recomendado'] }}</p>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-gray-700 mb-1">Aplicação:</p>
                                <p class="text-sm text-gray-600">{{ $condition['aplicacao'] }}</p>
                            </div>

                           <div>
                                <p class="text-sm font-medium text-gray-700 mb-1">Justificativa:</p>
                                <p class="text-sm text-gray-600">{{ $condition['justificativa'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Sistema TPMS (condicional) -->
            @if($article->getData()['vehicle_data']['has_tpms'] ?? false)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Sistema de Monitoramento de Pressão (TPMS)
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="flex items-start">
                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500/10 to-blue-500/20 flex items-center justify-center mr-4 flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Monitoramento Automático Disponível</h3>
                            <p class="text-gray-700 mb-4">
                                Esta pickup possui sistema TPMS que monitora constantemente a pressão dos pneus e alerta sobre variações críticas no painel de instrumentos.
                            </p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <h4 class="font-medium text-green-800 mb-2">Vantagens do TPMS:</h4>
                                    <ul class="text-sm text-green-700 space-y-1">
                                        <li>• Alerta em tempo real</li>
                                        <li>• Maior segurança com carga</li>
                                        <li>• Prevenção de acidentes</li>
                                        <li>• Economia de combustível</li>
                                    </ul>
                                </div>
                                
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <h4 class="font-medium text-yellow-800 mb-2">Importante Lembrar:</h4>
                                    <ul class="text-sm text-yellow-700 space-y-1">
                                        <li>• Não substitui verificação manual</li>
                                        <li>• Alerta apenas quedas críticas</li>
                                        <li>• Verificar quinzenalmente mesmo assim</li>
                                        <li>• Recalibrar após reset</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Conversão de Unidades -->
            @if(!empty($article->getData()['content']['conversao_unidades']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Conversão de Unidades de Pressão
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-[#0E368A] text-white">
                                <tr>
                                    <th class="py-3 px-4 text-left font-medium text-sm">PSI</th>
                                    <th class="py-3 px-4 text-left font-medium text-sm">kgf/cm²</th>
                                    <th class="py-3 px-4 text-left font-medium text-sm">Bar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($article->getData()['content']['conversao_unidades']['tabela_conversao'] as $conversion)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ $conversion['psi'] }} PSI</td>
                                    <td class="py-3 px-4 text-sm text-gray-700">{{ $conversion['kgf_cm2'] }}</td>
                                    <td class="py-3 px-4 text-sm text-gray-700">{{ $conversion['bar'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(!empty($article->getData()['content']['conversao_unidades']['observacao']))
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-700">
                            <span class="font-medium">Observação:</span> {{ $article->getData()['content']['conversao_unidades']['observacao'] }}
                        </p>
                    </div>
                    @endif
                </div>
            </section>
            @endif

            <!-- Cuidados e Recomendações -->
            @if(!empty($article->getData()['content']['cuidados_recomendacoes']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Cuidados Específicos para Pickups
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        @php $recommendations = $article->getData()['content']['cuidados_recomendacoes'] @endphp
                        @php $chunkedRecommendations = array_chunk($recommendations, ceil(count($recommendations) / 2)) @endphp

                        @foreach($chunkedRecommendations as $column)
                        <div class="space-y-5">
                            @foreach($column as $recommendation)
                            <div class="flex items-start">
                                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3 flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        @if(str_contains($recommendation['categoria'], 'Verificação'))
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        @elseif(str_contains($recommendation['categoria'], 'Carga'))
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M9 1l8 6v10" />
                                        @elseif(str_contains($recommendation['categoria'], 'Rodízio'))
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        @endif
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-md font-medium text-gray-900 mb-1">{{ $recommendation['categoria'] }}</h3>
                                    <p class="text-sm text-gray-700">{{ $recommendation['descricao'] }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>

                    <!-- Alerta Específico para Pickups -->
                    <div class="mt-6 bg-[#E06600]/5 border border-[#E06600]/20 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0 mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#E06600]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-md font-medium text-[#E06600] mb-1">Atenção Especial para Pickups</h3>
                                <p class="text-sm text-gray-700">
                                    Pickups sofrem variações maiores de carga que carros comuns. Variações de peso de 300-1000kg na caçamba exigem ajustes frequentes na pressão dos pneus para manter segurança e economia.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Banner de Anúncio 3 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-3]
            </div>

            <!-- Impacto no Desempenho -->
            @if(!empty($article->getData()['content']['impacto_pressao']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Impacto da Pressão no Desempenho
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Subcalibrado -->
                    @if(!empty($article->getData()['content']['impacto_pressao']['subcalibrado']))
                    @php $subcalibrado = $article->getData()['content']['impacto_pressao']['subcalibrado'] @endphp
                    <div class="bg-white rounded-lg border-2 border-red-200 p-5">
                        <div class="text-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-red-100 to-red-200 flex items-center justify-center mx-auto mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-red-800">{{ $subcalibrado['titulo'] }}</h3>
                        </div>
                        
                        <ul class="space-y-2 text-sm">
                            @foreach($subcalibrado['problemas'] as $problema)
                            <li class="flex items-start text-red-700">
                                <span class="inline-block w-1 h-1 rounded-full bg-red-500 mt-2 mr-2 flex-shrink-0"></span>
                                <span>{{ $problema }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Ideal -->
                    @if(!empty($article->getData()['content']['impacto_pressao']['ideal']))
                    @php $ideal = $article->getData()['content']['impacto_pressao']['ideal'] @endphp
                    <div class="bg-white rounded-lg border-2 border-green-200 p-5">
                        <div class="text-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-green-100 to-green-200 flex items-center justify-center mx-auto mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-green-800">{{ $ideal['titulo'] }}</h3>
                        </div>
                        
                        <ul class="space-y-2 text-sm">
                            @foreach($ideal['beneficios'] as $beneficio)
                            <li class="flex items-start text-green-700">
                                <span class="inline-block w-1 h-1 rounded-full bg-green-500 mt-2 mr-2 flex-shrink-0"></span>
                                <span>{{ $beneficio }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Sobrecalibrado -->
                    @if(!empty($article->getData()['content']['impacto_pressao']['sobrecalibrado']))
                    @php $sobrecalibrado = $article->getData()['content']['impacto_pressao']['sobrecalibrado'] @endphp
                    <div class="bg-white rounded-lg border-2 border-orange-200 p-5">
                        <div class="text-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-orange-100 to-orange-200 flex items-center justify-center mx-auto mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-orange-800">{{ $sobrecalibrado['titulo'] }}</h3>
                        </div>
                        
                        <ul class="space-y-2 text-sm">
                            @foreach($sobrecalibrado['problemas'] as $problema)
                            <li class="flex items-start text-orange-700">
                                <span class="inline-block w-1 h-1 rounded-full bg-orange-500 mt-2 mr-2 flex-shrink-0"></span>
                                <span>{{ $problema }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </section>
            @endif

            <!-- Conclusão -->
            @if(!empty($article->getData()['content']['consideracoes_finais']))
            <section class="mb-12 bg-gray-50 rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-4">Considerações Finais</h2>
                <div class="prose prose-lg max-w-none text-gray-800">
                    {!! nl2br(e($article->getData()['content']['consideracoes_finais'])) !!}
                </div>
            </section>
            @endif

            <!-- Perguntas Frequentes -->
            @if(!empty($article->getData()['content']['perguntas_frequentes']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Perguntas Frequentes
                </h2>

                <div class="space-y-4">
                    @foreach($article->getData()['content']['perguntas_frequentes'] as $faq)
                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                        <button class="flex justify-between items-center w-full px-5 py-4 text-left text-gray-900 font-medium focus:outline-none hover:bg-gray-50 faq-toggle">
                            <span>{{ $faq['pergunta'] }}</span>
                            <svg class="h-5 w-5 text-[#0E368A] faq-icon transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div class="px-5 py-4 border-t border-gray-200 bg-gray-50 faq-content hidden">
                            <p class="text-gray-700">{{ $faq['resposta'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Nota informativa -->
            @include('auto-info-center::article.partials.info_note_manual')

            <!-- Créditos e Atualização -->
            @include('auto-info-center::article.partials.update_content')
        </article>
    </div>

    <!-- Créditos Equipe Editorial -->
    @include('auto-info-center::article.partials.editorial_team')

    <!-- Newsletter Simplificada -->
    @include('auto-info-center::article.partials.newsletter')
</main>

<!-- JavaScript para FAQ Toggle e Navegação -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // FAQ Toggle Functionality
    const faqToggles = document.querySelectorAll('.faq-toggle');
    
    faqToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const content = this.nextElementSibling;
            const icon = this.querySelector('.faq-icon');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        });
    });

    // Smooth scroll para links internos
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

    // Analytics tracking para interações
    function trackEvent(action, label) {
        if (typeof gtag !== 'undefined') {
            gtag('event', action, {
                'event_category': 'Pickup_Tire_Pressure',
                'event_label': label
            });
        }
    }

    // Track FAQ clicks
    faqToggles.forEach((trigger, index) => {
        trigger.addEventListener('click', function() {
            trackEvent('faq_toggle', `question_${index + 1}`);
        });
    });
});

// Função para scroll suave até a tabela de carga
function scrollToLoadTable() {
    const loadTableSection = document.getElementById('tabela-carga-completa');
    
    if (loadTableSection) {
        loadTableSection.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
        
        // Adiciona um highlight temporário
        loadTableSection.style.backgroundColor = '#dbeafe';
        setTimeout(() => {
            loadTableSection.style.backgroundColor = '';
        }, 2000);
    }
}
</script>

@endsection