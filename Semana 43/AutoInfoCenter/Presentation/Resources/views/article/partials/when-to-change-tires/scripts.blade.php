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
                    this.style.transform = 'scale(1.005)';
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

        // Animação para barras de durabilidade
        const durabilityBars = document.querySelectorAll('.durability-bar div');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const bar = entry.target;
                    const width = bar.style.width;
                    bar.style.width = '0%';
                    setTimeout(() => {
                        bar.style.width = width;
                        bar.style.transition = 'width 1s ease-out';
                    }, 200);
                }
            });
        });

        durabilityBars.forEach(bar => observer.observe(bar));

        // Copy to clipboard para pressões de pneus
        const pressureDisplays = document.querySelectorAll('[data-copy-pressure]');
        pressureDisplays.forEach(display => {
            display.style.cursor = 'pointer';
            display.title = 'Clique para copiar pressão';
            
            display.addEventListener('click', function() {
                const pressure = this.textContent.trim();
                navigator.clipboard.writeText(pressure).then(() => {
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

        // Contador para timeline de verificação
        const timelineItems = document.querySelectorAll('.verification-timeline .timeline-item');
        timelineItems.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-20px)';
            
            setTimeout(() => {
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
                item.style.transition = 'all 0.5s ease-out';
            }, index * 200);
        });

        // Expandir/recolher cards de sinais críticos
        const criticalCards = document.querySelectorAll('.critical-sign-card');
        criticalCards.forEach(card => {
            const header = card.querySelector('h3');
            if (header) {
                header.style.cursor = 'pointer';
                header.addEventListener('click', function() {
                    const content = card.querySelector('.critical-content');
                    if (content) {
                        content.style.display = content.style.display === 'none' ? 'block' : 'none';
                    }
                });
            }
        });

        // Tooltips para especificações técnicas
        const specItems = document.querySelectorAll('.spec-tooltip');
        specItems.forEach(item => {
            const tooltip = item.querySelector('.tooltip-content');
            if (tooltip) {
                item.addEventListener('mouseenter', function() {
                    tooltip.style.visibility = 'visible';
                    tooltip.style.opacity = '1';
                });
                
                item.addEventListener('mouseleave', function() {
                    tooltip.style.visibility = 'hidden';
                    tooltip.style.opacity = '0';
                });
            }
        });

        // Progress indicator para leitura do artigo
        const progressBar = document.createElement('div');
        progressBar.className = 'fixed top-0 left-0 h-1 bg-[#0E368A] z-50 transition-all duration-300';
        progressBar.style.width = '0%';
        document.body.appendChild(progressBar);

        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset;
            const docHeight = document.body.offsetHeight - window.innerHeight;
            const scrollPercent = (scrollTop / docHeight) * 100;
            progressBar.style.width = scrollPercent + '%';
        });

        // Quick actions para verificação rápida
        const quickActions = document.createElement('div');
        quickActions.className = 'fixed bottom-4 right-4 space-y-2 z-40';
        quickActions.innerHTML = `
            <div class="bg-[#0E368A] text-white p-3 rounded-full shadow-lg cursor-pointer hover:bg-[#0A2868] transition-colors duration-200" title="Verificação Rápida">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        `;
        
        // Mostrar apenas em desktop
        if (window.innerWidth > 768) {
            document.body.appendChild(quickActions);
            
            quickActions.addEventListener('click', function() {
                const procedureSection = document.querySelector('section:has(h2:contains("Procedimento"))');
                if (procedureSection) {
                    procedureSection.scrollIntoView({ behavior: 'smooth' });
                }
            });
        }

        // Lazy loading melhorado para imagens
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    img.style.opacity = '1';
                    imageObserver.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px 0px'
        });

        images.forEach(img => {
            img.style.opacity = '0';
            imageObserver.observe(img);
        });

        // Validação de pressão em tempo real (se houver inputs)
        const pressureInputs = document.querySelectorAll('input[data-pressure]');
        pressureInputs.forEach(input => {
            input.addEventListener('input', function() {
                const value = parseFloat(this.value);
                const min = parseFloat(this.dataset.min || 0);
                const max = parseFloat(this.dataset.max || 100);
                
                if (value < min || value > max) {
                    this.style.borderColor = '#ef4444';
                    this.style.backgroundColor = '#fef2f2';
                } else {
                    this.style.borderColor = '#10b981';
                    this.style.backgroundColor = '#f0fdf4';
                }
            });
        });

        // Analytics tracking para interações importantes
        const trackEvent = (action, category, label) => {
            if (typeof gtag !== 'undefined') {
                gtag('event', action, {
                    event_category: category,
                    event_label: label
                });
            }
        };

        // Track clicks em pressões recomendadas
        pressureDisplays.forEach(display => {
            display.addEventListener('click', () => {
                trackEvent('copy_pressure', 'tire_maintenance', display.textContent.trim());
            });
        });

        // Track scroll para seções importantes
        const importantSections = document.querySelectorAll('section h2');
        const sectionObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const sectionTitle = entry.target.textContent;
                    trackEvent('section_view', 'tire_guide', sectionTitle);
                }
            });
        }, { threshold: 0.5 });

        importantSections.forEach(section => sectionObserver.observe(section));
    });
</script>