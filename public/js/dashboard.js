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
