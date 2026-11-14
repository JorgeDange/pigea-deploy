# PIGEA — Problema de envio de email (produção)

> Documento de continuidade. Registado em 2026-11-14.
> Estado no fecho: **por resolver/validar** — vê secção 9.

---

## 1. Contexto

A aplicação (Laravel 12, em `https://pigea.ao`) usa email em três lugares:

| Onde | O quê |
|---|---|
| `NewsletterController@subscribe` (público) | Email de confirmação (dupla opt-in) ao subscrever |
| `NewsletterAdminController@reenviarConfirmacao` / «confirmar todos» | Reenvio manual da confirmação |
| `ContactForm` / Fortify (recuperar password) | Mensagem de contacto / reset de password |

Todos os envios são **síncronos** (`Mail::to()->send(...)`, sem fila) — não é necessário `queue:work`.

## 2. Sintoma original

O utilizador reportou que «o envio de email não funciona». Em produção, toda a tentativa de
envio terminava num erro visível na página (500) para os botões de reenvio da newsletter.

## 3. Linha do tempo dos erros (do `storage/logs/laravel.log`)

| # | Data/hora | Erro (linha principal) | Causa raiz |
|---|---|---|---|
| 1 | 2026-11-14 | *(nada saía)* | `.env` do servidor com `MAIL_MAILER=log` — tudo ficava em `storage/logs/laravel.log`, nada era entregue. Configuração inicial de segurança até ter SMTP real. |
| 2 | 2026-11-14 22:21 | `getaddrinfo for smtp.pigea.ao failed: Name or service not known` | `MAIL_HOST=smtp.pigea.ao` — **o host não existia no DNS** (verificado publicamente: `smtp.pigea.ao` sem registo A). |
| 3 | 2026-11-14 | `mail.qnbangola.ao` — DNS existe mas domínio descontinuado | Decisão do cliente: o domínio de produção passa a ser **apenas `pigea.ao`** («esqueça o pigea.qnbangola.ao»). |
| 4 | 2026-11-14 22:37/22:38 | `535 Incorrect authentication data` (authenticators LOGIN e PLAIN) | Já havia ligação TLS e diálogo SMTP, mas a password recusada. Confirmado por via webmail (`https://pigea.ao/webmail`) que a **conta existe** e a credencial do utilizador é outra/parte mal. |
| 5 | 2026-11-14 (fim) | *(pendente de validação)* | A password tem **caracteres especiais**. A solução técnica: **aspas simples** no `.env`. Ainda não validado contra a produção. |

## 4. Estado DNS do `pigea.ao` (verificado 2026-11-14)

| Registo | Valor | OK? |
|---|---|---|
| A `pigea.ao` | 102.219.126.171 | ✔ |
| A `mail.pigea.ao` | 102.219.126.171 (mesmo IP — cPanel padrão) | ✔ |
| A `www.pigea.ao` | 102.219.126.171 | ✔ |
| A `smtp.pigea.ao` | — | ✘ não existe |
| MX `pigea.ao` | `pigea.ao` (prio 0) | ✔ |
| SPF `pigea.ao` | `v=spf1 +a +mx +ip4:102.219.126.171 ~all` | ✔ |
| DKIM | **não confirmado** — verificar no cPanel → «Email Deliverability» | ⚠ |

O servidor da aplicação e o mail coexistem no domínio; SPF cobre o relay. Para não cair em SPAM, falta confirmar DKIM.

## 5. Configuração de referência (`.env` do servidor)

```dotenv
APP_URL=https://pigea.ao
MAIL_MAILER=smtp
MAIL_HOST=mail.pigea.ao
MAIL_PORT=465
MAIL_SCHEME=smtps        # porta 587 → MAIL_SCHEME=smtp
MAIL_USERNAME=newsletter@pigea.ao
MAIL_PASSWORD='<password com aspas simples se tiver caracteres especiais>'
MAIL_FROM_ADDRESS=newsletter@pigea.ao
MAIL_FROM_NAME=PIGEA
```

Regras:
- `MAIL_USERNAME` e `MAIL_FROM_ADDRESS` têm de ser **iguais** (o relay recusa remetentes não autorizados — «sender not authorized»).
- O projecto lê **`MAIL_SCHEME`** — a chave antiga `MAIL_ENCRYPTION` é **ignorada** neste Laravel 12 (`config/mail.php` → `'scheme' => env('MAIL_SCHEME')`).
- **Aspas simples** são literais no `.env` (nada é interpolado). Aspas duplas interpretam `${...}`. Se a password tiver aspas simples, muda-a no cPanel para uma sem elas.
- Não pode haver **linhas duplicadas** (`MAIL_PASSWORD`, `MAIL_USERNAME`, `MAIL_MAILER`) — a última ganha silenciosamente.

## 6. Passo obrigatório após qualquer edição do `.env`

No Gestor de Ficheiros do cPanel: **apagar `bootstrap/cache/config.php`** (e `services.php` / `packages.php`, se existirem). Enquanto existirem, o Laravel ignora o `.env`.

## 7. Como ver o motivo real no servidor

`storage/logs/laravel.log` (Gestor de Ficheiros → mais recente). Procurar por:
- `production.ERROR` + `535` → credenciais recusadas;
- `Connection could not be established` → DNS/porta/bloqueio de saída;
- `local.ERROR` em teste → erro de código.

## 8. Notas e riscos registados

- Passwords antigas expostas nesta e em sessões anteriores (base de dados `qnbangolaco_pigea`, `APP_KEY` original). **Rodar ambas** quando o site estabilizar.
- Nenhuma credencial foi gravada no Git. O `.env` e o `.env.production` locais continuam fora do Git.
- `.env.example` foi alinhado para `APP_URL=https://pigea.ao` (commit pendente de autorização).
- O domínio `pigea.qnbangola.ao` deixou de ser o alvo; o `/laravel` deixou de existir.
- Em **desenvolvimento**, `.env` usa `MAIL_MAILER=log` — nada sai; autenticação não é necessária localmente.

## 9. Estado e próximos passos

1. **Validar**: após pôr a password com aspas simples e apagar `bootstrap/cache/config.php`, clicar em «Reenviar confirmação» e confirmar receção (o email pode cair em SPAM até o DKIM ficar OK).
2. Confirmar **DKIM** em cPanel → Email Deliverability → `pigea.ao` (ligar com o botão, se estiver desligado).
3. Considerar **trocar a password** da `newsletter@pigea.ao` para uma só com letras/números (elimina problemas de `.env` para sempre).
4. Se a saída para o **465** for bloqueada pelo ISP, tentar **587 + `MAIL_SCHEME=smtp`**; em último caso, relay externo (Mailgun/SES/Resend).
5. Regressão visual ligada ao email: a página 500 no «reenviar» poderia ser convertida em mensagem amigável (try/catch) — **tarefa por agendar**, não foi feita.
6. Evento exposto: nenhuma password deve aparecer em memória/docs. Caso chegue a aparecer, rodar imediatamente.
