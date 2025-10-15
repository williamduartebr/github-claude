<style>
    /* Estilos específicos para template de cronograma de revisões de veículos híbridos */
    .timeline-icon {
        @apply text-[#0E368A] mr-2;
    }

    /* Timeline híbrida com gradiente */
    .hybrid-timeline {
        background: linear-gradient(135deg, rgba(14, 54, 138, 0.1), rgba(16, 185, 129, 0.1));
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

    /* Destaque especial para componentes híbridos */
    .hybrid-critical {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(255, 193, 7, 0.05));
        border-left: 4px solid #ffc107;
    }

    /* Animações para elementos híbridos */
    .hybrid-card {
        transition: all 0.3s ease;
    }

    .hybrid-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Gradientes específicos para híbridos */
    .hybrid-gradient {
        background: linear-gradient(135deg, #0E368A, #10b981);
    }

    .hybrid-border {
        border-image: linear-gradient(45deg, #0E368A, #10b981) 1;
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

        .hybrid-gradient {
            background: #0E368A !important;
        }
    }

    /* Indicadores visuais para sistemas híbridos */
    .hybrid-indicator {
        position: relative;
    }

    .hybrid-indicator::before {
        content: '⚡';
        position: absolute;
        top: -5px;
        right: -5px;
        font-size: 12px;
        background: linear-gradient(135deg, #10b981, #059669);
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>