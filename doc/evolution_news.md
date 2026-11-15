# PIGEA — Evolução da Newsletter (FASE N2, "Newsletter Pro") — PENDENTE

> **Estado (2026-11-15):** pendente de decisão do cliente. Não implementado.
> Este documento descreve a visão completa para quando for aprovada.
> Nada do que está aqui foi pedido para ser feito.
> Fonte: descrição do cliente em conversa de 2026-11-15 (email funciona, envio manual pelo painel, limite de 100 emails por envio, prazos de subscrição curtos).

---

## 1. Objectivo

Evoluir o módulo de Newsletter actual (N1 — subscrição com dupla confirmação, lista de subscritores e exportação CSV) para um módulo **activo de envio controlado pelo próprio painel**:

- compor e enviar edições de email para os subscritores a partir do admin;
- respeitar um **limite de 100 emails por envio** (restrição do fornecedor SMTP / hospedagem);
- **gerir o prazo de subscrição** (mensal / trimestral / anual) com avisos automáticos de expiração e renovação — o painel mostra quem está a expirar e quem expirou;
- controlar manualmente o que sai (nunca automático por publicação, salvo decisão explícita).

---

## 2. Estado actual (quando este documento foi escrito)

| Peça | Situação |
|---|---|
| Tabela `newsletter_subscribers` | Existe (`pending` / `confirmed` / `unsubscribed`); token único; sem colunas de expiração |
| Envio | Apenas o email de **confirmação da subscrição** (dupla opt-in); nenhum envio de edição/edição do portal |
| Cron / queue | Nada configurado no servidor (`queue:work` não corre; não há `newsletter_editions` nem `jobs` de newsletter) |
| SMTP | Funcional na produção (`pigea.ao`); o cliente autoriza a conta `newsletter@pigea.ao` como remetente |
| Prazo de subscrição | Não controlado; não há renovação nem avisos de expiração |
| Painel admin | `/gestao-pigea/newsletter` — KPIs (Total/Confirmados/Pendentes/Cancelados), confirmar/cancelar/reabilitar, reenviar confirmação, adicionar/remover, exportar CSV |

## 3. Especificação funcional proposta

### 3.1 Envio controlado pelo painel ("edições")

- Novo ecrã `/gestao-pigea/newsletter/enviar` (admin/editor):
  - **Assunto** e **corpo** (campo de texto com HTML básico ou Markdown — preview antes de enviar);
  - destinatários: **apenas subscritores com status `confirmed` e não expirados**;
  - botão **"Enviar edição"** com confirmação própria (`PIGEAConfirm`) com a contagem de destinatários;
  - cria uma edição (`newsletter_editions`) com `status = pending` e dispara jobs.
- Lista de edições `/gestao-pigea/newsletter/edicoes` (histórico):
  - coluna com assunto, data/hora, ambiente (estado), total/enviados/falhados;
  - ações: **Retomar** (se pausada), **Reenviar aos que falharam**, **Ver relatório** (CSV "email | estado | erro");
  - opção **"Usar último artigo"** (preencher assunto/corpo/imagem a partir da notícia mais recente) — opcional, UX.
- **Nunca** enviar automaticamente por publicação sem decisão explícita do cliente.

### 3.2 Gestão de limites (máx 100 por execução)

O envio por lotes é obrigatório para não violar o limite do fornecedor (assunção: "100 emails por envio/lote", a confirmar com o cliente/fornecedor na decisão).

Arquitetura proposta:

1. Tabela `newsletter_editions`:
   - `id`, `assunto`, `corpo_html` (preview), `total`, `n_enviados`, `n_falhados`, `estado` (`pending|sending|paused|done|failed`), `criado_por`, `created_at`, `finished_at`.
2. Tabela `newsletter_edition_items` (opcional, para relatório por destinatário) ou tabela de jobs de Laravel (`jobs`) com um job por destinatário+lote.
3. Um job `SendNewsletterChunk` envia **100 emails** no máximo por execução; cada job **enfileira o próximo lote** enquanto houver destinatários pendentes — padrão "fila auto-encadeada" genérico.
4. `queue:work` precisa de correr em background no servidor (ver secção 5 — requisito obrigatório).
5. Cada envio regista sucesso/falha por destinatário; falhas ficam guardadas para reenvio manual.

### 3.3 Prazo de subscrição e renovação

Modelo de prazo (proposto; a confirmar pelo cliente):

- Prazo **global** do portal: todos os subscritores têm o mesmo período (ex.: **anual** — 12 meses a partir da confirmação). Alternativa mais complexa: prazo escolhido por subscritor no acto de subscrição (mensal/trimestral/anual).
- Coluna `expires_at` em `newsletter_subscribers` preenchida no momento da confirmação (e na renovação).
- É sugerido `status` adicional: **`expired`** (subscrição vencida) — diferente de `unsubscribed` (o cliente cancelou).
  - `confirmed` → `expired` quando `expires_at < hoje` (rotina diária).
  - `expired` → `confirmed` na renovação (manual ou automática pelo token de renovação).

Avisos automáticos por email:

1. **Aviso de expiração próxima** — X dias antes (configurado no admin, ex.: 7 e/ou 30 dias);
2. **Aviso de expiração efetiva** — no dia em que a subscrição expira;
3. **Confirmação de renovação** — após renovação/actualização ("a tua subscrição foi renovada até <data>").

