-- ============================================================
-- PIGEA — patch «Fase B» (prop.md, decisões do cliente 2026-11-16)
-- Executar no phpMyAdmin após DUMP FRESCO (export completo).
-- Idempotente — pode correr mais do que uma vez sem danos.
-- ============================================================

-- 1) Utilizadores: coluna `must_change_password` (reset de senha obriga
--    a troca no próximo acesso — decisão do cliente).
ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `must_change_password` TINYINT(1) NOT NULL DEFAULT 0 AFTER `last_login_at`;

-- Regista a migração no tracking (o servidor não corre migrate).
INSERT IGNORE INTO `migrations` (`migration`, `batch`)
VALUES ('2026_11_16_010000_add_must_change_password_to_users_table', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`));

-- 2) Permissões novas (Spatie): `institucional.manage` (só admin) e
--    `newsletter.manage` (admin + editor).
INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT 'institucional.manage', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `name` = 'institucional.manage' AND `guard_name` = 'web');

INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT 'newsletter.manage', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `name` = 'newsletter.manage' AND `guard_name` = 'web');

-- Ligar permissões aos papéis: institucional → admin; newsletter → admin + editor.
SET @p_inst = (SELECT `id` FROM `permissions` WHERE `name` = 'institucional.manage' AND `guard_name` = 'web');
SET @p_news = (SELECT `id` FROM `permissions` WHERE `name` = 'newsletter.manage' AND `guard_name` = 'web');
SET @r_admin = (SELECT `id` FROM `roles` WHERE `name` = 'admin' AND `guard_name` = 'web');
SET @r_editor = (SELECT `id` FROM `roles` WHERE `name` = 'editor' AND `guard_name` = 'web');

INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
  (@p_inst, @r_admin),
  (@p_news, @r_admin),
  (@p_news, @r_editor);

-- 4) Verificação final — deve devolver tudo «ok».
SELECT 'coluna must_change_password (1=ok)' AS verificacao, COUNT(*) AS resultado
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='must_change_password';
SELECT 'permissoes novas (2=ok)' AS verificacao, COUNT(*) AS resultado
  FROM `permissions` WHERE `name` IN ('institucional.manage','newsletter.manage');
SELECT 'ligacoes (3=ok)' AS verificacao, COUNT(*) AS resultado
  FROM `role_has_permissions` rp
  JOIN `roles` r ON r.id = rp.role_id
  JOIN `permissions` p ON p.id = rp.permission_id
  WHERE p.name IN ('institucional.manage','newsletter.manage');
-- 5) Limpar a cache de permissões (se o host tiver Terminal, faz também: php artisan optimize:clear)
--    O Spatie guarda permissões/roles em cache do Laravel — sem isto, as novas
--    role_has_permissions podem demorar até ~24h a propagar-se.
--
-- FIM DO PATCH — verificação devolve tudo «ok».

