/* ==========================================================================
   PIGEA — Editor de notícias: inicialização completa do TinyMCE (Fase 2)
   --------------------------------------------------------------------------
   Self-hosted (public/js/tinymce). Lista branca ÚNICA do artigo vive em
   config/article.php e no ArticleSanitizer (servidor, Fase 3) — este ficheiro
   configura o TinyMCE com a MESMA lista.
   ========================================================================== */

/* ---------- TinyMCE (Fase 2) ---------- */
tinymce.init({
	selector: '#conteudo-editor',
	height: 520,
	min_height: 320,
	max_height: 900,

	// Janela própria, sem clutter (Fase 2)
	menubar: 'edit view insert format table',
	branding: false,
	promotion: false,
	license_key: 'gpl',
	language: 'pt_PT',
	language_url: '/js/tinymce/langs/pt_PT.js',
	iframe_aria_text: 'Corpo da notícia',
	elementpath: false,

	// Mesmo CSS do site (Fase 1) e o mesmo wrapper (.article-body)
	body_class: 'article-body',
	content_css: '/css/article.css',

	plugins: ['advlist', 'autolink', 'lists', 'link', 'image', 'table', 'code', 'fullscreen', 'wordcount', 'searchreplace', 'autoresize', 'visualblocks'],

	toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table | styles | removeformat | code fullscreen',

	// Sem H1: o título da notícia é o H1 da página pública
	block_formats: 'Parágrafo=p; Título 2=h2; Título 3=h3',

	// "Estilos do PIGEA" — substitui os botões soltos dropcap/blockquote/twocols
	style_formats: [
		{ title: 'Letra capitular', selector: 'p', classes: 'drop-cap' },
		{ title: 'Citação em destaque', selector: 'blockquote', classes: 'pull-quote' },
		{ title: 'Coluna estreita', selector: 'p', classes: 'narrow' },
		{ title: 'Duas colunas', selector: 'div', classes: 'two-cols' },
		{ title: 'Imagem à esquerda', selector: 'figure', classes: 'media-left' },
		{ title: 'Imagem à direita', selector: 'figure', classes: 'media-right' }
	],

	// Lista branca: manter EXATAMENTE alinhada com config/article.php (servidor).
	valid_elements: 'p,h2,h3,strong,em,u,s,ul,ol,li,a[href|target|rel],blockquote,figure,figcaption,img[src|alt|width|height|loading],table,thead,tbody,tr,th,td[colspan|rowspan],br,div,hr',
	valid_classes: 'p:drop-cap,narrow;blockquote:pull-quote,narrow;div:two-cols;figure:media-left,media-right',

	// Colagem: por omissão o TinyMCE descarta style/fonts/cores/classes alheias
	// (essas propriedades não existem em valid_elements); reforçado no servidor
	// pelo ArticleSanitizer (Fase 3) — o paste nunca escapa. Imagens coladas
	// (data URI) ficam de fora: só pelo handler de upload (Fase 4).
	paste_data_images: false,

	// Diálogo de imagem: texto alternativo obrigatório, legenda, sem aba avançada
	image_description: true,
	image_caption: true,
	image_advtab: false,
	automatic_uploads: true,

	// Upload do corpo: rota autenticada + permissão (Fase 4)
	images_upload_handler: function (blobInfo, progress) {
		const form = document.getElementById('news-editor-form');
		const token = form.querySelector('input[name="_token"]').value;
		const url = form.getAttribute('data-image-url');

		return new Promise(function (resolve, reject) {
			const data = new FormData();
			data.append('imagem', blobInfo.blob(), blobInfo.filename());
			data.append('_token', token);

			fetch(url, {
				method: 'POST',
				body: data,
				headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
				credentials: 'same-origin'
			})
				.then(function (res) {
					if (res.status === 419 || res.status === 401) {
						reject('Sessão expirada. Recarregue a página e tente novamente.');
						return;
					}
					if (!res.ok) {
						return res.json().catch(function () { return {}; }).then(function (body) {
							reject(body.message || 'Falha no upload da imagem.');
						});
					}
					return res.json();
				})
				.then(function (body) {
					resolve(body.location);
				})
				.catch(function () {
					reject('Falha de rede ao carregar a imagem.');
				});
		});
	},

	// Contador de palavras na barra de estado (via plugin wordcount)
	statusbar: true,

	// Ao submeter, o TinyMCE sincroniza o textarea — garantido também
	// explicitamente no listener "submit" abaixo (Fase 2 requisito).
	setup: function (editor) {
		editor.on('change keyup undo', function () {
			const form = document.getElementById('news-editor-form');
			if (form) form.dataset.dirty = '1';
		});
	}
});

