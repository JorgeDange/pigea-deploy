<div id="contact-form" style="margin-top: 20px;">
    <div id="message">
        @if ($estado === 'ok')
            <div class="form-ok">Mensagem enviada com sucesso! A equipa PIGEA responderá através do email indicado.</div>
        @elseif ($estado === 'erro')
            {{ $erro }}
        @endif
    </div>
    <form  class="custom-form" action="#" name="contactform" id="contactform" wire:submit.prevent="enviar">
        <fieldset>
            <input type="text" name="name" id="name" placeholder="Nome *" wire:model="name" required/>
            <input type="text"  name="email" id="email" placeholder="Email *" wire:model="email" required/>
            <textarea name="comments"  id="comments" cols="40" rows="3" placeholder="Mensagem:" wire:model="comments"></textarea>
        </fieldset>
        <button class="btn   color-bg float-btn" id="submit">Enviar mensagem <i class="fas fa-caret-right"></i></button>
    </form>
</div>
