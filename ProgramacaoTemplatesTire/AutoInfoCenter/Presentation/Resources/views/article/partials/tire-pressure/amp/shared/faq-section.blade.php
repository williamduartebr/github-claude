{{-- 
Partial: tire-pressure/amp/car/faq-section.blade.php
Seção de perguntas frequentes específicas para carros - AMP
Versão AMP com FAQ categorizada e interativa
--}}

@php
    $carFaq = $article->getData()['car_faq'] ?? [];
    $generalFaq = $article->getData()['faq'] ?? [];
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $vehicleName = $vehicleInfo['model_name'] ?? $vehicleInfo['full_name'] ?? 'seu veículo';
@endphp

@if(!empty($carFaq) || !empty($generalFaq))
<section class="car-faq-section">
    <h2 class="section-title">❓ Perguntas Frequentes</h2>
    <div class="faq-subtitle">Sobre pressão de pneus em {{ $vehicleName }}</div>
    
    <!-- FAQ Específicas do Carro -->
    @if(!empty($carFaq))
    <div class="faq-category car-specific">
        <h3 class="category-title">🚗 Específicas do Veículo</h3>
        <amp-accordion expand-single-section>
            @foreach($carFaq as $index => $faq)
            <section class="faq-item">
                <h4 class="faq-question">
                    <span class="question-icon">{{ $index + 1 }}</span>
                    {{ $faq['pergunta'] ?? $faq['question'] ?? '' }}
                </h4>
                <div class="faq-answer">
                    <p>{{ $faq['resposta'] ?? $faq['answer'] ?? '' }}</p>
                    @if(!empty($faq['tip']))
                    <div class="faq-tip">
                        <span class="tip-icon">💡</span>
                        <span class="tip-text">{{ $faq['tip'] }}</span>
                    </div>
                    @endif
                </div>
            </section>
            @endforeach
        </amp-accordion>
    </div>
    @endif
    
    <!-- FAQ Gerais + Específicas Padrão para Carros -->
    <div class="faq-category general">
        <h3 class="category-title">🔧 Questões Gerais</h3>
        <amp-accordion expand-single-section>
            <!-- FAQ Padrão para Carros -->
            <section class="faq-item">
                <h4 class="faq-question">
                    <span class="question-icon">📊</span>
                    Qual a pressão ideal para {{ $vehicleName }}?
                </h4>
                <div class="faq-answer">
                    <p>A pressão ideal varia conforme a versão e situação de uso. Para {{ $vehicleName }}, consulte a etiqueta no chassi ou manual do proprietário. Geralmente fica entre 30-35 PSI para uso normal.</p>
                    <div class="faq-tip">
                        <span class="tip-icon">⚠️</span>
                        <span class="tip-text">Sempre verifique com pneus frios (3h sem rodar)</span>
                    </div>
                </div>
            </section>
            
            <section class="faq-item">
                <h4 class="faq-question">
                    <span class="question-icon">🛞</span>
                    Preciso calibrar o estepe também?
                </h4>
                <div class="faq-answer">
                    <p>Sim! O estepe deve ser verificado mensalmente. Geralmente usa pressão mais alta (cerca de 60 PSI) para suportar o peso total do veículo temporariamente.</p>
                    <div class="faq-tip">
                        <span class="tip-icon">💡</span>
                        <span class="tip-text">Estepe baixo pode deixar você na mão quando mais precisar</span>
                    </div>
                </div>
            </section>
            
            <section class="faq-item">
                <h4 class="faq-question">
                    <span class="question-icon">🌡️</span>
                    A temperatura afeta a pressão dos pneus?
                </h4>
                <div class="faq-answer">
                    <p>Sim! A cada 10°C de variação na temperatura, a pressão muda aproximadamente 1 PSI. No verão, pode ser necessário ajustar ligeiramente para baixo.</p>
                    <div class="faq-tip">
                        <span class="tip-icon">🔥</span>
                        <span class="tip-text">Nunca calibre pneus quentes - espere esfriar</span>
                    </div>
                </div>
            </section>
            
            <section class="faq-item">
                <h4 class="faq-question">
                    <span class="question-icon">⚡</span>
                    O que é TPMS e como funciona?
                </h4>
                <div class="faq-answer">
                    <p>TPMS (Sistema de Monitoramento da Pressão dos Pneus) é obrigatório em carros novos. Sensores nos pneus alertam quando a pressão está baixa através de uma luz no painel.</p>
                    <div class="faq-tip">
                        <span class="tip-icon">🔔</span>
                        <span class="tip-text">Luz acesa = verificar pressão imediatamente</span>
                    </div>
                </div>
            </section>
            
            <section class="faq-item">
                <h4 class="faq-question">
                    <span class="question-icon">🚗</span>
                    Devo aumentar a pressão com carro cheio?
                </h4>
                <div class="faq-answer">
                    <p>Sim! Com 4-5 passageiros + bagagem, aumente a pressão traseira em 2-4 PSI conforme tabela do manual. Isso compensa o peso extra e mantém estabilidade.</p>
                    <div class="faq-tip">
                        <span class="tip-icon">⚖️</span>
                        <span class="tip-text">Peso extra = pressão extra, especialmente no eixo traseiro</span>
                    </div>
                </div>
            </section>
            
            <section class="faq-item">
                <h4 class="faq-question">
                    <span class="question-icon">⏰</span>
                    Com que frequência devo verificar?
                </h4>
                <div class="faq-answer">
                    <p>Verifique mensalmente e sempre antes de viagens longas. Use um manômetro digital para maior precisão. Verificação visual diária também ajuda.</p>
                    <div class="faq-tip">
                        <span class="tip-icon">📅</span>
                        <span class="tip-text">Marque no calendário - 1º dia de cada mês</span>
                    </div>
                </div>
            </section>
            
            <section class="faq-item">
                <h4 class="faq-question">
                    <span class="question-icon">💰</span>
                    Pressão errada gasta mais combustível?
                </h4>
                <div class="faq-answer">
                    <p>Sim! Pneus murchos podem aumentar o consumo em até 15%. Pressão correta melhora economia, durabilidade dos pneus e segurança.</p>
                    <div class="faq-tip">
                        <span class="tip-icon">💵</span>
                        <span class="tip-text">Pressão correta = economia no posto e na loja de pneus</span>
                    </div>
                </div>
            </section>
            
            <section class="faq-item">
                <h4 class="faq-question">
                    <span class="question-icon">🔧</span>
                    Onde fica a etiqueta com a pressão correta?
                </h4>
                <div class="faq-answer">
                    <p>Geralmente no batente da porta do motorista, às vezes no console central ou porta-luvas. Sempre consulte o manual se não encontrar.</p>
                    <div class="faq-tip">
                        <span class="tip-icon">🏷️</span>
                        <span class="tip-text">Nunca use a pressão máxima escrita no pneu!</span>
                    </div>
                </div>
            </section>
            
            <!-- FAQ do conteúdo dinâmico se houver -->
            @if(!empty($generalFaq))
            @foreach($generalFaq as $index => $faq)
            <section class="faq-item">
                <h4 class="faq-question">
                    <span class="question-icon">{{ $index + 9 }}</span>
                    {{ $faq['pergunta'] ?? $faq['question'] ?? '' }}
                </h4>
                <div class="faq-answer">
                    <p>{{ $faq['resposta'] ?? $faq['answer'] ?? '' }}</p>
                    @if(!empty($faq['tip']))
                    <div class="faq-tip">
                        <span class="tip-icon">💡</span>
                        <span class="tip-text">{{ $faq['tip'] }}</span>
                    </div>
                    @endif
                </div>
            </section>
            @endforeach
            @endif
        </amp-accordion>
    </div>
    
    <!-- FAQ de Problemas Comuns -->
    <div class="faq-category problems">
        <h3 class="category-title">🚨 Problemas Comuns</h3>
        <amp-accordion expand-single-section>
            <section class="faq-item">
                <h4 class="faq-question">
                    <span class="question-icon">⚠️</span>
                    Pneu perdendo ar constantemente?
                </h4>
                <div class="faq-answer">
                    <p>Pode ser furo pequeno, válvula defeituosa ou problema na roda. Procure uma borracharia para inspeção. Não ignore perda gradual de pressão.</p>
                    <div class="faq-tip">
                        <span class="tip-icon">🔍</span>
                        <span class="tip-text">Teste: passe água com sabão para encontrar vazamentos</span>
                    </div>
                </div>
            </section>
            
            <section class="faq-item">
                <h4 class="faq-question">
                    <span class="question-icon">📳</span>
                    Carro vibrando ou "puxando" para um lado?
                </h4>
                <div class="faq-answer">
                    <p>Pode ser pressão desigual entre os pneus. Verifique e ajuste primeiro. Se persistir, pode ser alinhamento ou balanceamento.</p>
                    <div class="faq-tip">
                        <span class="tip-icon">🎯</span>
                        <span class="tip-text">Diferença de 3-4 PSI já causa "puxada" na direção</span>
                    </div>
                </div>
            </section>
            
            <section class="faq-item">
                <h4 class="faq-question">
                    <span class="question-icon">🔋</span>
                    Luz do TPMS não apaga após calibrar?
                </h4>
                <div class="faq-answer">
                    <p>Alguns carros precisam de reset manual do TPMS. Consulte o manual ou concessionária. Pode ser sensor com defeito.</p>
                    <div class="faq-tip">
                        <span class="tip-icon">🔄</span>
                        <span class="tip-text">Procedure de reset varia por marca - não improvise</span>
                    </div>
                </div>
            </section>
            
            <section class="faq-item">
                <h4 class="faq-question">
                    <span class="question-icon">⏱️</span>
                    Quando trocar os pneus?
                </h4>
                <div class="faq-answer">
                    <p>Quando o sulco atingir 1,6mm, houver desgaste irregular, rachaduras laterais ou mais de 5 anos de uso (mesmo com pouco km).</p>
                    <div class="faq-tip">
                        <span class="tip-icon">📏</span>
                        <span class="tip-text">Use uma moeda de R$ 0,25 - se a borda aparecer, está na hora de trocar</span>
                    </div>
                </div>
            </section>
        </amp-accordion>
    </div>
    
    <!-- Dicas Rápidas -->
    <div class="quick-tips">
        <h4 class="tips-title">⚡ Dicas Rápidas</h4>
        <div class="tips-grid">
            <div class="tip-card pressure">
                <div class="tip-icon">📊</div>
                <div class="tip-content">
                    <div class="tip-title">Pressão Certa</div>
                    <div class="tip-text">Etiqueta na porta > Manual > Borracharia confiável</div>
                </div>
            </div>
            
            <div class="tip-card frequency">
                <div class="tip-icon">📅</div>
                <div class="tip-content">
                    <div class="tip-title">Frequência</div>
                    <div class="tip-text">Mensal + antes de viagens longas</div>
                </div>
            </div>
            
            <div class="tip-card temperature">
                <div class="tip-icon">🌡️</div>
                <div class="tip-content">
                    <div class="tip-title">Temperatura</div>
                    <div class="tip-text">Sempre calibrar com pneus frios</div>
                </div>
            </div>
            
            <div class="tip-card spare">
                <div class="tip-icon">🛞</div>
                <div class="tip-content">
                    <div class="tip-title">Estepe</div>
                    <div class="tip-text">Verificar mensalmente - pressão mais alta</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Call to Action -->
    <div class="faq-cta">
        <div class="cta-header">
            <span class="cta-icon">🤝</span>
            <span class="cta-title">Ainda tem dúvidas?</span>
        </div>
        <div class="cta-content">
            Consulte sempre o manual do proprietário do seu {{ $vehicleName }} ou procure uma concessionária autorizada para orientações específicas.
        </div>
        <div class="cta-warning">
            <strong>⚠️ Importante:</strong> Essas informações são orientativas. Sempre siga as especificações do fabricante.
        </div>
    </div>
