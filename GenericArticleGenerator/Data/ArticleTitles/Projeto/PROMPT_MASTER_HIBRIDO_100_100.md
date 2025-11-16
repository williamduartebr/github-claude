# 🤖 PROMPT MASTER HÍBRIDO: TÍTULOS TÉCNICOS + ANTI-IA

## 📋 COMO USAR

Copie este prompt e preencha apenas os 4 campos marcados:

```
Gere 2 versões de títulos (200 totais) + seeder MySQL:

**CATEGORIA:** [PREENCHER: Nome da Categoria]
**CATEGORY_ID:** [PREENCHER: ID numérico]
**CATEGORY_SLUG:** [PREENCHER: slug-da-categoria]
**START_SUBCATEGORY_ID:** [PREENCHER: ID inicial - ex: 300]

---

# ⚠️ ESTRATÉGIA HÍBRIDA

## VERSÃO 1: TÉCNICA (100 títulos)
- Objetivo: SEO puro, indexação rápida
- CTR esperado: 3-6%
- Google vai roubar (AI Overview)
- Mas gera tráfego base constante

## VERSÃO 2: ANTI-IA (100 títulos)  
- Objetivo: Forçar clique, experiência real
- CTR esperado: 12-18%
- Google não consegue resumir
- Tráfego qualificado + RPM alto

---

# 📁 ESTRUTURA DOS ARQUIVOS

Gere **5 arquivos separados**:

1. `titulos_[slug]_TECNICO.json` (100 títulos técnicos)
2. `titulos_[slug]_ANTI_IA.json` (100 títulos anti-IA)
3. `create_subcategories_[slug]_TECNICO.sql`
4. `create_subcategories_[slug]_ANTI_IA.sql`
5. `verify_all_[slug].sql`

---

# 📋 VERSÃO 1: TÉCNICA (100 títulos)

## Estrutura JSON

```json
{
  "category_id": 999,
  "category_slug": "slug-categoria",
  "theme": "technical_reference_tables",
  "version": "TECNICA_1.0",
  "description": "Tabelas técnicas de referência - SEO direto",
  "total_titles": 100,
  "target_audience": "Busca informação direta no Google",
  "content_strategy": "Informação objetiva, tabelas, especificações",
  "seo_strategy": "Indexação rápida, volume alto, CTR baixo aceito",
  "subcategories": {
    "subcategoria-1-tecnica": {
      "subcategory_id": 300,
      "priority": "high",
      "estimated_articles": 35,
      "titles": [
        "Tabela de X: Especificações Completas 2025",
        "Guia Técnico Y: Referência Atualizada",
        "X - Tabela de Referência: Todos os Modelos",
        "... (35 títulos)"
      ]
    },
    "subcategoria-2-tecnica": {
      "subcategory_id": 301,
      "priority": "high",
      "estimated_articles": 35,
      "titles": ["... (35 títulos)"]
    },
    "subcategoria-3-tecnica": {
      "subcategory_id": 302,
      "priority": "medium",
      "estimated_articles": 30,
      "titles": ["... (30 títulos)"]
    }
  }
}
```

## Padrão de Títulos TÉCNICOS

### ✅ FÓRMULA BASE:
```
"Tabela de [TEMA]: [Especificação] [Versão/Modelo] [Ano]"
```

### ✅ VARIAÇÕES PERMITIDAS:
- "Tabela de Óleo Toyota Corolla: 2.0 16V Flex 2020-2025"
- "Guia Técnico Fluido CVT Honda: Fit, HR-V, Civic"
- "Especificações Óleo Motor 0W-20: API SP, ILSAC GF-6"
- "Referência Completa Suspensão: Golf MK7, MK8"
- "Tabela de Pneus Aro 16: 205/55 R16 - Medidas e Pressões"

### ✅ ELEMENTOS OBRIGATÓRIOS:
- ✅ Palavra "Tabela" ou "Guia" ou "Referência"
- ✅ Especificação técnica (modelo, versão, ano)
- ✅ Tom neutro e informativo
- ✅ Ano atual (2025) quando relevante

### ❌ NUNCA USAR EM TÉCNICOS:
- ❌ "Economizei R$ X"
- ❌ "Testei X marcas"
- ❌ "Mecânico revela"
- ❌ "Erro fatal"
- ❌ Qualquer tom emocional

---

# 🔥 VERSÃO 2: ANTI-IA (100 títulos)

## Estrutura JSON

```json
{
  "category_id": 999,
  "category_slug": "slug-categoria",
  "theme": "experience_based_data_driven",
  "version": "ANTI_IA_1.0",
  "description": "Experiência real + dados concretos - Força clique",
  "total_titles": 100,
  "target_audience": "Busca solução real, quer experiência",
  "content_strategy": "Dados, planilhas, economia, erros evitáveis - ZERO fotos/vídeos",
  "seo_strategy": "CTR alto, tráfego qualificado, RPM premium",
  "subcategories": {
    "subcategoria-1-experiencia": {
      "subcategory_id": 303,
      "priority": "high",
      "estimated_articles": 35,
      "titles": [
        "X: Economizei R$ 2.400 em 100.000km (Planilha)",
        "Testei 5 Marcas de Y: Uma Custou R$ 8.000",
        "Z: 3 Erros Que Custam R$ 12.000 (Lista)",
        "... (35 títulos)"
      ]
    },
    "subcategoria-2-experiencia": {
      "subcategory_id": 304,
      "priority": "high",
      "estimated_articles": 35,
      "titles": ["... (35 títulos)"]
    },
    "subcategoria-3-experiencia": {
      "subcategory_id": 305,
      "priority": "medium",
      "estimated_articles": 30,
      "titles": ["... (30 títulos)"]
    }
  }
}
```

## Padrão de Títulos ANTI-IA

### ✅ FÓRMULA BASE:
```
"[Tema]: [Hook com Dados Concretos] + [Formato]"
```

### ✅ VARIAÇÕES PERMITIDAS:

**Economia/Prejuízo:**
- "Óleo Corolla: Economizei R$ 2.400 em 3 Anos (Planilha Excel)"
- "CVT Honda: Óleo Errado Custou R$ 18.000 em Retífica"
- "Suspensão: Quanto Você Perde Não Trocando (Calculadora)"

**Teste/Comparação:**
- "Testei 5 Óleos no Delivery: Um Causou Consumo Absurdo"
- "Pneus: Comparei 6 Marcas em 80.000km - Dados Reais"
- "Amortecedores: Original vs Paralelo - Teste 100.000km"

**Erro/Alerta:**
- "Freio: 3 Erros Que Custam R$ 5.000 (Como Evitar)"
- "Bateria: Este Erro Me Deixou na Mão 4x em 1 Ano"
- "Filtro de Ar: Por Que 'Baratinho' Custa Mais Caro"

**Autoridade:**
- "Mecânico com 20 Anos: Segredo Para Motor Durar 300k"
- "Frotista com 50 Vans Revela: Quanto Gasta em Óleo/Ano"
- "Engenheiro Toyota Explica: Por Que 0W-20 É Obrigatório"

**Dados/Planilha:**
- "Rodei 250.000km: Planilha de Todos os Custos"
- "Análise Laboratorial: Óleo Genérico Tinha 40% de Usado"
- "Tabela Real: Quanto Custa Cada km Com Óleo Premium vs Comum"

### ✅ ELEMENTOS OBRIGATÓRIOS:
- ✅ Números concretos (R$, km, %, anos)
- ✅ Formato específico (Planilha, Lista, Calculadora, Tabela, Análise)
- ✅ Experiência real (Rodei, Testei, Comparei)
- ✅ Hook emocional (Economizar, Evitar prejuízo)

### ❌ NUNCA USAR EM ANTI-IA:
- ❌ "Veja as fotos"
- ❌ "Assista ao vídeo"
- ❌ "Confira as imagens"
- ❌ "Tutorial em vídeo"
- ❌ Promessas visuais

---

# 🗄️ ESTRUTURA SQL

## Arquivo: `create_subcategories_[slug]_TECNICO.sql`

```sql
-- ============================================
-- SEEDER: Subcategorias TÉCNICAS
-- Categoria: [NOME]
-- Category ID: [ID]
-- IDs: [START_ID] a [START_ID+2]
-- Versão: TÉCNICA
-- ============================================

