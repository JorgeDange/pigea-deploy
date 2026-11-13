// Newsletter modal (doc/newsletter.md) — aparece a pedir subscrição em
// todas as páginas públicas após ~3s. Desaparece de vez quando a pessoa
// subscreve (localStorage «pigea_newsletter_ok»); fechar é só para esta visita.
(function () {
    'use strict';

    var LOCAL = 'pigea_newsletter_ok';

    function lsObter(chave) {
        try { return window.localStorage.getItem(chave); } catch (e) { return null; }
    }
    function lsDefinir(chave, valor) {
        try { window.localStorage.setItem(chave, valor); } catch (e) { /* sem storage: sem memória */ }
    }

    var modal = document.getElementById('newsletter-modal');
    if (!modal) { return; }

    // Já subscreveu antes (localStorage) ou acabou de subscrever agora (flash) → nunca mostrar.
    if (lsObter(LOCAL) === '1' || modal.dataset.subscrita === '1') {
        lsDefinir(LOCAL, '1');
        modal.parentNode.removeChild(modal);
        return;
    }

    var timer = null;
    var card = modal.querySelector('.newsletter-modal-card');
    var form = document.getElementById('newsletter-modal-form');
    var sucesso = document.getElementById('newsletter-modal-sucesso');
    var conteudo = document.getElementById('newsletter-modal-conteudo');

    function abrir() {
        modal.classList.add('ativo');
        document.documentElement.classList.add('newsletter-modal-aberta');
        var input = document.getElementById('newsletter-modal-email');
        if (input) { setTimeout(function () { input.focus(); }, 250); }
    }

    function fechar() {
        modal.classList.remove('ativo');
        document.documentElement.classList.remove('newsletter-modal-aberta');
        if (timer) { clearTimeout(timer); timer = null; }
    }

    timer = setTimeout(abrir, 3000);

    modal.addEventListener('click', function (e) {
        var fecharBtn = e.target.closest('[data-fechar-modal]');
        if (fecharBtn) { fechar(); }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { fechar(); }
    });

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var botao = form.querySelector('button[type="submit"]');
            if (botao) { botao.disabled = true; }

            var dados = new FormData(form);
            window.fetch(form.action, {
                method: 'POST',
                body: dados,
                headers: { 'X-Requested-With': 'fetch', 'Accept': 'application/json' },
                credentials: 'same-origin'
            })
            .then(function (resposta) {
                if (resposta.status === 422) {
                    return resposta.json().then(function (erros) {
                        var primeira = erros && erros.errors && erros.errors.email && erros.errors.email[0];
                        window.alert(primeira || 'Escreva um email válido.');
                        if (botao) { botao.disabled = false; }
                    });
                }
                if (!resposta.ok) { throw new Error('resposta inesperada'); }
                return resposta.json();
            })
            .then(function (json) {
                if (!json || json.ok !== true) { throw new Error('resposta inválida'); }
                // Subscrição registada → nunca mais mostrar a modal nesta máquina.
                lsDefinir(LOCAL, '1');
                if (conteudo && sucesso) {
                    conteudo.hidden = true;
                    sucesso.hidden = false;
                }
            })
            .catch(function () {
                // Fallback: deixa o formulário seguir como POST normal (recarrega).
                form.submit();
            });
        });
    }
})();
