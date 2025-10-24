{{-- resources/views/auto-info-center/partials/category-scripts.blade.php --}}

@push('scripts')
<!-- Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const marcaSelect = document.getElementById('marca');
        const modeloSelect = document.getElementById('modelo');
        const anoSelect = document.getElementById('ano');
        const aplicarFiltrosBtn = document.getElementById('aplicar-filtros');

        // Detecta se é a página "Todos os Modelos"
        const isAllModelsPage = window.location.pathname.includes('/todos-os-modelos');

        // Dados dos modelos por marca
        const modelos = {
            'renault': ['Oroch', 'Duster', 'Sandero', 'Logan', 'Kwid', 'Captur', 'Fluence', 'Symbol'],
            'toyota': ['Corolla Cross', 'Corolla', 'Hilux', 'SW4', 'Yaris', 'Etios', 'Prius', 'RAV4'],
            'volkswagen': ['Nivus', 'T-Cross', 'Taos', 'Polo', 'Golf', 'Jetta', 'Passat', 'Tiguan'],
            'fiat': ['Toro', 'Strada', 'Pulse', 'Argo', 'Cronos', 'Mobi', 'Uno', 'Palio'],
            'chevrolet': ['S10', 'Onix', 'Tracker', 'Cruze', 'Spin', 'Prisma', 'Cobalt', 'Equinox'],
            'honda': ['City', 'Civic', 'HR-V', 'WR-V', 'Fit', 'Accord', 'CR-V', 'Pilot'],
            'hyundai': ['Creta', 'HB20', 'Tucson', 'i30', 'Santa Fe', 'Azera', 'Elantra', 'ix35'],
            'kia': ['Sportage', 'Seltos', 'Cerato', 'Sorento', 'Soul', 'Picanto', 'Rio', 'Optima'],
            'ram': ['1500', '2500', 'Classic', 'Tradesmen', 'Laramie', 'Big Horn', 'Rebel', 'Limited'],
            'suzuki': ['Jimny', 'Vitara', 'S-Cross', 'Swift', 'Baleno', 'Alto', 'Celerio', 'Ertiga']
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
    });
</script>
@endpush