INSERT INTO maintenance_subcategories (
    id, 
    maintenance_category_id, 
    name, 
    slug, 
    description, 
    priority,
    meta_data,
    created_at, 
    updated_at
) VALUES
(
    [START_ID], 
    [CATEGORY_ID], 
    'Nome Subcategoria 1 (Técnica)', 
    'subcategoria-1-tecnica',
    'Tabelas técnicas e referências - Versão TÉCNICA',
    'high',
    '{"version": "TECNICA", "article_type": "reference_table", "ctr_target": "3-6%"}',
    NOW(), 
    NOW()
),
(
    [START_ID + 1], 
    [CATEGORY_ID], 
    'Nome Subcategoria 2 (Técnica)', 
    'subcategoria-2-tecnica',
    'Guias técnicos completos - Versão TÉCNICA',
    'high',
    '{"version": "TECNICA", "article_type": "technical_guide", "ctr_target": "3-6%"}',
    NOW(), 
    NOW()
),
(
    [START_ID + 2], 
    [CATEGORY_ID], 
    'Nome Subcategoria 3 (Técnica)', 
    'subcategoria-3-tecnica',
    'Especificações detalhadas - Versão TÉCNICA',
    'medium',
    '{"version": "TECNICA", "article_type": "specifications", "ctr_target": "3-6%"}',
    NOW(), 
    NOW()
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    slug = VALUES(slug),
    description = VALUES(description),
    meta_data = VALUES(meta_data),
    updated_at = NOW();
```

## Arquivo: `create_subcategories_[slug]_ANTI_IA.sql`

```sql
-- ============================================
-- SEEDER: Subcategorias ANTI-IA
-- Categoria: [NOME]
-- Category ID: [ID]
-- IDs: [START_ID+3] a [START_ID+5]
-- Versão: ANTI-IA
-- ============================================

INSERT INTO maintenance_subcategories (
    id, 
    maintenance_category_id, 
    name, 
    slug, 
    description, 
    priority,
    meta_data,
    created_at, 
    updated_at
) VALUES
(
    [START_ID + 3], 
    [CATEGORY_ID], 
    'Nome Subcategoria 1 (Experiência)', 
    'subcategoria-1-experiencia',
    'Experiência real com dados concretos - Versão ANTI-IA',
    'high',
    '{"version": "ANTI_IA", "article_type": "experience_data", "ctr_target": "12-18%"}',
    NOW(), 
    NOW()
),
(
    [START_ID + 4], 
    [CATEGORY_ID], 
    'Nome Subcategoria 2 (Experiência)', 
    'subcategoria-2-experiencia',
    'Testes comparativos e economia - Versão ANTI-IA',
    'high',
    '{"version": "ANTI_IA", "article_type": "comparison_test", "ctr_target": "12-18%"}',
    NOW(), 
    NOW()
),
(
    [START_ID + 5], 
    [CATEGORY_ID], 
    'Nome Subcategoria 3 (Experiência)', 
    'subcategoria-3-experiencia',
    'Erros evitáveis e autoridade - Versão ANTI-IA',
    'medium',
    '{"version": "ANTI_IA", "article_type": "warning_authority", "ctr_target": "12-18%"}',
    NOW(), 
    NOW()
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    slug = VALUES(slug),
    description = VALUES(description),
    meta_data = VALUES(meta_data),
    updated_at = NOW();
```

## Arquivo: `verify_all_[slug].sql`

```sql
-- ============================================
-- VERIFICAÇÃO COMPLETA: TÉCNICOS + ANTI-IA
-- Categoria: [NOME DA CATEGORIA]
-- ============================================

-- 1. Resumo Geral
SELECT 
    '=== RESUMO GERAL ===' AS info,
    COUNT(*) AS total_subcategories,
    SUM(CASE WHEN slug LIKE '%-tecnica' THEN 1 ELSE 0 END) AS tecnicas,
    SUM(CASE WHEN slug LIKE '%-experiencia' THEN 1 ELSE 0 END) AS anti_ia
FROM maintenance_subcategories
WHERE maintenance_category_id = [CATEGORY_ID];

-- 2. Subcategorias TÉCNICAS
SELECT 
    '=== TÉCNICAS ===' AS tipo,
    id,
    name,
    slug,
    priority,
    JSON_EXTRACT(meta_data, '$.ctr_target') AS ctr_target
FROM maintenance_subcategories
WHERE maintenance_category_id = [CATEGORY_ID]
AND slug LIKE '%-tecnica'
ORDER BY id;

-- 3. Subcategorias ANTI-IA
SELECT 
    '=== ANTI-IA ===' AS tipo,
    id,
    name,
    slug,
    priority,
    JSON_EXTRACT(meta_data, '$.ctr_target') AS ctr_target
FROM maintenance_subcategories
WHERE maintenance_category_id = [CATEGORY_ID]
AND slug LIKE '%-experiencia'
ORDER BY id;

-- 4. Próximo ID disponível
SELECT 
    '=== PRÓXIMO ID ===' AS info,
    MAX(id) + 1 AS next_id
FROM maintenance_subcategories;

-- 5. Contagem de artigos esperados (baseado nos JSONs)
SELECT 
    '=== ARTIGOS ESPERADOS ===' AS info,
    'TÉCNICOS' AS versao,
    100 AS total_titulos,
    '35 + 35 + 30' AS distribuicao
UNION ALL
SELECT 
    '=== ARTIGOS ESPERADOS ===' AS info,
    'ANTI-IA' AS versao,
    100 AS total_titulos,
    '35 + 35 + 30' AS distribuicao;
```

---

# ✅ CHECKLIST DE VALIDAÇÃO

## JSON TÉCNICO:
- [ ] 100 títulos totais (35+35+30)
- [ ] Formato: "Tabela/Guia/Referência + Especificação"
- [ ] Tom neutro e informativo
- [ ] Ano 2025 quando relevante
- [ ] ZERO tom emocional

## JSON ANTI-IA:
- [ ] 100 títulos totais (35+35+30)
- [ ] Todos têm números (R$, km, %)
- [ ] Formato específico (Planilha, Lista, etc)
- [ ] Experiência real mencionada
- [ ] ZERO promessas visuais

## SQL:
- [ ] 6 subcategorias (3 técnicas + 3 anti-IA)
- [ ] IDs sequenciais corretos
- [ ] meta_data com version e ctr_target
- [ ] ON DUPLICATE KEY UPDATE presente
- [ ] Query de verificação completa

---

# 📤 FORMATO DE ENTREGA

Entregue **5 arquivos**:

1. **`titulos_[slug]_TECNICO.json`**
   - 100 títulos técnicos
   - 3 subcategorias (35+35+30)

2. **`titulos_[slug]_ANTI_IA.json`**
   - 100 títulos anti-IA
   - 3 subcategorias (35+35+30)

3. **`create_subcategories_[slug]_TECNICO.sql`**
   - Seeder para 3 subcategorias técnicas

4. **`create_subcategories_[slug]_ANTI_IA.sql`**
   - Seeder para 3 subcategorias anti-IA

5. **`verify_all_[slug].sql`**
   - Verificação completa (técnicos + anti-IA)

---

# 🚀 AGORA GERE OS ARQUIVOS!

Com base nos dados fornecidos, gere os 5 arquivos da estratégia híbrida.

**Lembre-se:**
- ✅ 200 títulos totais (100+100)
- ✅ 6 subcategorias (3+3)
- ✅ IDs sequenciais
- ✅ Versões completamente separadas
- ✅ SQL com meta_data diferenciado
```
