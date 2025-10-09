# README - Importação e Publicação de Artigos

## 📋 Visão Geral do Processo

Este documento descreve o processo completo de **importação de veículos do CSV** e **publicação dos artigos gerados** para a coleção temporária (TempArticle).

### 🎯 Objetivo
1. **Importar dados** de veículos do arquivo CSV
2. **Gerar artigos** de cronograma de revisões 
3. **Publicar artigos** na coleção temporária para revisão
4. **Preparar conteúdo** para publicação final no blog

### 📊 Fluxo Completo
```
CSV → Análise → Geração → Correção → Publicação → Blog
```

---

## 📊 1. Análise do CSV

### Comandos de Análise Inicial
```bash
# Estatísticas básicas do CSV
php artisan review-schedule:csv-stats data/todos_veiculos.csv

# Estatísticas detalhadas (mostra todas as marcas)
php artisan review-schedule:csv-stats data/todos_veiculos.csv --detailed

# Preview por tipo de veículo
php artisan review-schedule:csv-stats data/todos_veiculos.csv --vehicle-type=electric
php artisan review-schedule:csv-stats data/todos_veiculos.csv --vehicle-type=motorcycle
php artisan review-schedule:csv-stats data/todos_veiculos.csv --vehicle-type=hybrid

# Preview por marca específica
php artisan review-schedule:csv-stats data/todos_veiculos.csv --make=BMW
php artisan review-schedule:csv-stats data/todos_veiculos.csv --make=Tesla
```

---

## 🎯 2. Geração de Artigos por Tipo

### Carros Elétricos
```bash
# Teste com dry-run
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=electric --limit=5 --dry-run

# Geração controlada
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=electric --limit=10 --batch=5

# Validação específica para elétricos
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=electric --limit=5 --template-validation
```

### Carros Híbridos
```bash
# Dry run para híbridos
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=hybrid --limit=5 --dry-run

# Geração de híbridos
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=hybrid --limit=8 --batch=4
```

### Motocicletas
```bash
# Dry run para motos
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=motorcycle --limit=5 --dry-run

# Geração de motocicletas
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=motorcycle --limit=10 --batch=5

# Motos a partir de uma linha específica
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=motorcycle --start=50 --limit=15
```

### Carros Convencionais
```bash
# Dry run para carros
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=car --limit=5 --dry-run

# Geração de carros convencionais
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=car --limit=20 --batch=10

# Carros de marca específica
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=car --make=Chevrolet --limit=15
```

---

## 🔍 3. Filtros Específicos de Importação

### Por Marca
```bash
# Apenas BMW
php artisan review-schedule:generate data/todos_veiculos.csv --make=BMW --limit=10

# Apenas Tesla (elétricos)
php artisan review-schedule:generate data/todos_veiculos.csv --make=Tesla --limit=5

# Apenas Ducati (motocicletas)
php artisan review-schedule:generate data/todos_veiculos.csv --make=Ducati --limit=8
```

### Por Ano
```bash
# Veículos de 2020 a 2025
php artisan review-schedule:generate data/todos_veiculos.csv --year=2020-2025 --limit=25

# Apenas 2024
php artisan review-schedule:generate data/todos_veiculos.csv --year=2024 --limit=20

# Carros elétricos de 2023-2025
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=electric --year=2023-2025 --limit=12
```

### Combinando Filtros
```bash
# BMW elétricos de 2022-2025
php artisan review-schedule:generate data/todos_veiculos.csv --make=BMW --vehicle-type=electric --year=2022-2025 --limit=8

# Motos Yamaha
php artisan review-schedule:generate data/todos_veiculos.csv --make=Yamaha --vehicle-type=motorcycle --limit=10
```

---

## 🔧 4. Correção de Qualidade

### Verificação Pós-Geração
```bash
# Verificação rápida geral
php artisan review-schedule:quick-content-check --limit=1000

# Análise detalhada do cronograma
php artisan review-schedule:analyze-detailed-schedule --limit=500

# Análise da visão geral
php artisan review-schedule:analyze-overview --limit=500 --show-examples
```

