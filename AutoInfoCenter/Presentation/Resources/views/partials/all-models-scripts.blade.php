@push('head_scripts')

<!-- Structured Data Dinâmico -->
<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "CollectionPage",
        "headline": "{{ $category->name }} - Todos os Modelos",
        "description": "Veja todos os modelos disponíveis para {{ strtolower($category->name) }}. Guias completos e especializados para cada veículo.",
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ route('info.category.all-models', $category->slug) }}"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Mercado Veículos",
            "logo": {
                "@type": "ImageObject",
                "url": "https://mercadoveiculos.s3.amazonaws.com/statics/logos/logo-mercadoveiculos-write.svg",
                "width": 260,
                "height": 48
            }
        },
        "image": "https://mercadoveiculos.com/images/{{ $category->slug }}-todos-modelos-capa.jpg",
        "datePublished": "{{ $category->created_at->utc()->toAtomString() }}",
        "dateModified": "{{ $category->updated_at->utc()->toAtomString() }}",
        "author": {
            "@type": "Organization",
            "name": "Mercado Veículos"
        },
        "about": {
            "@type": "Thing",
            "name": "{{ $category->name }}"
        },
        "hasPart": [
            @if($articles->count() > 0)
                @foreach($articles->take(20) as $index => $article)
                    {
                        "@type": "Article",
                        "headline": "{{ $article->title }}",
                        "description": "{{ !empty($article->content['introducao']) ? Str::limit(strip_tags($article->content['introducao']), 160) : 'Guia sobre ' . strtolower($category->name) . ' para veículos.' }}",
                        "image": "https://mercadoveiculos.com/images/{{ $article->vehicle_info['make_slug'] ?? 'default' }}-{{ $article->vehicle_info['model_slug'] ?? 'car' }}.jpg",
                        "url": "{{ url('/info/' . $article->slug) }}",
                        "datePublished": "{{ $article->created_at->utc()->toAtomString() }}"
                    }@if(!$loop->last),@endif
                @endforeach
            @endif
        ],
        "breadcrumb": {
            "@type": "BreadcrumbList",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": "Início",
                    "item": "{{ url('/') }}"
                },
                {
                    "@type": "ListItem",
                    "position": 2,
                    "name": "Informações",
                    "item": "{{ route('info.category.index') }}"
                },
                {
                    "@type": "ListItem",
                    "position": 3,
                    "name": "{{ $category->name }}",
                    "item": "{{ route('info.category.show', $category->slug) }}"
                },
                {
                    "@type": "ListItem",
                    "position": 4,
                    "name": "Todos os Modelos",
                    "item": "{{ route('info.category.all-models', $category->slug) }}"
                }
            ]
        },
        "isPartOf": {
            "@type": "WebSite",
            "name": "Mercado Veículos",
            "url": "{{ url('/') }}"
        },
        "mainEntity": {
            "@type": "ItemList",
            "numberOfItems": {{ $pagination['total'] ?? 0 }},
            "itemListElement": [
                @if($articles->count() > 0)
                    @foreach($articles->take(20) as $index => $article)
                        {
                            "@type": "ListItem",
                            "position": {{ $loop->iteration }},
                            "url": "{{ url('/info/' . $article->slug) }}"
                        }@if(!$loop->last),@endif
                    @endforeach
                @endif
            ]
        }
    }
</script>

<!-- FAQ Schema.org Específico para All Models -->
<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "Como encontrar informações específicas sobre {{ strtolower($category->name) }} para meu veículo?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Use os filtros disponíveis nesta página para selecionar a marca, modelo e ano do seu veículo. Temos guias específicos para cada combinação de veículo e {{ strtolower($category->name) }}, com informações técnicas precisas e atualizadas."
                }
            },
            {
                "@type": "Question",
                "name": "Quantos modelos de veículos são cobertos para {{ strtolower($category->name) }}?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Atualmente temos {{ $pagination['total'] ?? 0 }} guias específicos sobre {{ strtolower($category->name) }} cobrindo as principais marcas vendidas no Brasil, incluindo Renault, Toyota, Volkswagen, Fiat, Chevrolet, Honda, Hyundai, Kia e outras."
                }
            },
            {
                "@type": "Question",
                "name": "Os guias são atualizados regularmente?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Sim, nossos guias sobre {{ strtolower($category->name) }} são constantemente atualizados com as mais recentes informações dos fabricantes e melhores práticas da indústria automotiva. Novos modelos são adicionados conforme lançados no mercado brasileiro."
                }
            },
            {
                "@type": "Question",
                "name": "Posso confiar nas informações técnicas apresentadas?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Todas as informações são baseadas em especificações oficiais dos fabricantes e manuais técnicos. Nossos especialistas verificam e validam cada guia para garantir precisão e confiabilidade das informações sobre {{ strtolower($category->name) }}."
                }
            }
        ]
    }
