<div class="directorio-listing">
    {{-- Abas: Empresas | Profissionais | Artigos/Documentos (restrutura.md §6) — padrão de abas do tema (.tabs-widget/.tabs-menu) --}}
    <div class="content-tabs-wrap tabs-act tabs-widget fl-wrap">
        <div class="content-tabs fl-wrap">
            <ul class="tabs-menu fl-wrap no-list-style">
                <li class="{{ $aba === 'empresas' ? 'current' : '' }}"><a href="#tab-empresas" wire:click.prevent="mostrar('empresas')"><i class="fas fa-building" aria-hidden="true"></i>Empresas</a></li>
                <li class="{{ $aba === 'profissionais' ? 'current' : '' }}"><a href="#tab-profissionais" wire:click.prevent="mostrar('profissionais')"><i class="fas fa-user-tie" aria-hidden="true"></i>Profissionais</a></li>
                <li class="{{ $aba === 'documentos' ? 'current' : '' }}"><a href="#tab-documentos" wire:click.prevent="mostrar('documentos')"><i class="fas fa-file-alt" aria-hidden="true"></i>Artigos/Documentos</a></li>
            </ul>
        </div>
    </div>
    <input type="text" placeholder="Filtrar por nome ou área de atuação…" aria-label="Filtrar por nome ou área de atuação" class="filter-search" wire:model.live.debounce.400ms="busca">

    @if ($aba === 'empresas')
        <div class="row">
            @forelse ($empresas as $empresa)
                {{-- Iniciais da empresa para o avatar — 2 letras significativas (sem sufixos legais) --}}
                @php
                    $palavras = preg_split('/\s+/u', trim((string) $empresa->nome_empresa)) ?: [];
                    $sufixos = ['de', 'da', 'do', 'dos', 'das', 'e', 'lda', 'sa', 's.a', 's.a.', 'ltda', 'limitada', 'inc', 'corp'];
                    $uteis = array_values(array_filter($palavras, fn ($p) => mb_strlen($p) > 0 && ! in_array(mb_strtolower($p), $sufixos, true)));
                    $iniciais = mb_strtoupper(mb_substr($uteis[0] ?? 'E', 0, 1) . mb_substr($uteis[1] ?? '', 0, 1));
                @endphp
                <div class="col-md-6" wire:key="empresa-{{ $empresa->id }}">
                    <div class="box-widget-content fl-wrap">
                        <div class="author-widget fl-wrap">
                            <div aria-hidden="true">
                                <div><span>{{ $iniciais }}</span></div>
                            </div>
                            <div>
                                <h4>{{ $empresa->nome_empresa }}</h4>
                                <span><i class="fas fa-check-circle" aria-hidden="true"></i>Empresa aprovada</span>
                                @if ($empresa->areas_atuacao)
                                    <div>
                                        @foreach ($empresa->areas_atuacao as $area)
                                            <span class="post-category-marker">{{ $area }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                <p><i class="fas fa-phone-alt" aria-hidden="true"></i>{{ $empresa->contactos }}</p>
                                {{-- NIF deliberadamente NÃO apresentado no público (restrutura.md §6) --}}
                                @if ($empresa->redes_sociais)
                                    <div>
                                        <ul class="footer-social fl-wrap">
                                            @foreach ($empresa->redes_sociais as $rede => $url)
                                                <li><a href="{{ $url }}" target="_blank" rel="noopener" title="{{ ucfirst($rede) }}" aria-label="{{ ucfirst($rede) }} — {{ $empresa->nome_empresa }}"><i class="fab fa-{{ $rede === 'linkedin' ? 'linkedin-in' : ($rede === 'instagram' ? 'instagram' : 'facebook-f') }}" aria-hidden="true"></i></a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-md-12">
                    <div class="empty-state">
                        <i class="fas fa-building empty-state-icon" aria-hidden="true"></i>
                        <p class="empty-state-title">Nada encontrado</p>
                        <p class="empty-state-text">{{ $busca !== '' ? 'Nenhuma empresa corresponde à pesquisa. Ajuste os termos ou limpe o filtro.' : 'Ainda não há empresas aprovadas no diretório.' }}</p>
                    </div>
                </div>
            @endforelse
        </div>
        <div class="clearfix"></div>
        <div class="pagination" role="navigation" aria-label="Paginação de empresas">
            @if ($empresas->currentPage() > 1)
                <a href="#" wire:click.prevent="gotoPage({{ $empresas->currentPage() - 1 }}, 'paginaEmpresas')" class="prevposts-link" aria-label="Página anterior"><i class="fas fa-caret-left" aria-hidden="true"></i></a>
            @endif
            @for ($pagina = 1; $pagina <= $empresas->lastPage(); $pagina++)
                <a href="#" wire:click.prevent="gotoPage({{ $pagina }}, 'paginaEmpresas')" @class(['current-page' => $pagina === $empresas->currentPage()]) aria-label="Página {{ $pagina }}" @if ($pagina === $empresas->currentPage()) aria-current="page" @endif>{{ str_pad($pagina, 2, '0', STR_PAD_LEFT) }}.</a>
            @endfor
            @if ($empresas->currentPage() < $empresas->lastPage())
                <a href="#" wire:click.prevent="gotoPage({{ min($empresas->currentPage() + 1, $empresas->lastPage()) }}, 'paginaEmpresas')" class="nextposts-link" aria-label="Página seguinte"><i class="fas fa-caret-right" aria-hidden="true"></i></a>
            @endif
        </div>
    @elseif ($aba === 'profissionais')
        <div class="row">
            @forelse ($profissionais as $profissional)
                {{-- Iniciais do profissional para o avatar — nome útil (sem honoríficos "Eng.ª"/"Dr."/…), primeira + última palavra --}}
                @php
                    $raw = trim((string) $profissional->nome_completo);
                    $tokens = preg_split('/\s+/u', $raw) ?: [];
                    $honorificos = ['eng', 'engr', 'dr', 'dra', 'prof', 'sr', 'sra', 'srta', 'mr', 'mrs'];
                    $uteis = [];
                    foreach ($tokens as $token) {
                        // Normaliza para a base dos honoríficos: "Eng.ª" → "eng", "Dr." → "dr"
                        $base = mb_strtolower($token, 'UTF-8');
                        $base = preg_replace('/[\.\-,]/u', '', $base);
                        $base = preg_replace('/[ªº°]/u', '', $base);
                        if ($base === '' || in_array($base, $honorificos, true)) {
                            continue;
                        }
                        $uteis[] = $token;
                    }
                    if (count($uteis) === 0) {
                        $uteis = $tokens; // segurança: nunca fica sem iniciais
                    }
                    $primeira = mb_strtoupper(mb_substr($uteis[0], 0, 1, 'UTF-8'), 'UTF-8');
                    $ultima = count($uteis) > 1 ? mb_strtoupper(mb_substr(end($uteis), 0, 1, 'UTF-8'), 'UTF-8') : '';
                    $iniciais = $primeira.$ultima;
                @endphp
                <div class="col-md-6" wire:key="profissional-{{ $profissional->id }}">
                    <div class="box-widget-content fl-wrap">
                        <div class="author-widget fl-wrap">
                            <div aria-hidden="true">
                                <div><span>{{ $iniciais }}</span></div>
                            </div>
                            <div>
                                <h4>{{ $profissional->nome_completo }}</h4>
                                <span><i class="fas fa-check-circle" aria-hidden="true"></i>Profissional aprovado</span>
                                @if ($profissional->formacao_academica)
                                    <p>{{ $profissional->formacao_academica }}</p>
                                @endif
                                @if ($profissional->areas_atuacao)
                                    <div>
                                        @foreach ($profissional->areas_atuacao as $area)
                                            <span class="post-category-marker">{{ $area }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                <p><i class="fas fa-phone-alt" aria-hidden="true"></i>{{ $profissional->contactos }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-md-12">
                    <div class="empty-state">
                        <i class="fas fa-user-tie empty-state-icon" aria-hidden="true"></i>
                        <p class="empty-state-title">Nada encontrado</p>
                        <p class="empty-state-text">{{ $busca !== '' ? 'Nenhum profissional corresponde à pesquisa. Ajuste os termos ou limpe o filtro.' : 'Ainda não há profissionais aprovados no diretório.' }}</p>
                    </div>
                </div>
            @endforelse
        </div>
        <div class="clearfix"></div>
        <div class="pagination" role="navigation" aria-label="Paginação de profissionais">
            @if ($profissionais->currentPage() > 1)
                <a href="#" wire:click.prevent="gotoPage({{ $profissionais->currentPage() - 1 }}, 'paginaProfissionais')" class="prevposts-link" aria-label="Página anterior"><i class="fas fa-caret-left" aria-hidden="true"></i></a>
            @endif
            @for ($pagina = 1; $pagina <= $profissionais->lastPage(); $pagina++)
                <a href="#" wire:click.prevent="gotoPage({{ $pagina }}, 'paginaProfissionais')" @class(['current-page' => $pagina === $profissionais->currentPage()]) aria-label="Página {{ $pagina }}" @if ($pagina === $profissionais->currentPage()) aria-current="page" @endif>{{ str_pad($pagina, 2, '0', STR_PAD_LEFT) }}.</a>
            @endfor
            @if ($profissionais->currentPage() < $profissionais->lastPage())
                <a href="#" wire:click.prevent="gotoPage({{ min($profissionais->currentPage() + 1, $profissionais->lastPage()) }}, 'paginaProfissionais')" class="nextposts-link" aria-label="Página seguinte"><i class="fas fa-caret-right" aria-hidden="true"></i></a>
            @endif
        </div>
    @else
        <div class="row">
            @forelse ($documentos as $documento)
                @php
                    $iconeDoc = match ($documento->tipo) {
                        'lei' => 'fa-gavel',
                        'artigo' => 'fa-newspaper',
                        'documento' => 'fa-file-contract',
                        default => 'fa-file-alt',
                    };
                @endphp
                <div class="col-md-6" wire:key="documento-{{ $documento->id }}">
                    <div class="box-widget-content fl-wrap">
                        <div class="author-widget fl-wrap">
                            <div aria-hidden="true">
                                <div class="directorio-icone"><i class="fas {{ $iconeDoc }}"></i></div>
                            </div>
                            <div>
                                <span class="post-category-marker">{{ $documento->tipo_label }}</span>
                                <h4>{{ $documento->titulo }}</h4>
                                <p>{{ $documento->descricao }}</p>
                                @if ($documento->areas_atuacao)
                                    <div>
                                        @foreach ($documento->areas_atuacao as $area)
                                            <span class="post-category-marker">{{ $area }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ $documento->link_download }}" target="_blank" rel="noopener" class="btn float-btn color-bg">Download<i class="fas fa-download" aria-hidden="true"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-md-12">
                    <div class="empty-state">
                        <i class="fas fa-file-alt empty-state-icon" aria-hidden="true"></i>
                        <p class="empty-state-title">Nada encontrado</p>
                        <p class="empty-state-text">{{ $busca !== '' ? 'Nenhum documento corresponde à pesquisa. Ajuste os termos ou limpe o filtro.' : 'Ainda não há documentos publicados pela equipa.' }}</p>
                    </div>
                </div>
            @endforelse
        </div>
        <div class="clearfix"></div>
        <div class="pagination" role="navigation" aria-label="Paginação de documentos">
            @if ($documentos->currentPage() > 1)
                <a href="#" wire:click.prevent="gotoPage({{ $documentos->currentPage() - 1 }}, 'paginaDocumentos')" class="prevposts-link" aria-label="Página anterior"><i class="fas fa-caret-left" aria-hidden="true"></i></a>
            @endif
            @for ($pagina = 1; $pagina <= $documentos->lastPage(); $pagina++)
                <a href="#" wire:click.prevent="gotoPage({{ $pagina }}, 'paginaDocumentos')" @class(['current-page' => $pagina === $documentos->currentPage()]) aria-label="Página {{ $pagina }}" @if ($pagina === $documentos->currentPage()) aria-current="page" @endif>{{ str_pad($pagina, 2, '0', STR_PAD_LEFT) }}.</a>
            @endfor
            @if ($documentos->currentPage() < $documentos->lastPage())
                <a href="#" wire:click.prevent="gotoPage({{ min($documentos->currentPage() + 1, $documentos->lastPage()) }}, 'paginaDocumentos')" class="nextposts-link" aria-label="Página seguinte"><i class="fas fa-caret-right" aria-hidden="true"></i></a>
            @endif
        </div>
    @endif
</div>
