<!-- JavaScript para FAQ Toggle e Navegação -->
<script>
    document.addEventListener('DOMContentLoaded', function() {


    // Conversão de pressão automática para motos
    const conversionInputs = document.querySelectorAll('.pressure-conversion-input');
    conversionInputs.forEach(input => {
        input.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (!isNaN(value)) {
                if (this.dataset.from === 'psi') {
                    const kgfResult = document.querySelector('#kgf-result');
                    const barResult = document.querySelector('#bar-result');
                    if (kgfResult) kgfResult.textContent = (value * 0.070307).toFixed(2);
                    if (barResult) barResult.textContent = (value * 0.068948).toFixed(2);
                }
            }
        });
    });

    // FAQ Toggle Functionality
    const faqToggles = document.querySelectorAll('.faq-toggle');
    
    faqToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const content = this.nextElementSibling;
            const icon = this.querySelector('.faq-icon');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        });
    });

    // Smooth scroll para links internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
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

    // Analytics tracking para interações
    function trackEvent(action, label) {
        if (typeof gtag !== 'undefined') {
            gtag('event', action, {
                'event_category': 'Car_Tire_Pressure',
                'event_label': label
            });
        }
    }

    // Track FAQ clicks
    faqToggles.forEach((trigger, index) => {
        trigger.addEventListener('click', function() {
            trackEvent('faq_toggle', `question_${index + 1}`);
        });
    });
});

// Função para scroll suave até a tabela de carga
function scrollToLoadTable() {
    const loadTableSection = document.getElementById('tabela-carga-completa');
    
    if (loadTableSection) {
        loadTableSection.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
        
        // Adiciona um highlight temporário
        loadTableSection.style.backgroundColor = '#dbeafe';
        setTimeout(() => {
            loadTableSection.style.backgroundColor = '';
        }, 2000);
    }
}
</script>