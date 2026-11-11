<div class="directorio-listing">
    {{-- Abas: Empresas | Artigos/Documentos (restrutura.md §6) --}}
    <div class="dashboard-filters" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 22px;">
        <button type="button" class="btn small-btn {{ $aba === 'empresas' ? 'color-bg' : 'color2-bg' }}" wire:click="mostrar('empresas')">
            <i class="fas fa-building"></i> Empresas
        </button>
        <button type="button" class="btn small-btn {{ $aba === 'documentos' ? 'color-bg' : 'color2-bg' }}" wire:click="mostrar('documentos')">
            <i class="fas fa-file-alt"></i> Artigos/Documentos
        </button>
        <input type="text" placeholder="Filtrar por nome ou área de atuação…" class="filter-search" style="flex: 1; min-width: 200px;" wire:model.live.debounce.400ms="busca">
    </div>

    @if ($aba === 'empresas')
        <div class="row">
            @forelse ($empresas as $empresa)
                <div class="col-md-6" wire:key="empresa-{{ $empresa->id }}">
                    <div class="box-widget-content fl-wrap" style="height: 100%; margin-bottom: 22px;">
                        <div class="author-widget fl-wrap" style="padding: 18px 20px;">
                            <h4 style="margin-bottom: 4px;">{{ $empresa->nome_empresa }}</h4>
                            @if ($empresa->areas_atuacao)
                                <div style="margin: 10px 0 12px;">
                                    @foreach ($empresa->areas_atuacao as $area)
                                        <span style="display: inline-block; background: #f4f4f8; border-radius: 3px; padding: 3px 9px; font-size: 12px; margin: 2px 3px 2px 0;">{{ $area }}</span>
                                    @endforeach
                                </div>
                            @endif
                            <p style="margin-bottom: 6px;"><i class="fas fa-phone"></i> {{ $empresa->contactos }}</p>
                            @if ($empresa->redes_sociais)
                                <div class="footer-social fl-wrap" style="margin-top: 10px;">
                                    <ul>
                                        @foreach ($empresa->redes_sociais as $rede => $url)
                                            <li><a href="{{ $url }}" target="_blank" rel="noopener" title="{{ ucfirst($rede) }}"><i class="fab fa-{{ $rede === 'linkedin' ? 'linkedin-in' : ($rede === 'instagram' ? 'instagram' : 'facebook-f') }}"></i></a></li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            {{-- NIF deliberadamente NÃO apresentado no público (restrutura.md §6) --}}
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-md-12"><p>{{ $busca !== '' ? 'Nenhuma empresa corresponde à pesquisa.' : 'Ainda não há empresas aprovadas no diretório.' }}</p></div>
            @endforelse
        </div>
        <div class="dashboard-pagination">
            @for ($pagina = 1; $pagina <= $empresas->lastPage(); $pagina++)
            <a href="#" wire:click="gotoPage({{ $pagina }}, 'paginaEmpresas')" @class(['active' => $pagina === $empresas->currentPage()])>{{ $pagina }}</a>
            @endfor
            @if ($empresas->lastPage() > 1)
            <a href="#" wire:click="gotoPage({{ min($empresas->currentPage() + 1, $empresas->lastPage()) }}, 'paginaEmpresas')">»</a>
            @endif
        </div>
    @else
        <div class="row">
            @forelse ($documentos as $documento)
                <div class="col-md-6" wire:key="documento-{{ $documento->id }}">
                    <div class="box-widget-content fl-wrap" style="height: 100%; margin-bottom: 22px;">
                        <div class="author-widget fl-wrap" style="padding: 18px 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: baseline; gap: 10px;">
                                <h4 style="margin-bottom: 4px;">{{ $documento->titulo }}</h4>
                                <span style="display: inline-block; background: #f4f4f8; border-radius: 3px; padding: 3px 9px; font-size: 12px; white-space: nowrap;">{{ $documento->tipo_label }}</span>
                            </div>
                            <p style="margin: 8px 0 12px;">{{ $documento->descricao }}</p>
                            @if ($documento->areas_atuacao)
                                <div style="margin-bottom: 12px;">
                                    @foreach ($documento->areas_atuacao as $area)
                                        <span style="display: inline-block; background: #f4f4f8; border-radius: 3px; padding: 3px 9px; font-size: 12px; margin: 2px 3px 2px 0;">{{ $area }}</span>
                                    @endforeach
                                </div>
                            @endif
                            <a href="{{ $documento->link_download }}" target="_blank" rel="noopener" class="btn float-btn color-bg small-btn"><i class="fas fa-download"></i> Download</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-md-12"><p>{{ $busca !== '' ? 'Nenhum documento corresponde à pesquisa.' : 'Ainda não há documentos publicados pela equipa.' }}</p></div>
            @endforelse
        </div>
        <div class="dashboard-pagination">
            @for ($pagina = 1; $pagina <= $documentos->lastPage(); $pagina++)
            <a href="#" wire:click="gotoPage({{ $pagina }}, 'paginaDocumentos')" @class(['active' => $pagina === $documentos->currentPage()])>{{ $pagina }}</a>
            @endfor
            @if ($documentos->lastPage() > 1)
            <a href="#" wire:click="gotoPage({{ min($documentos->currentPage() + 1, $documentos->lastPage()) }}, 'paginaDocumentos')">»</a>
            @endif
        </div>
    @endif
</div>