</section>
@endif

<style>
/* Seção principal */
.car-faq-section {
    margin: 24px 0;
    padding: 20px;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    border-radius: 16px;
    border: 2px solid #2563eb;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e40af;
    margin-bottom: 8px;
    text-align: center;
    border-bottom: 3px solid #2563eb;
    padding-bottom: 8px;
}

.faq-subtitle {
    font-size: 12px;
    color: #3b82f6;
    text-align: center;
    margin-bottom: 20px;
    font-style: italic;
}

/* Categorias de FAQ */
.faq-category {
    margin-bottom: 20px;
}

.category-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
    padding: 8px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

/* Componente AMP Accordion */
amp-accordion {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 16px;
}

amp-accordion section {
    border-bottom: 1px solid #e5e7eb;
}

amp-accordion section:last-child {
    border-bottom: none;
}

/* Perguntas */
.faq-question {
    font-size: 14px;
    padding: 16px;
    margin: 0;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    font-weight: 600;
    color: #1f2937;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: background-color 0.2s ease;
}

.faq-question:hover {
    background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
}

.question-icon {
    font-size: 12px;
    background: #2563eb;
    color: white;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    flex-shrink: 0;
}

/* Respostas */
.faq-answer {
    padding: 16px;
    background: #fff;
    color: #4b5563;
    line-height: 1.6;
}

