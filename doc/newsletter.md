# 📰 Newsletter do PIGEA

> Documento vivo — a fonte da verdade da Newsletter do portal PIGEA:
> o que existe, o que está planeado, como está integrado e como operar.
>
> Última atualização: 2026-11-13 (FASE N1 implementada — ver secção 7).

## 1. Localização no código

| Parte | Caminho |
|---|---|
| Documento de plano/estado | `doc/newsletter.md` (este ficheiro) |
| Fases de implementação (registo do cliente) | `doc/fases_e.md` |
| Base de dados | tabela `newsletter_subscribers` |
| Modelo | `app/Models/NewsletterSubscriber.php` |
| Envio de email (confirmação) | `app/Mail/NewsletterConfirmation.php` + `resources/views/emails/newsletter-confirmacao.blade.php` |
| Controlador público | `app/Http/Controllers/Frontend/NewsletterController.php` |
| Formulário público (página de Contactos) | `resources/views/components/newsletter-form.blade.php` |
| **Modal** (todas as páginas públicas) | `resources/views/components/newsletter-modal.blade.php` + `public/js/newsletter-modal.js` |
| Admin (gestão de subscritores) | `app/Http/Controllers/Admin/NewsletterAdminController.php` + `resources/views/admin/newsletter.blade.php` |
| Exportação CSV | rota `admin.newsletter.exportar` (StreamedResponse, BOM UTF-8) |

## 2. Decisões do cliente (2026-11-13)

| Decisão | Escolha |
|---|---|
| Tipo de newsletter | **Opção B — newsletter dentro do próprio site** (sem Mailchimp/Brevo) |
| Mecanismo de subscrição | **Modal em todas as páginas** (padrão dos sites de notícias) — aparece ~3s após cada carregamento; só desaparece depois de a pessoa subscrever |
| Rodapé | **Removida a caixa do rodapé** — pedido do cliente (a modal é o veículo) |
| Contactos | **Mantém o cartão «Receba as novidades»** na página /contactos |
| Campos do formulário | **Só o EMAIL** (a pedido do cliente — o nome foi descartado do fluxo) |
| Dupla opt-in | **Sim** — só entra na lista após clicar na ligação enviada por email |
| Fechar a modal | Esconde **apenas para a visita actual**; reaparece na próxima página — deliberado (é um pedido, não um pop-up persuasivo) |
| Persistência após subscrição | `localStorage[«pigea_newsletter_ok»] = 1` — não volta a aparecer **nunca** neste navegador |
| Transporte de email (dev) | `MAIL_MAILER=log` (as confirmações ficam no `storage/logs/laravel.log` para validar sem enviar email real) |
| Transporte de email (produção) | `MAIL_MAILER=smtp` + credenciais reais (ex.: Brevo, Mailjet, SendGrid ou relay próprio) |

## 3. FASE N1 — O que o utilizador vê hoje

### 3.1 No site (público)

- **Modal «Receba as novidades do PIGEA»** em **todas as páginas públicas**: aparece ~3 segundos após o carregamento, com campo **Email** + botão «Subscrever» + texto curto e um «✕» no canto superior direito.
- A modal **só desaparece de vez depois de subscrever** (`localStorage` próprio). Se a pessoa usar o «✕», esconde-se na visita atual e volta à próxima página — deliberado (padrão pedido pelo cliente).
- A **página de Contactos** (`/contactos`) mantém o cartão equivalente «Receba as novidades».
- O formulário submete por POST AJAX (progressive enhancement: sem JS também funciona como POST normal com flash).
- A mensagem de feedback («Verifique o seu email — enviámos-lhe o link de confirmação» / erros) aparece por cima do formulário; na modal, o formulário é substituído por «Verifique o seu email».
- **Design**: a modal não bloqueia o conteúdo de leitura (não é 100% da altura) e tem backdrop suave.
- O formulário público **só aceita email**; nada de nome (decisão do cliente).

### 3.2 Fluxo de subscrição (dupla opt-in)

