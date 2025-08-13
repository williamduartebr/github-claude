{{-- 
Partial: tire-pressure/amp/shared/newsletter-signup.blade.php
Newsletter signup compartilhado para carros e motocicletas - AMP
Versão AMP com cadastro para dicas de manutenção e lembretes
--}}

@php
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $vehicleType = strpos(strtolower($article->getData()['title'] ?? ''), 'motocicleta') !== false ? 'motorcycle' : 'car';
    $vehicleName = $vehicleInfo['model_name'] ?? ($vehicleType === 'motorcycle' ? 'sua motocicleta' : 'seu carro');
@endphp

<section class="newsletter-signup-section">
    <div class="newsletter-container">
        <!-- Header Principal -->
        <div class="newsletter-header">
            <div class="header-icon">
                @if($vehicleType === 'motorcycle')
                🏍️
                @else
                🚗
                @endif
            </div>
            <div class="header-content">
                <div class="header-title">Mantenha {{ $vehicleName }} Sempre Seguro</div>
                <div class="header-subtitle">Receba dicas exclusivas de manutenção de pneus</div>
            </div>
        </div>
        
        <!-- Benefícios -->
        <div class="benefits-section">
            <h3 class="benefits-title">📬 O que você vai receber:</h3>
            <div class="benefits-grid">
                <div class="benefit-item">
                    <span class="benefit-icon">📅</span>
                    <span class="benefit-text">Lembretes de calibragem</span>
                </div>
                <div class="benefit-item">
                    <span class="benefit-icon">💡</span>
                    <span class="benefit-text">Dicas de economia</span>
                </div>
                <div class="benefit-item">
                    <span class="benefit-icon">🔧</span>
                    <span class="benefit-text">Guias de manutenção</span>
                </div>
                <div class="benefit-item">
                    <span class="benefit-icon">💰</span>
                    <span class="benefit-text">Ofertas exclusivas</span>
                </div>
            </div>
        </div>
        
        <!-- Formulário de Cadastro -->
        <div class="signup-form-container">
            <form method="post" 
                  action-xhr="/newsletter/subscribe"
                  custom-validation-reporting="as-you-go">
                
                <!-- Campo Nome -->
                <div class="form-group">
                    <label for="newsletter-name" class="form-label">
                        <span class="label-icon">👤</span>
                        <span class="label-text">Seu nome</span>
                    </label>
                    <input type="text" 
                           id="newsletter-name"
                           name="name"
                           class="form-input"
                           placeholder="Digite seu nome"
                           required
                           pattern="[A-Za-zÀ-ÿ\s]{2,50}"
                           title="Nome deve ter entre 2 e 50 caracteres">
                    <div class="form-error" visible-when-invalid="valueMissing">
                        Por favor, digite seu nome
                    </div>
                    <div class="form-error" visible-when-invalid="patternMismatch">
                        Nome deve conter apenas letras e espaços
                    </div>
                </div>
                
                <!-- Campo Email -->
                <div class="form-group">
                    <label for="newsletter-email" class="form-label">
                        <span class="label-icon">📧</span>
                        <span class="label-text">Seu melhor email</span>
                    </label>
                    <input type="email" 
                           id="newsletter-email"
                           name="email"
                           class="form-input"
                           placeholder="seuemail@exemplo.com"
                           required>
                    <div class="form-error" visible-when-invalid="valueMissing">
                        Por favor, digite seu email
                    </div>
                    <div class="form-error" visible-when-invalid="typeMismatch">
                        Por favor, digite um email válido
                    </div>
                </div>
                
                <!-- Campo Veículo -->
                <div class="form-group">
                    <label for="newsletter-vehicle" class="form-label">
                        <span class="label-icon">
                            @if($vehicleType === 'motorcycle')
                            🏍️
                            @else
                            🚗
                            @endif
                        </span>
                        <span class="label-text">Seu veículo (opcional)</span>
                    </label>
                    <input type="text" 
                           id="newsletter-vehicle"
                           name="vehicle"
                           class="form-input"
                           placeholder="Ex: Honda CB 600F 2020"
                           value="{{ $vehicleInfo['full_name'] ?? '' }}">
                </div>
                
                <!-- Frequência de Email -->
                <div class="form-group frequency-group">
                    <div class="frequency-label">
                        <span class="label-icon">📆</span>
                        <span class="label-text">Frequência dos emails:</span>
                    </div>
                    <div class="frequency-options">
                        <label class="frequency-option">
                            <input type="radio" name="frequency" value="weekly" checked>
                            <span class="radio-custom"></span>
                            <span class="option-text">Semanal</span>
                        </label>
                        <label class="frequency-option">
                            <input type="radio" name="frequency" value="monthly">
                            <span class="radio-custom"></span>
                            <span class="option-text">Mensal</span>
                        </label>
                    </div>
                </div>
                
                <!-- Interesses -->
                <div class="form-group interests-group">
                    <div class="interests-label">
                        <span class="label-icon">⭐</span>
                        <span class="label-text">Seus interesses:</span>
                    </div>
                    <div class="interests-options">
                        <label class="interest-option">
                            <input type="checkbox" name="interests[]" value="maintenance" checked>
                            <span class="checkbox-custom"></span>
                            <span class="option-text">Manutenção preventiva</span>
                        </label>
                        <label class="interest-option">
                            <input type="checkbox" name="interests[]" value="economy">
                            <span class="checkbox-custom"></span>
                            <span class="option-text">Economia de combustível</span>
                        </label>
                        <label class="interest-option">
                            <input type="checkbox" name="interests[]" value="safety">
                            <span class="checkbox-custom"></span>
                            <span class="option-text">Dicas de segurança</span>
                        </label>
                        <label class="interest-option">
                            <input type="checkbox" name="interests[]" value="promotions">
                            <span class="checkbox-custom"></span>
                            <span class="option-text">Promoções e ofertas</span>
                        </label>
                    </div>
                </div>
                
                <!-- Campos Hidden -->
                <input type="hidden" name="source" value="tire-pressure-article">
                <input type="hidden" name="vehicle_type" value="{{ $vehicleType }}">
                <input type="hidden" name="article_slug" value="{{ $article->slug ?? '' }}">
                
                <!-- LGPD Consent -->
                <div class="form-group consent-group">
                    <label class="consent-option">
                        <input type="checkbox" name="lgpd_consent" required>
                        <span class="checkbox-custom"></span>
                        <span class="consent-text">
                            Concordo em receber emails com dicas e ofertas. 
                            <a href="/politica-privacidade" target="_blank" class="privacy-link">
                                Política de Privacidade
                            </a>
                        </span>
                    </label>
                    <div class="form-error" visible-when-invalid="valueMissing">
                        É necessário concordar para continuar
                    </div>
                </div>
                
                <!-- Botão de Envio -->
                <div class="form-group submit-group">
                    <button type="submit" class="submit-button">
                        <span class="button-icon">📬</span>
                        <span class="button-text">Quero Receber Dicas Gratuitas</span>
                    </button>
                </div>
            </form>
            
            <!-- Estados do Formulário -->
            <div submitting class="form-status submitting">
                <div class="status-icon">⏳</div>
                <div class="status-text">Cadastrando...</div>
            </div>
            
            <div submit-success class="form-status success">
                <div class="status-icon">✅</div>
                <div class="status-text">
                    <strong>Cadastro realizado com sucesso!</strong><br>
                    Verifique seu email para confirmar a inscrição.
                </div>
            </div>
            
            <div submit-error class="form-status error">
                <div class="status-icon">❌</div>
                <div class="status-text">
                    <strong>Ops! Algo deu errado.</strong><br>
                    Tente novamente em alguns minutos.
                </div>
            </div>
        </div>
        
        <!-- Garantias e Segurança -->
        <div class="security-badges">
            <div class="security-item">
                <span class="security-icon">🔒</span>
                <span class="security-text">100% Seguro</span>
            </div>
            <div class="security-item">
                <span class="security-icon">📧</span>
                <span class="security-text">Sem Spam</span>
            </div>
            <div class="security-item">
                <span class="security-icon">❌</span>
                <span class="security-text">Cancele Quando Quiser</span>
            </div>
        </div>
        
        <!-- Social Proof -->
        <div class="social-proof">
            <div class="proof-stats">
                <div class="stat-item">
                    <div class="stat-number">25.000+</div>
                    <div class="stat-label">Pessoas já cadastradas</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">4.8⭐</div>
                    <div class="stat-label">Avaliação média</div>
                </div>
            </div>
            <div class="testimonial">
                <div class="testimonial-text">
                    "Graças às dicas, economizo R$ 50 por mês em combustível!"
                </div>
                <div class="testimonial-author">- Maria Silva, São Paulo</div>
            </div>
        </div>
    </div>
