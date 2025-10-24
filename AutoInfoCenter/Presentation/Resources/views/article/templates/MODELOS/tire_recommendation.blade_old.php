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

<main id="main-content" class="container mx-auto px-4">
    <div itemscope itemtype="https://schema.org/TechArticle">
        <meta itemprop="vehicleEngine"
            content="{{ $article->vehicle_info['make'] }} {{ $article->vehicle_info['model'] }} {{ $article->vehicle_info['engine'] ?? '' }}" />
        <meta itemprop="category" content="{{ $article->category['name'] }}" />

        <article class="max-w-4xl mx-auto pt-6 pb-12">

            @php
            $imageDefault = \Str::slug( sprintf("%s-%s", $article->category['slug'] ?? '',
            $article->vehicle_info['vehicle_type'] ?? 'pneus'));
            @endphp

            <div class="relative rounded-lg overflow-hidden mb-8 mt-2">
                <img src="https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/{{  $imageDefault  }}.png"
                    alt="{{ $article->title }}" class="w-full h-64 object-cover"
                    onerror="this.src='https:\/\/mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/default-tire.png'">
                <div class="absolute inset-0 bg-gradient-to-t from-black/100 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-6 text-white">
                    <h1 class="text-3xl md:text-4xl font-semibold leading-tight">{{ $article->title }}</h1>
                    <p class="text-sm mt-2 opacity-90">Atualizado em: {{ $article->formated_updated_at }}</p>
                </div>
            </div>

            <!-- Introdução -->
            <div class="mb-10">
                <p class="text-lg text-gray-800 leading-relaxed">
                    {{ $article->introduction }}
                </p>
            </div>

            <!-- Banner de Anúncio 1 -->
            <div class="my-8">
                [ADSENSE-ARTICLE-1]
            </div>

            <!-- Especificações Oficiais -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Especificações
                    Oficiais {{ $article->vehicle_info['make'] ?? '' }}</h2>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-200">
                        <!-- Pneu Dianteiro -->
                        <div class="p-6">
                            <div class="flex items-center mb-5">
                                <div
                                    class="flex-shrink-0 h-12 w-12 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900">Pneu Dianteiro</h3>
                            </div>

                            <div class="space-y-4">
                                <div class="flex justify-between border-b border-gray-100 pb-2">
                                    <span class="text-gray-600">Medida Original:</span>
                                    <span class="font-medium text-gray-900">{{ $article->official_specs['pneu_dianteiro']['medida_original'] ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between border-b border-gray-100 pb-2">
                                    <span class="text-gray-600">Índice de Carga:</span>
                                    <span class="font-medium text-gray-900">{{ $article->official_specs['pneu_dianteiro']['indice_carga'] ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between border-b border-gray-100 pb-2">
                                    <span class="text-gray-600">Índice de Velocidade:</span>
                                    <span class="font-medium text-gray-900">{{ $article->official_specs['pneu_dianteiro']['indice_velocidade'] ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between border-b border-gray-100 pb-2">
                                    <span class="text-gray-600">Pressão Recomendada:</span>
                                    <span class="font-medium text-gray-900">{{ $article->official_specs['pneu_dianteiro']['pressao_recomendada'] ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Capacidade de Carga:</span>
                                    <span class="font-medium text-gray-900">{{ $article->official_specs['pneu_dianteiro']['capacidade_carga'] ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Pneu Traseiro -->
                        <div class="p-6">
                            <div class="flex items-center mb-5">
                                <div
                                    class="flex-shrink-0 h-12 w-12 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900">Pneu Traseiro</h3>
                            </div>

                            <div class="space-y-4">
                                <div class="flex justify-between border-b border-gray-100 pb-2">
                                    <span class="text-gray-600">Medida Original:</span>
                                    <span class="font-medium text-gray-900">{{ $article->official_specs['pneu_traseiro']['medida_original'] ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between border-b border-gray-100 pb-2">
                                    <span class="text-gray-600">Índice de Carga:</span>
                                    <span class="font-medium text-gray-900">{{ $article->official_specs['pneu_traseiro']['indice_carga'] ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between border-b border-gray-100 pb-2">
                                    <span class="text-gray-600">Índice de Velocidade:</span>
                                    <span class="font-medium text-gray-900">{{ $article->official_specs['pneu_traseiro']['indice_velocidade'] ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between border-b border-gray-100 pb-2">
                                    <span class="text-gray-600">Pressão Recomendada:</span>
                                    <span class="font-medium text-gray-900">{{ $article->official_specs['pneu_traseiro']['pressao_recomendada'] ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Capacidade de Carga:</span>
                                    <span class="font-medium text-gray-900">{{ $article->official_specs['pneu_traseiro']['capacidade_carga'] ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 bg-[#0E368A]/5 p-4 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#0E368A]" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <p class="ml-3 text-sm text-gray-700">
                            Os índices de carga e velocidade indicados são os mínimos recomendados pelo fabricante. É
                            possível utilizar pneus com índices superiores, mas nunca inferiores aos especificados.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Melhores Pneus Dianteiros -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Melhores Pneus
                    Dianteiros para {{ $article->vehicle_info['make'] ?? '' }} {{ $article->vehicle_info['model'] ?? '' }}</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @if(isset($article->front_tires) && is_array($article->front_tires))
                        @foreach($article->front_tires as $tire)
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                            <div class="bg-[#0E368A] text-white px-4 py-3">
                                <h3 class="font-medium">{{ $tire['categoria'] ?? 'Recomendado' }}</h3>
                            </div>
                            <div class="p-5">
                                <div class="flex items-center justify-center mb-4">
                                    <span class="inline-block bg-[#E06600] text-white text-xs font-medium px-2.5 py-1 rounded">
                                        {{ $tire['categoria'] === 'Melhor Custo-Benefício' ? 'MAIS VENDIDO' : 'PREMIUM' }}
                                    </span>
                                </div>
                                <h4 class="text-xl font-semibold text-center mb-3">{{ $tire['nome_pneu'] ?? 'N/A' }}</h4>
                                <div class="flex justify-between text-sm mb-4">
                                    <span class="text-gray-600">Medida:</span>
                                    <span class="font-medium">{{ $tire['medida'] ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between text-sm mb-4">
                                    <span class="text-gray-600">Índice de Carga:</span>
                                    <span class="font-medium">{{ $tire['indice_carga'] ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between text-sm mb-4">
                                    <span class="text-gray-600">Índice de Velocidade:</span>
                                    <span class="font-medium">{{ $tire['indice_velocidade'] ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Tipo:</span>
                                    <span class="font-medium">{{ $tire['tipo'] ?? 'N/A' }}</span>
                                </div>

                                <div class="mt-5 grid grid-cols-2 gap-2 text-sm">
                                    <div class="bg-green-50 p-1.5 rounded text-center">
                                        <span class="block text-xs text-gray-500">Preço Médio</span>
                                        <span class="font-medium text-green-700">{{ $tire['preco_medio'] ?? 'N/A' }}</span>
                                    </div>
                                    <div class="bg-blue-50 p-1.5 rounded text-center">
                                        <span class="block text-xs text-gray-500">Durabilidade</span>
                                        <span class="font-medium text-blue-700">{{ $tire['durabilidade'] ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </section>

            <!-- Banner de Anúncio 2 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-2]
            </div>

            <!-- Melhores Pneus Traseiros -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Melhores Pneus
                    Traseiros para {{ $article->vehicle_info['make'] ?? '' }} {{ $article->vehicle_info['model'] ?? '' }}</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @if(isset($article->rear_tires) && is_array($article->rear_tires))
                        @foreach($article->rear_tires as $tire)
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                            <div class="bg-[#0E368A] text-white px-4 py-3">
                                <h3 class="font-medium">{{ $tire['categoria'] ?? 'Recomendado' }}</h3>
                            </div>
                            <div class="p-5">
                                <div class="flex items-center justify-center mb-4">
                                    <span class="inline-block bg-{{ $tire['categoria'] === 'Melhor Custo-Benefício' ? '[#E06600]' : 'gray-200' }} text-{{ $tire['categoria'] === 'Melhor Custo-Benefício' ? 'white' : 'gray-800' }} text-xs font-medium px-2.5 py-1 rounded">
                                        {{ $tire['categoria'] === 'Melhor Custo-Benefício' ? 'MAIS VENDIDO' : 'PREMIUM' }}
                                    </span>
                                </div>
                                <h4 class="text-xl font-semibold text-center mb-3">{{ $tire['nome_pneu'] ?? 'N/A' }}</h4>
                                <div class="flex justify-between text-sm mb-4">
                                    <span class="text-gray-600">Medida:</span>
                                    <span class="font-medium">{{ $tire['medida'] ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between text-sm mb-4">
                                    <span class="text-gray-600">Índice de Carga:</span>
                                    <span class="font-medium">{{ $tire['indice_carga'] ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between text-sm mb-4">
                                    <span class="text-gray-600">Índice de Velocidade:</span>
                                    <span class="font-medium">{{ $tire['indice_velocidade'] ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Tipo:</span>
                                    <span class="font-medium">{{ $tire['tipo'] ?? 'N/A' }}</span>
                                </div>

                                <div class="mt-5 grid grid-cols-2 gap-2 text-sm">
                                    <div class="bg-green-50 p-1.5 rounded text-center">
                                        <span class="block text-xs text-gray-500">Preço Médio</span>
                                        <span class="font-medium text-green-700">{{ $tire['preco_medio'] ?? 'N/A' }}</span>
                                    </div>
                                    <div class="bg-blue-50 p-1.5 rounded text-center">
                                        <span class="block text-xs text-gray-500">Durabilidade</span>
                                        <span class="font-medium text-blue-700">{{ $tire['durabilidade'] ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </section>

            <!-- Comparativo por Tipo de Uso -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Comparativo por
                    Tipo de Uso</h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse bg-white rounded-lg overflow-hidden shadow-sm">
                        <thead>
                            <tr class="bg-[#0E368A] text-white">
                                <th class="py-3 px-4 text-left text-sm font-medium">Tipo de Uso</th>
                                <th class="py-3 px-4 text-left text-sm font-medium">Melhor Pneu Dianteiro</th>
                                <th class="py-3 px-4 text-left text-sm font-medium">Melhor Pneu Traseiro</th>
                                <th class="py-3 px-4 text-left text-sm font-medium">Características</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($article->usage_comparison) && is_array($article->usage_comparison))
                                @foreach($article->usage_comparison as $index => $usage)
                                <tr class="border-b border-gray-200 {{ $index % 2 == 0 ? '' : 'bg-gray-50' }}">
                                    <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ $usage['tipo_uso'] ?? 'N/A' }}</td>
                                    <td class="py-3 px-4 text-sm text-gray-700">{{ $usage['melhor_dianteiro'] ?? 'N/A' }}</td>
                                    <td class="py-3 px-4 text-sm text-gray-700">{{ $usage['melhor_traseiro'] ?? 'N/A' }}</td>
                                    <td class="py-3 px-4 text-sm text-gray-700">{{ $usage['caracteristicas'] ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Banner de Anúncio 3 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-3]
            </div>

            <!-- Guia de Desgaste e Substituição -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Guia de Desgaste e
                    Substituição</h2>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Indicadores de Desgaste -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <div class="bg-[#0E368A]/10 w-8 h-8 rounded-full flex items-center justify-center mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#0E368A]" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                Indicadores de Desgaste
                            </h3>

                            <ul class="space-y-3 text-gray-700 ml-2">
                                @if(isset($article->wear_guide['indicadores_desgaste']) && is_array($article->wear_guide['indicadores_desgaste']))
                                    @foreach($article->wear_guide['indicadores_desgaste'] as $indicator)
                                    <li class="flex items-baseline">
                                        <span class="text-[#0E368A] mr-2">•</span>
                                        <span><span class="font-medium">{{ $indicator['indicador'] ?? '' }}:</span> {{ $indicator['descricao'] ?? '' }}</span>
                                    </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>

                        <!-- Quando Substituir -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <div class="bg-[#0E368A]/10 w-8 h-8 rounded-full flex items-center justify-center mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#0E368A]" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                Quando Substituir
                            </h3>

                            <ul class="space-y-3 text-gray-700 ml-2">
                                @if(isset($article->wear_guide['quando_substituir']) && is_array($article->wear_guide['quando_substituir']))
                                    @foreach($article->wear_guide['quando_substituir'] as $situation)
                                    <li class="flex items-baseline">
                                        <span class="text-[#0E368A] mr-2">•</span>
                                        <span><span class="font-medium">{{ $situation['situacao'] ?? '' }}:</span> {{ $situation['descricao'] ?? '' }}</span>
                                    </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Dicas de Manutenção -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Dicas de Manutenção
                    para Prolongar a Vida dos Pneus</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if(isset($article->maintenance_tips) && is_array($article->maintenance_tips))
                        @foreach($article->maintenance_tips as $tipCategory)
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#0E368A]" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">{{ $tipCategory['categoria'] ?? 'Dicas' }}</h3>
                            </div>

                            <ul class="space-y-2 text-gray-700 ml-2">
                                @if(isset($tipCategory['dicas']) && is_array($tipCategory['dicas']))
                                    @foreach($tipCategory['dicas'] as $tip)
                                    <li class="flex items-baseline">
                                        <span class="text-[#0E368A] mr-2">•</span>
                                        <span>{{ $tip }}</span>
                                    </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                        @endforeach
                    @endif
                </div>
            </section>

            <!-- Perguntas Frequentes -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Perguntas
                    Frequentes</h2>

                <div class="space-y-4">
                    @if(isset($article->faq) && is_array($article->faq))
                        @foreach($article->faq as $question)
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                            <div class="p-5">
                                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $question['pergunta'] ?? '' }}</h3>
                                <p class="text-gray-700">{{ $question['resposta'] ?? '' }}</p>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </section>

            <!-- Conclusão -->
            <section class="mb-12 bg-gray-50 rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-4">Considerações Finais</h2>
                <p class="text-gray-800 mb-4">{{ $article->final_considerations ?? '' }}</p>
            </section>

            <!-- Artigos Relacionados -->
            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Conteúdo Relacionado
                </h2>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="/info/calibragem/{{ $article->vehicle_info['make_slug'] ?? 'veiculo' }}-{{ $article->vehicle_info['model_slug'] ?? 'modelo' }}/" class="group">
                        <div class="h-32 rounded-lg bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/10 border border-gray-200 flex items-center justify-center mb-2 hover:shadow-md transition-all">
                            <div class="text-center px-4">
                                <div class="mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-[#0E368A]"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="font-medium text-[#151C25]">Calibragem</span>
                            </div>
                        </div>
                        <h3 class="font-medium text-[#0E368A] group-hover:text-[#0A2868] group-hover:underline transition-colors">
                            Calibragem de Pneus
                        </h3>
                    </a>

                    <a href="/info/oleo/{{ $article->vehicle_info['make_slug'] ?? 'veiculo' }}-{{ $article->vehicle_info['model_slug'] ?? 'modelo' }}/" class="group">
                        <div class="h-32 rounded-lg bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/10 border border-gray-200 flex items-center justify-center mb-2 hover:shadow-md transition-all">
                            <div class="text-center px-4">
                                <div class="mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-[#0E368A]"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                    </svg>
                                </div>
                                <span class="font-medium text-[#151C25]">Óleo</span>
                            </div>
                        </div>
                        <h3 class="font-medium text-[#0E368A] group-hover:text-[#0A2868] group-hover:underline transition-colors">
                            Óleo Recomendado
                        </h3>
                    </a>

                    <a href="/info/consumo/{{ $article->vehicle_info['make_slug'] ?? 'veiculo' }}-{{ $article->vehicle_info['model_slug'] ?? 'modelo' }}/" class="group">
                        <div class="h-32 rounded-lg bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/10 border border-gray-200 flex items-center justify-center mb-2 hover:shadow-md transition-all">
                            <div class="text-center px-4">
                                <div class="mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-[#0E368A]"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span class="font-medium text-[#151C25]">Consumo</span>
                            </div>
                        </div>
                        <h3 class="font-medium text-[#0E368A] group-hover:text-[#0A2868] group-hover:underline transition-colors">
                            Consumo Real
                        </h3>
                    </a>

                    <a href="/info/manutencao/{{ $article->vehicle_info['make_slug'] ?? 'veiculo' }}-{{ $article->vehicle_info['model_slug'] ?? 'modelo' }}/" class="group">
                        <div class="h-32 rounded-lg bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/10 border border-gray-200 flex items-center justify-center mb-2 hover:shadow-md transition-all">
                            <div class="text-center px-4">
                                <div class="mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-[#0E368A]"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <span class="font-medium text-[#151C25]">Manutenção</span>
                            </div>
                        </div>
                        <h3 class="font-medium text-[#0E368A] group-hover:text-[#0A2868] group-hover:underline transition-colors">
                            Guia de Manutenção
                        </h3>
                    </a>
                </div>
            </section>

            <!-- Créditos e Atualização -->
            <div class="text-xs text-gray-500 flex justify-between items-center pt-4 border-t border-gray-200">
                <span>Atualizado em: {{ $article->formated_updated_at }}</span>
                <div class="flex space-x-4">
                    <a href="/sobre/metodologia/" class="hover:text-[#0E368A] hover:underline">Nossa metodologia</a>
                    <a href="/contato/correcoes/" class="hover:text-[#0E368A] hover:underline">Reportar erro</a>
                </div>
            </div>
        </article>
    </div>

    <!-- Créditos Equipe Editorial -->
    <section class="max-w-4xl mx-auto mb-8 pt-4">
        <div class="bg-[#0E368A]/5 rounded-lg p-6 border border-gray-200 shadow-sm">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-[#0E368A] rounded-full flex items-center justify-center mr-3 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-[#151C25]">Equipe Editorial Mercado Veículos</h3>
            </div>

            <div class="text-gray-700 space-y-2 pl-3 ml-10 border-l-2 border-[#0E368A]/40">
                <p class="text-sm leading-relaxed">Recomendações de pneus desenvolvidas com base em especificações oficiais dos fabricantes e testes de campo especializados.</p>
                <p class="text-sm leading-relaxed">Nosso processo editorial rigoroso garante informações precisas e atualizadas, com revisão por especialistas em pneus para cada artigo publicado.</p>
            </div>

            <div class="flex items-center justify-end mt-5 pt-4 border-t border-gray-200">
                <a href="/sobre/equipe-editorial/"
                    class="text-[#0E368A] text-sm hover:text-[#0A2868] hover:underline flex items-center">
                    Conheça nossa metodologia
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Newsletter Simplificada -->
    <section class="max-w-4xl mx-auto mb-12 md:mb-16">
        <div class="bg-[#151C25] text-white rounded-lg shadow-md p-6">
            <div class="md:flex md:items-center">
                <div class="md:w-2/3 md:pr-8 mb-6 md:mb-0">
                    <h2 class="text-2xl font-semibold mb-3">Receba Dicas de Pneus</h2>
                    <p class="text-gray-200 leading-relaxed">Cadastre-se para receber guias exclusivos, alertas de recall e dicas personalizadas para os pneus do seu {{ $article->vehicle_info['make'] ?? 'veículo' }}.</p>
                </div>
                <div class="md:w-1/3">
                    <form class="flex flex-col space-y-3">
                        <input type="email"
                            class="w-full px-4 py-3 rounded-lg text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#E06600] border-0"
                            placeholder="Seu email">
                        <button type="submit"
                            class="bg-[#E06600] hover:bg-[#B35200] text-white font-medium px-6 py-3 rounded-lg transition-colors">
                            Cadastrar
                        </button>
                        <p class="text-xs text-gray-400">Seus dados estão seguros conosco. Consulte nossa <a href="#"
                                class="underline hover:text-white">política de privacidade</a>.</p>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@push('styles')
<style>
    /* Estilos específicos para template de recomendação de pneus */
    .tire-icon {
        @apply text-[#0E368A] mr-2;
    }

    /* Hover effects para cards de pneus */
    .tire-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    }

    /* Badges dinâmicos */
    .tire-badge-premium {
        @apply bg-gray-200 text-gray-800;
    }

    .tire-badge-bestseller {
        @apply bg-[#E06600] text-white;
    }

    /* Tabela responsiva específica */
    .usage-comparison-table {
        font-size: 14px;
    }

    .usage-comparison-table th,
    .usage-comparison-table td {
        padding: 12px 8px;
        vertical-align: top;
    }

    /* Cards de guia de desgaste */
    .wear-guide-card {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-left: 4px solid #0E368A;
    }

    /* Dicas de manutenção com ícones */
    .maintenance-tip-card:hover {
        background-color: #f8fafc;
        border-color: #0E368A;
        transition: all 0.2s ease;
    }

    /* FAQ com sombras suaves */
    .faq-card {
        transition: box-shadow 0.2s ease;
    }

    .faq-card:hover {
        box-shadow: 0 4px 12px rgba(14, 54, 138, 0.1);
    }

    /* Estilos para impressão */
    @media print {
        .no-print {
            display: none !important;
        }

        main {
            padding: 0 !important;
        }

        /* Força quebras de página apropriadas */
        section {
            page-break-inside: avoid;
        }

        .tire-card,
        .wear-guide-card,
        .maintenance-tip-card {
            page-break-inside: avoid;
            margin-bottom: 1rem;
        }

        /* Cores em impressão */
        .bg-\[#0E368A\] {
            background-color: #0E368A !important;
            -webkit-print-color-adjust: exact;
        }

        .text-\[#0E368A\] {
            color: #0E368A !important;
            -webkit-print-color-adjust: exact;
        }

        /* Remover sombras e efeitos em impressão */
        .shadow-sm,
        .shadow-md {
            box-shadow: none !important;
        }
    }

    /* Responsividade específica para pneus */
    @media (max-width: 768px) {
        .tire-specs-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .usage-comparison-table {
            font-size: 12px;
        }

        .usage-comparison-table th,
        .usage-comparison-table td {
            padding: 8px 4px;
        }

        /* Stack cards de pneus em mobile */
        .grid-cols-1.md\:grid-cols-3 {
            grid-template-columns: 1fr;
        }

        .grid-cols-1.md\:grid-cols-2 {
            grid-template-columns: 1fr;
        }
    }

    /* Animações para entrada dos cards */
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .tire-card,
    .wear-guide-card,
    .maintenance-tip-card {
        animation: slideInUp 0.6s ease-out;
    }

    /* Delay progressivo para cards */
    .tire-card:nth-child(1) { animation-delay: 0.1s; }
    .tire-card:nth-child(2) { animation-delay: 0.2s; }
    .tire-card:nth-child(3) { animation-delay: 0.3s; }

    /* Scroll suave para tabelas */
    .overflow-x-auto {
        scrollbar-width: thin;
        scrollbar-color: #0E368A #f1f5f9;
    }

    .overflow-x-auto::-webkit-scrollbar {
        height: 8px;
    }

    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #0E368A;
        border-radius: 4px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #0A2868;
    }

    /* Estados de loading para imagens */
    img[loading="lazy"] {
        transition: opacity 0.3s ease;
    }

    img[loading="lazy"]:not([src]) {
        opacity: 0;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar formatação de tabelas responsivas
        const tables = document.querySelectorAll('table');
        tables.forEach(table => {
            table.classList.add('w-full');
            table.querySelectorAll('th, td').forEach(cell => {
                cell.classList.add('px-4', 'py-2');
            });
        });

        // Adicionar indicador de scroll para tabelas em mobile
        const tableContainers = document.querySelectorAll('.overflow-x-auto');
        tableContainers.forEach(container => {
            const table = container.querySelector('table');
            if (table && table.scrollWidth > container.clientWidth) {
                container.classList.add('relative');
                const indicator = document.createElement('div');
                indicator.className = 'absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-white to-transparent pointer-events-none';
                container.appendChild(indicator);

                container.addEventListener('scroll', function() {
                    if (container.scrollLeft + container.clientWidth >= container.scrollWidth - 5) {
                        indicator.style.display = 'none';
                    } else {
                        indicator.style.display = 'block';
                    }
                });
            }
        });

        // Highlight de linha da tabela no hover (apenas desktop)
        if (window.innerWidth > 768) {
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f8fafc';
                    this.style.transform = 'scale(1.01)';
                    this.style.transition = 'all 0.2s ease';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                    this.style.transform = '';
                });
            });
        }

        // Smooth scroll para âncoras internas
        const anchorLinks = document.querySelectorAll('a[href^="#"]');
        anchorLinks.forEach(link => {
            link.addEventListener('click', function(e) {
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

        // Lazy loading para imagens
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));

        // Tooltip para especificações de pneus
        const specItems = document.querySelectorAll('[data-tooltip]');
        specItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                const tooltip = document.createElement('div');
                tooltip.className = 'absolute z-50 px-2 py-1 text-xs text-white bg-gray-900 rounded shadow-lg';
                tooltip.textContent = this.dataset.tooltip;
                tooltip.style.top = '-30px';
                tooltip.style.left = '50%';
                tooltip.style.transform = 'translateX(-50%)';
                this.style.position = 'relative';
                this.appendChild(tooltip);
            });
            
            item.addEventListener('mouseleave', function() {
                const tooltip = this.querySelector('.absolute');
                if (tooltip) tooltip.remove();
            });
        });

        // Copy to clipboard para medidas de pneus
        const tireSpecs = document.querySelectorAll('[data-copy]');
        tireSpecs.forEach(spec => {
            spec.style.cursor = 'pointer';
            spec.title = 'Clique para copiar';
            
            spec.addEventListener('click', function() {
                navigator.clipboard.writeText(this.dataset.copy).then(() => {
                    const originalText = this.textContent;
                    this.textContent = 'Copiado!';
                    this.style.color = '#10b981';
                    
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.style.color = '';
                    }, 1500);
                });
            });
        });

        // Filtro para cards de pneus (se houver múltiplas categorias)
        const filterButtons = document.querySelectorAll('[data-filter]');
        const tireCards = document.querySelectorAll('.tire-card');
        
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.dataset.filter;
                
                // Atualizar botões ativos
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Filtrar cards
                tireCards.forEach(card => {
                    if (filter === 'all' || card.dataset.category === filter) {
                        card.style.display = 'block';
                        card.style.animation = 'slideInUp 0.6s ease-out';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Contador de economia estimada (se aplicável)
        const savingsCounters = document.querySelectorAll('[data-savings]');
        savingsCounters.forEach(counter => {
            const target = parseInt(counter.dataset.savings);
            let current = 0;
            const increment = target / 30;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                counter.textContent = `R$ ${Math.round(current)}`;
            }, 50);
        });
    });
</script>
@endpush