-- ============================================================
-- PIGEA — patch de esquema e dados para a produção
-- Aplica as 30 migrações que nunca correram na produção.
--
-- ANTES DE EXECUTAR (obrigatório):
--   1) Backup completo da BD (phpMyAdmin → Exportar → SQL)
--   2) Correr Secção 1 (verificação) primeiro
--   3) Só depois a Secção 2 (patch)
-- ============================================================

-- ===== SECÇÃO 1 — PRÉ-VERIFICAÇÃO (correr obrigatoriamente primeiro) =====

SELECT IF(COUNT(*) > 0,
          CONCAT('PARAR: ', COUNT(*), ' NIFs DUPLICADOS'),
          'OK — pode prosseguir') AS verificacao_nif
FROM (SELECT nif FROM companies GROUP BY nif HAVING COUNT(*) > 1) t;

-- ===== SECÇÃO 2 — PATCH =====

-- 2026_11_10_104620_create_media_table
CREATE TABLE IF NOT EXISTS `media` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `model_type` VARCHAR(255) NOT NULL,
  `model_id` BIGINT UNSIGNED NOT NULL,
  `uuid` CHAR(36) UNIQUE,
  `collection_name` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `mime_type` VARCHAR(255),
  `disk` VARCHAR(255) NOT NULL,
  `conversions_disk` VARCHAR(255),
  `size` BIGINT UNSIGNED NOT NULL,
  `manipulations` JSON NOT NULL,
  `custom_properties` JSON NOT NULL,
  `generated_conversions` JSON NOT NULL,
  `responsive_images` JSON NOT NULL,
  `order_column` INT UNSIGNED,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  KEY `media_model_type_model_id_index` (`model_type`, `model_id`),
  KEY `media_uuid_index` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2026_11_10_120000_create_companies_table
CREATE TABLE IF NOT EXISTS `companies` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nome_empresa` VARCHAR(255) NOT NULL,
  `nif` VARCHAR(255) NOT NULL,
  `contactos` VARCHAR(255) NOT NULL,
  `areas_atuacao` JSON NOT NULL,
  `redes_sociais` JSON NULL,
  `status` ENUM('pendente', 'aprovado', 'rejeitado') NOT NULL DEFAULT 'pendente',
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  KEY `companies_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2026_11_10_120001_create_professionals_table
CREATE TABLE IF NOT EXISTS `professionals` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nome_completo` VARCHAR(255) NOT NULL,
  `formacao_academica` VARCHAR(255) NOT NULL,
  `contactos` VARCHAR(255) NOT NULL,
  `areas_atuacao` JSON NOT NULL,
  `status` ENUM('pendente', 'aprovado', 'rejeitado') NOT NULL DEFAULT 'pendente',
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  KEY `professionals_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2026_11_10_120002_create_directory_documents_table
CREATE TABLE IF NOT EXISTS `directory_documents` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(255) NOT NULL,
  `descricao` VARCHAR(500) NOT NULL,
  `tipo` ENUM('artigo', 'documento', 'lei') NOT NULL,
  `link_download` VARCHAR(255) NOT NULL,
  `areas_atuacao` JSON NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2026_11_10_120003_create_banners_table
