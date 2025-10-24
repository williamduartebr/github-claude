<style>
    /* Estilos específicos para template quando trocar pneus */
    .tire-wear-icon {
        @apply text-[#0E368A] mr-2;
    }

    /* Cards de sintomas com animações */
    .symptom-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    /* Barras de progresso para fatores de durabilidade */
    .durability-bar {
        height: 8px;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .durability-bar.positive {
        background: linear-gradient(90deg, #10b981, #34d399);
    }

    .durability-bar.negative {
        background: linear-gradient(90deg, #ef4444, #f87171);
    }

    /* Timeline para cronograma */
    .verification-timeline {
        position: relative;
    }

    .verification-timeline::before {
        content: '';
        position: absolute;
        left: 16px;
        top: 40px;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, #0E368A, transparent);
    }

    .verification-timeline .timeline-item:last-child::after {
        display: none;
    }

    /* Tabelas responsivas */
    .tire-specs-table {
        font-size: 14px;
    }

    .tire-specs-table th,
    .tire-specs-table td {
        padding: 12px 8px;
        vertical-align: top;
    }

    /* Cards de sinais críticos */
    .critical-sign-card {
        border-left: 4px solid #dc2626;
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    }

    .critical-sign-card:hover {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        transition: all 0.2s ease;
    }

    /* Manutenção preventiva com ícones */
    .maintenance-card:hover {
        background-color: #f8fafc;
        border-color: #0E368A;
        transition: all 0.2s ease;
    }

    /* Procedimento com steps numerados */
    .procedure-step {
        counter-increment: step-counter;
    }

    .procedure-step::before {
        content: counter(step-counter);
        background: #0E368A;
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        margin-right: 8px;
    }

    /* Especificações do veículo */
    .vehicle-specs-card {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border: 1px solid #cbd5e1;
    }

    .pressure-display {
        background: linear-gradient(135deg, rgba(14, 54, 138, 0.1) 0%, rgba(14, 54, 138, 0.05) 100%);
        border: 2px solid rgba(14, 54, 138, 0.2);
    }

    /* FAQ com efeitos hover */
    .faq-card {
        transition: all 0.2s ease;
    }

    .faq-card:hover {
        box-shadow: 0 4px 12px rgba(14, 54, 138, 0.1);
        border-color: #0E368A;
    }

    /* Badges de severidade e importância */
    .severity-high {
        @apply bg-red-100 text-red-800;
    }

    .severity-medium {
        @apply bg-yellow-100 text-yellow-800;
    }

    .severity-low {
        @apply bg-green-100 text-green-800;
    }

    .importance-high {
        @apply bg-red-100 text-red-800;
    }

    .importance-medium {
        @apply bg-blue-100 text-blue-800;
    }

    .importance-low {
        @apply bg-gray-100 text-gray-800;
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

        .symptom-card,
        .critical-sign-card,
        .maintenance-card {
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

        /* Ajustar tamanhos para impressão */
        h1 { font-size: 24px !important; }
        h2 { font-size: 20px !important; }
        h3 { font-size: 18px !important; }
    }

    /* Responsividade específica */
    @media (max-width: 768px) {
        .tire-specs-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .tire-specs-table {
            font-size: 12px;
        }

        .tire-specs-table th,
        .tire-specs-table td {
            padding: 8px 4px;
        }

        /* Stack cards em mobile */
        .grid-cols-1.md\:grid-cols-2 {
            grid-template-columns: 1fr;
        }

        /* Ajustar procedure steps em mobile */
        .procedure-step::before {
            width: 20px;
            height: 20px;
            font-size: 10px;
        }

        /* Pressure display responsivo */
        .pressure-display {
            font-size: 18px;
        }
    }

    /* Animações para entrada dos elementos */
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

    .symptom-card,
    .critical-sign-card,
    .maintenance-card {
        animation: slideInUp 0.6s ease-out;
    }

    /* Delay progressivo para cards */
    .symptom-card:nth-child(1) { animation-delay: 0.1s; }
    .symptom-card:nth-child(2) { animation-delay: 0.2s; }
    .symptom-card:nth-child(3) { animation-delay: 0.3s; }
    .symptom-card:nth-child(4) { animation-delay: 0.4s; }

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

    /* Highlights interativos */
    .interactive-highlight:hover {
        background-color: #f0f9ff;
        border-color: #0EA5E9;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    /* Tooltips para especificações técnicas */
    .spec-tooltip {
        position: relative;
    }

    .spec-tooltip:hover .tooltip-content {
        visibility: visible;
        opacity: 1;
    }

    .tooltip-content {
        visibility: hidden;
        opacity: 0;
        position: absolute;
        z-index: 50;
        bottom: 125%;
        left: 50%;
        margin-left: -80px;
        background-color: #1f2937;
        color: white;
        text-align: center;
        border-radius: 6px;
        padding: 8px;
        font-size: 12px;
        transition: opacity 0.3s;
        width: 160px;
    }

    .tooltip-content::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #1f2937 transparent transparent transparent;
    }
</style>