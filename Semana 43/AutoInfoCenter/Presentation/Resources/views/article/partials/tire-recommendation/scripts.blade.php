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