/* ---------- Foco no primeiro campo com erro, após validação ---------- */
(function () {
	var primeiro = document.querySelector('.form-error');
	if (!primeiro) return;
	var grupo = primeiro.closest('.dashboard-form-group');
	var campo = grupo ? grupo.querySelector('input, select, textarea') : null;
	// O TinyMCE esconde o textarea (#conteudo-editor) — o foco vai para o editor.
	if (campo && campo.id === 'conteudo-editor' && window.tinymce) {
		var ed = tinymce.get('conteudo-editor');
		if (ed) { ed.focus(); return; }
		var area = document.querySelector('.tox .tox-edit-area');
		if (area) { area.setAttribute('tabindex', '-1'); area.focus(); return; }
	}
	if (campo) campo.focus();
})();

/* ---------- Guarda contra duplo clique nos botões de submit ---------- */
(function () {
	var form = document.getElementById('news-editor-form');
	if (!form) return;
	form.addEventListener('submit', function () {
		// Garantia de sincronização mesmo sem eventos do TinyMCE
		if (window.tinymce) tinymce.triggerSave();
		form.querySelectorAll('button[type="submit"]').forEach(function (b) { b.disabled = true; });
	});
})();

/* ---------- Pré-visualização: fetch para /noticias/pre-visualizar ---------- */
(function () {
	var modal = document.getElementById('news-preview-modal');
	var previewTitulo = document.getElementById('preview-titulo');
	var previewSubtitulo = document.getElementById('preview-subtitulo');
	var previewCategoria = document.getElementById('preview-categoria');
	var previewArticle = document.getElementById('preview-article');
	var previewError = document.getElementById('preview-error');
	var capaInput = document.getElementById('capa');
	var previewCapa = document.getElementById('preview-capa');
	var ultimoPreviewUrl = null;

	function abrirPreview() {
		var form = document.getElementById('news-editor-form');
		var ed = window.tinymce && tinymce.get('conteudo-editor');
		var conteudo = ed && ed.getContent ? ed.getContent() : document.getElementById('conteudo-editor').value;

		previewTitulo.textContent = document.getElementById('titulo').value || '(sem título)';
		var sub = document.getElementById('subtitulo').value.trim();
		previewSubtitulo.textContent = sub;
		previewSubtitulo.hidden = sub === '';

		var selectCat = document.getElementById('categoria');
		previewCategoria.textContent = selectCat.selectedIndex > 0 ? selectCat.options[selectCat.selectedIndex].text : 'Geral';

		if (ultimoPreviewUrl) { URL.revokeObjectURL(ultimoPreviewUrl); ultimoPreviewUrl = null; }
		if (capaInput && capaInput.files && capaInput.files[0]) {
			ultimoPreviewUrl = URL.createObjectURL(capaInput.files[0]);
			previewCapa.src = ultimoPreviewUrl;
			previewCapa.hidden = false;
		} else {
			previewCapa.removeAttribute('src');
			previewCapa.hidden = true;
		}

		// Corpo via servidor: MESMO parcial do site público, sanitizado —
		// nunca confiamos aqui no conteúdo tal como está.
		var data = new FormData();
		data.append('conteudo', conteudo);

		var token = form.querySelector('input[name="_token"]').value;

		previewError.hidden = true;
		previewArticle.innerHTML = '<p><em>A carregar…</em></p>';

		fetch(form.getAttribute('data-preview-url'), {
			method: 'POST',
			body: data,
			headers: { 'X-CSRF-TOKEN': token, 'Accept': 'text/html' },
			credentials: 'same-origin'
		})
			.then(function (res) {
				if (!res.ok) throw new Error('HTTP ' + res.status);
				return res.text();
			})
			.then(function (html) {
				previewArticle.innerHTML = html;
			})
			.catch(function () {
				previewArticle.innerHTML = '';
				previewError.hidden = false;
				previewError.textContent = 'Não foi possível gerar a pré-visualização agora. O conteúdo guardado no servidor é que vale.';
			});
	}

	document.addEventListener('click', function (e) {
		var btn = e.target.closest('[data-modal="news-preview-modal"]');
		if (btn) abrirPreview();
	});

	// Revogar o object URL em qualquer caminho de fecho (×, rodapé, Escape, backdrop)
	new MutationObserver(function () {
		if (!modal.classList.contains('open') && ultimoPreviewUrl) {
			URL.revokeObjectURL(ultimoPreviewUrl);
			ultimoPreviewUrl = null;
		}
	}).observe(modal, { attributes: true, attributeFilter: ['class'] });

	// Trap de Tab/Shift+Tab dentro do diálogo (WCAG 2.4.3)
	modal.addEventListener('keydown', function (e) {
		if (e.key !== 'Tab') return;
		var focaveis = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
		if (!focaveis.length) return;
		var primeiro = focaveis[0];
		var ultimo = focaveis[focaveis.length - 1];
		var ativo = document.activeElement;
		var noContainer = ativo === modal || !modal.contains(ativo);
		if (e.shiftKey && (ativo === primeiro || noContainer)) { ultimo.focus(); e.preventDefault(); }
		else if (!e.shiftKey && (ativo === ultimo || noContainer)) { primeiro.focus(); e.preventDefault(); }
	});
})();