</section>

<style>
/* Seção principal */
.newsletter-signup-section {
    margin: 32px 0;
    padding: 0;
}

.newsletter-container {
    max-width: 600px;
    margin: 0 auto;
    background: linear-gradient(135deg, #0f172a, #1e293b);
    border-radius: 20px;
    padding: 24px;
    color: white;
    position: relative;
    overflow: hidden;
}

.newsletter-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
}

/* Header */
.newsletter-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    text-align: left;
}

.header-icon {
    font-size: 32px;
    flex-shrink: 0;
}

.header-content {
    flex: 1;
}

.header-title {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 4px;
    color: #f1f5f9;
}

.header-subtitle {
    font-size: 14px;
    color: #cbd5e1;
}

/* Benefícios */
.benefits-section {
    margin-bottom: 24px;
}

.benefits-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 12px;
    color: #f1f5f9;
    text-align: center;
}

.benefits-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.benefit-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
}

.benefit-icon {
    font-size: 16px;
    flex-shrink: 0;
}

.benefit-text {
    font-size: 12px;
    color: #e2e8f0;
    font-weight: 500;
}

/* Formulário */
.signup-form-container {
    position: relative;
}

.form-group {
    margin-bottom: 16px;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 6px;
}

.label-icon {
    font-size: 14px;
}

