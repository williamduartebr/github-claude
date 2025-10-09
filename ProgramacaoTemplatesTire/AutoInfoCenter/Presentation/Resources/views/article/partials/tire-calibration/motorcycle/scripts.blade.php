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

    // Destaque para alertas críticos
    const criticalAlerts = document.querySelectorAll('.critical-alert');
    criticalAlerts.forEach(alert => {
        alert.addEventListener('mouseenter', function() {
            this.classList.add('scale-105');
            this.style.transition = 'transform 0.2s ease';
        });
        alert.addEventListener('mouseleave', function() {
            this.classList.remove('scale-105');
        });
    });

    // Auto-scroll para seção de calibragem em motos esportivas
    const sportWarningButton = document.querySelector('#sport-calibration-guide');
    if (sportWarningButton) {
        sportWarningButton.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('#calibration-procedure').scrollIntoView({
                behavior: 'smooth'
            });
        });
    }

    // FAQ Toggle (se houver FAQ)
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
});
</script>