/* ===== cms_v.md FASE 6 - contadores de caracteres e slug único ===== */

(function () {
    var pares = [
        ['titulo', 'contador-titulo', 190],
        ['subtitulo', 'contador-subtitulo', 500],
        ['meta_title', 'contador-meta-title', 60],
        ['meta_description', 'contador-meta-description', 160]
    ];

    pares.forEach(function (par) {
        var input = document.getElementById(par[0]);
        var contador = document.getElementById(par[1]);
        if (!input || !contador) return;
        var limite = par[2];

        function atualizar() {
            var n = input.value.length;
            contador.textContent = n;
            var host = contador.closest('.form-counter');
            if (host) {
                host.classList.toggle('over', n > limite);
                host.classList.toggle('warn', n > Math.round(limite * 0.9) && n <= limite);
            }
        }

        input.addEventListener('input', atualizar);
        atualizar();
    });
})();

(function () {
    var slug = document.getElementById('slug');
    var estado = document.getElementById('slug-state');
    if (!slug || !estado) return;

    var timer = null;
    var ultimoValor = '';

    function verificar() {
        var valor = slug.value.trim();

        if (valor === '' || valor === ultimoValor) {
            if (valor === '') { estado.textContent = ''; estado.className = 'slug-state'; }
            return;
        }

        ultimoValor = valor;

        var url = slug.getAttribute('data-slug-url');
        var ignorar = slug.getAttribute('data-ignorar');

        fetch(url + '?slug=' + encodeURIComponent(valor) + (ignorar ? '&ignorar=' + encodeURIComponent(ignorar) : ''), {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
            .then(function (res) { return res.ok ? res.json() : Promise.reject(new Error('HTTP ' + res.status)); })
            .then(function (data) {
                if (data.unico) {
                    estado.textContent = 'Endereço disponível.';
                    estado.className = 'slug-state ok';
                } else {
                    estado.textContent = 'Este endereço já existe — escolhe outro.';
                    estado.className = 'slug-state taken';
                }
            })
            .catch(function () {
                estado.textContent = '';
                estado.className = 'slug-state';
            });
    }

    slug.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(verificar, 400);
    });
})();

/* ===== cms_v.md FASE 8.1 - Autosave (debounce 15s, sem Livewire) ===== */