.label-text {
    font-size: 13px;
    font-weight: 600;
    color: #f1f5f9;
}

.form-input {
    width: 100%;
    padding: 12px;
    border: 2px solid #334155;
    border-radius: 8px;
    background: #1e293b;
    color: white;
    font-size: 14px;
    transition: border-color 0.2s ease;
}

.form-input:focus {
    outline: none;
    border-color: #3b82f6;
}

.form-input::placeholder {
    color: #64748b;
}

.form-error {
    color: #ef4444;
    font-size: 11px;
    margin-top: 4px;
    display: none;
}

.form-error[visible] {
    display: block;
}

/* Frequência */
.frequency-group {
    background: rgba(255, 255, 255, 0.05);
    padding: 12px;
    border-radius: 8px;
}

.frequency-label {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}

.frequency-options {
    display: flex;
    gap: 16px;
}

.frequency-option {
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
}

.frequency-option input[type="radio"] {
    display: none;
}

.radio-custom {
    width: 16px;
    height: 16px;
    border: 2px solid #64748b;
    border-radius: 50%;
    position: relative;
    flex-shrink: 0;
}

.frequency-option input[type="radio"]:checked + .radio-custom {
    border-color: #3b82f6;
}

.frequency-option input[type="radio"]:checked + .radio-custom::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 8px;
    height: 8px;
    background: #3b82f6;
    border-radius: 50%;
}

.option-text {
    font-size: 12px;
    color: #e2e8f0;
    font-weight: 500;
}

/* Interesses */
.interests-group {
    background: rgba(255, 255, 255, 0.05);
    padding: 12px;
    border-radius: 8px;
}

