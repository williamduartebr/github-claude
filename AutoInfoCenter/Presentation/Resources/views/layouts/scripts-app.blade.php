<!-- Scripts do site -->
<script>
    // Botão de voltar ao topo
    const btnVoltar = document.getElementById('back-to-top');

    window.addEventListener('scroll', function () {
        if (window.pageYOffset > 300) {
            btnVoltar.classList.remove('opacity-0', 'invisible');
            btnVoltar.classList.add('opacity-100', 'visible');
        } else {
            btnVoltar.classList.remove('opacity-100', 'visible');
            btnVoltar.classList.add('opacity-0', 'invisible');
        }
    });

    btnVoltar.addEventListener('click', function () {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Smooth scroll para âncoras internas
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);

            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Menu mobile
    const mobileMenuButton = document.querySelector('[aria-controls="mobile-menu"]');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function () {
            const expanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !expanded);
            mobileMenu.classList.toggle('hidden');
        });
    }
</script>