### Correções Automáticas
```bash
# Correção do cronograma detalhado
php artisan review-schedule:fix-detailed-schedule --limit=1000 --force

# Correção da visão geral
php artisan review-schedule:fix-overview --limit=250 --force

# Validação final
php artisan review-schedule:quick-content-check --limit=1000
```

---

## 📤 5. Publicação para TempArticle

### Comandos de Publicação
```bash
# Verificar estatísticas antes da publicação
php artisan review-schedule:stats

# Simulação da publicação (recomendado)
php artisan review-schedule:publish-temp --limit=100 --dry-run

# Publicação real com confirmação
php artisan review-schedule:publish-temp --limit=100

# Publicação automática (sem confirmação)
php artisan review-schedule:publish-temp --limit=100 --confirm

# Pular duplicatas
php artisan review-schedule:publish-temp --limit=100 --skip-duplicates --confirm
```

### Opções de Status
```bash
# Publicar apenas artigos em draft (padrão)
php artisan review-schedule:publish-temp --status=draft --limit=100

# Publicar artigos publicados
php artisan review-schedule:publish-temp --status=published --limit=50
```

### Publicação em Lotes
```bash
# Lote pequeno para teste
php artisan review-schedule:publish-temp --limit=20 --dry-run

# Lote médio
php artisan review-schedule:publish-temp --limit=100 --confirm

# Lote grande
php artisan review-schedule:publish-temp --limit=500 --skip-duplicates --confirm
```

---

## 📊 6. Workflow Completo Recomendado

### Fase 1: Preparação e Análise
```bash
# 1. Analisar CSV
php artisan review-schedule:csv-stats data/todos_veiculos.csv --detailed

# 2. Verificar estatísticas atuais
php artisan review-schedule:stats
```

### Fase 2: Geração por Tipo (Teste)
```bash
# 3. Testar cada tipo com dry-run
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=electric --limit=2 --dry-run
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=car --limit=2 --dry-run
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=hybrid --limit=2 --dry-run
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=motorcycle --limit=2 --dry-run
```

### Fase 3: Geração Controlada
```bash
# 4. Gerar pequenos lotes por tipo
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=electric --limit=10 --batch=5
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=car --limit=20 --batch=10
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=hybrid --limit=8 --batch=4
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=motorcycle --limit=15 --batch=5
```

### Fase 4: Verificação e Correção
```bash
# 5. Verificar qualidade
php artisan review-schedule:quick-content-check --limit=1000

# 6. Corrigir problemas se necessário
php artisan review-schedule:fix-detailed-schedule --limit=1000 --force
php artisan review-schedule:fix-overview --limit=250 --force

# 7. Validação final
php artisan review-schedule:quick-content-check --limit=1000
```

### Fase 5: Publicação
```bash
# 8. Simular publicação
php artisan review-schedule:publish-temp --limit=100 --dry-run

# 9. Publicar lotes controlados
php artisan review-schedule:publish-temp --limit=50 --confirm
php artisan review-schedule:publish-temp --limit=100 --skip-duplicates --confirm

# 10. Verificar estatísticas finais
php artisan review-schedule:stats
```

---

## 🎯 7. Cenários de Uso Específicos

### Desenvolvimento e Teste
```bash
# Gerar apenas 1 de cada tipo para teste rápido
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=electric --limit=1 --dry-run
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=car --limit=1 --dry-run
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=hybrid --limit=1 --dry-run
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=motorcycle --limit=1 --dry-run

# Publicação de teste
php artisan review-schedule:publish-temp --limit=5 --dry-run
```

### Produção Controlada
```bash
# Gerar em pequenos lotes com verificação
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=electric --limit=50 --batch=10
php artisan review-schedule:quick-content-check --limit=50  # Verificar qualidade

# Publicar após validação
php artisan review-schedule:publish-temp --limit=50 --confirm
```

### Correção de Problemas
```bash
# Regenerar artigos com problemas
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=electric --limit=20 --force

# Republicar artigos corrigidos
php artisan review-schedule:publish-temp --limit=20 --skip-duplicates --confirm
```

---

## 📊 8. Estrutura de Dados Gerados