@push('head_scripts')
@php
    $collectionSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'headline' => $category->name . (request()->routeIs('info.category.all-models') ? ' - Todos os Modelos' : ' - Guia Completo'),
        'description' => $category->description . (request()->routeIs('info.category.all-models') ? ' Encontre informações completas para todos os modelos de veículos disponíveis.' : ''),
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => request()->routeIs('info.category.all-models') ? route('info.category.all-models', $category->slug) : route('info.category.show', $category->slug)
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Mercado Veículos',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => 'https://mercadoveiculos.s3.amazonaws.com/statics/logos/logo-mercadoveiculos-write.svg',
                'width' => 260,
                'height' => 48
            ]
        ],
        'image' => request()->routeIs('info.category.all-models') ? 'https://mercadoveiculos.com/images/' . $category->slug . '-todos-modelos-capa.jpg' : 'https://mercadoveiculos.com/images/' . $category->slug . '-capa.jpg',
        'datePublished' => $category->created_at->utc()->toAtomString(),
        'dateModified' => $category->updated_at->utc()->toAtomString(),
        'author' => [
            '@type' => 'Organization',
            'name' => 'Mercado Veículos'
        ],
        'about' => [
            '@type' => 'Thing',
            'name' => $category->name
        ],
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => 'Mercado Veículos',
            'url' => url('/')
        ]
    ];

    // Adicionar hasPart se existirem artigos
    if ($articles->count() > 0) {
        $collectionSchema['hasPart'] = $articles->take(10)->map(function($article) use ($category) {
            return [
                '@type' => 'Article',
                'headline' => $article->title,
                'description' => !empty($article->content['introducao']) ? Str::limit(strip_tags($article->content['introducao']), 160) : 'Guia sobre ' . strtolower($category->name) . ' para veículos.',
                'image' => 'https://mercadoveiculos.com/images/' . ($article->vehicle_info['make_slug'] ?? 'default') . '-' . ($article->vehicle_info['model_slug'] ?? 'car') . '.jpg',
                'url' => url('/info/' . $article->slug),
                'datePublished' => $article->created_at->utc()->toAtomString()
            ];
        })->toArray();
    }

    // Adicionar breadcrumb
    $breadcrumbItems = [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Início',
            'item' => url('/')
        ],
        [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'Informações',
            'item' => route('info.category.index')
        ]
    ];

    if (request()->routeIs('info.category.all-models')) {
        $breadcrumbItems[] = [
            '@type' => 'ListItem',
            'position' => 3,
            'name' => $category->name,
            'item' => route('info.category.show', $category->slug)
        ];
        $breadcrumbItems[] = [
            '@type' => 'ListItem',
            'position' => 4,
            'name' => 'Todos os Modelos',
            'item' => route('info.category.all-models', $category->slug)
        ];
    } else {
        $breadcrumbItems[] = [
            '@type' => 'ListItem',
            'position' => 3,
            'name' => $category->name,
            'item' => route('info.category.show', $category->slug)
        ];
    }

    $collectionSchema['breadcrumb'] = [
        '@type' => 'BreadcrumbList',
        'itemListElement' => $breadcrumbItems
    ];

    // Adicionar mainEntity se existirem artigos
    if ($articles->count() > 0) {
        $collectionSchema['mainEntity'] = [
            '@type' => 'ItemList',
            'numberOfItems' => $pagination['total'] ?? 0,
            'itemListElement' => $articles->take(10)->map(function($article, $index) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'url' => url('/info/' . $article->slug)
                ];
            })->toArray()
        ];
    }

    // Schema FAQ
    $faqQuestions = [
        [
            '@type' => 'Question',
            'name' => 'Por que ' . strtolower($category->name) . ' é importante?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $category->description . ' A manutenção adequada garante segurança, economia e durabilidade do veículo.'
            ]
        ],
        [
            '@type' => 'Question',
            'name' => 'Com que frequência devo verificar ' . strtolower($category->name) . '?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Recomenda-se verificar regularmente conforme especificações do fabricante do veículo. Consulte sempre o manual do proprietário para intervalos específicos de manutenção.'
            ]
        ],
        [
            '@type' => 'Question',
            'name' => 'Onde encontro informações específicas para meu veículo sobre ' . strtolower($category->name) . '?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Você pode encontrar informações específicas no manual do proprietário, site do fabricante ou utilizando nossos filtros por marca, modelo e ano nesta página para encontrar guias específicos para seu veículo.'
            ]
        ]
    ];

    if (request()->routeIs('info.category.all-models')) {
        $faqQuestions[] = [
            '@type' => 'Question',
            'name' => 'Quantos modelos de veículos são cobertos nesta seção?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Temos ' . ($pagination['total'] ?? 0) . ' guias específicos sobre ' . strtolower($category->name) . ' para diferentes modelos de veículos das principais marcas vendidas no Brasil.'
            ]
        ];
    }

    $faqQuestions[] = [
        '@type' => 'Question',
        'name' => 'Quais veículos são cobertos nesta seção de ' . strtolower($category->name) . '?',
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => 'Cobrimos as principais marcas vendidas no Brasil, incluindo Renault, Toyota, Volkswagen, Fiat, Chevrolet, Honda, Hyundai, Kia, RAM e Suzuki. Use os filtros para encontrar informações específicas para seu modelo e ano.'
        ]
    ];

    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faqQuestions
    ];
@endphp

<!-- Structured Data Dinâmico -->
<script type="application/ld+json">
{!! json_encode($collectionSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

<!-- FAQ Schema.org Dinâmico -->
<script type="application/ld+json">
{!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush