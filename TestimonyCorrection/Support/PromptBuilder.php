<?php
namespace Src\TestimonyCorrection\Support;

class PromptBuilder
{
    public static function buildPrompt(array $drafts, $article): string
    {
        $title = $article->title ?? '';
        $tags = implode(', ', $article->tags ?? []);

        // Pequeno trecho de texto do artigo para contextualizar
        $summary = '';
        foreach ($article->content['blocks'] ?? [] as $b) {
            if (($b['block_type'] ?? '') === 'text') {
                $summary = substr(strip_tags($b['content']['text'] ?? ''), 0, 600);
                break;
            }
        }

        // SUPER PROMPT COMPLETO – OTIMIZADO PARA CLAUDE
        $rules = <<<PROMPT
Você é especialista em transformar depoimentos artificiais em relatos humanos, naturais e coerentes. 
Seu trabalho é pegar depoimentos gerados por IA e reescrevê-los como testemunhos reais, com autenticidade, coloquialismo e comportamentos que pessoas comuns têm.

====================================================================
🎯 OBJETIVO
Transformar os depoimentos enviados em versões:
- naturais
- verossímeis
- coloquiais
- coerentes com plataformas reais
- com emoções reais
- sem comportamento impossível
- sem linguagem de IA
- sem perfeccionismo técnico

====================================================================
📌 CONTEXTO DO ARTIGO
Título: {$title}
Tags relevantes: {$tags}

Resumo do artigo:
{$summary}

Use esse contexto apenas para orientar tom e coerência — não copie a linguagem do texto técnico para dentro do depoimento.

====================================================================
📌 COMO CORRIGIR OS DEPOIMENTOS

### 1) LINGUAGEM HUMANA E NATURAL
Use:
- “tava”, “tá”, “pra”, “uns”
- expressões emocionais variadas (“levei um susto”, “doeu no bolso”, “sinceramente”)
- frases naturais, sem formalidade
- variações reais (“pô”, “puts”, “olha”, “pra ser sincero”)
- Evitar repetição de “cara”, “mano” (usar no máximo 1 vez)
- Não usar termos técnicos demais

### 2) COMPORTAMENTO REALISTA
Nunca coloque ações irreais:
❌ trocar óleo de câmbio em casa  
❌ fazer análise técnica profissional  
❌ documentar tudo em planilha  
❌ fazer testes científicos  

Faça assim:
✔ comprar óleo e levar na oficina  
✔ perguntar em grupos  
✔ seguir dica de mecânico  
✔ falar de custo, impacto, emoção  

### 3) NARRATIVA NÃO PERFEITA
Evitar:
❌ passo a passo perfeito  
❌ datas exatas  
❌ números exatos artificiais  
❌ comparações técnicas demais  

Use narrativa solta, como alguém contando num comentário real.

### 4) PLATAFORMA (contexto) — MUITO IMPORTANTE
Cada depoimento DEVE ter **uma plataforma válida e coerente**.

Distribuição natural:
- 30% YouTube / TikTok  
- 25% Fóruns  
- 20% Oficinas  
- 15% Grupos WhatsApp / Telegram  
- 10% Reviews / Blogs  

### 5) REGRAS DE LOCALIZAÇÃO (O MAIS IMPORTANTE)
Analisar o depoimento e contexto e aplicar:

📌 **YouTube / TikTok**
- NÃO usar cidade-estado
- usar no máximo região se o texto mencionar (“sou do sul”)
- caso contrário → NÃO incluir localização

📌 **Facebook / Instagram**
- Pode usar cidade-estado
- Sempre coerente com o que o texto cita

📌 **Fóruns**
- Pode usar cidade-estado
- Deve ser coerente com cidades mencionadas no comentário

📌 **Oficinas**
- Sempre usar cidade-estado (cliente da oficina em X)

📌 **Grupos de WhatsApp/Telegram**
- Pode usar cidade-estado se fizer sentido, mas não obrigatório

📌 **Reviews / Blogs**
- Pode usar cidade-estado, mas evitar detalhes excessivos

📌 **Correção de incoerência**
Se o texto menciona:
- “calor de São Paulo”
- “voltando para Salvador”
- “fui até Recife”
→ O author DEVE refletir essa cidade.

Se houver conflito → usar a cidade mais mencionada.

### 6) CAMPO AUTHOR
Formato:
- “Nome X., Cidade-Estado”
- 80% sem idade
- 20% com idade apenas quando fizer sentido (ex: “Roberto, mecânico há 25 anos”)
- nunca nome completo com sobrenome inteiro
- nunca formato de ficha cadastral

### 7) CAMPO CONTENT
Estrutura final do bloco:

{
  "block_id": "id",
  "block_type": "testimony",
  "heading": "...",
  "content": {
    "quote": "texto corrigido e natural",
    "author": "Nome X., Local",
    "vehicle": "Carro (se vier do draft)",
    "context": "Texto sobre a plataforma e situação realista"
  }
}

### 8) PROIBIDO
❌ datas exatas (exceto quando forem naturais)  
❌ “documentamos”, “monitoramos”, “teste X meses”  
❌ “participou de estudo”  
❌ “relatórios laboratoriais detalhados”  
❌ “uso 60% urbano / 40% rodovia”  
❌ histórias perfeitas demais  
❌ linguagem técnica de IA  
❌ cidade incoerente  

### 9) SAÍDA OBRIGATÓRIA
- APENAS blocos “testimony”
- Um JSON **por linha**
- SEM explicações
- SEM texto adicional
- SEM comentários
- Apenas JSONL limpo e válido

PROMPT;

        return "Você corrigirá depoimentos artificiais.\n\n" .
               "TÍTULO DO ARTIGO: {$title}\n" .
               "TAGS: {$tags}\n\n" .
               $rules .
               "\n\nDEPOIMENTOS PARA CORREÇÃO:\n" .
               json_encode($drafts, JSON_UNESCAPED_UNICODE) .
               "\n\nRETORNE APENAS JSONL:\n";
    }
}
