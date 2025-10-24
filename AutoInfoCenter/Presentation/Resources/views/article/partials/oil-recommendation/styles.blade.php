<style>
    /* Estilos específicos para template de recomendação de óleo */
    .benefits-icon {
        @apply text-[#0E368A] mr-2;
    }

    /* Estilos para impressão */
    @media print {
        .no-print {
            display: none !important;
        }

        main {
            padding: 0 !important;
        }

        /* Força quebras de página em locais apropriados */
        section {
            page-break-inside: avoid;
        }
    }
</style>