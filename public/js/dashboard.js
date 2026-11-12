/* ==========================================================================
   PIGEA — Dashboard / Painéis
   ----------------------------------------------------------------------------
   Apenas os handlers com elementos vivos no frontend/admin. Tudo o resto do
   protótipo (formulários "demo estático" que interceptaram os submits — o
   P0 —, tabs de autenticação, registo público, "marcar todas lidas", remover
   itens, filtros de demonstração) foi removido na Fase 8; os formulários
   submetem normalmente no Laravel e a porta pública de entrada é /login.
   ========================================================================== */
$(function () {
    'use strict';

    /* ---------- Sidebar (mobile) — dashboard-header.blade.php ---------- */
    $(document).on('click', '.sidebar-toggle', function () {
        $('.dashboard-wrap').toggleClass('sidebar-open');
    });
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.dashboard-sidebar, .sidebar-toggle').length) {
            $('.dashboard-wrap').removeClass('sidebar-open');
        }
    });

    /* ---------- Modais ("Adicionar utilizador" e pré-visualização) ---------- */
    $(document).on('click', '.js-modal-open', function () {
        var id = $(this).data('modal');
        var $modal = $('#' + id);
        $('.dashboard-modal.open').removeClass('open'); // só um aberto de cada vez
        $modal.addClass('open').attr('tabindex', '-1').focus();
        $modal.data('trigger', this); // guarda o botão de origem para nele devolver o foco
        return false;
    });

    $(document).on('click', '.js-modal-close, [data-role="fechar-modal"]', function () {
        var $modal = $(this).closest('.dashboard-modal');
        $modal.removeClass('open');
        var trigger = $modal.data('trigger');
        if (trigger) $(trigger).focus(); else $('.js-modal-open:focus').focus();
        return false;
    });
    $(document).on('click', '.dashboard-modal', function (e) {
        if (e.target === this) {
            var $modal = $(this);
            $modal.removeClass('open');
            // Mesmo contrato das outras duas rotas de fecho (× e Escape):
            // o foco volta ao botão que abriu o modal.
            var trigger = $modal.data('trigger');
            if (trigger) $(trigger).focus();
        }
    });
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            var $open = $('.dashboard-modal.open');
            if ($open.length) {
                $open.removeClass('open');
                var trigger = $open.last().data('trigger');
                if (trigger) $(trigger).focus();
            }
        }
    });
});

/* ===== cms_v.md FASE 5 - Toast global e confirmação genérica ==========
   Toast substitui o flash simples; confirm-modal para ações destrutivas
   (apagar). Voz PT; sem dependências novas. */
window.PIGEAToast = function (msg, tipo) {
    var regiao = document.querySelector('.toast-region');
    if (!regiao) return;
    var toast = document.createElement('div');
    toast.className = 'toast' + (tipo ? ' ' + tipo : '');
    toast.setAttribute('role', 'status');
    var texto = document.createElement('span');
    texto.textContent = msg;
    var fechar = document.createElement('button');
    fechar.className = 'toast-fechar';
    fechar.type = 'button';
    fechar.setAttribute('aria-label', 'Fechar aviso');
    fechar.innerHTML = '<i class="fa fa-times" aria-hidden="true"></i>';
    fechar.addEventListener('click', function () { regiao.removeChild(toast); });
    toast.appendChild(texto);
    toast.appendChild(fechar);
    regiao.appendChild(toast);
    setTimeout(function () { if (toast.parentNode === regiao) regiao.removeChild(toast); }, 5000);
};