(function () {
    var form = document.getElementById('news-editor-form');
    var indicador = document.getElementById('autosave-indicador');
    if (!form || !indicador) return;

    var form = document.getElementById('news-editor-form');
    var indicador = document.getElementById('autosave-indicador');
    if (!form || !indicador) return;

    // Fase 11B/fase 12: o autosave tambem funciona para novos artigos —
    // na primeira gravacao cria o rascunho e passa a fazer PUT (update).
    var id = form.getAttribute('data-id') || '';

    var autosaveUrl = form.getAttribute('data-autosave-url');
    var updateUrl = form.getAttribute('data-update-url');

    var emVoo = false;
    var timers = { pendente: null };
    var ultimoPayload = null;

    function setIndicador(estado, texto) {
        indicador.textContent = texto;
        indicador.className = 'autosave' + (estado ? ' ' + estado : '');
    }

    function guardar() {
        var ed = window.tinymce && tinymce.get('conteudo-editor');
        var conteudo = ed && ed.getContent ? ed.getContent() : document.getElementById('conteudo-editor').value;
        var titulo = document.getElementById('titulo').value.trim();

        if (titulo === '') {
            setIndicador('', 'Escreve um título para ativar o autosave.');
            return;
        }

        var payload = {
            id: id ? Number(id) : null,
            titulo: titulo,
            conteudo: conteudo,
            subtitulo: document.getElementById('subtitulo').value.trim(),
            categoria: document.getElementById('categoria').value || null
        };

        var chave = JSON.stringify(payload);
        if (chave === ultimoPayload) return;
        ultimoPayload = chave;

        var token = form.querySelector('input[name="_token"]').value;

        setIndicador('saving', 'A guardar…');
        emVoo = true;

        fetch(autosaveUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        })
            .then(function (res) {
                emVoo = false;
                if (res.status === 419 || res.status === 401) {
                    setIndicador('error', 'Sessão expirada — guarda manualmente.');
                    throw new Error('Sessão expirada');
                }
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (data) {
                if (data && data.ok) {
                    // Se o rascunho foi criado agora, passar o form a update por PUT.
                    if (data.id && !id) {
                        id = String(data.id);
                        form.setAttribute('data-id', id);

                        if (updateUrl) {
                            form.action = updateUrl.replace('/ID', '/' + id);
                        }
                        // Inserir o hidden _method=PUT se ainda não existir.
                        if (!form.querySelector('input[name="_method"]')) {
                            var hm = document.createElement('input');
                            hm.type = 'hidden';
                            hm.name = '_method';
                            hm.value = 'PUT';
                            form.appendChild(hm);
                        }
                    }

                    var hora = ((data.saved_at || '').split('T')[1] || '').substring(0, 5);
                    setIndicador('saved', 'Guardado às ' + hora);
                } else {
                    setIndicador('error', 'Não foi possível guardar.');
                }
            })
            .catch(function () {
                emVoo = false;
                setIndicador('error', 'Não foi possível guardar. Tenta manualmente.');
            });
    }

    function atender() {
        clearTimeout(timers.pendente);
        setIndicador('pending', 'A guardar em 15s…');
        timers.pendente = setTimeout(guardar, 15000);
    }

    form.addEventListener('input', atender);

    // O TinyMCE emite os seus próprios eventos — liga-os diretamente ao handler
    // (o editor já está inicializado quando este ficheiro corre).
    function ligarTinyMCE() {
        if (!window.tinymce) return;
        var ed = tinymce.get('conteudo-editor');
        if (ed) {
            ed.on('change keyup undo redo', atender);
        }
    }
    ligarTinyMCE();

    // Aviso ao sair com alterações por guardar
    window.addEventListener('beforeunload', function (e) {
        if (ultimoPayload === null) return; // nada foi guardado ainda; o form normal trata disso
        var ed = window.tinymce && tinymce.get('conteudo-editor');
        var assinatura = [
            document.getElementById('titulo').value,
            ed && ed.getContent ? ed.getContent() : ''
        ].join('|');

        if (assinatura === ultimoPayload) return;
        e.preventDefault();
        e.returnValue = 'Tens alterações por guardar nesta notícia.';
    });
})();

/* ===== Fase 11 / cms_v.md - switch «Ficheiro vs Link» na capa ======= */

(function () {
    var radios = document.querySelectorAll('input[name="capa_fonte"]');
    var painelFicheiro = document.getElementById('capa-ficheiro');
    var painelLink = document.getElementById('capa-link');
    var inputFicheiro = document.getElementById('capa');
    var inputLink = document.getElementById('capa_link');

    if (!radios.length || !painelFicheiro || !painelLink) return;

    function aplicar() {
        var valor = (document.querySelector('input[name="capa_fonte"]:checked') || {}).value || 'ficheiro';

        painelFicheiro.hidden = valor !== 'ficheiro';
        painelLink.hidden = valor !== 'link';

        if (inputFicheiro) inputFicheiro.disabled = valor !== 'ficheiro';
        if (inputLink) inputLink.disabled = valor !== 'link';
    }

    radios.forEach(function (r) {
        r.addEventListener('change', aplicar);
    });

    aplicar();
})();

/* ===== autor.md - switch «Ficheiro vs Link» na foto do autor convidado ===== */
(function () {
    var radios = document.querySelectorAll('input[name="guest_author_foto_fonte"]');
    var painelFicheiro = document.getElementById('guest-foto-ficheiro');
    var painelLink = document.getElementById('guest-foto-link');
    var inputFicheiro = document.getElementById('guest_author_avatar');
    var inputLink = document.getElementById('guest_author_avatar_link');

    if (!radios.length || !painelFicheiro || !painelLink) return;

    function aplicar() {
        var valor = (document.querySelector('input[name="guest_author_foto_fonte"]:checked') || {}).value || 'ficheiro';

        painelFicheiro.hidden = valor !== 'ficheiro';
        painelLink.hidden = valor !== 'link';

        if (inputFicheiro) inputFicheiro.disabled = valor !== 'ficheiro';
        if (inputLink) inputLink.disabled = valor !== 'link';
    }

    radios.forEach(function (r) {
        r.addEventListener('change', aplicar);
    });

    aplicar();
})();
