<div class="cookie-info-bar">
    <div class="container">
        <div class="cookie-info-bar_title"><i class="fal fa-cookie"></i>O PIGEA utiliza cookies. Saiba mais sobre a nossa política: <a href="#">Política de Cookies</a></div>
        <a href="#" class="sicb_btn color-bg">Aceitar</a>
        <a href="#" class="sicb_btn">Rejeitar</a>
    </div>
</div>
{{-- Persistência da escolha.
     O js/scripts.js do protótipo (linhas 93-102) apenas anima e remove a barra;
     sem este bloco a escolha não ficava guardada e a barra reaparecia sempre.
     Usa localStorage (não cookies) para não depender do próprio consentimento. --}}
<script>
(function () {
    var KEY = 'pigea.cookie-consent';
    var bar = document.currentScript.previousElementSibling;
    if (!bar || !bar.classList.contains('cookie-info-bar')) { return; }

    var stored = null;
    try { stored = window.localStorage.getItem(KEY); } catch (e) { /* storage indisponível */ }

    if (stored) { bar.parentNode.removeChild(bar); return; }

    var buttons = bar.querySelectorAll('.sicb_btn');
    Array.prototype.forEach.call(buttons, function (btn) {
        btn.addEventListener('click', function () {
            var choice = btn.classList.contains('color-bg') ? 'accepted' : 'rejected';
            try { window.localStorage.setItem(KEY, choice); } catch (e) {}
            document.dispatchEvent(new CustomEvent('pigea:cookie-consent', { detail: choice }));
        });
    });
})();
</script>
