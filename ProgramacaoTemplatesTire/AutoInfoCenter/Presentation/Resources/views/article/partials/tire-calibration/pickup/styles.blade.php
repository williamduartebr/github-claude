<style>
    /* Estilos específicos para template de calibragem de pickup */
    .pressure-highlight {
        @apply bg-gradient-to-r from-blue-100 to-blue-200 border-blue-300 text-blue-800 font-bold;
    }

    .pickup-card {
        @apply transform transition-all duration-200 hover: scale-105 hover:shadow-lg;
    }

    .pickup-gradient {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    }

    /* Animações para cards de pressão */
    @keyframes pulse-orange {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(251, 146, 60, 0.7);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(251, 146, 60, 0);
        }
    }

    .pressure-card:hover {
        animation: pulse-orange 1.5s infinite;
    }

    /* Responsividade específica para pickup */
    @media (max-width: 768px) {
        .pickup-pressure-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .pickup-specs-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Estilos para detalhes/summary */
    details[open] summary {
        @apply border-b border-gray-200 mb-4 pb-4;
    }

    /* Estilos para impressão */
    @media print {
        .no-print {
            display: none !important;
        }

        .pressure-highlight {
            @apply border-2 border-gray-400 bg-gray-100;
        }

        main {
            padding: 0 !important;
        }

        section {
            page-break-inside: avoid;
        }
    }
</style>