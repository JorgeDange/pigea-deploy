# PIGEA — Especificações de Imagem

> Para: designer responsável pelas imagens do site.
> Actualizado: 2026-11-14. Valida as medidas contra o código em produção.
> Fonte: textos de ajuda do admin + CSS (`topo.css`) + conversões (Spatie Media Library, WebP q85).

---

## 1. Notícias / Posts

| Uso | Dimensão recomendada | Proporção | Peso máx. | Formatos |
|---|---|---|---|---|
| **Capa do artigo** (cartão da home, listagem, artigo e fundo do Hero) | **1200 × 675 px** | 16:9 | 2 MB | JPG, PNG, WebP |
| **Imagens dentro do texto** ("imagens de contexto") | **800 px de largura** (altura livre), até 10 por artigo | — | 4 MB cada | JPG, PNG, WebP |
| **Hero (fundo de ecrã inteiro na home)** — usa a capa do artigo | Se quiseres nitidez máxima em ecrãs grandes: **1920 × 1080 px** | 16:9 | 2 MB | JPG, PNG, WebP |

> **Importante:** as capas são reutilizadas como fundo de ecrã inteiro no Hero (com sobreposição escura). Envia as capas com o **tema centralizado e sem texto colado às bordas** — o Hero recorta conforme o ecrã.

## 2. Destaques (faixa promocional)

| Uso | Dimensão | Proporção | Peso máx. | Formatos |
|---|---|---|---|---|
| **Destaque** (painel admin → Destaques) | **1200 × 600 px** | ~2:1 | 4 MB | JPG, PNG, WebP |

## 3. Banners publicitários (painel admin → Publicidade)

| Posição | Dimensão | Notas de recorte | Formatos / Peso |
|---|---|---|---|
| **Largo** (faixa horizontal a largura total do conteúdo) | **970 × 250 px** (alternativa: 970 × 90 px) | 100% da largura, altura máx 250 px, **recorte centrado** — manter o elemento principal ao centro na vertical | JPG, PNG, WebP · máx 4 MB |
| **Topo** (barra fina no topo do site) | **970 × 90 px** | Altura cortada pelo site (máx ~46 px; telemóvel ~26 px) — faixa fina e legível | JPG, PNG, WebP · máx 4 MB |
| **Lateral** (colunas/sidebar, ex.: artigo de notícia) | **Quadrado — ex. 600 × 600 px** (mín. 300 × 300) | 100% da coluna da sidebar com **recorte quadrado (1:1)** | JPG, PNG, WebP · máx 4 MB |
| **Rodapé** | Mesmo do Largo (970 × 90 ou 970 × 250) | Largura total do rodapé | JPG, PNG, WebP · máx 4 MB |

### Regras dos banners (automatismo do site)
- O upload valida o tipo real do ficheiro, aceita até 4 MB e **reduz automaticamente para 1600 px de largura e converte para WebP qualidade 85**. Enviar 8K não ajuda.
- Por cima/ao lado da imagem aparece sempre o rótulo discreto **"Publicidade"** — deixa margens limpas; nada de texto importante colado à borda superior.
- Os banners laterais **rodam automaticamente** (até 4 em sequência, troca a cada 5 s) — todos os slides devem ter o mesmo peso visual.
- Se o banner for só imagem sem link de destino, **não é clicável**; com link, abre noutra janela.

## 4. Logótipo / Marca

| Uso | Dimensão | Notas |
|---|---|---|
| **Logótipo** (cabeçalho e rodapé) | Rec. **400 × 100 px** (4:1) | PNG com fundo transparente preferível (máx 2 MB). No cabeçalho aparece com **25 px** de altura; no rodapé com **44 px**. Se o logótipo tiver traços finos, envia com resolução maior (ex.: 800 × 200 px) |
| **Favicon** (ícone do separador) | **32 × 32 px** | PNG, máx 256 KB. Sem texto pequeno — tem de ser legível a 16 px |

## 5. Autor / Avatar

| Uso | Dimensão | Peso máx. | Formatos |
|---|---|---|---|
| Foto do autor do artigo / perfil no painel | **150 × 150 px** (quadrada) | 1 MB | JPG, PNG |

## 6. Regras gerais para todas as imagens

- **JPG** para fotografias; **PNG** para logótipos/elementos com transparência; **WebP** aceite em tudo.
- Sem texto pequeno dentro das imagens (não se lê em telemóvel).
- Os pesos máximos acima são **limites do servidor** — mais leve é sempre melhor.
- Cores: o site usa base **preta/branca com amarelo PIGEA** (#FFD100) e vermelho pontual — alinhar as imagens promocionais com essa paleta.

## 7. O que o site NÃO faz hoje (para o designer saber)

- **Não existe campo "imagem para redes sociais" (og:image) por artigo** — ao partilhar um link, aparece o título/descrição, não a capa. Se for necessário, é funcionalidade nova (orçamento à parte).
- As **imagens de contexto** (corpo do artigo) não têm dimensão fixa — o site limita apenas a largura máxima exibida (800 px).
