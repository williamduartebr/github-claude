@extends('auto-info-center::layouts.app')

@push('head')
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-5108844086542870"
    crossorigin="anonymous"></script>

<title>{{ $seo['title'] ?? "Calibragem de Pneus {$vehicle_info['full_name']} - Guia Completo | Mercado Veículos" }}</title>
<meta name="description" content="{{ $seo['meta_description'] ?? "Guia completo e oficial sobre calibragem de pneus para {$vehicle_info['full_name']}. Pressões ideais, recomendações do fabricante e dicas de segurança." }}">

<!-- Canonical e Idiomas -->
<link rel="canonical" href="{{ $urls['canonical'] }}" />
<link rel="alternate" hreflang="pt-BR" href="{{ $urls['canonical'] }}" />

<!-- Open Graph / Facebook -->
<meta property="og:type" content="article" />
<meta property="og:title" content="{{ $seo['og_title'] ?? $seo['title'] }}" />
<meta property="og:description" content="{{ $seo['og_description'] ?? $seo['meta_description'] }}" />
<meta property="og:image" content="{{ $seo['og_image'] ?? $vehicle_info['image_url'] }}" />
<meta property="og:url" content="{{ $urls['canonical'] }}" />
<meta property="og:site_name" content="Mercado Veículos" />

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo['og_title'] ?? $seo['title'] }}">
<meta name="twitter:description" content="{{ $seo['og_description'] ?? $seo['meta_description'] }}">
<meta name="twitter:image" content="{{ $seo['og_image'] ?? $vehicle_info['image_url'] }}">

<!-- Preload de recursos críticos -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" href="https://mercadoveiculos.s3.amazonaws.com/statics/logos/logo-mercadoveiculos-write.svg" as="image">

@if(!empty($structured_data))
<script type="application/ld+json">
    {!! json_encode($structured_data) !!}
</script>
@endif
@endpush

@section('content')

@include('auto-info-center::article.partials.breadcrumb')