1. Visitante carrega a página → a **modal** aparece após ~3s (todas as páginas públicas).
2. Preenche o Email e «Subscrever» → **POST `/newsletter`** (progressive enhancement: sem JS funciona igual, com flash).
3. O sistema cria/atualiza o registo como **`pending`** (por omissão) com um `token` aleatório de 64 carateres.
4. O sistema envia **um email de confirmação** com a ligação `GET /newsletter/confirmar/{token}`.
5. Ao clicar na ligação:
   - token válido → `status` = `confirmed`, `confirmed_at` preenchido; mostra a página de «Obrigado — subscrição confirmada»;
   - token inválido/expirado → página de erro amigável;
   - já confirmado → página neutra «A sua subscrição já estava confirmada».
6. **Cancelamento**: `GET /newsletter/cancelar/{token}` → `status` = `unsubscribed` (link que acompanhará os emails da N2 e pode ser usado a qualquer momento).
7. Um email já com `unsubscribed` → voltar a subscrever cria novo `pending` (regra deliberada).
8. Email já `confirmed` → a subscrição responde «Este email já está subscrito» sem duplicar.
9. **A modal não volta a aparecer**: submissão (POST) com sucesso → `localStorage[«pigea_newsletter_ok»]=1` em todo o navegador; pedido do cliente ("modal que aparece sempre que o user carrega uma das páginas do site e não aparece quando já se fez a submissão ou subscrição").

### 3.3 No painel (admin/editor)

- **`/gestao-pigea/newsletter`** — visível apenas para **admin** (dados de contacto são PII; o editor não acede).
- Blocos: **confirmados / pendentes / cancelados** (números reais, em destaque).
- **Tabela** com filtro por estado + pesquisa por email + paginação; por linha: email, estado, desde quando, ação (remover).
- **Adicionar manualmente** um email (campanhas presenciais/anúncios — marcado como `confirmed`).
- **Remover** remove a linha (delete permanente).
- **Exportar CSV**: descarrega `newsletter-subscritores.csv` (BOM UTF-8; colunas Email, Confirmado em, Criado em) — apenas confirmados.
- Aparece no menu lateral, bloco **Sistema** (admin), com ícone `fa-envelope`.

## 4. Arquitetura técnica (N1)

### 4.1 Tabela `newsletter_subscribers`

| Coluna | Tipo | Notas |
|---|---|---|
| `id` | bigint PK | |
| `email` | string(190) UNIQUE | chave funcional da lista (**único dado pessoal**) |
| `token` | string(64) index | token único por regra de negócio (confirmar/cancelar) |
| `status` | enum(`pending`,`confirmed`,`unsubscribed`) | default `pending` |
| `confirmed_at` | timestamp NULL | quando confirmou o opt-in |
| `created_at` / `updated_at` | timestamps | |

### 4.2 Rotas (públicas)

| Verbo | URL | Ação |
|---|---|---|
| POST | `/newsletter` | Criar/atualizar subscrição + enviar confirmação. **Throttle 10/min por IP.** Aceita JSON (`Accept: application/json`) → `{ok, mensagem}` ou 422 com erros por campo — a modal usa isto; sem JS usa flash de sessão |
| GET | `/newsletter/confirmar/{token}` | Confirmar (dupla opt-in) |
| GET | `/newsletter/cancelar/{token}` | Cancelar subscrição |

### 4.3 Administração

| Verbo | URL | Ação |
|---|---|---|
| GET | `/gestao-pigea/newsletter` | Página de gestão (lista + estatísticas + adicionar) |
| POST | `/gestao-pigea/newsletter/adicionar` | Adicionar manualmente (admin) |
| DELETE | `/gestao-pigea/newsletter/{id}` | Remover linha (admin) |
| GET | `/gestao-pigea/newsletter/exportar.csv` | Exportar CSV da lista confirmada (admin) |

### 4.4 Segurança e privacidade