### Artigo na Coleção review_schedule_articles
```json
{
  "_id": "ObjectId",
  "title": "Cronograma de Revisões do Tesla Model 3 2024",
  "slug": "cronograma-revisoes-tesla-model-3-2024",
  "status": "draft",
  "content": {
    "introducao": "...",
    "visao_geral_revisoes": [...],
    "cronograma_detalhado": [...],
    "manutencao_preventiva": {...},
    "pecas_atencao": [...],
    "perguntas_frequentes": [...],
    "consideracoes_finais": "...",
    "extracted_entities": {
      "marca": "Tesla",
      "modelo": "Model 3", 
      "ano": "2024",
      "tipo_veiculo": "electric"
    }
  },
  "created_at": "2025-06-22T...",
  "updated_at": "2025-06-22T..."
}
```

### Artigo na Coleção temp_articles (após publicação)
```json
{
  "_id": "ObjectId",
  "title": "Cronograma de Revisões do Tesla Model 3 2024",
  "slug": "cronograma-revisoes-tesla-model-3-2024",
  "status": "draft",
  "category_id": 21,
  "category_name": "Revisões Programadas",
  "content": "...", // HTML renderizado
  "meta_description": "...",
  "featured_image": null,
  "source": "review_schedule",
  "template": "review_schedule",
  "original_id": "ObjectId_da_review_schedule_articles",
  "vehicle_info": {
    "make": "Tesla",
    "model": "Model 3",
    "year": "2024",
    "type": "electric"
  },
  "created_at": "2025-06-22T...",
  "updated_at": "2025-06-22T..."
}
```

---

## ⚠️ 9. Considerações Importantes

### Backup e Segurança
- **Sempre** execute `--dry-run` primeiro
- Considere fazer backup das coleções antes de operações em massa
- Use `--limit` pequeno para testes iniciais

### Ordem de Execução
1. **Análise** do CSV primeiro
2. **Geração** por tipo de veículo
3. **Correção** de qualidade
4. **Publicação** controlada

### Monitoramento
- Use `--batch` pequeno para melhor controle de erro
- Monitore logs durante execução
- Valide qualidade após cada etapa
- Verifique estatísticas regularmente

### Performance
- Gere em lotes pequenos (5-10 artigos)
- Use filtros específicos para reduzir carga
- Monitore uso de memória em operações grandes

---

## 🎯 10. Comandos Essenciais (Resumo)

### Análise e Preparação
```bash
php artisan review-schedule:csv-stats data/todos_veiculos.csv --detailed
php artisan review-schedule:stats
```

### Geração Completa por Tipo
```bash
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=electric --limit=20 --batch=5
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=car --limit=50 --batch=10
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=hybrid --limit=15 --batch=5
php artisan review-schedule:generate data/todos_veiculos.csv --vehicle-type=motorcycle --limit=25 --batch=5
```

### Correção de Qualidade
```bash
php artisan review-schedule:quick-content-check --limit=1000
php artisan review-schedule:fix-detailed-schedule --limit=1000 --force
php artisan review-schedule:fix-overview --limit=250 --force
```

### Publicação
```bash
php artisan review-schedule:publish-temp --limit=100 --dry-run
php artisan review-schedule:publish-temp --limit=500 --skip-duplicates --confirm
```

---

## 📞 Suporte e Solução de Problemas

### Debug de Geração
```bash
# Debug de veículo específico
php artisan review-schedule:debug data/todos_veiculos.csv --vehicle=1 --detailed

# Verificar estrutura dos artigos
php artisan review-schedule:debug-structure --limit=5
```

### Problemas Comuns
1. **Limite não funciona**: Verificar signature do comando
2. **Templates misturados**: Usar `--template-validation`
3. **Muitos erros**: Usar lotes menores com `--batch`
4. **Duplicatas**: Usar `--skip-duplicates` na publicação

### Em Caso de Problemas
1. Execute comandos de análise primeiro
2. Use `--dry-run` para testar
3. Verifique logs de erro
4. Execute validação após correções
5. Use lotes menores se houver problemas de memória

---

**Data de Criação**: Junho 2025  
**Versão**: 1.0  
**Status**: Processo Completo Documentado