# README - Análise e Correção de Cronogramas Detalhados

## 📋 Visão Geral do Processo

Este documento descreve o processo completo de **análise e correção** dos campos `cronograma_detalhado` e `visao_geral_revisoes` nos artigos de revisão de veículos.

### 🎯 Objetivo
Identificar e corrigir automaticamente artigos com estruturas incompletas ou inválidas nas seções de cronograma e visão geral das revisões.

### 📊 Problema Identificado
- **985 artigos** com cronogramas incompletos (campos ausentes: quilometragem, tempo, custo_estimado)
- **250 artigos** com visão geral insuficiente (arrays com menos de 3 revisões)
- **0% de completude** inicial nos cronogramas detalhados

---

## 🔍 Comandos de Análise

### 1. Verificação Rápida Geral
```bash
# Análise completa de cronograma + overview
php artisan review-schedule:quick-content-check --limit=1000

# Por tipo de veículo
php artisan review-schedule:quick-content-check --vehicle-type=motorcycle --limit=200
```

### 2. Análise Detalhada do Cronograma
```bash
# Análise completa da estrutura
php artisan review-schedule:analyze-detailed-schedule --limit=1000

# Apenas artigos problemáticos
php artisan review-schedule:analyze-detailed-schedule --only-broken --limit=500

# Por tipo específico
php artisan review-schedule:analyze-detailed-schedule --vehicle-type=electric --limit=100
```

### 3. Análise da Visão Geral
```bash
# Análise da seção overview
php artisan review-schedule:analyze-overview --limit=500 --show-examples

# Debug de problemas específicos
php artisan review-schedule:debug-overview --limit=20 --show-content
```

### 4. Verificação de Problemas Reais
```bash
# Encontrar artigos realmente problemáticos
php artisan review-schedule:find-real-problems --limit=1000 --show-examples=10
```

---

## 🔧 Comandos de Correção

### 1. Correção do Cronograma Detalhado
```bash
# Simulação (recomendado primeiro)
php artisan review-schedule:fix-detailed-schedule --limit=100 --dry-run

# Correção real
php artisan review-schedule:fix-detailed-schedule --limit=1000 --force

# Por tipo de veículo
php artisan review-schedule:fix-detailed-schedule --vehicle-type=motorcycle --limit=200 --force
```

### 2. Correção da Visão Geral
```bash
# Simulação
php artisan review-schedule:fix-overview --limit=250 --dry-run

# Correção real  
php artisan review-schedule:fix-overview --limit=250 --force

# Por tipo específico
php artisan review-schedule:fix-overview --vehicle-type=car --limit=100 --force
```

---

## 📊 Estruturas Esperadas

### Cronograma Detalhado (cronograma_detalhado)
```json
{
  "cronograma_detalhado": [
    {
      "numero_revisao": 1,
      "intervalo": "10.000 km ou 12 meses",
      "km": "10.000",
      "servicos_principais": [
        "Verificação minuciosa do sistema de freios",
        "Substituição do filtro de ar-condicionado",
        "Diagnóstico básico dos sistemas elétricos",
        "Inspeção detalhada dos pneumáticos"
      ],
      "verificacoes_complementares": [
        "Verificação da pressão dos pneus",
        "Teste da bateria e sistema de carga",
        "Inspeção do sistema de iluminação",
        "Verificação dos níveis de fluidos"
      ],
      "estimativa_custo": "R$ 280 - R$ 350",
      "observacoes": "Primeira revisão focada em adaptação do veículo"
    }
  ]
}
```

### Visão Geral das Revisões (visao_geral_revisoes)
```json
{
  "visao_geral_revisoes": [
    {
      "revisao": "1ª Revisão",
      "intervalo": "10.000 km ou 12 meses",
      "principais_servicos": "Óleo, filtros, verificações básicas",
      "estimativa_custo": "R$ 280 - R$ 350"
    },
    {
      "revisao": "2ª Revisão",
      "intervalo": "20.000 km ou 24 meses",
      "principais_servicos": "Óleo, filtros de ar e combustível",
      "estimativa_custo": "R$ 320 - R$ 420"
    }
  ]
}
```

---

## 🚀 Workflow Recomendado

