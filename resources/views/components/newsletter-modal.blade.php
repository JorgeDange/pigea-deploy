{{-- Modal de subscrição da Newsletter do PIGEA.

     Padrão dos sites de notícias: aparece ~3s após cada carregamento, em
     todas as páginas públicas. Só desaparece de vez depois de SUBSCREVER.
     Fechar (✕) = só para esta visita.

     Sem JavaScript: este bloco não mostra nada (a subscrição em Contactos
     continua disponível). --}}
@php
    $jaSubscreveu = session('newsletter_msg') !== null;
@endphp

<div class="newsletter-modal" id="newsletter-modal" role="dialog" aria-modal="true" aria-labelledby="newsletter-modal-titulo" @if ($jaSubscreveu) data-subscrita="1" @endif>
    <div class="newsletter-modal-backdrop" data-fechar-modal></div>
    <div class="newsletter-modal-card" role="document">
        <button type="button" class="newsletter-modal-fechar" data-fechar-modal aria-label="Não quero subscrever">&times;</button>

        <div class="newsletter-modal-conteudo" id="newsletter-modal-conteudo">
            <i class="fas fa-envelope newsletter-modal-icone" aria-hidden="true"></i>
            <h3 id="newsletter-modal-titulo">Receba o PIGEA no seu email</h3>

            <form class="newsletter-modal-form" id="newsletter-modal-form" action="{{ route('newsletter.subscribe') }}" method="POST">
                @csrf
                <input type="email" class="newsletter-modal-input" id="newsletter-modal-email" name="email" required maxlength="190" autocomplete="email" placeholder="O seu email" aria-label="Email">
                <button type="submit" class="newsletter-modal-btn color-bg">Subscrever <i class="fas fa-paper-plane" aria-hidden="true"></i></button>
            </form>
        </div>

        <div class="newsletter-modal-sucesso" id="newsletter-modal-sucesso" hidden>
            <i class="fas fa-check-circle newsletter-modal-sucesso-icone" aria-hidden="true"></i>
            <h3>Verifique o seu email</h3>
            <button type="button" class="newsletter-modal-btn color-bg" data-fechar-modal>Entendido</button>
        </div>
    </div>
</div>

<script src="{{ asset('js/newsletter-modal.js') }}" defer></script>
