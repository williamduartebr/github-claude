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
        @if(!empty($article->vehicle_full_name))
        <meta itemprop="vehicleEngine" content="{{ $article->vehicle_full_name }}" />
        @endif
        <meta itemprop="category" content="Manutenção Automotiva" />

        <article class="max-w-4xl mx-auto pt-6 pb-12">
            @php
            $imageDefault = \Str::slug(sprintf("%s-%s", $article->category['slug'] ?? 'calibragem-pneus', 
                $article->vehicle_info['vehicle_type'] ?? 'car'));
            @endphp

            <div class="relative rounded-lg overflow-hidden mb-8 mt-2 hidden md:block">
                <img src="https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/tire_pressure_car.png"
                    alt="{{ $article->title }}" class="w-full h-64 object-cover"
                    onerror="this.src='https:\/\/mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/tire_pressure_car.png'">
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
            @if(!empty($article->article_content['introduction']['content']))
            <div class="mb-10">
                <p class="text-lg text-gray-800 leading-relaxed">
                    {{ $article->article_content['introduction']['content'] }}
                </p>
            </div>
            @endif

            <!-- Banner de Anúncio 1 -->
            <div class="my-8">
                [ADSENSE-ARTICLE-1]
            </div>

            <!-- Especificações dos Pneus -->
            @if(!empty($article->vehicle_info))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    🚗 Especificações dos Pneus do {{ $article->vehicle_info['full_name'] ?? 'Veículo' }}
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">📏 Informações dos Pneus</h3>
                            <div class="space-y-3">
                                @if(!empty($article->vehicle_info['tire_size']))
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Medida dos Pneus:</span>
                                    <span class="font-medium">{{ $article->vehicle_info['tire_size'] }}</span>
                                </div>
                                @endif
                                @if(!empty($article->vehicle_info['pressure_empty_display']))
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Pressão Vazio:</span>
                                    <span class="font-medium">{{ $article->vehicle_info['pressure_empty_display'] }}</span>
                                </div>
                                @endif
                                @if(!empty($article->vehicle_info['pressure_loaded_display']))
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Pressão Carregado:</span>
                                    <span class="font-medium">{{ $article->vehicle_info['pressure_loaded_display'] }}</span>
                                </div>
                                @endif
                                @if(!empty($article->vehicle_info['category']))
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Categoria:</span>
                                    <span class="font-medium">{{ $article->vehicle_info['category'] }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">🔧 Recomendações Gerais</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Frequência de Verificação:</span>
                                    <span class="font-medium">{{ $article->template_data['calibration_frequency'] ?? '15 dias' }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Rodízio Recomendado:</span>
                                    <span class="font-medium">{{ $article->template_data['alignment_frequency'] ?? '10.000 km' }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Profundidade Mínima:</span>
                                    <span class="font-medium">{{ $article->template_data['min_tread_depth'] ?? '1.6mm' }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Economia Potencial:</span>
                                    <span class="font-medium text-green-600">{{ $article->template_data['estimated_fuel_savings'] ?? 'até 10%' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-700">
                            <span class="font-medium">💡 Dica:</span> Sempre calibre os pneus com o veículo frio, 
                            preferencialmente pela manhã antes de rodar mais de 2 km.
                        </p>
                    </div>
                </div>
            </section>
            @endif

            <!-- Tabela de Pressões -->
            @if(!empty($article->article_content['pressure_table']) && !empty($article->article_content['pressure_table']['rows']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    {{ $article->article_content['pressure_table']['title'] ?? 'Pressões Recomendadas' }}
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse">
                            <thead>
                                <tr class="bg-[#0E368A] text-white">
                                    @if(!empty($article->article_content['pressure_table']['headers']))
                                        @foreach($article->article_content['pressure_table']['headers'] as $header)
                                        <th class="py-3 px-4 text-left font-medium text-sm">{{ $header }}</th>
                                        @endforeach
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($article->article_content['pressure_table']['rows'] as $index => $row)
                                <tr class="border-b border-gray-200 {{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                                    @if(is_array($row))
                                        @foreach($row as $cell)
                                        <td class="py-3 px-4 text-sm {{ $loop->first ? 'font-medium' : '' }}">{{ $cell }}</td>
                                        @endforeach
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(!empty($article->article_content['pressure_table']['note']))
                    <div class="p-4 bg-gray-50 text-sm text-gray-700">
                        <span class="font-medium">📌 Importante:</span> {{ $article->article_content['pressure_table']['note'] }}
                    </div>
                    @endif
                </div>
            </section>
            @endif

            <!-- Como Calibrar -->
            @if(!empty($article->article_content['how_to_calibrate']) && !empty($article->article_content['how_to_calibrate']['steps']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    {{ $article->article_content['how_to_calibrate']['title'] ?? 'Como Calibrar os Pneus' }}
                </h2>

                <div class="relative bg-white rounded-lg border border-gray-200 p-6">
                    <div class="absolute left-6 inset-y-0 w-0.5 bg-[#0E368A]/20"></div>

                    <div class="space-y-8">
                        @foreach($article->article_content['how_to_calibrate']['steps'] as $index => $step)
                        @if(!empty($step))
                        <div class="relative pl-8">
                            <div class="absolute left-0 -translate-x-1/2 w-6 h-6 rounded-full bg-[#0E368A] flex items-center justify-center text-white font-medium">
                                {{ $index + 1 }}
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Passo {{ $index + 1 }}</h3>
                                <p class="text-gray-700">{{ $step }}</p>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </section>
            @endif

            <!-- Banner de Anúncio 2 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-2]
            </div>

            <!-- Conteúdo Intermediário -->
            @if(!empty($article->article_content['middle_content']['content']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    {{ $article->article_content['middle_content']['title'] ?? 'Informações Importantes' }}
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <p class="text-gray-800 leading-relaxed">{{ $article->article_content['middle_content']['content'] }}</p>
                </div>
            </section>
            @endif

            <!-- Checklist de Manutenção -->
            @if(!empty($article->article_content['maintenance_checklist']) && !empty($article->article_content['maintenance_checklist']['items']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    {{ $article->article_content['maintenance_checklist']['title'] ?? 'Checklist de Manutenção' }}
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <ul class="space-y-3">
                        @foreach($article->article_content['maintenance_checklist']['items'] as $item)
                        @if(!empty($item))
                        <li class="flex items-start">
                            <div class="h-5 w-5 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-3 flex-shrink-0 mt-0.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <p class="text-gray-700">{{ $item }}</p>
                        </li>
                        @endif
                        @endforeach
                    </ul>
                </div>
            </section>
            @endif

            <!-- Dicas e Avisos -->
            @if((!empty($article->article_content['tips']) && count($article->article_content['tips']) > 0) || 
                (!empty($article->article_content['warnings']) && count($article->article_content['warnings']) > 0))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    💡 Dicas e Avisos Importantes
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Dicas -->
                    @if(!empty($article->article_content['tips']) && count($article->article_content['tips']) > 0)
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">✅ Dicas Úteis</h3>
                        <div class="space-y-3">
                            @foreach($article->article_content['tips'] as $tip)
                            @if(!empty($tip['content']))
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <p class="text-sm text-blue-800">{{ $tip['content'] }}</p>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Avisos -->
                    @if(!empty($article->article_content['warnings']) && count($article->article_content['warnings']) > 0)
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">⚠️ Avisos Importantes</h3>
                        <div class="space-y-3">
                            @foreach($article->article_content['warnings'] as $warning)
                            @if(!empty($warning['content']))
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <p class="text-sm text-yellow-800">{{ $warning['content'] }}</p>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </section>
            @endif

            <!-- Benefícios da Calibragem Correta -->
            @if(!empty($article->template_data['safety_benefits']) || !empty($article->template_data['economic_benefits']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    🎯 Benefícios da Calibragem Correta
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Benefícios de Segurança -->
                    @if(!empty($article->template_data['safety_benefits']) && is_array($article->template_data['safety_benefits']))
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                                <span class="text-2xl">🛡️</span>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Benefícios de Segurança</h3>
                        </div>

                        <ul class="space-y-2">
                            @foreach($article->template_data['safety_benefits'] as $benefit)
                            @if(!empty($benefit))
                            <li class="flex items-start">
                                <div class="h-5 w-5 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-700">{{ $benefit }}</p>
                            </li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Benefícios Econômicos -->
                    @if(!empty($article->template_data['economic_benefits']) && is_array($article->template_data['economic_benefits']))
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-green-500/5 to-green-500/15 flex items-center justify-center mr-3">
                                <span class="text-2xl">💰</span>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Benefícios Econômicos</h3>
                        </div>

                        <ul class="space-y-2">
                            @foreach($article->template_data['economic_benefits'] as $benefit)
                            @if(!empty($benefit))
                            <li class="flex items-start">
                                <div class="h-5 w-5 rounded-full bg-green-500/10 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-700">{{ $benefit }}</p>
                            </li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </section>
            @endif

            <!-- Banner de Anúncio 3 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-3]
            </div>

            <!-- Perguntas Frequentes -->
            @if(!empty($article->article_content['faq']) && !empty($article->article_content['faq']['items']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    ❓ {{ $article->article_content['faq']['title'] ?? 'Perguntas Frequentes' }}
                </h2>

                <div class="space-y-4">
                    @foreach($article->article_content['faq']['items'] as $question)
                    @if(!empty($question['question']) && !empty($question['answer']))
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                        <div class="p-5">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">🤔 {{ $question['question'] }}</h3>
                            <p class="text-gray-700">{{ $question['answer'] }}</p>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Conclusão -->
            @if(!empty($article->article_content['conclusion']['content']))
            <section class="mb-12 bg-gray-50 rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-4">
                    🏁 {{ $article->article_content['conclusion']['title'] ?? 'Considerações Finais' }}
                </h2>
                <p class="text-gray-800 mb-4">{{ $article->article_content['conclusion']['content'] }}</p>
            </section>
            @endif

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
    /* Estilos específicos para template de calibragem de pneus - carros */
    .tire-pressure-icon {
        @apply text-[#0E368A] mr-2;
    }

    /* Destaque para pressões */
    .pressure-highlight {
        background: linear-gradient(135deg, rgba(14, 54, 138, 0.1), rgba(14, 54, 138, 0.05));
        border-left: 4px solid #0E368A;
    }

    /* Animações suaves */
    .pressure-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .pressure-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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

        // Animação suave para cards de pressão
        const pressureCards = document.querySelectorAll('.pressure-card');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        });

        pressureCards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });

        // Destaque especial para dados de pressão
        const pressureValues = document.querySelectorAll('[data-pressure-value]');
        pressureValues.forEach(value => {
            value.addEventListener('mouseenter', function() {
                this.style.backgroundColor = 'rgba(14, 54, 138, 0.1)';
                this.style.borderRadius = '4px';
                this.style.padding = '2px 4px';
                this.style.transition = 'all 0.2s ease';
            });
            
            value.addEventListener('mouseleave', function() {
                this.style.backgroundColor = 'transparent';
                this.style.padding = '0';
            });
        });
    });
</script>
@endpush