### Passo 1: Análise Inicial
```bash
# Verificação geral do estado atual
php artisan review-schedule:quick-content-check --limit=1000
```

### Passo 2: Análise Detalhada
```bash
# Cronograma detalhado
php artisan review-schedule:analyze-detailed-schedule --limit=1000

# Visão geral
php artisan review-schedule:analyze-overview --limit=500 --show-examples
```

### Passo 3: Correção por Etapas
```bash
# 1. Testar correção do cronograma
php artisan review-schedule:fix-detailed-schedule --limit=50 --dry-run

# 2. Aplicar correção do cronograma
php artisan review-schedule:fix-detailed-schedule --limit=1000 --force

# 3. Testar correção da overview
php artisan review-schedule:fix-overview --limit=50 --dry-run

# 4. Aplicar correção da overview
php artisan review-schedule:fix-overview --limit=250 --force
```

### Passo 4: Validação Final
```bash
# Verificar se problemas foram resolvidos
php artisan review-schedule:quick-content-check --limit=1000

# Análise final de qualidade
php artisan review-schedule:analyze-detailed-schedule --limit=100
```

---

## 📈 Tipos de Problemas Detectados

### Cronograma Detalhado
- **MISSING_DETAILED_SCHEDULE**: Seção ausente
- **INSUFFICIENT_REVISIONS**: Menos de 3 revisões
- **MISSING_REQUIRED_FIELD**: Campos obrigatórios ausentes
- **INCOMPLETE_REVISION_SERVICES**: Poucos serviços por revisão
- **EMPTY_COMPLEMENTARY_CHECKS**: Verificações vazias

### Visão Geral
- **MISSING_OVERVIEW**: Seção ausente
- **INVALID_STRUCTURE**: Tipo inválido
- **Array com menos de 3 elementos**: Quantidade insuficiente
- **INCOMPLETE_TABLE**: Campos obrigatórios ausentes

---

## 🔧 Templates por Tipo de Veículo

### Carros Convencionais
- **Intervalos**: 10k, 20k, 30k, 40k, 50k, 60k km
- **Foco**: Óleo, filtros, correias, transmissão

### Motocicletas  
- **Intervalos**: 1k, 5k, 10k, 15k, 20k, 25k km
- **Foco**: Óleo, corrente, válvulas, freios

### Veículos Elétricos
- **Intervalos**: 10k, 20k, 30k, 40k, 50k, 60k km
- **Foco**: Sistemas elétricos, bateria, software

### Veículos Híbridos
- **Intervalos**: 10k, 20k, 30k, 40k, 50k, 60k km  
- **Foco**: Sistemas duplos, eletrônica híbrida

---

## 📊 Resultados Esperados

### Antes da Correção
```
Cronograma Detalhado: 0% completos (985 problemas)
Visão Geral: 74% completos (250 problemas)  
Ambas Seções: 74% completos
```

### Após a Correção
```
Cronograma Detalhado: 100% completos (0 problemas)
Visão Geral: 100% completos (0 problemas)
Ambas Seções: 100% completos
```

---

## ⚠️ Considerações Importantes

### Backup
- **Sempre** execute `--dry-run` primeiro
- Considere fazer backup do banco antes de correções em massa

### Ordem de Execução
1. Cronograma detalhado primeiro
2. Visão geral depois
3. Validação final

### Monitoramento
- Use `--limit` pequeno para testar
- Monitore logs de erro durante a execução
- Valide qualidade após cada correção

---

## 🎯 Comandos Essenciais (Resumo)

```bash
# ANÁLISE RÁPIDA
php artisan review-schedule:quick-content-check --limit=1000

# CORREÇÃO COMPLETA
php artisan review-schedule:fix-detailed-schedule --limit=1000 --force
php artisan review-schedule:fix-overview --limit=250 --force

# VALIDAÇÃO FINAL
php artisan review-schedule:quick-content-check --limit=1000
```

---

## 📞 Suporte

Em caso de problemas:
1. Execute primeiro os comandos de análise
2. Use `--dry-run` para testar
3. Verifique logs de erro
4. Execute validação após correções

**Data de Criação**: Junho 2025  
**Versão**: 1.0  
**Status**: Testado e Validado