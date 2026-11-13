
---

## FASE N — Módulo de Notificações (opção B) ✅ (implementada 2026-11-13)

**Pedido do cliente:** "a modal de notificação deve ter dados reais" — escolha no questionário guiado: **Opção B — módulo completo** (não só mostrar contagens, nem só melhorar o painel).

**O que é (novo, não nada estático):**
- **Tabela `notifications`** (Laravel polimórfica, id UUID) — cada linha = notificação deste utilizador. `data` guarda icone/titulo/texto/href; `read_at` null = não lida.
- **Isto é um histórico real de eventos** — a partir de 2026-11-13 as notificações nascem nos instantes em que algo acontece:
  - nova **empresa** submetida (registo fica `pendente`) → notificação para admin+editor;
  - novo **profissional** submetido (pendente) → notificação para admin+editor;
  - nova **notícia publicada** → notificação para admin+editor;
  - notícia enviada para **revisão** → notificação para admin+editor;
  - novo **utilizador** da equipa criada → notificação para admin+editor;
  - novo **projeto publicado** → notificação SOMENTE quando a área pública de Projetos estiver ligada (`config('features.projects_publico') === true`) — hoje está excluída.
- **Notificações persistidas para cada destinatário** — permite "lida/não lida" e «Ver todas as não lidas».
- No painel o **sino mostra as não lidas deste utilizador** (não um número de moderações em espera); cada item mostra «Nova» (vermelha) até ser lida; o botão **«Marcar todas lido»** (aparece no cabeçalho do modal só quando há pelo menos uma não lida) marca todas de uma vez.

**Implementação:**
- Migração `2026_11_13_000500_create_notifications_table.php`.
- `app/Notifications/EventoPainel.php` — classe única de notificação (via `database`).
- `app/Support/Notificador.php` — ponto único: `Notificador::enviarParaEquipa(icone, titulo, texto, href)` envia a **todos** os utilizadores com role `admin` ou `editor` (UUID por destinatário, nunca dados pessoais).
- `app/Providers/AppServiceProvider::registarObservers()` — observadores dos modelos: Company, Professional, User, News (created + updated), Project (só se a área de Projetos estiver pública). Os observadores disparam para qualquer caminho de criação (controladores, seeders, tinker).
- `App\Support\NotificacoesPainel::obter()` — lê a tabela (últimas 8), scoped ao utilizador autenticado; `naoLidas()` alimenta o badge do sino.
- `partials/notificacoes` — estado não lido («Nova») + estado lido; o modal no `layouts/dashboard` mostra «Marcar todas lido» quando há ≥1 não lida; `POST /gestao-pigea/notificacoes/ler` (admin+editor) = `NotificacoesController@lerTodas`.
- `dashboard-header` — o sino mostra `naoLidas()` do utilizador autenticado (zero ou pequeno count real; antes mostrava moderações em espera).
- CSS `.notif-badge-nova` em `dashboard.css` → `admin.css` reconstruído.

**CA:**
- [x] Cada evento aumenta as não lidas do utilizador (teste `test_empresa_criada_gera_notificacao_para_a_equipa`, `test_noticia_publicada_gera_notificacao`)
- [x] «Marcar todas lido» zera as não lidas e incrementa as lidas (teste `test_marcar_todas_como_lido`)
- [x] Anónimo redireciona; editor não pode marcar não-lido de outros utilizadores
- [x] Dados reais — nada hardcoded; cada item é persistido no momento do evento
- [x] Suíte: **261 testes / 885 assertions** verde; `view:cache` limpo

**Nota importante (honesta):** as 22 notificações existentes no dev foram geradas **agora** por 2 eventos reais disparados via vosso `artisan tinker` desta sessão (uma empresa `VulcanGeo — SL` criada por `firstOrCreate`; uma notícia passada a `em_revisao` via `update`). Em produção, só nascem eventos reais (a seed da base de testes `pigea_test` nunca passa por aqui — é recriada por teste). Se os 11 destinatários por ação parecer muito, basta ajustar `Notificador::enviarParaEquipa()`.