</script>
@endpush

@push('scripts')
<!-- Scripts para All Models -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const marcaSelect = document.getElementById('marca');
        const modeloSelect = document.getElementById('modelo');
        const anoSelect = document.getElementById('ano');
        const aplicarFiltrosBtn = document.getElementById('aplicar-filtros');

        // Dados dos modelos por marca (expandido com mais marcas)
        const modelos = {
            'renault': ['Oroch', 'Duster', 'Sandero', 'Logan', 'Kwid', 'Captur', 'Fluence', 'Symbol', 'Clio'],
            'toyota': ['Corolla Cross', 'Corolla', 'Hilux', 'SW4', 'Yaris', 'Etios', 'Prius', 'RAV4', 'Camry'],
            'volkswagen': ['Nivus', 'T-Cross', 'Taos', 'Polo', 'Golf', 'Jetta', 'Passat', 'Tiguan', 'Gol', 'Motor AP'],
            'fiat': ['Toro', 'Strada', 'Pulse', 'Argo', 'Cronos', 'Mobi', 'Uno', 'Palio', 'Siena'],
            'chevrolet': ['S10', 'Onix', 'Tracker', 'Cruze', 'Spin', 'Prisma', 'Cobalt', 'Equinox', 'Camaro'],
            'honda': ['City', 'Civic', 'HR-V', 'WR-V', 'Fit', 'Accord', 'CR-V', 'Pilot', 'Fan 150'],
            'hyundai': ['Creta', 'HB20', 'Tucson', 'i30', 'Santa Fe', 'Azera', 'Elantra', 'ix35', 'Veloster'],
            'kia': ['Sportage', 'Seltos', 'Cerato', 'Sorento', 'Soul', 'Picanto', 'Rio', 'Optima', 'Stinger'],
            'ram': ['1500', '2500', 'Classic', 'Tradesmen', 'Laramie', 'Big Horn', 'Rebel', 'Limited'],
            'suzuki': ['Jimny', 'Vitara', 'S-Cross', 'Swift', 'Baleno', 'Alto', 'Celerio', 'Ertiga'],
            'yamaha': ['XTZ 150 Crosser', 'Fazer 250', 'MT-03', 'R3', 'Tenere 250', 'Factor 125', 'Neo 125']
        };

        // Preenche filtros com valores da URL se existirem
        const urlParams = new URLSearchParams(window.location.search);
        const marcaAtual = urlParams.get('marca');
        const modeloAtual = urlParams.get('modelo');
        const anoAtual = urlParams.get('ano');

        if (marcaAtual) {
            marcaSelect.value = marcaAtual;
            carregarModelos(marcaAtual, modeloAtual);
        }
        if (anoAtual) {
            anoSelect.value = anoAtual;
        }

        // Comportamento do filtro de marca
        marcaSelect.addEventListener('change', function() {
            const marca = this.value;
            carregarModelos(marca);
        });

        function carregarModelos(marca, modeloSelecionado = null) {
            // Limpar opções atuais
            modeloSelect.innerHTML = '<option value="">Todos os modelos</option>';

            // Se uma marca foi selecionada, adicionar modelos correspondentes
            if (marca && modelos[marca]) {
                modelos[marca].forEach(function(modelo) {
                    const option = document.createElement('option');
                    option.value = modelo.toLowerCase().replace(/\s+/g, '-');
                    option.textContent = modelo;
                    if (modeloSelecionado && option.value === modeloSelecionado) {
                        option.selected = true;
                    }
                    modeloSelect.appendChild(option);
                });
            }
        }

        // Aplicar filtros
        aplicarFiltrosBtn.addEventListener('click', function() {
            const params = new URLSearchParams();
            
            if (marcaSelect.value) params.set('marca', marcaSelect.value);
            if (modeloSelect.value) params.set('modelo', modeloSelect.value);
            if (anoSelect.value) params.set('ano', anoSelect.value);
            
            // Remove page para voltar à primeira página
            params.delete('page');
            
            const queryString = params.toString();
            const newUrl = window.location.pathname + (queryString ? '?' + queryString : '');
            
            window.location.href = newUrl;
        });

        // Detectar Enter nos selects
        [marcaSelect, modeloSelect, anoSelect].forEach(select => {
            select.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    aplicarFiltrosBtn.click();
                }
            });
        });

        // Loading state para botão
        aplicarFiltrosBtn.addEventListener('click', function() {
            this.innerHTML = 'Aplicando...';
            this.disabled = true;
        });

        // Scroll suave para resultados após carregamento
        if (urlParams.has('page') || urlParams.has('marca') || urlParams.has('modelo') || urlParams.has('ano')) {
            setTimeout(() => {
                const mainContent = document.getElementById('main-content');
                if (mainContent) {
                    mainContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 100);
        }
    });
</script>

@endpush