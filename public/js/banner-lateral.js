/* ============================================================================
   FASE 18 — BANNER VERTICAL rotativo (posição `lateral`, 2..4 slides)
   ----------------------------------------------------------------------------
   - Mesmo padrão do destaque.js (FASE 15): jQuery ready → dispara DEPOIS de
     `scripts.js` (initGmag), garantindo o Swiper do tema carregado.
   - O tema nunca inicializa `.banner-lateral-slider` (fora dos seletores
     dele) — criamos o nosso Swiper com autoplay.
   - Troca a cada 5000ms (data-autoplay do markup); 1 só slide → parado
     (o componente nem renderiza slider nesse caso).
   - loop só com mais de 1 slide; prefers-reduced-motion respeitado.
   ============================================================================ */
(function () {
    "use strict";

    function initBannerLateral() {
        if (typeof Swiper === "undefined") {
            return;
        }

        var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

        document.querySelectorAll(".banner-lateral-slider").forEach(function (el) {
            if (el.swiper) {
                return; // já inicializado (ex.: navegação Back/Forward cache)
            }

            var total = el.querySelectorAll(".swiper-slide").length;
            if (total <= 1) {
                return; // slider nunca renderiza com 1 slide, mas por segurança
            }

            var delay = parseInt(el.getAttribute("data-autoplay") || "5000", 10);

            new Swiper(el, {
                loop: total > 1,
                slidesPerView: 1,
                spaceBetween: 0,
                centeredSlides: false,
                freeMode: false,
                grabCursor: true,
                mousewheel: false,
                parallax: false,
                speed: 700,
                autoplay: reduced
                    ? false
                    : { delay: delay, enabled: true, disableOnInteraction: false, pauseOnMouseEnter: true },
            });
        });
    }

    if (window.jQuery) {
        window.jQuery(document).ready(function () { initBannerLateral(); });
    } else if (document.readyState !== "loading") {
        initBannerLateral();
    } else {
        document.addEventListener("DOMContentLoaded", initBannerLateral);
    }
})();
