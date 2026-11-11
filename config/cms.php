<?php

/**
 * Fase 6 (cms_v.md) — interruptores do fluxo editorial do CMS.
 *
 * editor_pode_publicar:
 *   true  → a permissão `news.manage` basta para publicar (fluxo atual, permissivo);
 *   false → apenas `admin` (ou quem tiver permissão de publicado) publica;
 *           editores submetem para revisão (status `em_revisao`) e o admin
 *           publica/devolve/despublica a partir da lista/editor.
 *
 * Alterar estas flags NUNCA altera o papel de segurança: quem publica sem
 * permissão continua bloqueado no controlador (defesa em profundidade).
 */
return [
    'editor_pode_publicar' => (bool) env('EDITOR_PODE_PUBLICAR', true),
];
