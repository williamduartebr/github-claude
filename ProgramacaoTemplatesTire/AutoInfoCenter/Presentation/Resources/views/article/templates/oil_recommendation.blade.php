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
            $article->vehicle_info['vehicle_type'] ?? 'recomendacao'));
            @endphp

            <div class="relative rounded-lg overflow-hidden mb-8 mt-2 hidden md:block">
                <img src="https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/{{  $imageDefault  }}.png"
                    alt="{{ $article->title }}" class="w-full h-64 object-cover"
                    onerror="this.src='https:\/\/mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/default-car.png'">
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

            <!-- Recomendações Principais - Versão Cards -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Óleos Recomendados
                    para {{ $article->vehicle_info['make'] ?? 'Veículo' }} {{ $article->vehicle_info['model'] ?? '' }}</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Óleo Recomendado pela Fabricante -->
                    @if(!empty($article->manufacturer_recommendation) && is_array($article->manufacturer_recommendation))
                    <div
                        class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                        <div class="bg-[#0E368A] text-white px-4 py-3">
                            <h3 class="font-medium">Recomendação Oficial</h3>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center justify-center mb-4">
                                <span
                                    class="inline-block bg-[#E06600] text-white text-xs font-medium px-2.5 py-1 rounded">PREFERENCIAL</span>
                            </div>
                            <h4 class="text-xl font-semibold text-center mb-3">{{
                                $article->manufacturer_recommendation['nome_oleo'] ?? 'N/A' }}</h4>
                            @if(!empty($article->manufacturer_recommendation['classificacao']))
                            <div class="flex justify-between text-sm mb-4">
                                <span class="text-gray-600">Classificação:</span>
                                <span class="font-medium">{{ $article->manufacturer_recommendation['classificacao'] }}</span>
                            </div>
                            @endif
                            @if(!empty($article->manufacturer_recommendation['viscosidade']))
                            <div class="flex justify-between text-sm mb-4">
                                <span class="text-gray-600">Viscosidade:</span>
                                <span class="font-medium">{{ $article->manufacturer_recommendation['viscosidade'] }}</span>
                            </div>
                            @endif
                            @if(!empty($article->manufacturer_recommendation['especificacao']))
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Especificação:</span>
                                <span class="font-medium">{{ $article->manufacturer_recommendation['especificacao'] }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Alternativa Premium -->
                    @if(!empty($article->premium_alternative) && is_array($article->premium_alternative))
                    <div
                        class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                        <div class="bg-gray-700 text-white px-4 py-3">
                            <h3 class="font-medium">Alternativa Premium</h3>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center justify-center mb-4">
                                <span
                                    class="inline-block bg-gray-200 text-gray-800 text-xs font-medium px-2.5 py-1 rounded">COMPATÍVEL</span>
                            </div>
                            <h4 class="text-xl font-semibold text-center mb-3">{{
                                $article->premium_alternative['nome_oleo'] ?? 'N/A' }}</h4>
                            @if(!empty($article->premium_alternative['classificacao']))
                            <div class="flex justify-between text-sm mb-4">
                                <span class="text-gray-600">Classificação:</span>
                                <span class="font-medium">{{ $article->premium_alternative['classificacao'] }}</span>
                            </div>
                            @endif
                            @if(!empty($article->premium_alternative['viscosidade']))
                            <div class="flex justify-between text-sm mb-4">
                                <span class="text-gray-600">Viscosidade:</span>
                                <span class="font-medium">{{ $article->premium_alternative['viscosidade'] }}</span>
                            </div>
                            @endif
                            @if(!empty($article->premium_alternative['especificacao']))
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Especificação:</span>
                                <span class="font-medium">{{ $article->premium_alternative['especificacao'] }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Opção Econômica -->
                    @if(!empty($article->economic_option) && is_array($article->economic_option))
                    <div
                        class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                        <div class="bg-gray-700 text-white px-4 py-3">
                            <h3 class="font-medium">Opção Econômica</h3>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center justify-center mb-4">
                                <span
                                    class="inline-block bg-gray-200 text-gray-800 text-xs font-medium px-2.5 py-1 rounded">ALTERNATIVA</span>
                            </div>
                            <h4 class="text-xl font-semibold text-center mb-3">{{ $article->economic_option['nome_oleo'] ?? 'N/A' }}</h4>
                            @if(!empty($article->economic_option['classificacao']))
                            <div class="flex justify-between text-sm mb-4">
                                <span class="text-gray-600">Classificação:</span>
                                <span class="font-medium">{{ $article->economic_option['classificacao'] }}</span>
                            </div>
                            @endif
                            @if(!empty($article->economic_option['viscosidade']))
                            <div class="flex justify-between text-sm mb-4">
                                <span class="text-gray-600">Viscosidade:</span>
                                <span class="font-medium">{{ $article->economic_option['viscosidade'] }}</span>
                            </div>
                            @endif
                            @if(!empty($article->economic_option['especificacao']))
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Especificação:</span>
                                <span class="font-medium">{{ $article->economic_option['especificacao'] }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                @if(!empty($article->specifications['especificacao_minima']) && !empty($article->vehicle_info))
                <div class="mt-6 bg-[#0E368A]/5 p-4 rounded-lg">
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
                            Para o motor {{ $article->vehicle_info['engine'] ?? 'do veículo' }} do {{ $article->vehicle_info['make'] ?? '' }}
                            {{ $article->vehicle_info['model'] ?? '' }}, é fundamental utilizar óleos que atendam às
                            especificações {{ $article->specifications['especificacao_minima'] }} para garantir a
                            proteção ideal do motor.
                        </p>
                    </div>
                </div>
                @endif
            </section>

            <!-- Especificações Técnicas -->
            @if(!empty($article->specifications) && is_array($article->specifications))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Especificações
                    Técnicas</h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-[#0E368A] text-white">
                                <th class="py-3 px-4 text-left font-medium text-sm">Especificação</th>
                                <th class="py-3 px-4 text-left font-medium text-sm">{{ $article->vehicle_info['make'] ?? 'Veículo' }}
                                    {{ $article->vehicle_info['model'] ?? '' }} {{ $article->vehicle_info['engine'] ?? '' }}</th>
                                <th class="py-3 px-4 text-left font-medium text-sm">Observações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($article->specifications['capacidade_oleo']))
                            <tr class="border-b border-gray-200 bg-white">
                                <td class="py-3 px-4 text-sm">Capacidade de Óleo</td>
                                <td class="py-3 px-4 text-sm font-medium">{{ $article->specifications['capacidade_oleo'] }}</td>
                                <td class="py-3 px-4 text-sm">{{ $article->specifications['capacidade_sem_filtro'] ?? 'Incluindo filtro' }}</td>
                            </tr>
                            @endif
                            @if(!empty($article->specifications['viscosidade']))
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <td class="py-3 px-4 text-sm">Viscosidade Recomendada</td>
                                <td class="py-3 px-4 text-sm font-medium">{{ $article->specifications['viscosidade'] }}</td>
                                <td class="py-3 px-4 text-sm">Para todas as temperaturas operacionais</td>
                            </tr>
                            @endif
                            @if(!empty($article->specifications['especificacao_minima']))
                            <tr class="border-b border-gray-200 bg-white">
                                <td class="py-3 px-4 text-sm">Especificação Mínima</td>
                                <td class="py-3 px-4 text-sm font-medium">{{ $article->specifications['especificacao_minima'] }}</td>
                                <td class="py-3 px-4 text-sm">Requisito do fabricante</td>
                            </tr>
                            @endif
                            @if(!empty($article->specifications['intervalo_troca']))
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <td class="py-3 px-4 text-sm">Intervalo de Troca</td>
                                <td class="py-3 px-4 text-sm font-medium">{{ $article->specifications['intervalo_troca'] }}</td>
                                <td class="py-3 px-4 text-sm">O que ocorrer primeiro</td>
                            </tr>
                            @endif
                            @if(!empty($article->specifications['filtro_oleo']))
                            <tr class="border-b border-gray-200 bg-white">
                                <td class="py-3 px-4 text-sm">Filtro de Óleo</td>
                                <td class="py-3 px-4 text-sm font-medium">{{ $article->specifications['filtro_oleo'] }}</td>
                                <td class="py-3 px-4 text-sm">Recomendação do fabricante</td>
                            </tr>
                            @endif
                            @if(!empty($article->specifications['codigo_filtro']))
                            <tr class="bg-gray-50">
                                <td class="py-3 px-4 text-sm">Código Filtro Original</td>
                                <td class="py-3 px-4 text-sm font-medium">{{ $article->specifications['codigo_filtro'] }}</td>
                                <td class="py-3 px-4 text-sm">Filtro original</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </section>
            @endif

            <!-- Banner de Anúncio 2 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-2]
            </div>

            <!-- Benefícios do Óleo Correto -->
            @if(!empty($article->benefits) && is_array($article->benefits) && count($article->benefits) > 0)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Benefícios do Óleo
                    Correto</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($article->benefits as $benefit)
                    @if(!empty($benefit['titulo']))
                    <div class="flex items-start">
                        <div
                            class="flex-shrink-0 h-10 w-10 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#0E368A]" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-1">{{ $benefit['titulo'] }}</h3>
                            @if(!empty($benefit['descricao']))
                            <p class="text-gray-700">{{ $benefit['descricao'] }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Condições Especiais de Uso -->
            @if(!empty($article->usage_conditions) && is_array($article->usage_conditions))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Condições Especiais
                    de Uso</h2>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                    <p class="text-gray-800 mb-6">Dependendo das condições de uso do seu {{
                        $article->vehicle_info['make'] ?? 'veículo' }} {{ $article->vehicle_info['model'] ?? '' }}, ajustes nas
                        recomendações podem ser necessários:</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        @if(!empty($article->usage_conditions['severo']) && is_array($article->usage_conditions['severo']))
                        <div class="bg-gray-50 rounded-lg p-5 border border-gray-100">
                            <h3 class="text-lg font-medium text-gray-900 mb-3 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Uso Severo
                            </h3>
                            @if(!empty($article->usage_conditions['severo']['condicoes']) && is_array($article->usage_conditions['severo']['condicoes']))
                            <ul class="space-y-2 text-gray-700">
                                @foreach($article->usage_conditions['severo']['condicoes'] as $condition)
                                @if(!empty($condition))
                                <li class="flex items-baseline">
                                    <span class="text-[#0E368A] mr-2">•</span>
                                    <span>{{ $condition }}</span>
                                </li>
                                @endif
                                @endforeach
                            </ul>
                            @endif
                            @if(!empty($article->usage_conditions['severo']['recomendacao']))
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <p class="text-sm font-medium text-gray-900">Recomendação:</p>
                                <p class="text-sm text-gray-700">{{ $article->usage_conditions['severo']['recomendacao'] }}</p>
                            </div>
                            @endif
                        </div>
                        @endif

                        @if(!empty($article->usage_conditions['normal']) && is_array($article->usage_conditions['normal']))
                        <div class="bg-gray-50 rounded-lg p-5 border border-gray-100">
                            <h3 class="text-lg font-medium text-gray-900 mb-3 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Uso Normal
                            </h3>
                            @if(!empty($article->usage_conditions['normal']['condicoes']) && is_array($article->usage_conditions['normal']['condicoes']))
                            <ul class="space-y-2 text-gray-700">
                                @foreach($article->usage_conditions['normal']['condicoes'] as $condition)
                                @if(!empty($condition))
                                <li class="flex items-baseline">
                                    <span class="text-[#0E368A] mr-2">•</span>
                                    <span>{{ $condition }}</span>
                                </li>
                                @endif
                                @endforeach
                            </ul>
                            @endif
                            @if(!empty($article->usage_conditions['normal']['recomendacao']))
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <p class="text-sm font-medium text-gray-900">Recomendação:</p>
                                <p class="text-sm text-gray-700">{{ $article->usage_conditions['normal']['recomendacao'] }}</p>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>

                    @if(!empty($article->usage_conditions['dica_adicional']))
                    <div class="bg-yellow-50 p-4 rounded-md border-l-4 border-yellow-400">
                        <p class="text-sm text-yellow-800">
                            <span class="font-bold">Dica importante:</span> {{ $article->usage_conditions['dica_adicional'] }}
                        </p>
                    </div>
                    @endif
                </div>
            </section>
            @endif

            <!-- Banner de Anúncio 3 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-3]
            </div>

            <!-- Procedimento de Troca -->
            @if(!empty($article->change_procedure) && is_array($article->change_procedure) && count($article->change_procedure) > 0)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Procedimento de
                    Troca</h2>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                    <ol class="space-y-6">
                        @foreach($article->change_procedure as $index => $step)
                        @if(!empty($step['passo']))
                        <li class="flex">
                            <div
                                class="flex-shrink-0 h-8 w-8 rounded-full bg-[#0E368A] text-white flex items-center justify-center font-medium">
                                {{ $index + 1 }}</div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900 mb-1">{{ $step['passo'] }}</h3>
                                @if(!empty($step['descricao']))
                                <p class="text-gray-700">{{ $step['descricao'] }}</p>
                                @endif
                            </div>
                        </li>
                        @endif
                        @endforeach
                    </ol>

                    @if(!empty($article->environmental_note))
                    <div class="mt-6 bg-[#0E368A]/5 p-4 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#0E368A]"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <p class="ml-3 text-sm text-gray-700">
                                {{ $article->environmental_note }}
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
            </section>
            @endif

            <!-- Perguntas Frequentes -->
            @if(!empty($article->faq) && is_array($article->faq) && count($article->faq) > 0)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Perguntas
                    Frequentes</h2>

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
    /* Estilos específicos para template de recomendação de óleo */
    .benefits-icon {
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

        /* Força quebras de página em locais apropriados */
        section {
            page-break-inside: avoid;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Função para mostrar/ocultar perguntas frequentes
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