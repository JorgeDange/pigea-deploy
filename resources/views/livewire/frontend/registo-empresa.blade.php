<div id="registo-empresa" style="margin-top: 20px;">
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

        <form class="custom-form" name="registo-empresa" wire:submit.prevent="submit">
            <fieldset>
                <input type="text" name="nome_empresa" placeholder="Nome da Empresa *" wire:model="nome_empresa" />
                @error('nome_empresa')<p class="form-error" role="alert">{{ $message }}</p>@enderror

                <input type="text" name="nif" placeholder="NIF *" wire:model="nif" />
                @error('nif')<p class="form-error" role="alert">{{ $message }}</p>@enderror

                <input type="text" name="contactos" placeholder="Contactos (telefone e/ou email) *" wire:model="contactos" />
                @error('contactos')<p class="form-error" role="alert">{{ $message }}</p>@enderror

                <label style="margin-top: 12px; display: block;"><strong>Áreas de Atuação *</strong></label>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 8px; margin: 8px 0 16px;">
                    @foreach ($areasLista as $area)
                        <div class="filter-tags">
                            <input type="checkbox" id="area-empresa-{{ $loop->index }}" value="{{ $area }}" wire:model="areas_atuacao" />
                            <label for="area-empresa-{{ $loop->index }}">{{ $area }}</label>
                        </div>
                    @endforeach
                </div>
                @error('areas_atuacao')<p class="form-error" role="alert">{{ $message }}</p>@enderror

                <label style="display: block; margin-bottom: 8px;"><strong>Redes Sociais</strong> <small>(opcional)</small></label>
                <input type="url" name="facebook" placeholder="https://facebook.com/a-sua-empresa" wire:model="facebook" />
                @error('facebook')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                <input type="url" name="linkedin" placeholder="https://linkedin.com/company/a-sua-empresa" wire:model="linkedin" />
                @error('linkedin')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                <input type="url" name="instagram" placeholder="https://instagram.com/a-sua-empresa" wire:model="instagram" />
                @error('instagram')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                <input type="url" name="youtube" placeholder="https://youtube.com/@a-sua-empresa" wire:model="youtube" />
                @error('youtube')<p class="form-error" role="alert">{{ $message }}</p>@enderror
            </fieldset>

            <div class="clearfix"></div>
            <button type="submit" class="btn color-bg float-btn">Submeter registo <i class="fas fa-caret-right"></i></button>
        </form>
    @endif
</div>
