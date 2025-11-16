# 🧪 Comandos de Teste - Sistema Atualizado Claude 2025

## 📋 Pré-requisitos
```bash
# Verificar se API key está configurada
php artisan tinker
>>> config('services.anthropic.api_key')
>>> exit

# Limpar cache
php artisan cache:clear
php artisan config:clear
```

---

## 1️⃣ TESTE: Modelo STANDARD (Claude 3.7 Sonnet)

### Dry Run (Simulação):
```bash
php artisan temp-article:generate-standard --limit=1 --dry-run
```

### Execução Real:
```bash
# Gerar 1 artigo
php artisan temp-article:generate-standard --limit=1 --show-stats

# Gerar 5 artigos com categoria específica
php artisan temp-article:generate-standard --limit=5 --category=oleo

# Gerar com auto-escalação
php artisan temp-article:generate-standard --limit=3 --auto-escalate

# Retry de falhas
php artisan temp-article:generate-standard --retry-failed --limit=2
```

### Resultado Esperado:
```
✅ Taxa de Sucesso: 70-80%
💰 Custo por artigo: 2.3 unidades
⏱️ Tempo médio: 15-30s
```

---

## 2️⃣ TESTE: Modelo INTERMEDIATE (Sonnet 4.0)

### Dry Run:
```bash
php artisan temp-article:generate-intermediate --limit=1 --dry-run
```

### Execução Real:
```bash
# Processar apenas falhas do standard
php artisan temp-article:generate-intermediate --only-failed-standard --limit=3

# Processar artigos de alta prioridade
php artisan temp-article:generate-intermediate --priority=high --limit=2

# Com força de retry
php artisan temp-article:generate-intermediate --force-retry --limit=1
```

### Resultado Esperado:
```
✅ Taxa de Sucesso: 85-95%
💰 Custo por artigo: 3.5 unidades
⏱️ Tempo médio: 20-40s
```

---

## 3️⃣ TESTE: Modelo PREMIUM (Sonnet 4.5)

### ⚠️ ATENÇÃO: Use com MODERAÇÃO!

### Dry Run:
```bash
php artisan temp-article:generate-premium --limit=1 --dry-run
```

### Execução Real (Casos Críticos Apenas):
```bash
# 1 artigo crítico
php artisan temp-article:generate-premium --only-critical --limit=1

# Com confirmação forçada (cuidado!)
php artisan temp-article:generate-premium --limit=1 --force-confirm --max-cost=10
```

### Resultado Esperado:
```
✅ Taxa de Sucesso: 95%+
💰 Custo por artigo: 4.0 unidades
⏱️ Tempo médio: 30-60s
```

---

## 4️⃣ TESTE: Seed de Títulos

```bash
# Gerar 10 títulos de teste
php artisan temp-article:seed --count=10 --dry-run

# Gerar 5 títulos de óleo
php artisan temp-article:seed --count=5 --category=oleo --priority=high

# Gerar 20 títulos variados
php artisan temp-article:seed --count=20 --category=all
```

---

## 5️⃣ VERIFICAR ESTATÍSTICAS

### Ver estatísticas do sistema:
```bash
php artisan temp-article:generate-standard --show-stats --dry-run
```

### Query manual no banco:
```bash
php artisan tinker
```

```php
// Ver distribuição por status
use Src\InfoArticleGenerator\Infrastructure\Eloquent\GenerationTempArticle;

GenerationTempArticle::selectRaw('generation_status, COUNT(*) as count')
    ->groupBy('generation_status')
    ->get();

// Ver por modelo usado
GenerationTempArticle::selectRaw('generation_model_used, COUNT(*) as count')
    ->whereNotNull('generation_model_used')
    ->groupBy('generation_model_used')
    ->get();

// Calcular custo total
$articles = GenerationTempArticle::whereNotNull('generation_cost')->get();
$totalCost = $articles->sum('generation_cost');
echo "Custo total: {$totalCost} unidades\n";

// Taxa de sucesso
$total = GenerationTempArticle::whereNotNull('generated_at')->count();
$success = GenerationTempArticle::whereIn('generation_status', ['generated', 'validated', 'published'])->count();
$rate = round(($success / $total) * 100, 1);
echo "Taxa de sucesso: {$rate}%\n";
```

---

## 6️⃣ FLUXO COMPLETO DE TESTE

### Passo 1: Seed
```bash
php artisan temp-article:seed --count=5 --category=oleo
```

### Passo 2: Gerar com Standard
```bash
php artisan temp-article:generate-standard --limit=5 --show-stats
```

### Passo 3: Escalar Falhas (se houver)
```bash
php artisan temp-article:generate-intermediate --only-failed-standard --limit=3
```

### Passo 4: Premium para Casos Críticos (se necessário)
```bash
php artisan temp-article:generate-premium --only-critical --limit=1
```

---

## 7️⃣ MONITORAR LOGS

### Logs em tempo real:
```bash
# Standard
tail -f storage/logs/claude-generation-standard.log

# Intermediate
tail -f storage/logs/claude-generation-intermediate.log

# Laravel geral
tail -f storage/logs/laravel.log
```

