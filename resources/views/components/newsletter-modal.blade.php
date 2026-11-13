{{-- Modal de subscrição da Newsletter do PIGEA (doc/newsletter.md).

     Mecânica adotada pela maioria dos sites de notícias:
      - aparece em TODAS as páginas públicas após ~3 segundos;
      - só desaparece de vez depois de a pessoa subscrever (localStorage);
      - fechar (X/backdrop/Esc) → esconde só para a visita actual; volta no
        próximo carregamento — de propósito: é um pedido, não uma persuasão.

     Sem JavaScript: este bloco não mostra nada (a subscrição normal nos
     Contactos continua disponível). --}}
@php
    $jaSubscreveu = session('newsletter_msg') !== null;
@endphp

<div class="newsletter-modal" id="newsletter-modal" role="dialog" aria-modal="true" aria-labelledby="newsletter-modal-titulo" @if ($jaSubscreveu) data-subscrita="1" @endif>
    <div class="newsletter-modal-backdrop" data-fechar-modal></div>
    <div class="newsletter-modal-card" role="document">
        <button type="button" class="newsletter-modal-fechar" data-fechar-modal aria-label="Não quero subscrever">&times;</button>

        <div class="newsletter-modal-conteudo" id="newsletter-modal-conteudo">
            <i class="fas fa-envelope-open-text newsletter-modal-icone" aria-hidden="true"></i>
            <h3 id="newsletter-modal-titulo">Receba as novidades do PIGEA</h3>
            <p class="newsletter-modal-texto">
                O que está a acontecer na mineração e nas geociências em Angola — diretamente no seu email. Confirmação por email.
            </p>

            <form class="newsletter-modal-form" id="newsletter-modal-form" action="{{ route('newsletter.subscribe') }}" method="POST">
                @csrf
                <label class="newsletter-modal-label" for="newsletter-modal-email">Email</label>
                <input type="email" class="newsletter-modal-input" id="newsletter-modal-email" name="email" required maxlength="190" autocomplete="email" placeholder="o.seu@email.com">
                <button type="submit" class="newsletter-modal-btn color-bg">Subscrever <i class="fas fa-paper-plane" aria-hidden="true"></i></button>
            </form>

            <p class="newsletter-modal-nota">Sem spam — cancele quando quiser.</p>
        </div>

        <div class="newsletter-modal-sucesso" id="newsletter-modal-sucesso" hidden>
            <i class="fas fa-check-circle newsletter-modal-sucesso-icon" aria-hidden="true"></i>
            <h3>Verifique o seu email</h3>
            <p>Enviámos-lhe o link de confirmação. Clique nele para completar a subscrição.</p>
            <button type="button" class="newsletter-modal-btn color-bg" data-fechar-modal>Entendido</button>
        </div>
    </div>
</div>

<script src="{{ asset('js/newsletter-modal.js') }}" defer></script>
