<style>
    /* Estilos específicos para template de cronograma de revisões de motocicletas */
    .timeline-icon {
        @apply text-[#0E368A] mr-2;
    }

    /* Timeline responsive */
    @media (max-width: 768px) {
        .timeline-container {
            padding-left: 3rem;
        }
        
        .timeline-marker {
            left: 0.5rem;
            width: 3rem;
            height: 3rem;
        }
        
        .timeline-marker .inner {
            width: 2rem;
            height: 2rem;
        }
    }

    /* Destaque especial para componentes críticos de motos */
    .motorcycle-critical {
        background: linear-gradient(135deg, rgba(224, 102, 0, 0.1), rgba(224, 102, 0, 0.05));
        border-left: 4px solid #E06600;
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