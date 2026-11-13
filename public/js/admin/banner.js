/* ==========================================================================
   PIGEA — banners e destaques
   --------------------------------------------------------------------------
   - Widget de upload com progresso (.upload-async) — substitui o input cru.
     Ficheiro vai por XHR → /banner/imagem e injeta o caminho no hidden
     `imagem_link` (o formulário segue só com metadata + link).
   - Toggle Ficheiro/Link externo (radios) com pré-visualização.
   - `.js-confirm` e `.js-confirm-livewire` — confirmação no #confirm-modal
     (já presente no layout). Nada de window.confirm / alert.
   ========================================================================== */

(function () {
    'use strict';

    var HIDDEN = 'is-hidden';

    $(function () {
        // ---------- helpers ----------
        function formatarTamanho(bytes) {
            if (!bytes || bytes <= 0) return '0 B';
            var units = ['B', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(1024));
            var v = bytes / Math.pow(1024, i);
            return (i === 0 ? v : v.toFixed(1)) + ' ' + units[i];
        }

        function tokenCsrf(form) {
            var input = form ? form.querySelector('input[name="_token"]') : null;
            return input ? input.value : '';
        }

        // ---------- Widget .upload-async ----------
        $('.upload-async').each(function () {
            var root = this;
            var $form = $(root).closest('form');
            var $previewGlobalImg = $form.find('#banner-preview-img');
            var $previewGlobalEmpty = $form.find('#banner-preview-empty');
            var input = root.querySelector('input[type="file"]');
            var chooseBtn = root.querySelector('.upload-async-button');
            var previewImg = root.querySelector('.upload-async-img');
            var previewWrap = root.querySelector('.upload-async-preview');
            var currentNameEl = root.querySelector('.upload-async-current');
            var nameEl = root.querySelector('.upload-async-name');
            var removeBtn = root.querySelector('.upload-async-remove');
            var progressWrap = root.querySelector('.upload-progress');
            var bar = root.querySelector('.upload-progress-bar');
            var pct = root.querySelector('.upload-progress-value');
            var errorEl = root.querySelector('.upload-async-erro');
            var hidden = ($form.get(0) && $form.get(0).querySelector('input[type="hidden"][name="imagem_link"]')) || root.querySelector('input[type="hidden"][name="imagem_link"]');
            var uploadUrl = root.getAttribute('data-upload-url');
            var accept = ['image/jpeg', 'image/png', 'image/webp'];
            var maxBytes = 4 * 1024 * 1024;

            function setError(msg) {
                if (errorEl) errorEl.textContent = msg || '';
            }

            function mostrarCard(src) {
                if (!$previewGlobalImg.length) return;
                $previewGlobalImg.attr('src', src).removeClass(HIDDEN);
                if ($previewGlobalEmpty.length) $previewGlobalEmpty.addClass(HIDDEN);
            }

            function limparCard() {
                if (!$previewGlobalImg.length) return;
                $previewGlobalImg.attr('src', '').addClass(HIDDEN);
                if ($previewGlobalEmpty.length) $previewGlobalEmpty.removeClass(HIDDEN);
            }

            function setPreview(src) {
                if (previewImg && previewWrap) {
                    previewImg.src = src;
                    previewWrap.hidden = false;
                }
                mostrarCard(src);
            }

            function clearPreview() {
                if (previewImg && previewWrap) {
                    previewImg.removeAttribute('src');
                    previewWrap.hidden = true;
                }
                limparCard();
            }

            function setProgress(done, total) {
                var pctValue = total > 0 ? Math.round((done / total) * 100) : 0;
                if (bar) bar.style.width = pctValue + '%';
                if (pct) pct.textContent = pctValue + '%';
            }

            function resetToIdle() {
                if (progressWrap) progressWrap.hidden = true;
                setProgress(0, 1);
            }

            function iniciarUpload(file) {
                if (accept.indexOf(file.type) === -1) {
                    setError('Formato não suportado. Usa JPG, PNG ou WebP.');
                    return;
                }
                if (file.size > maxBytes) {
                    setError('A imagem excede o máximo de 4MB.');
                    return;
                }

                setError('');
                if (progressWrap) progressWrap.hidden = false;
                setProgress(0, file.size);

                var dados = new FormData();
                dados.append('imagem', file);

                var leitor = new FileReader();
                leitor.onload = function (ev) { setPreview(ev.target.result); };
                leitor.readAsDataURL(file);

                var xhr = new XMLHttpRequest();
                xhr.open('POST', uploadUrl, true);
                xhr.setRequestHeader('X-CSRF-TOKEN', tokenCsrf($form.get(0)));
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.upload.onprogress = function (ev) {
                    if (ev.lengthComputable) setProgress(ev.loaded, ev.total);
                };
                xhr.onreadystatechange = function () {
                    if (xhr.readyState !== 4) return;
                    var resposta = null;
                    try { resposta = JSON.parse(xhr.responseText || '{}'); } catch (e) { resposta = null; }

                    if (xhr.status >= 200 && xhr.status < 300 && resposta && resposta.location) {
                        if (hidden) hidden.value = resposta.location;
                        if (removeBtn) removeBtn.hidden = false;
                        if (nameEl) nameEl.textContent = file.name + ' · ' + formatarTamanho(file.size) + ' · carregada';
                        resetToIdle();
                        setError('');
                        if (resposta.url) mostrarCard(resposta.url);
                    } else {
                        var msg = (resposta && (resposta.message || resposta.error)) || 'Falha no carregamento. Tenta novamente.';
                        if (xhr.status === 422 && resposta && resposta.errors) {
                            msg = Object.values(resposta.errors).flat().join(' ');
                        }
                        setError(msg);
                        resetToIdle();
                        clearPreview();
                    }
                };
                xhr.onerror = function () {
                    setError('Erro de rede no carregamento. Tenta novamente.');
                    resetToIdle();
                    clearPreview();
                };
                xhr.send(dados);
            }

            if (chooseBtn && input) {
                chooseBtn.addEventListener('click', function () { input.click(); });
            }
            if (input) {
                input.addEventListener('change', function () {
                    var ficheiro = input.files && input.files[0];
                    if (ficheiro) iniciarUpload(ficheiro);
                });
            }
            ['dragenter', 'dragover'].forEach(function (ev) {
                root.addEventListener(ev, function (e) { e.preventDefault(); root.classList.add('is-dragover'); });
            });
            ['dragleave', 'drop'].forEach(function (ev) {
                root.addEventListener(ev, function (e) { e.preventDefault(); root.classList.remove('is-dragover'); });
            });
            root.addEventListener('drop', function (e) {
                e.preventDefault();
                var ficheiro = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
                if (ficheiro) iniciarUpload(ficheiro);
            });

            if (removeBtn) {
                removeBtn.addEventListener('click', function () {
                    if (hidden) hidden.value = '';
                    clearPreview();
                    if (nameEl) nameEl.textContent = 'Sem imagem selecionada';
                    if (currentNameEl) currentNameEl.hidden = true;
                    if (input) input.value = '';
                    removeBtn.hidden = true;
                    setError('');
                });
            }
        });

        // ---------- Toggle Ficheiro/Link (campos no mesmo formulário) ----------
        $('input[name="imagem_fonte"]').each(function () {
            var radio = this;
            var $form = $(radio).closest('form');
            var $upload = $form.find('#imagem-ficheiro');
            var $linkPanel = $form.find('#imagem-link');
            var $hidden = $form.find('#imagem_link');
            var $linkUrlInput = $form.find('#imagem_link_url');
            var $previewGlobalImg = $form.find('#banner-preview-img');
            var $previewGlobalEmpty = $form.find('#banner-preview-empty');

            function toggleSrc(src) {
                if (src) {
                    $previewGlobalImg.attr('src', src).removeClass(HIDDEN);
                    if ($previewGlobalEmpty.length) $previewGlobalEmpty.addClass(HIDDEN);
                } else {
                    $previewGlobalImg.attr('src', '').addClass(HIDDEN);
                    if ($previewGlobalEmpty.length) $previewGlobalEmpty.removeClass(HIDDEN);
                }
            }

            function aplicarModo(modo) {
                if (modo === 'link') {
                    $upload.prop('hidden', true);
                    $linkPanel.prop('hidden', false);
                    if ($linkUrlInput.length) {
                        $hidden.val($linkUrlInput.val());
                        toggleSrc($linkUrlInput.val());
                    }
                } else {
                    $upload.prop('hidden', false);
                    $linkPanel.prop('hidden', true);
                    // Manter a prévia do upload actual (a img tem URL absoluta); senão limpa.
                    var srcAtual = $previewGlobalImg.length ? $previewGlobalImg.attr('src') : '';
                    toggleSrc(srcAtual && $previewGlobalImg.hasClass(HIDDEN) ? '' : srcAtual);
                }
            }

            radio.addEventListener('change', function () {
                if (radio.checked) aplicarModo(radio.value);
            });

            if ($linkUrlInput.length) {
                $linkUrlInput.on('input', function () {
                    $hidden.val($(this).val());
                    toggleSrc($(this).val());
                });
            }
        });

        // ---------- Caption Posição/Escopo no card de pré-visualização ----------
        $('#posicao, #escopo').on('change', function () {
            var $caption = $('#banner-preview-pos');
            if ($caption.length) $caption.text($(this).find('option:selected').text());
        });

        // ---------- Confirmação de ações (modal próprio; nada de window.confirm) ----------
        $(document).on('click', '.js-confirm-livewire', function () {
            var $btn = $(this);
            var $component = $btn.closest('[wire\\:id]');
            var componentId = $component.attr('wire:id');
            var action = $btn.data('action');
            var args = [];
            try { args = JSON.parse($btn.attr('data-args') || '[]'); } catch (e) { args = []; }
            if (window.PIGEAConfirm) {
                window.PIGEAConfirm(
                    $btn.data('title') || 'Confirmar ação',
                    $btn.data('message') || 'Tem a certeza?',
                    $btn.data('confirm-text') || 'Confirmar',
                    function () {
                        if (componentId && action && window.Livewire) {
                            window.Livewire.find(componentId).call.apply(null, [action].concat(args));
                        }
                    }
                );
            }
            return false;
        });
    });
})();