.faq-answer p {
    margin: 0 0 12px 0;
    font-size: 13px;
}

.faq-tip {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    border-radius: 6px;
    border-left: 3px solid #2563eb;
    margin-top: 8px;
}

.tip-icon {
    font-size: 12px;
    flex-shrink: 0;
}

.tip-text {
    font-size: 11px;
    color: #1e40af;
    font-weight: 500;
}

/* Dicas rápidas */
.quick-tips {
    margin-bottom: 20px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.tips-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.tips-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.tip-card {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px;
    background: #f8fafc;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}

.tip-card .tip-icon {
    font-size: 16px;
    color: #2563eb;
    flex-shrink: 0;
}

.tip-card .tip-content {
    flex: 1;
}

.tip-card .tip-title {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 2px;
}

.tip-card .tip-text {
    font-size: 9px;
    color: #6b7280;
    line-height: 1.3;
}

/* Call to Action */
.faq-cta {
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 2px solid #16a34a;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.cta-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    justify-content: center;
}

.cta-icon {
    font-size: 16px;
    color: #16a34a;
}

.cta-title {
    font-size: 14px;
    font-weight: 600;
    color: #166534;
}

.cta-content {
    font-size: 12px;
    color: #14532d;
    text-align: center;
    margin-bottom: 8px;
    line-height: 1.4;
}

.cta-warning {
    font-size: 10px;
    color: #991b1b;
    text-align: center;
    padding: 6px;
    background: rgba(254, 242, 242, 0.5);
    border-radius: 4px;
}

/* Responsividade mobile */
@media (max-width: 768px) {
    .car-faq-section {
        padding: 16px;
        margin: 16px 0;
    }
    
    .section-title {
        font-size: 18px;
    }
    
    .faq-question {
        font-size: 13px;
        padding: 12px;
        gap: 8px;
    }
    
    .question-icon {
        width: 18px;
        height: 18px;
        font-size: 11px;
    }
    
    .faq-answer {
        padding: 12px;
    }
    
    .faq-answer p {
        font-size: 12px;
    }
    
    .faq-tip {
        gap: 6px;
        padding: 6px 8px;
    }
    
    .tip-text {
        font-size: 10px;
    }
    
    .tips-grid {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    
    .tip-card {
        gap: 6px;
        padding: 8px;
    }
    
    .tip-card .tip-title {
        font-size: 10px;
    }
    
    .tip-card .tip-text {
        font-size: 8px;
    }
    
    .cta-content {
        font-size: 11px;
    }
    
    .cta-warning {
        font-size: 9px;
    }
}
</style>