.interests-label {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}

.interests-options {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.interest-option {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.interest-option input[type="checkbox"] {
    display: none;
}

.checkbox-custom {
    width: 16px;
    height: 16px;
    border: 2px solid #64748b;
    border-radius: 4px;
    position: relative;
    flex-shrink: 0;
}

.interest-option input[type="checkbox"]:checked + .checkbox-custom {
    background: #3b82f6;
    border-color: #3b82f6;
}

.interest-option input[type="checkbox"]:checked + .checkbox-custom::after {
    content: '✓';
    position: absolute;
    top: -2px;
    left: 2px;
    color: white;
    font-size: 12px;
    font-weight: bold;
}

/* Consentimento */
.consent-group {
    background: rgba(255, 255, 255, 0.05);
    padding: 12px;
    border-radius: 8px;
}

.consent-option {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    cursor: pointer;
}

.consent-text {
    font-size: 11px;
    color: #cbd5e1;
    line-height: 1.4;
}

.privacy-link {
    color: #60a5fa;
    text-decoration: underline;
}

/* Botão de envio */
.submit-group {
    margin-top: 20px;
}

.submit-button {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    border: none;
    border-radius: 12px;
    color: white;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: transform 0.2s ease;
}

.submit-button:active {
    transform: translateY(1px);
}

.button-icon {
    font-size: 18px;
}

.button-text {
    font-size: 14px;
}

/* Estados do formulário */
.form-status {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.95);
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    display: none;
}

.form-status[visible] {
    display: flex;
}

.status-icon {
    font-size: 48px;
}

.status-text {
    text-align: center;
    font-size: 14px;
    color: #e2e8f0;
}

.form-status.success .status-text {
    color: #10b981;
}

.form-status.error .status-text {
    color: #ef4444;
}

/* Badges de segurança */
.security-badges {
    display: flex;
    justify-content: center;
    gap: 16px;
    margin: 24px 0;
    padding-top: 16px;
    border-top: 1px solid #334155;
}

.security-item {
    display: flex;
    align-items: center;
    gap: 4px;
}

.security-icon {
    font-size: 12px;
}

.security-text {
    font-size: 10px;
    color: #94a3b8;
    font-weight: 500;
}

/* Social proof */
.social-proof {
    text-align: center;
    padding-top: 16px;
    border-top: 1px solid #334155;
}

.proof-stats {
    display: flex;
    justify-content: center;
    gap: 24px;
    margin-bottom: 16px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 16px;
    font-weight: 700;
    color: #60a5fa;
}

.stat-label {
    font-size: 10px;
    color: #94a3b8;
}

.testimonial {
    background: rgba(255, 255, 255, 0.05);
    padding: 12px;
    border-radius: 8px;
    border-left: 3px solid #3b82f6;
}

.testimonial-text {
    font-size: 12px;
    color: #e2e8f0;
    font-style: italic;
    margin-bottom: 4px;
}

.testimonial-author {
    font-size: 10px;
    color: #94a3b8;
}

/* Responsividade mobile */
@media (max-width: 768px) {
    .newsletter-container {
        margin: 0 16px;
        padding: 20px;
        border-radius: 16px;
    }
    
    .header-title {
        font-size: 18px;
    }
    
    .header-subtitle {
        font-size: 13px;
    }
    
    .benefits-grid {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    
    .form-input {
        padding: 10px;
        font-size: 13px;
    }
    
    .submit-button {
        padding: 14px;
        font-size: 14px;
    }
    
    .frequency-options {
        flex-direction: column;
        gap: 8px;
    }
    
    .proof-stats {
        gap: 16px;
    }
    
    .security-badges {
        flex-direction: column;
        gap: 8px;
        align-items: center;
    }
}

/* Validação visual */
.form-input:invalid {
    border-color: #ef4444;
}

.form-input:valid {
    border-color: #10b981;
}
</style>