$(function () {
    'use strict';

    /* Flash de sessão (quando a vista marca [data-flash-toast]) → toast */
    document.querySelectorAll('[data-flash-toast]').forEach(function (el) {
        window.PIGEAToast(el.textContent.trim(), el.getAttribute('data-flash-toast'));
        el.remove();
    });

    /* Modal de confirmação genérico (sem alert/confirm nativos).
       window.PIGEAConfirm abre #confirm-modal e regista a ação a executar
       no clique de «Confirmar». `.js-confirm` é o atalho para submissão de
       formulários; `.js-confirm-livewire` (banner.js) para ações
       Livewire. */
    var confirmAcaoPendente = null;

    window.PIGEAConfirm = function (title, message, confirmText, onConfirm) {
        $('#confirm-title').text(title || 'Confirmar ação');
        $('#confirm-message').text(message || 'Tem a certeza?');
        $('#confirm-action').text(confirmText || 'Confirmar');
        confirmAcaoPendente = typeof onConfirm === 'function' ? onConfirm : null;
        $('#confirm-modal').addClass('open').attr('tabindex', '-1').focus();
        return false;
    };

    $(document).on('click', '.js-confirm', function () {
        var $btn = $(this);
        window.PIGEAConfirm(
            $btn.data('confirm-title') || 'Confirmar ação',
            $btn.data('confirm-message') || 'Tem a certeza?',
            $btn.data('confirm-text') || 'Confirmar',
            function () {
                var formId = $btn.data('confirm-form') || '';
                var form = formId ? document.getElementById(formId) : null;
                if (form) form.submit();
            }
        );
        return false;
    });

    $(document).on('click', '#confirm-action', function () {
        $('#confirm-modal').removeClass('open');
        if (confirmAcaoPendente) {
            var acao = confirmAcaoPendente;
            confirmAcaoPendente = null;
            acao();
        }
    });

    /* Estados de loading em submits (todos os formulários marcados) */
    $(document).on('submit', 'form[data-loading-submit]', function () {
        $(this).find('button[type="submit"]').addClass('is-loading').prop('disabled', true);
    });

    /* Upload em PT: área de largar, botão PT, pré-visualização, remover */
    function ligarFileUpload(root) {
        var input = root.querySelector('input[type="file"]');
        var btn = root.querySelector('.file-upload-button');
        var preview = root.querySelector('.file-upload-preview');
        var nomeEl = root.querySelector('.file-upload-name');
        var erroEl = root.querySelector('.file-upload-erro');

        function bytesLegiveis(n) {
            if (n === 0) return '0 B';
            var u = ['B', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(n) / Math.log(1024));
            return (n / Math.pow(1024, i)).toFixed(i === 0 ? 0 : 1) + ' ' + u[i];
        }

        function validarEActualizar() {
            var ficheiros = input.files ? Array.prototype.slice.call(input.files) : [];
            if (!ficheiros.length) return;
            if (erroEl) erroEl.textContent = '';

            var total = 0;
            for (var i = 0; i < ficheiros.length; i++) {
                var f = ficheiros[i];
                total += f.size;
                var tipo = (f.type || '').toLowerCase();
                if (permitidos.indexOf(tipo) === -1) {
                    if (erroEl) erroEl.textContent = msgFormato;
                    input.value = '';
                    if (preview) { preview.hidden = true; }
                    if (nomeEl) nomeEl.textContent = '';
                    return;
                }
                if (maxBytes !== Infinity && f.size > maxBytes) {
                    if (erroEl) erroEl.textContent = 'O ficheiro excede o máximo de ' + bytesLegiveis(maxBytes) + '.';
                    input.value = '';
                    if (preview) { preview.hidden = true; }
                    if (nomeEl) nomeEl.textContent = '';
                    return;
                }
            }

            var primeiro = ficheiros[0];
            if (primeiro.type && primeiro.type.indexOf('image/') === 0) {
                var leitor = new FileReader();
                leitor.onload = function (ev) {
                    if (preview) { preview.src = ev.target.result; preview.hidden = false; }
                };
                leitor.readAsDataURL(primeiro);
            } else if (preview) {
                preview.hidden = true;
            }
            if (nomeEl) {
                nomeEl.textContent = ficheiros.length > 1
                    ? ficheiros.length + ' ficheiros selecionados (' + bytesLegiveis(total) + ')'
                    : primeiro.name + ' - ' + bytesLegiveis(primeiro.size);
            }
        }

        /* Tipos aceites por data-accept="image|pdf[,...]" (imagem por omissão) */
        var tiposAceites = {
            image: ['image/jpeg', 'image/png', 'image/webp'],
            pdf: ['application/pdf']
        };
        var aceita = (root.dataset.accept || 'image').toLowerCase().split(',');
        var permitidos = [];
        for (var k = 0; k < aceita.length; k++) {
            var lista = tiposAceites[aceita[k].trim()];
            if (lista) permitidos = permitidos.concat(lista);
        }
        if (!permitidos.length) permitidos = tiposAceites.image.slice();
        var soPdf = aceita.length === 1 && aceita[0].trim() === 'pdf';
        var msgFormato = soPdf
            ? 'Formato não suportado. Usa PDF.'
            : 'Formato não suportado. Usa JPG, PNG ou WebP.';

        /* Limite de tamanho por data-max-bytes (bytes).
           data-limit="capa" mantém o limite de 2MB de sempre. */
        var maxBytes = parseInt(root.dataset.maxBytes || '0', 10);
        if (!maxBytes || isNaN(maxBytes)) {
            maxBytes = root.dataset.limit === 'capa' ? 2 * 1024 * 1024 : Infinity;
        }

        if (btn) btn.addEventListener('click', function () { input.click(); });
        input.addEventListener('change', validarEActualizar);

        var remover = root.querySelector('.file-upload-remove');
        if (remover) remover.addEventListener('click', function () {
            input.value = '';
            if (preview) { preview.hidden = true; }
            if (nomeEl) nomeEl.textContent = '';
            if (erroEl) erroEl.textContent = '';
        });

        ['dragenter', 'dragover'].forEach(function (evt) {
            root.addEventListener(evt, function (e) { e.preventDefault(); root.classList.add('is-dragover'); });
        });
        ['dragleave', 'drop'].forEach(function (evt) {
            root.addEventListener(evt, function (e) { e.preventDefault(); root.classList.remove('is-dragover'); });
        });
        root.addEventListener('drop', function (e) {
            if (e.dataTransfer.files.length) { input.files = e.dataTransfer.files; validarEActualizar(); }
        });
    }

    document.querySelectorAll('.file-upload').forEach(ligarFileUpload);
});