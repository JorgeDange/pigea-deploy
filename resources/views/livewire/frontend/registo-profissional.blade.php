<div id="registo-profissional" style="margin-top: 20px;">
    @if ($estado === 'ok')
        <div class="form-ok" style="padding: 22px 26px; border: 1px solid #d8efd1; border-radius: 6px; background: #f2faf0;">
            <strong>Registo submetido com sucesso.</strong> Aguarde a aprovação da nossa equipa.
        </div>
    @else
        @if ($estado === 'erro')
            <div class="form-error-box" style="padding: 14px 18px; border: 1px solid #f2c9c9; border-radius: 6px; background: #fdf3f3; color: #b42318; margin-bottom: 16px;">
                {{ $erro }}
            </div>
        @endif

        <form class="custom-form" name="registo-profissional" wire:submit.prevent="submit">
            <fieldset>
                <input type="text" name="nome_completo" placeholder="Nome Completo *" wire:model="nome_completo" />
                @error('nome_completo')<p class="form-error" role="alert">{{ $message }}</p>@enderror

                <input type="text" name="formacao_academica" placeholder="Formação Académica *" wire:model="formacao_academica" />
                @error('formacao_academica')<p class="form-error" role="alert">{{ $message }}</p>@enderror

                <input type="text" name="contactos" placeholder="Contactos (telefone e/ou email) *" wire:model="contactos" />
                @error('contactos')<p class="form-error" role="alert">{{ $message }}</p>@enderror

                <label style="margin-top: 12px; display: block;"><strong>Áreas de Atuação *</strong></label>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 8px; margin: 8px 0 16px;">
                    @foreach ($areasLista as $area)
                        <div class="filter-tags">
                            <input type="checkbox" id="area-prof-{{ $loop->index }}" value="{{ $area }}" wire:model="areas_atuacao" />
                            <label for="area-prof-{{ $loop->index }}">{{ $area }}</label>
                        </div>
                    @endforeach
                </div>
                @error('areas_atuacao')<p class="form-error" role="alert">{{ $message }}</p>@enderror
            </fieldset>

            <div class="clearfix"></div>
            <button type="submit" class="btn color-bg float-btn">Submeter registo <i class="fas fa-caret-right"></i></button>
        </form>
    @endif
</div>
