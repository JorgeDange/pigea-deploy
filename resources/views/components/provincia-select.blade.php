{{-- Select de província partilhado (FASE E1): as 18 províncias oficiais de
     Angola + «Nacional» + «Outros» (texto livre, ex.: notícias de fora do país).

     - Lista: App\Support\Provincias::LISTA (fonte única de verdade).
     - «Outros» revela um campo de texto `provincia_outro` (máx. 100 caracteres);
       o texto digitado é o que fica gravado em `province`.
     - Valor guardado que não é um slug conhecido (ex.: «Holanda», ou o legado
       «cuanza-sul») é apresentado como «Outros» com o texto.

     Props:
       value  — valor guardado (`province`)
       id     — id base do select
       label  — rótulo (modo completo)
       hint   — nota ao lado do rótulo (modo completo)
       bare   — true = só select + input + JS (o formulário genérico
                `admin/conteudo-form` fornece moldura, rótulo e @error)

     Uso: <x-provincia-select :value="$noticia?->province" />
          <x-provincia-select :value="$campo['valor']" bare /> --}}
@props([
    'value' => null,
    'id' => 'provincia',
    'label' => 'Província',
    'hint' => '(opcional — usada no gráfico do painel)',
    'bare' => false,
])
@php
    use App\Support\Provincias;

    $atual = old('provincia', $value);
    $atualNormalizado = ($atual === null || $atual === '') ? null : Provincias::normalizar((string) $atual);
    $conhecido = $atualNormalizado !== null && array_key_exists($atualNormalizado, Provincias::LISTA);

    if ($atualNormalizado === null) {
        $selecionado = '';
        $textoLivre = old('provincia_outro', '');
    } elseif ($conhecido) {
        $selecionado = $atualNormalizado;
        $textoLivre = old('provincia_outro', '');
    } else {
        // Valor de texto livre (ou slug desconhecido): apresenta «Outros» + texto
        $selecionado = 'outros';
        $textoLivre = old('provincia_outro', (string) $atual);
    }
@endphp
<div class="{{ $bare ? '' : 'dashboard-form-group provincia-field' }}">
    @unless ($bare)
        <label class="dashboard-form-label" for="{{ $id }}">{{ $label }} <small class="form-label-hint">{{ $hint }}</small></label>
    @endunless
    <select class="dashboard-form-select provincia-select" id="{{ $id }}" name="provincia" aria-describedby="erro-provincia">
        <option value="">Sem província</option>
        @foreach (Provincias::LISTA as $slug => $rotulo)
            <option value="{{ $slug }}" @selected($selecionado === $slug)>{{ $rotulo }}</option>
        @endforeach
        <option value="outros" @selected($selecionado === 'outros')>Outros — escrever</option>
    </select>
    <input type="text" id="{{ $id }}_outro" name="provincia_outro" class="dashboard-form-input provincia-outro" maxlength="100"
        style="margin-top:8px{{ $selecionado === 'outros' ? '' : ';display:none' }}" placeholder="ex.: Internacional, Holanda,…" value="{{ $textoLivre }}"
        aria-label="Província de texto livre">
    @unless ($bare)
        <p class="helper-text">Para notícias/projetos de fora de Angola, escolha «Outros» e escreva o país/província livremente.</p>
        @error('provincia')<p class="form-error" id="erro-provincia" role="alert">{{ $message }}</p>@enderror
        @error('provincia_outro')<p class="form-error" id="erro-provincia-outro" role="alert">{{ $message }}</p>@enderror
    @endunless
    <script>
        (function () {
            var sel = document.getElementById(@json($id));
            var input = document.getElementById(@json($id . '_outro'));
            if (!sel || !input || sel.dataset.provinciaLigada) return;
            sel.dataset.provinciaLigada = '1';
            sel.addEventListener('change', function () {
                var outro = sel.value === 'outros';
                input.style.display = outro ? '' : 'none';
                if (!outro) {
                    input.value = '';
                } else {
                    input.focus();
                }
            });
        })();
    </script>
</div>
