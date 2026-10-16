/* ==========================================================================
   PIGEA — Dashboard / Painéis (comportamentos)
   Sem backend: os formulários são apenas demonstrativos (pré-Laravel).
   ========================================================================== */
$(function () {
    'use strict';

    /* ---------- Sidebar (mobile) ---------- */
    $(document).on('click', '.sidebar-toggle', function () {
        $('.dashboard-wrap').toggleClass('sidebar-open');
    });
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.dashboard-sidebar, .sidebar-toggle').length) {
            $('.dashboard-wrap').removeClass('sidebar-open');
        }
    });

    /* ---------- Tabs de autenticação (Entrar / Registar) ---------- */
    $(document).on('click', '.auth-tabs button', function () {
        var url = $(this).data('href');
        if (url) {
            window.location.href = url;
        }
    });

    /* ---------- Formulários (demo estático) ---------- */
    $(document).on('submit', 'form.auth-form, form.dashboard-form', function (e) {
        if (this.id === 'registoform') {
            return; /* tratado pelo handler específico abaixo */
        }
        var form = this;
        if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
            return true; /* deixa o browser validar */
        }
        e.preventDefault();
        var $msg = $(form).find('.dashboard-msg');
        if (!$msg.length) {
            $msg = $('<div class="dashboard-msg"></div>');
            $(form).append($msg);
        }
        $msg.removeClass('is-error').text('Demo estático — guardado com sucesso. A integração com o Laravel ficará disponível na Fase 3.').stop(true, true).fadeIn(200);
        return false;
    });

    /* ---------- Registo de empresa (mantém a mensagem do protótipo) ---------- */
    $(document).on('submit', '#registoform', function () {
        var form = this;
        if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
            form.reportValidity();
            return false;
        }
        $('#message').hide().html('<div class="form-ok">Registo enviado com sucesso! A equipa PIGEA analisará os dados e entrará em contacto através do email indicado.</div>').slideDown('slow');
        form.reset();
        return false;
    });
    $(document).on('keyup', '#registoform input, #registoform select, #registoform textarea', function () {
        $('#message').slideUp(1500);
    });

    /* ---------- Notificações ---------- */
    $(document).on('click', '.js-mark-all-read', function () {
        $('.notification-item.unread').removeClass('unread');
        $('.badge').text('0');
        return false;
    });

    /* ---------- Modal ---------- */
    $(document).on('click', '.js-modal-open', function () {
        var id = $(this).data('modal');
        $('#' + id).addClass('open');
        return false;
    });
    $(document).on('click', '.js-modal-close', function () {
        $(this).closest('.dashboard-modal').removeClass('open');
        return false;
    });
    $(document).on('click', '.dashboard-modal', function (e) {
        if (e.target === this) {
            $(this).removeClass('open');
        }
    });

    /* ---------- Remover itens (guardadas / listas) ---------- */
    $(document).on('click', '.js-remove-item', function () {
        $(this).closest('.job-card, .notification-item, tr').fadeOut(250, function () {
            $(this).remove();
        });
        return false;
    });

    /* ---------- Filtros demonstrativos ---------- */
    $(document).on('change', '.dashboard-filters select, .dashboard-filters input', function () {
        var $wrap = $(this).closest('.card, .dashboard-content');
        var $empty = $wrap.find('.filter-empty');
        if (!$empty.length) {
            $empty = $('<div class="filter-empty dashboard-msg is-error" style="display:block">Sem resultados para o filtro selecionado (dados de demonstração).</div>');
            $wrap.find('.dashboard-filters').after($empty);
        }
        $empty.show();
        setTimeout(function () {
            $empty.fadeOut(300);
        }, 1600);
    });
});