Renovação — opções (a decidir pelo cliente):
1. **Link único** (ou botão) no email de aviso → 1 clique renova automaticamente mais um período (mais conveniente; recomendada);
2. **Renovação manual** no painel — o admin clica "Renovar" na linha do subscritor;
3. **Re-confirmação** — a renovação exige novo opt-in de 2 passos (mais estrita; não recomendada).

### 3.4 Visibilidade no painel (KPIs e gestão)

Card no painel `/gestao-pigea/newsletter`:

- "A expirar nos próximos 30 dias" — clique abre lista;
- "Expirados";
- "Próxima expiração global" (se configurado global).

Na tabela/subscritor:

- coluna `expires_at` formatada (data) com destaque vermelho quando expirada, laranja quando expira em ≤ 30 dias;
- ações por linha: **Renovar**, **Reenviar aviso de expiração**, **Expiar manualmente**;
- filtros: Todos / A expirar / Expirados.

### 3.5 Emails ao subscritor

Modelos novos (com o tema PIGEA, HTML, "logo embutido" e replyTo claro):

1. **«A tua subscrição está a expirar»** — texto + link único com token (`/newsletter/renovar/{token}`);
2. **«A tua subscrição expirou»** — texto + link para renovar;
3. **«A tua subscrição foi renovada»** — confirmação, validade nova.
4. **Edição da newsletter** — a que for composta no painel (3.1).

## 4. Configuração no admin (secção própria «Newsletter»)

- **Prazo**: [ Mensal | Trimestral | Anual ] (default: Anual);
- **Avisar quantos dias antes**: [ 3 | 7 | 14 | 30 ] (default: 7);
- **Mostrar renovação manual no envio?** sim/não;
- **Email remetente**: `newsletter@pigea.ao` (default) ou outro.

## 5. Pré-requisitos / requisitos técnicos

| Requisito | Estado | Notas |
|---|---|---|
| SMTP funcional na produção | ✅ Funciona | `pigea.ao` confirmado; conta `newsletter@pigea.ao` |
| **Cron** (`schedule:run`) | ❌ Falta configurar | Sem ele, os lotes e avisos não correm. É o **bloqueante** |
| **Fila** (`queue:work`) | ❌ Falta configurar | Pode ser a mesma cron que executa a fila: `schedule:run` invoca a fila se o job estiver configurado como `$queued` adequadamente; em alternativa `queue:work --stop-when-empty` via cron a cada minuto |
| Migrações novas | ❌ Criar | `newsletter_editions`; colunas `expires_at`, extensão do enum `status` para `expired` |

**Nota .env de produção**: confirmar que `QUEUE_CONNECTION=database` e que a tabela `jobs` (já existe) tem o índice apropriado — no deploy manual FTP cPanel basta configurar uma entrada no Cron do alojamento: `* * * * * /opt/alt/phpXX/usr/bin/php ~/public_html/pigea-deploy/artisan schedule:run >> /dev/null 2>&1`. Isto envia/executa tudo o que estiver agendado, incluindo a fila e as rotinas de expiração.

## 6. Decisões que faltam ao cliente (por fazer)

1. Nenhuma tecnologia — isto é **produto/política**:
   - prazo de subscrição a usar (mensal/trimestral/anual; global ou por subscritor);
   - o que acontece ao expirar: email de aviso antes? quantos dias antes? — sim/não e quantos;
   - como se renova: link mágico de 1 clique ou pedido manual pelo painel?
2. O envio é só para `confirmed` ou também para `confirmed` + `expired` (os expirados recebem re-engajamento)?
3. Quem compõe a edição? Admin+editor? Só admin?
4. Os avisos de expiração são enviados sempre pela mesma conta `newsletter@pigea.ao`?
5. Cron no alojamento: têm acesso à seção "Cron Jobs" do cPanel (1 entrada por minuto)?
6. Limite de 100: é por envio (lote) ou por minuto/hora no fornecedor SMTP? Se for por hora, o modelo de envio muda (esperas entre lotes).
7. O conteúdo actual vem de onde? (último artigo? lista de artigos noutra ordem? sempre editorial?)

## 7. Riscos conhecidos

- **SPAM/reputação**: enviar centenas de emails a uma hora de reputação baixa pode marcar a conta como spam. Recomendação: enviar pouco, nunca "reativadas" sem validação; reforçar o "opt-out" simples em todos os emails.
- **Renovação por link** em vez de re-confirmação: conveniência sobre rigor — decisão do cliente.
- **Teste antes de produção**: com o volume actual de subscritores (pequenos), testar primeiro «simular prazo» + "toast para o 1º e 10º" letra de corte, antes de activar avisos a todos.
- **Sem cron, nada disto corre** — a decisão de avançar obriga a configurar cron a correr a fila + rotinas.

## 8. Ordem de implementação sugerida (quando aprovada)

1. Migrações (`expires_at`, enum `expired`, `newsletter_editions`) + `phpunit` actualizado;
2. Painel admin «Enviar edição» (composer + preview + envio em lote via fila);
3. Fila e cron no servidor (exemplo, e testar a enviar "teste");
4. Rotinas diárias de expiração (aviso prévio + expiração efectiva);
5. KPIs/listagem reforçados (A expirar / Expirados);
6. Testes automatizados; documentação final.
7. **Só depois, em produção**, activar os avisos automáticos (app de teste → produção com monitorização diária).

## 9. Dependência de documentação existente

- `doc/newsletter.md` — N1 (gestão, opt-in, KPIs, schema atual);
- `docs.md` — decisões globais (ax anti-bot, pin editorial, banners largo);
- `email.md` — histórico SMTP; confirmar que o SMTP usado é a conta `newsletter@pigea.ao` e não outra.
