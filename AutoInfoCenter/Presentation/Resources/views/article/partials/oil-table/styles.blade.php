<style>
    /* Estilos específicos para template de tabela de óleo */
    .oil-table-icon {
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

        section {
            page-break-inside: avoid;
        }
    }
</style>