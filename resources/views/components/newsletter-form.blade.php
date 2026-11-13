{{-- Formulário de subscrição da Newsletter do PIGEA (doc/newsletter.md).
     Decisão do cliente (2026-11-13): apenas o EMAIL — sem nome.

     Duas variantes: `rodape` (compacto, fundo escuro) e `contacto` (cartão
     branco na página de Contactos). Só funciona com JS desligado — POST
     para `newsletter.subscribe`, que envia o email de confirmação.

     Feedback pela sessão: flash `newsletter_msg =
     ['tipo' => 'ok'|'erro', 'texto' => string]`. --}}
@props(['variante' => 'rodape'])
@php
    $msg = session('newsletter_msg');
    $escuro = $variante === 'rodape';
    $id = 'newsletter-form-' . substr(md5($variante . microtime(true)), 0, 6);
@endphp

<form class="newsletter-form {{ $escuro ? 'newsletter-form-escura' : 'newsletter-form-clara' }}" action="{{ route('newsletter.subscribe') }}" method="POST" aria-label="Subscrever a newsletter do PIGEA">
    @csrf
    <h3 class="newsletter-titulo">Receba as novidades</h3>
    <p class="newsletter-subtitulo">Geociências em Angola diretamente no seu email.</p>

    @if (is_array($msg) && isset($msg['texto']))
        <p class="newsletter-msg newsletter-msg-{{ ($msg['tipo'] ?? 'ok') === 'ok' ? 'ok' : 'erro' }}" role="status">{{ $msg['texto'] }}</p>
    @endif

    <div class="newsletter-campos">
        <label class="newsletter-label" for="{{ $id }}-email">Email</label>
        <input class="newsletter-input" type="email" id="{{ $id }}-email" name="email" value="{{ old('email') }}" maxlength="190" autocomplete="email" required placeholder="o.seu@email.com">
        @error('email')<p class="newsletter-erro" role="alert">{{ $message }}</p>@enderror

        <button type="submit" class="newsletter-btn color-bg">Subscrever <i class="fas fa-paper-plane" aria-hidden="true"></i></button>
    </div>

    <p class="newsletter-nota">Ao subscrever aceita receber as novidades do PIGEA por email. Pode cancelar a qualquer momento.</p>
</form>
