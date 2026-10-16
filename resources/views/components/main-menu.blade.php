@php
    // Item de topo ativo, como no protótipo (cada página marca o seu grupo).
    $nomeRota = request()->route()?->getName() ?? '';
    $menuAtivo = match (true) {
        $nomeRota === 'home' => 'home',
        str_starts_with($nomeRota, 'news.') => 'noticias',
        str_starts_with($nomeRota, 'projects.') => 'projetos',
        str_starts_with($nomeRota, 'technologies.') => 'tecnologias',
        str_starts_with($nomeRota, 'suppliers.') => 'fornecedores',
        str_starts_with($nomeRota, 'jobs.') => 'empregos',
        str_starts_with($nomeRota, 'training.') => 'formacao',
        str_starts_with($nomeRota, 'legislation.')
            || str_starts_with($nomeRota, 'events.')
            || str_starts_with($nomeRota, 'research.')
            || str_starts_with($nomeRota, 'interviews.')
            || str_starts_with($nomeRota, 'directory.') => 'recursos',
        in_array($nomeRota, ['about', 'contacts', 'errors.404'], true) => 'sobre',
        default => '',
    };
@endphp
<div class="nav-holder main-menu">
    <nav>
        <ul>
            <li><a href="{{ route('home') }}" class="{{ $menuAtivo === 'home' ? 'act-link' : '' }}">Início</a></li>
            <li>
                <a href="#" class="{{ $menuAtivo === 'noticias' ? 'act-link' : '' }}">Notícias<i class="fas fa-caret-down"></i></a>
                <!--second level -->
                <ul>
                    <li><a href="{{ route('news.index') }}">Todas as Notícias</a></li>
                    <li><a href="{{ route('news.index', ['cat' => 'mineracao']) }}">Mineração</a></li>
                    <li><a href="{{ route('news.index', ['cat' => 'agua']) }}">Água</a></li>
                    <li><a href="{{ route('news.index', ['cat' => 'ambiente']) }}">Ambiente</a></li>
                    <li><a href="{{ route('news.index', ['cat' => 'investigacao']) }}">Investigação</a></li>
                    <li><a href="{{ route('news.index', ['cat' => 'internacional']) }}">Internacional</a></li>
                </ul>
                <!--second level end-->
            </li>
            <li><a href="{{ route('projects.index') }}" class="{{ $menuAtivo === 'projetos' ? 'act-link' : '' }}">Projetos</a></li>
            <li><a href="{{ route('technologies.index') }}" class="{{ $menuAtivo === 'tecnologias' ? 'act-link' : '' }}">Tecnologias</a></li>
            <li><a href="{{ route('suppliers.index') }}" class="{{ $menuAtivo === 'fornecedores' ? 'act-link' : '' }}">Fornecedores</a></li>
            <li><a href="{{ route('jobs.index') }}" class="{{ $menuAtivo === 'empregos' ? 'act-link' : '' }}">Empregos</a></li>
            <li><a href="{{ route('training.index') }}" class="{{ $menuAtivo === 'formacao' ? 'act-link' : '' }}">Formação</a></li>
            <li>
                <a href="#" class="{{ $menuAtivo === 'recursos' ? 'act-link' : '' }}">Recursos<i class="fas fa-caret-down"></i></a>
                <!--second level -->
                <ul>
                    <li><a href="{{ route('legislation.index') }}">Legislação</a></li>
                    <li><a href="{{ route('events.index') }}">Eventos</a></li>
                    <li><a href="{{ route('research.index') }}">Investigação</a></li>
                    <li><a href="{{ route('interviews.index') }}">Entrevistas</a></li>
                    <li><a href="{{ route('directory.index') }}">Diretório</a></li>
                </ul>
                <!--second level end-->
            </li>
            <li>
                <a href="#" class="{{ $menuAtivo === 'sobre' ? 'act-link' : '' }}">Sobre<i class="fas fa-caret-down"></i></a>
                <!--second level -->
                <ul>
                    <li><a href="{{ route('about') }}">Sobre o PIGEA</a></li>
                    <li><a href="{{ route('contacts') }}">Contactos</a></li>
                </ul>
                <!--second level end-->
            </li>
        </ul>
    </nav>
</div>