<!-- Conteúdo Principal -->
<main id="main-content" class="container mx-auto px-4">
    <div itemscope itemtype="https://schema.org/TechArticle">
        <meta itemprop="vehicleEngine" content="{{ $vehicle_info['full_name'] }}" />
        <meta itemprop="category" content="Manutenção de Motocicletas" />

        <!-- Tag Article -->
        <article class="max-w-4xl mx-auto pt-6 pb-12">
            <!-- Cabeçalho Minimalista -->
            <div class="mb-8">
                <div class="border-b-2 border-[#0E368A] pb-4">
                    <h1 class="text-3xl md:text-4xl font-semibold leading-tight text-[#151C25]">
                        Calibragem do Pneu da {{ $vehicle_info['full_name'] }}
                    </h1>
                    <p class="text-sm mt-2 text-gray-500">Atualizado em: {{ date('d \d\e F \d\e Y') }}</p>
                </div>
            </div>

            <!-- Introdução -->
            <div class="mb-10">
                <p class="text-lg text-gray-800 leading-relaxed">
                    {{ $article_content['introduction']['content'] ?? "A calibragem correta dos pneus da {$vehicle_info['full_name']} é fundamental para garantir segurança, desempenho e durabilidade durante o uso. Este guia apresenta as pressões ideais recomendadas pela fabricante e adaptações para diferentes condições de uso no Brasil." }}
                </p>
            </div>

            <!-- Banner de Anúncio 1 -->
            <div class="my-8">
                [ADSENSE-ARTICLE-1]
            </div>

            <!-- Informações do Veículo e Especificações dos Pneus -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Especificações dos Pneus Originais</h2>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Pneu Dianteiro</h3>

                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Medida:</span>
                                    <span class="font-medium">{{ $vehicle_info['tire_size_front'] ?? 'Verificar manual' }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Tipo:</span>
                                    <span class="font-medium">{{ $vehicle_info['tire_type'] ?? 'Misto' }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Pressão Normal:</span>
                                    <span class="font-medium">{{ $vehicle_info['pressure_front_display'] }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Pressão Máxima:</span>
                                    <span class="font-medium">{{ $vehicle_info['pressure_range_front']['max'] ?? $vehicle_info['pressure_front_display'] + 4 }} PSI</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Pneu Traseiro</h3>

                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Medida:</span>
                                    <span class="font-medium">{{ $vehicle_info['tire_size_rear'] ?? 'Verificar manual' }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Tipo:</span>
                                    <span class="font-medium">{{ $vehicle_info['tire_type'] ?? 'Misto' }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Pressão Normal:</span>
                                    <span class="font-medium">{{ $vehicle_info['pressure_rear_display'] }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Pressão Máxima:</span>
                                    <span class="font-medium">{{ $vehicle_info['pressure_range_rear']['max'] ?? $vehicle_info['pressure_rear_display'] + 4 }} PSI</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-700">
                            <span class="font-medium">Nota:</span> A {{ $vehicle_info['full_name'] }} é equipada com pneus específicos que oferecem boa performance. 
                            As medidas dos pneus são específicas para este modelo, contribuindo para seu comportamento característico.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Tabela de Calibragem -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Pressões Recomendadas</h2>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-[#0E368A] text-white">
                                @if(!empty($article_content['pressure_table']['headers']))
                                    @foreach($article_content['pressure_table']['headers'] as $header)
                                    <th class="py-3 px-4 text-center font-medium text-sm">{{ $header }}</th>
                                    @endforeach
                                @else
                                    <th class="py-3 px-4 text-left font-medium text-sm">Pneu</th>
                                    <th class="py-3 px-4 text-center font-medium text-sm">Pressão Normal (PSI)</th>
                                    <th class="py-3 px-4 text-center font-medium text-sm">Pressão Máxima (PSI)</th>
                                    <th class="py-3 px-4 text-center font-medium text-sm">Observações</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($article_content['pressure_table']['rows']))
                                @foreach($article_content['pressure_table']['rows'] as $index => $row)
                                <tr class="border-b border-gray-200 {{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                                    @foreach($row as $cellIndex => $cell)
                                    <td class="py-3 px-4 text-sm {{ $cellIndex == 0 ? 'font-medium' : 'text-center' }}">{{ $cell }}</td>
                                    @endforeach
                                </tr>
                                @endforeach
                            @else
                                <tr class="border-b border-gray-200 bg-white">
                                    <td class="py-3 px-4 text-sm font-medium">Dianteiro</td>
                                    <td class="py-3 px-4 text-sm text-center">{{ $vehicle_info['pressure_front_display'] ?? '29 PSI' }}</td>
                                    <td class="py-3 px-4 text-sm text-center">{{ ($vehicle_info['pressure_range_front']['max'] ?? 33) }} PSI</td>
                                    <td class="py-3 px-4 text-sm text-center">Calibrar a frio</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="py-3 px-4 text-sm font-medium">Traseiro</td>
                                    <td class="py-3 px-4 text-sm text-center">{{ $vehicle_info['pressure_rear_display'] ?? '32 PSI' }}</td>
                                    <td class="py-3 px-4 text-sm text-center">{{ ($vehicle_info['pressure_range_rear']['max'] ?? 36) }} PSI</td>
                                    <td class="py-3 px-4 text-sm text-center">Ajustar com garupa</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                    <div class="p-4 bg-gray-50 text-sm text-gray-700">
                        <span class="font-medium">Fonte:</span> Valores baseados no manual do proprietário da {{ $vehicle_info['full_name'] }} e recomendações técnicas para condições brasileiras.
                    </div>
                </div>
            </section>

            <!-- Recomendações Específicas -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Recomendações por Tipo de Uso</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-4.5-6.875" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Uso Urbano</h3>
                        </div>

                        <p class="text-gray-700 mb-4">
                            Para o uso predominantemente urbano, mantenha a pressão próxima da recomendação padrão. Isso oferece melhor estabilidade no asfalto, reduz o desgaste e melhora a economia de combustível.
                        </p>

                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-700">
                                <span class="font-medium">Dica técnica:</span> Em trânsito intenso, com paradas frequentes, a temperatura dos pneus pode aumentar. Verifique a calibragem sempre com os pneus frios para maior precisão.
                            </p>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Estradas e Rodovias</h3>
                        </div>

                        <p class="text-gray-700 mb-4">
                            Para viagens longas em rodovias, mantenha a pressão no limite superior da faixa recomendada. Isso melhora a estabilidade em alta velocidade, reduz a resistência ao rolamento e aumenta a autonomia.
                        </p>

                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-700">
                                <span class="font-medium">Dica técnica:</span> Em viagens longas, verifique a pressão dos pneus no mínimo a cada 500 km, pois variações de temperatura e altitude podem afetar a calibragem.
                            </p>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Com Passageiro (Garupa)</h3>
                        </div>

                        <p class="text-gray-700 mb-4">
                            Para pilotagem com passageiro, aumente a pressão do pneu traseiro para compensar o peso adicional. Isso evita o desgaste excessivo e mantém a estabilidade em curvas e frenagens.
                        </p>

                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-700">
                                <span class="font-medium">Dica técnica:</span> Adicione aproximadamente {{ $template_data['passenger_adjustment_amount'] ?? '2-3 PSI' }} no pneu traseiro quando andar com passageiro. O ajuste é especialmente importante para manter a estabilidade.
                            </p>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Com Carga ou Bagagem</h3>
                        </div>

                        <p class="text-gray-700 mb-4">
                            Para viagens com bagagem, aumente a pressão do pneu traseiro para compensar o peso adicional. Isso evita o desgaste excessivo e mantém a estabilidade em curvas e frenagens.
                        </p>

                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-700">
                                <span class="font-medium">Dica técnica:</span> Adicione aproximadamente 2-3 PSI à calibragem recomendada para cada 10 kg adicionais de carga (alforges, top case, bagagem). O ajuste é especialmente importante no pneu traseiro.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Banner de Anúncio 2 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-2]
            </div>

            <!-- Método de Calibragem Correta -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Procedimento de Calibragem Correto</h2>

                <div class="relative bg-white rounded-lg border border-gray-200 p-6">
                    <div class="absolute left-6 inset-y-0 w-0.5 bg-[#0E368A]/20"></div>

                    <div class="space-y-8">
                        @if(!empty($article_content['how_to_calibrate']['steps']))
                            @foreach($article_content['how_to_calibrate']['steps'] as $index => $step)
                            <div class="relative pl-8">
                                <div class="absolute left-0 -translate-x-1/2 w-6 h-6 rounded-full bg-[#0E368A] flex items-center justify-center text-white font-medium">
                                    {{ $index + 1 }}
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Passo {{ $index + 1 }}</h3>
                                <p class="text-gray-700">{{ $step }}</p>
                            </div>
                            @endforeach
                        @else
                            <div class="relative pl-8">
                                <div class="absolute left-0 -translate-x-1/2 w-6 h-6 rounded-full bg-[#0E368A] flex items-center justify-center text-white font-medium">1</div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Verificação com Pneus Frios</h3>
                                <p class="text-gray-700">Realize a calibragem sempre com os pneus frios, preferencialmente após pelo menos 3 horas de descanso da moto. A temperatura afeta significativamente a pressão interna.</p>
                            </div>

                            <div class="relative pl-8">
                                <div class="absolute left-0 -translate-x-1/2 w-6 h-6 rounded-full bg-[#0E368A] flex items-center justify-center text-white font-medium">2</div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Equipamento Adequado</h3>
                                <p class="text-gray-700">Utilize um calibrador de qualidade, preferencialmente digital ou de relógio, verificado periodicamente. Calibradores de posto frequentemente apresentam imprecisões.</p>
                            </div>

                            <div class="relative pl-8">
                                <div class="absolute left-0 -translate-x-1/2 w-6 h-6 rounded-full bg-[#0E368A] flex items-center justify-center text-white font-medium">3</div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Medição da Pressão Atual</h3>
                                <p class="text-gray-700">Antes de adicionar ar, meça a pressão atual para avaliar o nível de perda desde a última calibragem. Pressione firmemente contra a válvula.</p>
                            </div>

                            <div class="relative pl-8">
                                <div class="absolute left-0 -translate-x-1/2 w-6 h-6 rounded-full bg-[#0E368A] flex items-center justify-center text-white font-medium">4</div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Ajuste da Pressão</h3>
                                <p class="text-gray-700">Adicione ar se necessário, verificando a pressão regularmente até atingir o valor desejado. Se estiver sobrecalibrado, pressione o pino central da válvula brevemente.</p>
                            </div>

                            <div class="relative pl-8">
                                <div class="absolute left-0 -translate-x-1/2 w-6 h-6 rounded-full bg-[#0E368A] flex items-center justify-center text-white font-medium">5</div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Recolocação da Tampa</h3>
                                <p class="text-gray-700">Após verificar que a pressão está correta, recoloque a tampa da válvula. Este componente protege contra entrada de poeira e mantém a vedação.</p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-700">
                            <span class="font-medium">Frequência recomendada:</span> Verifique a pressão dos pneus da sua {{ $vehicle_info['full_name'] }} semanalmente e sempre antes de viagens longas. A pequena perda de pressão natural pode ser significativa para o comportamento da moto.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Banner de Anúncio 3 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-3]
            </div>

            <!-- Perguntas Frequentes -->
            @if(!empty($article_content['faq']['items']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">Perguntas Frequentes</h2>

                <div class="space-y-4">
                    @foreach($article_content['faq']['items'] as $faq)
                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                        <button class="flex justify-between items-center w-full px-5 py-4 text-left text-gray-900 font-medium focus:outline-none">
                            <span>{{ $faq['question'] }}</span>
                            <svg class="h-5 w-5 text-[#0E368A]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div class="px-5 py-4 border-t border-gray-200 bg-gray-50">
                            <p class="text-gray-700">{{ $faq['answer'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Conclusão -->
            <section class="mb-12 bg-gray-50 rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-4">Considerações Finais</h2>

                <p class="text-gray-800 mb-4">
                    {{ $article_content['conclusion']['content'] ?? "A calibragem correta dos pneus da {$vehicle_info['full_name']} não é apenas uma questão de manutenção, mas um fator determinante para segurança, desempenho e economia. Os valores recomendados neste guia representam um ponto de partida ideal." }}
                </p>

                <p class="text-gray-800 mb-4">
                    Lembre-se que cada ambiente exige uma abordagem específica: o asfalto pede pressões adequadas para estabilidade e economia, enquanto diferentes condições demandam ajustes específicos. O investimento em um bom calibrador e a verificação regular são práticas que estendem a vida útil dos pneus.
                </p>

                <p class="text-gray-800">
                    Ao combinar a calibragem ideal com a escolha correta do tipo de pneu para seu perfil de uso, você maximizará não apenas o desempenho da sua {{ $vehicle_info['full_name'] }}, mas também sua segurança e conforto em qualquer aventura.
                </p>
            </section>

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