- O `token` é gerado com `Str::random(64)` — não deriva do `id` nem do email; não é sequencial; não fica em logs além do email enviado.
- Nenhuma página pública expõe a lista de subscritores (nenhum endpoint devolve-os além do painel autenticado).
- **PII**: o único dado pessoal é o email; visível só por admin; a exportação CSV (email, datas) também é só para admin.
- **GDPR-like**: cancelar funciona a qualquer momento pelo link único; com o botão de remoção no painel, o apagamento é permanente.
- Consent: o formulário público inclui a legenda «Ao subscrever aceitar receber as novidades do PIGEA por email. Pode cancelar a qualquer momento.»
- Se no futuro a PIGEA enviar newsletters com nome, um campo de «Eliminação a pedido» completo (direito ao apagamento) → processo manual, documentado aqui.

## 5. FASE N2 — Envio de edições (pendente; não está no repositório)

Plano proposto (será implementado quando o cliente der o OK):

- **Tela «Enviar edição»** no painel admin: assunto, conteúdo (TinyMCE), pré-visualização, «Enviar para todos os confirmados».
- **Queue**: e-mails enfileirados (`database` queue já configurada) com rate-limit (ex.: 50/min).
- **Rodapé de cada email**: link de «Cancelar» (`/newsletter/cancelar/{token}`) + referência do PIGEA.
- **Registo de envios** (tabela `newsletter_editions`): assunto, data, enviados, falhas — aparece no painel admin.
- **Produção**: exige `MAIL_MAILER=smtp` + credenciais reais do cliente + cron a correr `queue:work`. Sem isto, as edições falham silenciosamente — a N2 só fica «em produção» quando o cliente configurar o SMTP.
- O envio N1 **não envia newsletters reais** — apenas a confirmação em texto simples (tema, assunto, link).

## 6. Como verificar localmente (ambiente de desenvolvimento)

1. Abrir `/` — a **modal** aparece após ~3s; fechar (✕) esconde-a só para a visita; submeter (POST)→ nunca mais; `localStorage` limpo em DevTools → `Application » Local Storage » pigea_newsletter_ok → Delete` para a tornar a ver.
2. Escrever um email válido → «Subscrever» → a modal troca para «Verifique o seu email»; em caso de email já confirmado, a modal diz que já está subscrito.
3. Uma linha no `storage/logs/laravel.log` com o conteúdo do email de confirmação (`MAIL_MAILER=log`); a ligação de confirmação está dentro desse email.
4. Abrir a ligação `.../newsletter/confirmar/{token}` → ver a página de agradecimento; o registo passa a `confirmed`.
5. No painel `/gestao-pigea/newsletter` — ver a contagem subir; pesquisar/filtrar; adicionar manualmente; exportar CSV; remover.
6. Teste **sem rede**: pôr `MAIL_MAILER=log` (já é o default em dev) e ver a confirmação no log — nunca enviar email real fora de produção.

## 7. Estado de implementação

| Fase | Estado | Observação |
|---|---|---|
| **N1** — subscrição (só email) + modal + gestão + CSV | ✅ Implementada (2026-11-13) | Suite completa 257/857 verde (13 testes dedicados); captura `qa-newsletter-modal.png` na página inicial e `qa-newsletter-admin.png` no painel. Sem envio de edições (FASE N2). |
| **N2** — envio de edições pelo painel | ⏳ Pendente | Depende de: SMTP real em produção + OK do cliente; o dev só regista os envios nos logs |

## 8. Notas de operação

- **Ambiente de testes**: a base de dados é recriada do zero nos testes — o `RoleSeeder` continua válido; a tabela é criada pela migração `2026_11_13_000400_create_newsletter_subscribers_table.php`.
- **Email de teste**: subscrição real cria o registo `pending`; nunca envia email fora de produção.
- **Tema de email**: texto simples (sem HTML nem imagens), sujeito «Confirma a tua subscrição na newsletter do PIGEA», adequado a qualquer cliente de email.
- **Nada deste módulo toca em código existente**: rotas, vistas, CSS e JS são próprios; a modal vive no `layouts/app.blade.php` (como `components.aside-panel` e `cookie-bar`) e o formato público (só email) respeita o resto do site.
