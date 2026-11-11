/* ============================================================================
   FASE 5.1 — MEGA MENU: comportamento (menu.md §5.1)
   ----------------------------------------------------------------------------
   - Desktop (≥1065px): abertura por hover/focus-within é 100% CSS (menu.css);
     o clique no item de topo navega para a página da secção (menu.md §3).
   - Mobile (≤1064px): acordeão — o clique no item de topo abre/fecha o painel
     da secção (um de cada vez) em vez de navegar; fora do menu fecha tudo.
   - Delegação em `document`: sobrevive ao clone `.menusb` do tema (scripts.js)
     e às trocas de página Livewire.
   ============================================================================ */
(function () {
    "use strict";

    var MOBILE = "(max-width: 1064px)";

    function ehMobile() {
        return window.matchMedia(MOBILE).matches;
    }

    function fecharTodos() {
        document.querySelectorAll(".mm-item.mm-aberto").forEach(function (item) {
            item.classList.remove("mm-aberto");
            definirExpandido(item, false);
        });
    }

    function definirExpandido(item, aberto) {
        var link = item.querySelector(".mm-topo");
        if (link) {
            link.setAttribute("aria-expanded", aberto ? "true" : "false");
        }
    }

    document.addEventListener("click", function (evento) {
        var link = evento.target.closest(".mm-topo");

        if (link && ehMobile()) {
            var item = link.closest(".mm-item");
            if (!item) {
                return;
            }
            evento.preventDefault(); // no mobile o topo abre/fecha o acordeão

            var jaAberto = item.classList.contains("mm-aberto");
            fecharTodos();
            if (!jaAberto) {
                item.classList.add("mm-aberto");
                definirExpandido(item, true);
            }
            return;
        }

        // Clique fora do menu fecha o acordeão (mobile).
        if (ehMobile() && !evento.target.closest(".mega-menu")) {
            fecharTodos();
        }
    });

    // Ao redimensionar para desktop, limpa o estado do acordeão.
    window.addEventListener("resize", function () {
        if (!ehMobile()) {
            fecharTodos();
        }
    });
})();
