/* ============================================================================
   FASE 5.1 — MEGA MENU: comportamento (menu.md §5.1)
   ----------------------------------------------------------------------------
   Desktop (≥1065px) — hover-intent:
     Entrar num item com NENHUM painel aberto → abre já.
     Entrar num item com OUTRO painel aberto → espera OPEN_SWITCH ms antes de
     trocar; se o rato sair entretanto, o timer é cancelado (passagens em
     diagonal não trocam de painel nem atiram o alvo ao ar).
     O painel só fecha: ao sair do menu, ao abrir outro item, ou CLOSE_LEAVE
     ms após sair do item (CLOSE_LEAVE >= OPEN_SWITCH — comutação atómica).
     Abertura por teclado continua via CSS :focus-within (a11y).
   Mobile (≤1064px) — acordeão:
     o clique no item de topo abre/fecha o painel da secção (um de cada vez);
     fora do menu fecha tudo.
   Delegação em `document` — sobrevive ao clone `.menusb` do tema (scripts.js)
   ============================================================================ */
(function () {
    "use strict";

    var MOBILE = "(max-width: 1064px)";
    var OPEN_SWITCH = 180; // ms antes de trocar de painel estando outro aberto
    var CLOSE_LEAVE = 220; // ms antes de fechar ao sair do item (>= OPEN_SWITCH)

    // Registro único de todos os timers pendentes de hover (evita fugas).
    var pendentes = new Set();

    function agendar(ms, fn) {
        var t = setTimeout(function () {
            pendentes.delete(t);
            fn();
        }, ms);
        pendentes.add(t);
        return t;
    }

    function cancelar(t) {
        clearTimeout(t);
        pendentes.delete(t);
    }

    function cancelarTodos() {
        pendentes.forEach(function (t) {
            clearTimeout(t);
        });
        pendentes.clear();
    }

    function ehMobile() {
        return window.matchMedia(MOBILE).matches;
    }

    function definirExpandido(item, aberto) {
        var link = item.querySelector(".mm-topo");
        if (link) {
            link.setAttribute("aria-expanded", aberto ? "true" : "false");
        }
    }

    // ---------------- Mobile: acordeão (um de cada vez) ----------------
    function fecharTodosAcordeao() {
        document.querySelectorAll(".mm-item.mm-aberto").forEach(function (item) {
            item.classList.remove("mm-aberto");
            definirExpandido(item, false);
        });
    }

    // ---------------- Desktop: hover-intent ----------------
    function fecharHoverTodos() {
        cancelarTodos();
        document.querySelectorAll(".mm-item.mm-hover").forEach(function (item) {
            item.classList.remove("mm-hover");
            definirExpandido(item, false);
        });
    }

    document.querySelectorAll(".mega-menu .mm-item").forEach(function (item) {
        var enterTimer = null;
        var leaveTimer = null;
        definirExpandido(item, false);

        item.addEventListener("mouseenter", function () {
            if (ehMobile()) {
                return;
            }
            cancelar(enterTimer);
            cancelar(leaveTimer);
            if (item.classList.contains("mm-hover")) {
                return;
            }
            // Há outro painel aberto? Só troca com intenção clara.
            var outroAberto = document.querySelector(".mega-menu .mm-item.mm-hover");
            var delay = outroAberto && outroAberto !== item ? OPEN_SWITCH : 0;
            enterTimer = agendar(delay, function () {
                fecharHoverTodos();
                item.classList.add("mm-hover");
                definirExpandido(item, true);
            });
        });

        item.addEventListener("mouseleave", function () {
            if (ehMobile()) {
                return;
            }
            cancelar(enterTimer);
            cancelar(leaveTimer);
            leaveTimer = agendar(CLOSE_LEAVE, function () {
                item.classList.remove("mm-hover");
                definirExpandido(item, false);
            });
        });
    });

    // Sair completamente do mega menu fecha de imediato.
    var mm = document.querySelector(".mega-menu");
    if (mm) {
        mm.addEventListener("mouseleave", function () {
            if (ehMobile()) {
                return;
            }
            fecharHoverTodos();
        });
    }

    // Clique num item de topo (mobile) — delegação em document;
    // no desktop o topo navega para a secção normalmente.
    document.addEventListener("click", function (evento) {
        var link = evento.target.closest(".mm-topo");

        if (link && ehMobile()) {
            var item = link.closest(".mm-item");
            if (!item) {
                return;
            }
            evento.preventDefault(); // no mobile o topo abre/fecha o acordeão

            var jaAberto = item.classList.contains("mm-aberto");
            fecharTodosAcordeao();
            if (!jaAberto) {
                item.classList.add("mm-aberto");
                definirExpandido(item, true);
            }
            return;
        }

        // Clique fora do menu fecha o acordeão (mobile).
        if (ehMobile() && !evento.target.closest(".mega-menu")) {
            fecharTodosAcordeao();
        }
    });

    // Ao redimensionar para desktop, limpa o estado do acordeão;
    // ao redimensionar para mobile, limpa o estado hover.
    window.addEventListener("resize", function () {
        if (ehMobile()) {
            fecharHoverTodos();
        } else {
            fecharTodosAcordeao();
        }
    });
})();
