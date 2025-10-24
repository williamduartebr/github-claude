<style>
    /* Estilos específicos para template de recomendação de pneus */
    .tire-icon {
        @apply text-[#0E368A] mr-2;
    }

    /* Hover effects para cards de pneus */
    .tire-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }

    /* Badges dinâmicos */
    .tire-badge-premium {
        @apply bg-gray-200 text-gray-800;
    }

    .tire-badge-bestseller {
        @apply bg-[#E06600] text-white;
    }

    /* Tabela responsiva específica */
    .usage-comparison-table {
        font-size: 14px;
    }

    .usage-comparison-table th,
    .usage-comparison-table td {
        padding: 12px 8px;
        vertical-align: top;
    }

    /* Cards de guia de desgaste */
    .wear-guide-card {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-left: 4px solid #0E368A;
    }

    /* Dicas de manutenção com ícones */
    .maintenance-tip-card:hover {
        background-color: #f8fafc;
        border-color: #0E368A;
        transition: all 0.2s ease;
    }

    /* FAQ com sombras suaves */
    .faq-card {
        transition: box-shadow 0.2s ease;
    }

    .faq-card:hover {
        box-shadow: 0 4px 12px rgba(14, 54, 138, 0.1);
    }

    /* Estilos para impressão */
    @media print {
        .no-print {
            display: none !important;
        }

        main {
            padding: 0 !important;
        }

        /* Força quebras de página apropriadas */
        section {
            page-break-inside: avoid;
        }

        .tire-card,
        .wear-guide-card,
        .maintenance-tip-card {
            page-break-inside: avoid;
            margin-bottom: 1rem;
        }

        /* Cores em impressão */
        .bg-\[#0E368A\] {
            background-color: #0E368A !important;
            -webkit-print-color-adjust: exact;
        }

        .text-\[#0E368A\] {
            color: #0E368A !important;
            -webkit-print-color-adjust: exact;
        }

        /* Remover sombras e efeitos em impressão */
        .shadow-sm,
        .shadow-md {
            box-shadow: none !important;
        }
    }

    /* Responsividade específica para pneus */
    @media (max-width: 768px) {
        .tire-specs-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .usage-comparison-table {
            font-size: 12px;
        }

        .usage-comparison-table th,
        .usage-comparison-table td {
            padding: 8px 4px;
        }

        /* Stack cards de pneus em mobile */
        .grid-cols-1.md\:grid-cols-3 {
            grid-template-columns: 1fr;
        }

        .grid-cols-1.md\:grid-cols-2 {
            grid-template-columns: 1fr;
        }
    }

    /* Animações para entrada dos cards */
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .tire-card,
    .wear-guide-card,
    .maintenance-tip-card {
        animation: slideInUp 0.6s ease-out;
    }

    /* Delay progressivo para cards */
    .tire-card:nth-child(1) {
        animation-delay: 0.1s;
    }

    .tire-card:nth-child(2) {
        animation-delay: 0.2s;
    }

    .tire-card:nth-child(3) {
        animation-delay: 0.3s;
    }

    /* Scroll suave para tabelas */
    .overflow-x-auto {
        scrollbar-width: thin;
        scrollbar-color: #0E368A #f1f5f9;
    }

    .overflow-x-auto::-webkit-scrollbar {
        height: 8px;
    }

    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #0E368A;
        border-radius: 4px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #0A2868;
    }

    /* Estados de loading para imagens */
    img[loading="lazy"] {
        transition: opacity 0.3s ease;
    }

    img[loading="lazy"]:not([src]) {
        opacity: 0;
    }
</style>