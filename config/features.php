<?php

/*
 * Interruptores de funcionalidade do portal (sem depender de pacotes).
 *
 * projects_publico (PROJECTS_PUBLICO, default false):
 *   "inicialmente a área de projetos fica excluída" (decisão do cliente,
 *   2026-11-13) — a área pública /projetos e /projetos/{slug} devolve 404;
 *   os links públicos também desaparecem (ver MenuCategorias/footer).
 *   O painel admin (gestão da lista/formulário) NÃO depende deste flag.
 *   Para reativar: PROJECTS_PUBLICO=true no .env (produção) e voltar a
 *   ligar o item em MenuCategorias/footer, ou simplesmente definir true.
 *   O phpunit.xml define true nos testes (a suite cobre as páginas).
 */

return [
    'projects_publico' => (bool) env('PROJECTS_PUBLICO', false),
];
