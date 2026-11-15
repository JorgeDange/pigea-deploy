-- ============================================================
-- PIGEA — repor a árvore de categorias de notícias em PRODUÇÃO
-- (seguro de executar várias vezes; só insere o que está em falta)
-- Corre primeiro a contagem de verificação (no final do ficheiro)
-- ============================================================

-- Passo 1 — 10 categorias principais (vai inserir apenas as que faltarem)
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Investigação', 'investigacao', 'news', NULL, NULL, NULL, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='investigacao');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Água', 'agua', 'news', NULL, NULL, NULL, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='agua');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Ambiente', 'ambiente', 'news', NULL, NULL, NULL, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='ambiente');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Mineração', 'mineracao', 'news', NULL, NULL, NULL, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='mineracao');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Petróleo e Gás', 'petroleo-e-gas', 'news', NULL, NULL, NULL, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='petroleo-e-gas');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Geociências', 'geociencias', 'news', NULL, NULL, NULL, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='geociencias');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Internacional', 'internacional', 'news', NULL, NULL, NULL, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='internacional');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Academia', 'academia', 'news', NULL, NULL, 9, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='academia');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Oportunidades', 'oportunidades', 'news', NULL, NULL, 10, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='oportunidades');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Outros', 'outros', 'news', NULL, NULL, 999, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='outros');

-- Passo 2 — 27 subcategorias (parent_id vem de um SELECT por slug; sem IDs fixos)

SET @p_mineracao    = (SELECT id FROM categories WHERE type='news' AND slug='mineracao'    LIMIT 1);
SET @p_petroleo     = (SELECT id FROM categories WHERE type='news' AND slug='petroleo-e-gas' LIMIT 1);
SET @p_geociencias  = (SELECT id FROM categories WHERE type='news' AND slug='geociencias'  LIMIT 1);
SET @p_academia     = (SELECT id FROM categories WHERE type='news' AND slug='academia'     LIMIT 1);
SET @p_oportunidades= (SELECT id FROM categories WHERE type='news' AND slug='oportunidades' LIMIT 1);

-- Mineração
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Prospecção e pesquisa', 'prospeccao-e-pesquisa', 'news', @p_mineracao, 'mineracao', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='prospeccao-e-pesquisa');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Exploração mineira', 'exploracao-mineira', 'news', @p_mineracao, 'mineracao', 2, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='exploracao-mineira');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Avaliação de jazidas', 'avaliacao-de-jazidas', 'news', @p_mineracao, 'mineracao', 3, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='avaliacao-de-jazidas');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Processamento mineral', 'processamento-mineral', 'news', @p_mineracao, 'mineracao', 4, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='processamento-mineral');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Ambiente mineiro', 'ambiente-mineiro', 'news', @p_mineracao, 'mineracao', 5, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='ambiente-mineiro');

-- Petróleo e Gás
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Exploração e produção', 'exploracao-e-producao', 'news', @p_petroleo, 'petroleo-e-gas', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='exploracao-e-producao');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Geologia e geofísica', 'geologia-e-geofisica', 'news', @p_petroleo, 'petroleo-e-gas', 2, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='geologia-e-geofisica');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Perfuração', 'perfuracao', 'news', @p_petroleo, 'petroleo-e-gas', 3, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='perfuracao');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Engenharia de reservatórios', 'engenharia-de-reservatorios', 'news', @p_petroleo, 'petroleo-e-gas', 4, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='engenharia-de-reservatorios');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Energia', 'energia', 'news', @p_petroleo, 'petroleo-e-gas', 5, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='energia');

-- Geociências
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Geologia', 'geologia', 'news', @p_geociencias, 'geociencias', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='geologia');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Geofísica', 'geofisica', 'news', @p_geociencias, 'geociencias', 2, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='geofisica');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Geodesia e GNSS', 'geodesia-e-gnss', 'news', @p_geociencias, 'geociencias', 3, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='geodesia-e-gnss');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Topografia e cartografia', 'topografia-e-cartografia', 'news', @p_geociencias, 'geociencias', 4, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='topografia-e-cartografia');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'SIG e geoprocessamento', 'sig-e-geoprocessamento', 'news', @p_geociencias, 'geociencias', 5, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='sig-e-geoprocessamento');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Hidrogeologia', 'hidrogeologia', 'news', @p_geociencias, 'geociencias', 6, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='hidrogeologia');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Geotecnia e ambiente', 'geotecnia-e-ambiente', 'news', @p_geociencias, 'geociencias', 7, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='geotecnia-e-ambiente');

-- Academia
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Cursos e formação', 'cursos-e-formacao', 'news', @p_academia, 'academia', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='cursos-e-formacao');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Workshops e webinars', 'workshops-e-webinars', 'news', @p_academia, 'academia', 2, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='workshops-e-webinars');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Artigos científicos', 'artigos-cientificos', 'news', @p_academia, 'academia', 3, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='artigos-cientificos');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Livros e publicações', 'livros-e-publicacoes', 'news', @p_academia, 'academia', 4, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='livros-e-publicacoes');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Investigação', 'investigacao-academia', 'news', @p_academia, 'academia', 5, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='investigacao-academia');

-- Oportunidades
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Emprego', 'emprego', 'news', @p_oportunidades, 'oportunidades', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='emprego');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Estágios', 'estagios', 'news', @p_oportunidades, 'oportunidades', 2, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='estagios');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Bolsas de estudo', 'bolsas-de-estudo', 'news', @p_oportunidades, 'oportunidades', 3, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='bolsas-de-estudo');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Concursos', 'concursos', 'news', @p_oportunidades, 'oportunidades', 4, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='concursos');
INSERT INTO categories (name, slug, type, parent_id, menu_group, `order`, created_at, updated_at)
SELECT 'Consultorias', 'consultorias', 'news', @p_oportunidades, 'oportunidades', 5, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE type='news' AND slug='consultorias');

-- Passo 3 — verificação final (sim, nesta mesma janela SQL)
SELECT 'tops' AS sec, COUNT(*) AS presentes FROM categories WHERE type='news' AND parent_id IS NULL
UNION ALL
SELECT 'filhos', COUNT(*) FROM categories WHERE type='news' AND parent_id IS NOT NULL;