CREATE TABLE IF NOT EXISTS `banners` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(255) NOT NULL,
  `imagem_url` VARCHAR(255) NOT NULL,
  `link_destino` VARCHAR(255) NULL,
  `posicao` ENUM('topo', 'sidebar', 'rodape') NOT NULL,
  `data_inicio` DATE NULL,
  `data_fim` DATE NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  KEY `banners_posicao_index` (`posicao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2026_11_10_120004_add_video_and_document_links_to_news_table
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `video_link` VARCHAR(255) NULL AFTER `image_caption`;
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `documento_link` VARCHAR(255) NULL AFTER `video_link`;

-- 2026_11_10_180000_add_soft_deletes_to_directory_documents_and_banners
ALTER TABLE `directory_documents` ADD COLUMN IF NOT EXISTS `deleted_at` TIMESTAMP NULL AFTER `areas_atuacao`;
ALTER TABLE `banners` ADD COLUMN IF NOT EXISTS `deleted_at` TIMESTAMP NULL AFTER `ativo`;

-- 2026_11_11_000000_add_unique_index_to_companies_nif
ALTER TABLE `companies` ADD UNIQUE INDEX `companies_nif_unique` (`nif`);

-- 2026_11_11_000001_drop_comments_count_from_content_tables
ALTER TABLE `news` DROP COLUMN IF EXISTS `comments_count`;
ALTER TABLE `projects` DROP COLUMN IF EXISTS `comments_count`;
ALTER TABLE `suppliers` DROP COLUMN IF EXISTS `comments_count`;
ALTER TABLE `events` DROP COLUMN IF EXISTS `comments_count`;
ALTER TABLE `legislation` DROP COLUMN IF EXISTS `comments_count`;
ALTER TABLE `research` DROP COLUMN IF EXISTS `comments_count`;
ALTER TABLE `interviews` DROP COLUMN IF EXISTS `comments_count`;
ALTER TABLE `technologies` DROP COLUMN IF EXISTS `comments_count`;
ALTER TABLE `directory` DROP COLUMN IF EXISTS `comments_count`;
ALTER TABLE `training` DROP COLUMN IF EXISTS `comments_count`;

-- 2026_11_11_000200_create_page_views_table
CREATE TABLE IF NOT EXISTS `page_views` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `date` DATE NOT NULL UNIQUE,
  `visits` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2026_11_11_100000_add_menu_fields_to_categories_table
ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `parent_id` BIGINT UNSIGNED NULL AFTER `id`;
ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `menu_group` VARCHAR(255) NULL AFTER `type`;
ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `order` INT UNSIGNED NULL AFTER `menu_group`;
ALTER TABLE `categories` ADD INDEX IF NOT EXISTS `categories_menu_group_index` (`menu_group`);
-- (foreign key propositadamente omitida: evita falha se categories já contém parent_id com dados)

-- 2026_11_11_180100_add_image_alt_to_news_table
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `image_alt` VARCHAR(255) NULL AFTER `image`;

-- 2026_11_11_181000_published_at_date_to_datetime_news_table
ALTER TABLE `news` MODIFY `published_at` DATETIME NULL;

-- 2026_11_11_201000_add_outros_category_to_news
INSERT IGNORE INTO `categories` (`name`, `slug`, `type`, `parent_id`, `menu_group`, `order`, `created_at`, `updated_at`)
VALUES ('Outros', 'outros', 'news', NULL, NULL, 999, NOW(), NOW());

-- 2026_11_11_202000_add_link_to_news_table
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `link` VARCHAR(255) NULL AFTER `documento_link`;

-- 2026_11_11_220000_promote_academia_oportunidades_to_news
UPDATE `categories` SET `slug` = 'investigacao-academia', `updated_at` = NOW()
WHERE `menu_group` = 'academia' AND `slug` = 'investigacao';

INSERT IGNORE INTO `categories` (`name`, `slug`, `type`, `parent_id`, `menu_group`, `order`, `created_at`, `updated_at`)
SELECT 'Academia', 'academia', 'news', NULL, NULL, 9, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE `type` = 'news' AND `parent_id` IS NULL AND `slug` = 'academia');

UPDATE `categories`
SET `type` = 'news', `parent_id` = NULL, `menu_group` = NULL, `order` = 9
WHERE `type` = 'news' AND `parent_id` IS NULL AND `slug` = 'academia';

INSERT IGNORE INTO `categories` (`name`, `slug`, `type`, `parent_id`, `menu_group`, `order`, `created_at`, `updated_at`)
SELECT 'Oportunidades', 'oportunidades', 'news', NULL, NULL, 10, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE `type` = 'news' AND `parent_id` IS NULL AND `slug` = 'oportunidades');

UPDATE `categories`
SET `type` = 'news', `parent_id` = NULL, `menu_group` = NULL, `order` = 10
WHERE `type` = 'news' AND `parent_id` IS NULL AND `slug` = 'oportunidades';

UPDATE `categories`
SET `type` = 'news',
    `parent_id` = (SELECT `id` FROM `categories` WHERE `type` = 'news' AND `parent_id` IS NULL AND `slug` = 'academia' LIMIT 1),
    `updated_at` = NOW()
WHERE `menu_group` = 'academia'
  AND `id` <> (SELECT `id` FROM `categories` WHERE `type` = 'news' AND `parent_id` IS NULL AND `slug` = 'academia' LIMIT 1)
  AND `parent_id` IS NULL;

UPDATE `categories`
SET `type` = 'news',
    `parent_id` = (SELECT `id` FROM `categories` WHERE `type` = 'news' AND `parent_id` IS NULL AND `slug` = 'oportunidades' LIMIT 1),
    `updated_at` = NOW()
WHERE `menu_group` = 'oportunidades'
  AND `id` <> (SELECT `id` FROM `categories` WHERE `type` = 'news' AND `parent_id` IS NULL AND `slug` = 'oportunidades' LIMIT 1)
  AND `parent_id` IS NULL;

-- 2026_11_12_090000_add_lateral_to_banners_posicao
ALTER TABLE `banners` MODIFY `posicao` ENUM('topo', 'sidebar', 'rodape', 'lateral') NOT NULL;

-- 2026_11_12_090000_create_destaques_table
CREATE TABLE IF NOT EXISTS `destaques` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(190) NOT NULL,
  `imagem_url` VARCHAR(2048) NOT NULL,
  `link_destino` VARCHAR(2048) NULL,
  `escopo` VARCHAR(16) NOT NULL DEFAULT 'site',
  `data_inicio` DATE NULL,
  `data_fim` DATE NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `ordem` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `deleted_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  KEY `destaques_escopo_ativo_index` (`escopo`, `ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2026_11_12_100000_add_guest_author_to_news_table
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `guest_author_name` VARCHAR(255) NULL AFTER `author_id`;
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `guest_author_avatar` VARCHAR(255) NULL AFTER `guest_author_name`;

-- 2026_11_12_130000_create_videos_table
CREATE TABLE IF NOT EXISTS `videos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(190) NOT NULL,
  `resumo` TEXT NULL,
  `url` VARCHAR(255) NOT NULL,
  `imagem` VARCHAR(255) NULL,
  `published_at` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  KEY `videos_published_at_index` (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2026_11_13_000100_add_destaque_editorial_to_news
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `destaque_editorial` TINYINT(1) NOT NULL DEFAULT 0 AFTER `date_label`;

-- 2026_11_13_000200_widen_projects_province
ALTER TABLE `projects` MODIFY `province` VARCHAR(120) NULL;

-- 2026_11_13_000300_add_largo_to_banners_posicao
ALTER TABLE `banners` MODIFY `posicao` ENUM('topo', 'sidebar', 'rodape', 'lateral', 'largo') NOT NULL;

-- 2026_11_13_000400_create_newsletter_subscribers_table
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(190) NOT NULL UNIQUE,
  `token` VARCHAR(64) NOT NULL,
  `status` ENUM('pending', 'confirmed', 'unsubscribed') NOT NULL DEFAULT 'pending',
  `confirmed_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  KEY `newsletter_subscribers_token_index` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2026_11_13_000500_create_notifications_table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `type` VARCHAR(255) NOT NULL,
  `notifiable_type` VARCHAR(255) NOT NULL,
  `notifiable_id` BIGINT UNSIGNED NOT NULL,
  `data` TEXT NOT NULL,
  `read_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  KEY `notifiable_notifiable_index` (`notifiable_type`, `notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2026_11_16_000000_newsletter_simplificar_statuses
-- Expandir o enum ANTES de escrever os valores novos (MySQL em strict mode).
ALTER TABLE `newsletter_subscribers` MODIFY COLUMN `status` ENUM('pending','confirmed','unsubscribed','active','cancelled') NOT NULL DEFAULT 'pending';
UPDATE `newsletter_subscribers` SET `status` = 'active' WHERE `status` = 'confirmed';
UPDATE `newsletter_subscribers` SET `status` = 'cancelled' WHERE `status` = 'unsubscribed';
ALTER TABLE `newsletter_subscribers` MODIFY COLUMN `status` ENUM('pending','active','cancelled') NOT NULL DEFAULT 'pending';

-- 2026_11_17_000000_add_email_to_companies_professionals
ALTER TABLE `companies` ADD COLUMN IF NOT EXISTS `email` VARCHAR(190) NULL AFTER `contactos`;
ALTER TABLE `professionals` ADD COLUMN IF NOT EXISTS `email` VARCHAR(190) NULL AFTER `contactos`;

-- 2026_11_18_000000_create_team_members_and_partners_tables
CREATE TABLE IF NOT EXISTS `team_members` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(190) NOT NULL,
  `cargo_select` VARCHAR(50) NOT NULL,
  `cargo_outro` VARCHAR(190) NULL,
  `foto_url` VARCHAR(2048) NULL,
  `facebook` VARCHAR(2048) NULL,
  `instagram` VARCHAR(2048) NULL,
  `linkedin` VARCHAR(2048) NULL,
  `ordem` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `deleted_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  KEY `team_members_cargo_select_ordem_index` (`cargo_select`, `ordem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `partners` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(190) NOT NULL,
  `tipo` ENUM('empresa','profissional') NOT NULL,
  `imagem_url` VARCHAR(2048) NULL,
  `detalhes` TEXT NULL,
  `ordem` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `deleted_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  KEY `partners_tipo_ordem_index` (`tipo`, `ordem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2026_11_19_000000_remove_missao_menu_item
-- Redundância: o item «Missão, visão e valores» apontava para a mesma página que
-- «Quem somos» (/sobre) — o cliente pediu a remoção. Verificado: 0 artigos associados.
DELETE FROM `categories` WHERE `menu_group` = 'sobre' AND `slug` = 'missao-visao-e-valores';

-- Renumerar o grupo `sobre` para 1..N (subquery correlacionada — compatível com
-- todas as variantes de MariaDB/MySQL; equivalente à migração equivalente).
UPDATE `categories` c1
SET c1.`order` = (
  SELECT COUNT(*) + 1
  FROM `categories` c2
  WHERE c2.`menu_group` = 'sobre'
    AND (c2.`order` < c1.`order` OR (c2.`order` = c1.`order` AND c2.`id` < c1.`id`))
)
WHERE c1.`menu_group` = 'sobre';

-- ============================================
-- Marca as migrações como "executadas"
-- ============================================
INSERT INTO `migrations` (`migration`, `batch`)
SELECT * FROM (
  SELECT '2026_11_10_104620_create_media_table' AS migration, (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`) AS batch
  UNION ALL SELECT '2026_11_10_120000_create_companies_table', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_10_120001_create_professionals_table', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_10_120002_create_directory_documents_table', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_10_120003_create_banners_table', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_10_120004_add_video_and_document_links_to_news_table', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_10_180000_add_soft_deletes_to_directory_documents_and_banners', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_11_000000_add_unique_index_to_companies_nif', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_11_000001_drop_comments_count_from_content_tables', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_11_000200_create_page_views_table', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_11_100000_add_menu_fields_to_categories_table', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_11_180100_add_image_alt_to_news_table', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_11_181000_published_at_date_to_datetime_news_table', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_11_201000_add_outros_category_to_news', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_11_202000_add_link_to_news_table', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_11_220000_promote_academia_oportunidades_to_news', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_12_090000_add_lateral_to_banners_posicao', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_12_090000_create_destaques_table', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_12_100000_add_guest_author_to_news_table', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_12_130000_create_videos_table', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_13_000100_add_destaque_editorial_to_news', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_13_000200_widen_projects_province', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_13_000300_add_largo_to_banners_posicao', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_13_000400_create_newsletter_subscribers_table', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_13_000500_create_notifications_table', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_16_000000_newsletter_simplificar_statuses', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_17_000000_add_email_to_companies_professionals', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_18_000000_create_team_members_and_partners_tables', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
  UNION ALL SELECT '2026_11_19_000000_remove_missao_menu_item', (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
) t
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migrations`.`migration` = t.migration);

-- ============================================
-- VERIFICAÇÃO FINAL
-- ============================================
SELECT COUNT(*) AS migracoes_marcadas_como_executadas
FROM `migrations`
WHERE `migration` LIKE '2026_11_%' OR `migration` = '2026_11_16_000000_newsletter_simplificar_statuses';
