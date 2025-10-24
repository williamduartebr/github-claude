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

        // Animação suave para cards de revisão híbrida
        const revisionCards = document.querySelectorAll('.hybrid-card, .revision-card');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        });

        revisionCards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });

        // Destaque especial para componentes críticos de híbridos
        const criticalParts = document.querySelectorAll('[data-hybrid-critical]');
        criticalParts.forEach(part => {
            part.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.02)';
                this.style.transition = 'transform 0.2s ease';
                this.style.boxShadow = '0 8px 25px rgba(16, 185, 129, 0.2)';
            });
            
            part.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
                this.style.boxShadow = '0 2px 4px rgba(0,0,0,0.05)';
            });
        });

        // Indicador visual para sistemas híbridos ativos
        const hybridElements = document.querySelectorAll('.hybrid-indicator');
        hybridElements.forEach(element => {
            element.style.position = 'relative';
            
            // Adiciona pulsação sutil para indicar sistema ativo
            setInterval(() => {
                element.style.boxShadow = '0 0 20px rgba(16, 185, 129, 0.3)';
                setTimeout(() => {
                    element.style.boxShadow = '0 2px 4px rgba(0,0,0,0.05)';
                }, 1000);
            }, 3000);
        });

        // Alerta especial para alta tensão
        const highVoltageElements = document.querySelectorAll('[data-high-voltage]');
        highVoltageElements.forEach(element => {
            element.style.border = '2px solid #ffc107';
            element.style.backgroundColor = 'rgba(255, 193, 7, 0.1)';
            
            element.addEventListener('click', function() {
                alert('⚠️ ATENÇÃO: Este componente envolve alta tensão. Manutenção deve ser realizada apenas por técnicos especializados!');
            });
        });
    });
</script>