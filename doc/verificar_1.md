# 🔍 Prompt de Auditoria Final e Fecho de Funcionalidades

## 🎯 Objetivo
Verificar o estado atual do código (baseado em `fase_agora.md`) e implementar as funcionalidades que ainda faltam para o lançamento, garantindo que a segurança e a gestão editorial estão completas.

## 📋 1. Auditoria de Segurança (Rotas)
- **Rota de Login:** O sistema atual usa `/login` ou já está a ler a variável `PIGEA_LOGIN_PATH` do `.env`? Se não estiver, implementar a rota dinâmica e desativar o `/login` fixo.
- **Rota do Admin:** Confirmar que o acesso é feito via `ADMIN_PATH` e que `/admin` retorna 404.
- **Rate Limiting:** Confirmar se o bloqueio de 5 tentativas falhadas está ativo.

## 📋 2. Auditoria do Editor de Notícias
- **Gestão de Destaques:** O formulário de notícias tem um toggle visual para `destaque_editorial`? Se não, adicionar um switch na barra lateral direita.
- **Sistema de Autores:** O editor permite escolher um autor interno (com foto) ou um autor convidado? Se não, implementar a lógica de fallback:
  - Se tiver foto → mostrar a foto.
  - Se não tiver → mostrar `images/logo.png`.
  - A assinatura deve aparecer no final do artigo público.
- **Preview:** O botão "Pré-visualizar" está funcional? (Abre um modal com o layout final).

## 📋 3. Ajustes Finais no Frontend
- **Home (Hero):** Confirmar que o `heroNews()` está a ordenar primeiro pelos `destaque_editorial = true` e depois pelos mais recentes.
- **Sidebar (Tags):** Confirmar que as tags são dinâmicas (calculadas) e não hardcoded.
- **Projetos:** O documento diz que `PROJECTS_PUBLICO=false`. Confirmar que a rota `/projetos` no site público está a dar 404 (conforme decisão do cliente).

## 📋 4. Checklist de Implementação
Para cada ponto acima que **não estiver** implementado, aplicar a correção. Para os que **já estiverem**, reportar como "OK" no relatório final.

## ✅ Critérios de Aceitação
- [ ] `/login` dá 404; a rota definida no `.env` funciona.
- [ ] O editor tem o toggle de "Destacar na Home".
- [ ] A assinatura do autor (com foto/fallback) aparece no artigo.
- [ ] O botão de Preview abre o modal correto.
- [ ] A home mostra primeiro os destaques, depois os recentes.