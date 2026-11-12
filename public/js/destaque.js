/* ============================================================================
   FASE 15 — DESTAQUE publicitário rotativo (pub.md, autoplay 4s)
   ----------------------------------------------------------------------------
   - Ordem de arranque: jQuery ready → dispara DEPOIS de `scripts.js`
     (initGmag), garantindo que o Swiper do tema já existe por esta altura;
     ali aplicamos autoplay ao Swiper EXISTENTE.
   - Sidebars (fora de .single-grid-slider): init próprio com 4s fixos.
   - Rotação a cada 4000ms (data-autoplay do markup); 1 só slide → parado.
   - loop só com mais de 1 slide; prefers-reduced-motion respeitado.
   ============================================================================ */
(function () {
    "use strict";

    function initDestaque() {
        if (typeof Swiper === "undefined") {
            return;
        }

        var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

        document.querySelectorAll(".destaque-slider").forEach(function (el) {
            var total = el.querySelectorAll(".swiper-slide").length;
            var delay = parseInt(el.getAttribute("data-autoplay") || "4000", 10);
            var autoplay = (reduced || total <= 1)
                ? false
                : { delay: delay, enabled: true, disableOnInteraction: false, pauseOnMouseEnter: true };

            // Swiper existente? (init do tema) — aplicar autoplay nele.
            var existing = el.swiper;
            if (existing && existing.autoplay) {
                existing.params.autoplay = autoplay ? autoplay : false;
                if (autoplay && existing.autoplay.stop) {
                    try {
                        existing.autoplay.stop();
                        existing.autoplay.start();
                    } catch (e) {}
                } else if (existing.autoplay.stop) {
                    try { existing.autoplay.stop(); } catch (e) {}
                }
                return;
            }

            // Sem Swiper (sidebar ou markup reorganizado) — criar o nosso.
            new Swiper(el, {
                preloadImages: false,
                loop: total > 1,
                centeredSlides: false,
                freeMode: false,
                slidesPerView: 1,
                spaceBetween: 0,
                grabCursor: true,
                mousewheel: false,
                parallax: false,
                speed: 700,
                autoplay: autoplay,
                pagination: {
                    el: el.querySelector(".sgs-pagination"),
                    clickable: true,
                },
            });
        });
    }

    if (window.jQuery) {
        window.jQuery(document).ready(function () { initDestaque(); });
    } else if (document.readyState !== "loading") {
        initDestaque();
    } else {
        document.addEventListener("DOMContentLoaded", initDestaque);
    }
})();
