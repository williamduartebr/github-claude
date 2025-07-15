@extends('auto-info-center::layouts.app')

@push('head')
<link rel="amphtml" href="{{ route('info.article.show.amp', $article->slug) }}">
<link rel="canonical" href="{{ route('info.article.show', $article->slug) }}">

@if(!empty($article->structured_data))
<script type="application/ld+json">
    {!! json_encode($article->structured_data) !!}
</script>
@endif
@endpush

@section('content')

@include('auto-info-center::article.partials.breadcrumb')

<main id="main-content" class="container mx-auto px-4">
    <div itemscope itemtype="https://schema.org/TechArticle">
        @if(!empty($article->vehicle_info))
        <meta itemprop="vehicleEngine"
            content="{{ $article->vehicle_info['make'] ?? '' }} {{ $article->vehicle_info['model'] ?? '' }} {{ $article->vehicle_info['engine'] ?? '' }}" />
        @endif
        <meta itemprop="category" content="{{ $article->category['name'] ?? 'Manutenção Automotiva' }}" />

        <article class="max-w-4xl mx-auto pt-6 pb-12">

            @php
            $imageDefault = \Str::slug( sprintf("%s-%s", $article->category['slug'] ?? 'oleo',
            $article->vehicle_info['vehicle_type'] ?? 'tabela'));
            @endphp

            <div class="relative rounded-lg overflow-hidden mb-8 mt-2 hidden md:block">
                <img src="https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/oil_table.png"
                    alt="{{ $article->title }}" class="w-full h-64 object-cover"
                    onerror="this.src='https:\/\/mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/oil_table.png'">
                <div class="absolute inset-0 bg-gradient-to-t from-black/100 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-6 text-white">
                    <h1 class="text-3xl md:text-4xl font-semibold leading-tight">{{ $article->title }}</h1>
                    @if(!empty($article->formated_updated_at))
                    <p class="text-sm mt-2 opacity-90">Atualizado em: {{ $article->formated_updated_at }}</p>
                    @endif
                </div>
                </div>

                <div class="mb-8 mt-2 block md:hidden">
                <h1 class="text-3xl font-semibold leading-tight text-gray-900">{{ $article->title }}</h1>
                @if(!empty($article->formated_updated_at))
                <p class="text-sm mt-2 text-gray-600">Atualizado em: {{ $article->formated_updated_at }}</p>
                @endif
            </div>

            <!-- Introdução -->
            @if(!empty($article->introduction))
            <div class="mb-10">
                <p class="text-lg text-gray-800 leading-relaxed">
                    {{ $article->introduction }}
                </p>
            </div>
            @endif

            <!-- Banner de Anúncio 1 -->
            <div class="my-8">
                [ADSENSE-ARTICLE-1]
            </div>

            <!-- Tabela Principal de Óleos -->
            @if(!empty($article->oil_table) && is_array($article->oil_table) && count($article->oil_table) > 0)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Tabela de Óleo por Geração e Motor
                </h2>

                <div class="overflow-x-auto bg-white rounded-lg border border-gray-200 shadow-sm">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-[#0E368A] text-white">
                                <th class="py-3 px-4 text-left text-sm font-medium">Geração</th>
                                <th class="py-3 px-4 text-left text-sm font-medium">Período</th>
                                <th class="py-3 px-4 text-left text-sm font-medium">Motor</th>
                                <th class="py-3 px-4 text-left text-sm font-medium">Óleo Recomendado</th>
                                <th class="py-3 px-4 text-left text-sm font-medium">Capacidade</th>
                                <th class="py-3 px-4 text-left text-sm font-medium">Intervalo de Troca</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($article->oil_table as $index => $oilEntry)
                            <tr class="border-b border-gray-200 {{ $index % 2 == 0 ? 'bg-gray-50' : 'bg-white' }}">
                                <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ $oilEntry['geracao'] ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm text-gray-700">{{ $oilEntry['periodo'] ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm text-gray-700">{{ $oilEntry['motor'] ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm text-gray-700 font-medium">{{ $oilEntry['oleo_recomendado'] ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm text-gray-700">{{ $oilEntry['capacidade'] ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm text-gray-700">{{ $oilEntry['intervalo_troca'] ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 bg-yellow-50 p-4 rounded-md border-l-4 border-yellow-400">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <p class="ml-3 text-sm text-yellow-800">
                            <span class="font-bold">Importante:</span> As capacidades listadas incluem a troca do
                            filtro de óleo. Para trocas sem substituição do filtro, reduza o volume em aproximadamente
                            0,2-0,3 litros para carros ou 0,1 litros para motos.
                        </p>
                    </div>
                </div>
            </section>
            @endif

            <!-- Especificações Detalhadas por Tipo de Óleo -->
            @if(!empty($article->oil_specifications) && is_array($article->oil_specifications) && count($article->oil_specifications) > 0)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Especificações Detalhadas por Tipo de Óleo
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($article->oil_specifications as $spec)
                    @if(!empty($spec['tipo_oleo']))
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                        <div class="bg-[#0E368A] text-white px-4 py-3">
                            <h3 class="font-medium">{{ $spec['tipo_oleo'] }}</h3>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center mb-4">
                                <div class="bg-[#0E368A]/10 w-12 h-12 rounded-full flex items-center justify-center mr-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">{{ $spec['aplicacao'] ?? 'Aplicação' }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">Especificações técnicas</p>
                                </div>
                            </div>

                            @if(!empty($spec['caracteristicas']) && is_array($spec['caracteristicas']) && count($spec['caracteristicas']) > 0)
                            <ul class="space-y-2 text-gray-700 mb-4">
                                @foreach($spec['caracteristicas'] as $caracteristica)
                                @if(!empty($caracteristica))
                                <li class="flex items-start">
                                    <div class="flex-shrink-0 h-5 w-5 rounded-full bg-[#0E368A]/20 flex items-center justify-center mt-0.5 mr-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-[#0E368A]"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span>{{ $caracteristica }}</span>
                                </li>
                                @endif
                                @endforeach
                            </ul>
                            @endif

                            @if(!empty($spec['marcas_recomendadas']))
                            <div class="mt-4 bg-gray-50 p-3 rounded-md text-sm">
                                <span class="font-medium">Marcas recomendadas:</span> 
                                @if(is_array($spec['marcas_recomendadas']))
                                    {{ implode(', ', $spec['marcas_recomendadas']) }}
                                @else
                                    {{ $spec['marcas_recomendadas'] }}
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Banner de Anúncio 2 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-2]
            </div>

            <!-- Filtros de Óleo Recomendados -->
            @if(!empty($article->oil_filters) && is_array($article->oil_filters) && count($article->oil_filters) > 0)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Filtros de Óleo Recomendados
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-[#0E368A] text-white">
                                    <th class="py-3 px-4 text-left text-sm font-medium">Geração</th>
                                    <th class="py-3 px-4 text-left text-sm font-medium">Motor</th>
                                    <th class="py-3 px-4 text-left text-sm font-medium">Código Original</th>
                                    <th class="py-3 px-4 text-left text-sm font-medium">Equivalentes Aftermarket</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($article->oil_filters as $index => $filter)
                                <tr class="border-b border-gray-200 {{ $index % 2 == 0 ? 'bg-gray-50' : 'bg-white' }}">
                                    <td class="py-3 px-4 text-sm text-gray-700">{{ $filter['geracao'] ?? 'N/A' }}</td>
                                    <td class="py-3 px-4 text-sm text-gray-700">{{ $filter['motor'] ?? 'N/A' }}</td>
                                    <td class="py-3 px-4 text-sm text-gray-700 font-medium">{{ $filter['codigo_original'] ?? 'N/A' }}</td>
                                    <td class="py-3 px-4 text-sm text-gray-700">
                                        @if(!empty($filter['equivalentes_aftermarket']))
                                            @if(is_array($filter['equivalentes_aftermarket']))
                                                {{ implode(', ', $filter['equivalentes_aftermarket']) }}
                                            @else
                                                {{ $filter['equivalentes_aftermarket'] }}
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
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
                            Recomenda-se a substituição do filtro de óleo a cada troca de óleo para garantir a máxima
                            proteção do motor. Filtros originais oferecem a melhor compatibilidade, mas as
                            alternativas aftermarket listadas apresentam qualidade equivalente.
                        </p>
                    </div>
                </div>
            </section>
            @endif

            <!-- Intervalos de Troca por Condição de Uso -->
            @if(!empty($article->maintenance_intervals) && is_array($article->maintenance_intervals) && count($article->maintenance_intervals) > 0)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Intervalos de Troca por Condição de Uso
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($article->maintenance_intervals as $interval)
                    @if(!empty($interval['tipo_uso']))
                    @php
                        $badgeColors = [
                            'green' => 'bg-green-600',
                            'yellow' => 'bg-yellow-600', 
                            'gray' => 'bg-gray-700',
                            'red' => 'bg-red-600'
                        ];
                        $iconColors = [
                            'green' => 'bg-green-100 text-green-600',
                            'yellow' => 'bg-yellow-100 text-yellow-600',
                            'gray' => 'bg-gray-200 text-gray-700',
                            'red' => 'bg-red-100 text-red-600'
                        ];
                        $bulletColors = [
                            'green' => 'text-green-600',
                            'yellow' => 'text-yellow-600',
                            'gray' => 'text-gray-700',
                            'red' => 'text-red-600'
                        ];
                        
                        $color = $interval['cor_badge'] ?? 'gray';
                        $headerClass = $badgeColors[$color] ?? $badgeColors['gray'];
                        $iconClass = $iconColors[$color] ?? $iconColors['gray'];
                        $bulletClass = $bulletColors[$color] ?? $bulletColors['gray'];
                    @endphp
                    
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden h-full">
                        <div class="{{ $headerClass }} text-white px-4 py-3">
                            <h3 class="font-medium">{{ $interval['tipo_uso'] }}</h3>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center mb-4">
                                <div class="{{ $iconClass }} w-10 h-10 rounded-full flex items-center justify-center mr-3">
                                    @if(($interval['icone'] ?? 'check') === 'check')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    @elseif(($interval['icone'] ?? 'check') === 'warning')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    @endif
                                </div>
                                <h4 class="font-semibold text-gray-900">{{ $interval['intervalo'] ?? 'N/A' }}</h4>
                            </div>

                            @if(!empty($interval['condicoes']) && is_array($interval['condicoes']) && count($interval['condicoes']) > 0)
                            <h5 class="text-sm font-medium text-gray-900 mb-2">Condições de uso:</h5>
                            <ul class="space-y-1 text-sm text-gray-700 mb-4">
                                @foreach($interval['condicoes'] as $condicao)
                                @if(!empty($condicao))
                                <li class="flex items-baseline">
                                    <span class="{{ $bulletClass }} mr-2">•</span>
                                    <span>{{ $condicao }}</span>
                                </li>
                                @endif
                                @endforeach
                            </ul>
                            @endif

                            @if(!empty($interval['observacoes']))
                            <p class="text-xs text-gray-600 mt-2">{{ $interval['observacoes'] }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Banner de Anúncio 3 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-3]
            </div>

            <!-- Perguntas Frequentes -->
            @if(!empty($article->faq) && is_array($article->faq) && count($article->faq) > 0)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Perguntas Frequentes
                </h2>

                <div class="space-y-4">
                    @foreach($article->faq as $question)
                    @if(!empty($question['pergunta']) && !empty($question['resposta']))
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                        <div class="p-5">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $question['pergunta'] }}</h3>
                            <p class="text-gray-700">{{ $question['resposta'] }}</p>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Conclusão -->
            @if(!empty($article->final_considerations))
            <section class="mb-12 bg-gray-50 rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-4">Considerações Finais</h2>
                <p class="text-gray-800 mb-4">{{ $article->final_considerations }}</p>
            </section>
            @endif

            <!-- Artigos Relacionados -->
            {{-- @include('auto-info-center::article.partials.related_content') --}}

            <!-- Créditos e Atualização -->
            @include('auto-info-center::article.partials.update_content')
        </article>
    </div>

    <!-- Créditos Equipe Editorial -->
    @include('auto-info-center::article.partials.editorial_team')

    <!-- Newsletter Simplificada -->
    @include('auto-info-center::article.partials.newsletter')
</main>
@endsection

@push('styles')
<style>
    /* Estilos específicos para template de tabela de óleo */
    .oil-table-icon {
        @apply text-[#0E368A] mr-2;
    }

    /* Estilos para impressão */
    @media print {
        .no-print {
            display: none !important;
        }

        main {
            padding: 0 !important;
        }

        section {
            page-break-inside: avoid;
        }
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
    });
</script>
@endpush