### Verificar erros:
```bash
grep "ERROR" storage/logs/claude-generation-*.log
grep "CRITICAL" storage/logs/claude-generation-*.log
```

---

## 8️⃣ TESTE DE CUSTOS

### Cenário 1: Custo Mínimo (só standard)
```bash
# 10 artigos só com standard
php artisan temp-article:generate-standard --limit=10
# Custo esperado: 23 unidades (10 × 2.3)
```

### Cenário 2: Custo Médio (standard + intermediate)
```bash
# 10 artigos standard
php artisan temp-article:generate-standard --limit=10
# Escalar 3 falhas
php artisan temp-article:generate-intermediate --only-failed-standard --limit=3
# Custo esperado: 23 + 10.5 = 33.5 unidades
```

### Cenário 3: Custo Alto (com premium)
```bash
# 5 standard + 2 intermediate + 1 premium
php artisan temp-article:generate-standard --limit=5
php artisan temp-article:generate-intermediate --only-failed-standard --limit=2
php artisan temp-article:generate-premium --only-critical --limit=1
# Custo esperado: 11.5 + 7 + 4 = 22.5 unidades
```

---

## 9️⃣ VALIDAÇÃO DE QUALIDADE

### Verificar JSON gerado:
```bash
php artisan tinker
```

```php
use Src\InfoArticleGenerator\Infrastructure\Eloquent\GenerationTempArticle;

// Pegar último artigo gerado
$article = GenerationTempArticle::where('generation_status', 'generated')
    ->latest('generated_at')
    ->first();

// Ver estrutura
print_r($article->generated_json);

// Validar campos obrigatórios
$required = ['title', 'slug', 'template', 'seo_data', 'metadata'];
foreach ($required as $field) {
    echo "{$field}: " . (isset($article->generated_json[$field]) ? '✅' : '❌') . "\n";
}

// Ver blocos de conteúdo
$blocks = $article->generated_json['metadata']['content_blocks'] ?? [];
echo "Total de blocos: " . count($blocks) . "\n";

// Verificar blocos obrigatórios
$requiredBlocks = ['intro', 'tldr', 'faq', 'conclusion'];
$blockTypes = array_column($blocks, 'block_type');
foreach ($requiredBlocks as $type) {
    echo "{$type}: " . (in_array($type, $blockTypes) ? '✅' : '❌') . "\n";
}
```

---

## 🔟 TESTE DE PERFORMANCE

### Benchmark de Velocidade:
```bash
# Medir tempo do standard
time php artisan temp-article:generate-standard --limit=1

# Medir tempo do intermediate
time php artisan temp-article:generate-intermediate --limit=1

# Medir tempo do premium
time php artisan temp-article:generate-premium --limit=1
```

### Análise de Throughput:
```bash
# Processar 10 artigos e medir tempo total
time php artisan temp-article:generate-standard --limit=10 --batch-size=5 --delay=2
```

---

## 🚨 TROUBLESHOOTING

### Se API retornar erro 401:
```bash
# Verificar API key
php artisan tinker
>>> env('ANTHROPIC_API_KEY')
>>> config('services.anthropic.api_key')
```

### Se artigos ficarem travados em "generating":
```bash
php artisan tinker
```
```php
// Resetar artigos travados
use Src\InfoArticleGenerator\Infrastructure\Eloquent\GenerationTempArticle;

GenerationTempArticle::where('generation_status', 'generating')
    ->where('generation_started_at', '<', now()->subHours(1))
    ->update([
        'generation_status' => 'failed',
        'generation_error' => 'Timeout manual - resetado'
    ]);
```

### Se custo está muito alto:
```bash
# Ver distribuição de modelos usados
php artisan tinker
```
```php
use Src\InfoArticleGenerator\Infrastructure\Eloquent\GenerationTempArticle;

$distribution = GenerationTempArticle::whereNotNull('generation_model_used')
    ->selectRaw('generation_model_used, COUNT(*) as count, SUM(generation_cost) as total_cost')
    ->groupBy('generation_model_used')
    ->get();

foreach ($distribution as $row) {
    echo "{$row->generation_model_used}: {$row->count} artigos, {$row->total_cost} unidades\n";
}
```

---

## ✅ CHECKLIST DE VALIDAÇÃO FINAL

Após rodar os testes, verificar:

- [ ] Standard gera artigos com sucesso
- [ ] Intermediate escala falhas corretamente
- [ ] Premium só roda manualmente
- [ ] Custos estão conforme esperado (2.3x, 3.5x, 4.0x)
- [ ] JSONs gerados estão válidos
- [ ] Campos de categoria preenchidos corretamente
- [ ] Blocos obrigatórios presentes
- [ ] Logs estão sendo gravados
- [ ] Estatísticas estão corretas
- [ ] Nenhum artigo travado em "generating"

---

## 📞 Próximos Passos

Após validar tudo funcionando:

1. **Ativar schedule automático**:
   ```bash
   # Verificar se cron está configurado
   crontab -l
   ```

2. **Monitorar primeira execução automática**:
   ```bash
   tail -f storage/logs/claude-generation-*.log
   ```

3. **Ajustar configurações** baseado em resultados reais

4. **Documentar quaisquer issues** encontrados

---

**Status**: ✅ PRONTO PARA TESTES