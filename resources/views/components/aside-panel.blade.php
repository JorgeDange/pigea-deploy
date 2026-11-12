@php
    // Atalhos fixos do painel lateral global (ícones). Contagens são
    // calculadas na sidebar de /noticias, não aqui — este painel é só navegação.
    $categoriasSidebar = [
        ['rotulo' => 'Mineração', 'icone' => 'far fa-podium', 'slug' => 'mineracao'],
        ['rotulo' => 'Água', 'icone' => 'far fa-tint', 'slug' => 'agua'],
        ['rotulo' => 'Ambiente', 'icone' => 'far fa-leaf', 'slug' => 'ambiente'],
        ['rotulo' => 'Investigação', 'icone' => 'far fa-flask', 'slug' => 'investigacao'],
        ['rotulo' => 'Internacional', 'icone' => 'far fa-globe', 'slug' => 'internacional'],
    ];
@endphp
<div class="aside-panel">
    <ul>
        @foreach ($categoriasSidebar as $categoriaSidebar)
        <li>
            <a href="{{ route('news.index', ['cat' => $categoriaSidebar['slug']]) }}">
                <i class="{{ $categoriaSidebar['icone'] }}"></i><span>{{ $categoriaSidebar['rotulo'] }}</span>
            </a>
        </li>
        @endforeach
    </ul>
</div>
