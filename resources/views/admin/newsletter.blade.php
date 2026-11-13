@extends('layouts.dashboard')

@section('title', 'Newsletter')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active" aria-current="page">Newsletter</li></ol>
@endsection

@section('content')

{{-- ================= ESTATÍSTICAS ================= --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-icon"><i class="fa fa-check-circle"></i></div>
        <div class="stat-card-body">
            <div class="stat-card-number">{{ number_format($confirmados, 0, ',', '.') }}</div>
            <div class="stat-card-label">Confirmados</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon"><i class="fa fa-hourglass-half"></i></div>
        <div class="stat-card-body">
            <div class="stat-card-number">{{ number_format($pendentes, 0, ',', '.') }}</div>
            <div class="stat-card-label">Pendentes</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon"><i class="fa fa-user-slash"></i></div>
        <div class="stat-card-body">
            <div class="stat-card-number">{{ number_format($cancelados, 0, ',', '.') }}</div>
            <div class="stat-card-label">Cancelados</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h3 class="card-title">Subscritores</h3>
        <a href="{{ route('admin.newsletter.exportar') }}" class="btn-secondary btn-sm"><i class="fa fa-file-csv"></i> Exportar CSV</a>
    </div>
    <div class="card-body">

        {{-- Flash de ações do painel (adicionar/remover) --}}
        @if (session('newsletter_msg') && isset(session('newsletter_msg')['texto']))
            <p class="newsletter-msg newsletter-msg-{{ (session('newsletter_msg')['tipo'] ?? 'ok') === 'ok' ? 'ok' : 'erro' }}" role="status">{{ session('newsletter_msg')['texto'] }}</p>
        @endif

        {{-- Filtro + pesquisa + adicionar --}}
        <form class="dashboard-filters" method="get" action="{{ route('admin.newsletter') }}" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:12px;">
            <input type="text" class="filter-search" name="q" value="{{ $busca }}" placeholder="Pesquisar email…" aria-label="Pesquisar subscritor">
            <select name="estado" aria-label="Filtrar por estado" onchange="this.form.submit()" style="max-width:180px;">
                <option value="" @selected($estado === '')>Estado: todos</option>
                <option value="confirmed" @selected($estado === 'confirmed')>Confirmados</option>
                <option value="pending" @selected($estado === 'pending')>Pendentes</option>
                <option value="unsubscribed" @selected($estado === 'unsubscribed')>Cancelados</option>
            </select>
            @if ($estado !== '' || $busca !== '')
                <a href="{{ route('admin.newsletter') }}" class="btn-secondary btn-sm">Limpar</a>
            @endif
        </form>

        <form method="post" action="{{ route('admin.newsletter.adicionar') }}" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center; padding:12px; background:#F8F8F8; border:1px solid #EDEDEA; border-radius:8px; margin-bottom:14px;">
            @csrf
            <strong style="font-size:12px; color:#3F3F3F; flex:0 0 auto;">Adicionar:</strong>
            <input type="email" name="email" class="filter-search" placeholder="email@exemplo.com" value="{{ old('email') }}" required maxlength="190" aria-label="Email do subscritor">
            <button type="submit" class="btn-primary btn-sm"><i class="fa fa-user-plus"></i> Adicionar</button>
        </form>
        @error('email')<p class="form-error" role="alert">{{ $message }}</p>@enderror

        <div class="table-wrap">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th scope="col">Email</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Desde</th>
                        <th scope="col">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subscritores as $subscritor)
                    <tr>
                        <td>{{ $subscritor->email }}</td>
                        <td>
                            @if ($subscritor->status === 'confirmed')
                                <span class="status-badge active">Confirmado</span>
                            @elseif ($subscritor->status === 'pending')
                                <span class="status-badge pending">Pendente</span>
                            @else
                                <span class="status-badge neutral">Cancelado</span>
                            @endif
                        </td>
                        <td>{{ $subscritor->created_at?->format('d/m/Y') ?? '—' }}</td>
                        <td>
                            <form method="post" action="{{ route('admin.newsletter.remover', $subscritor->id) }}" style="display:inline" id="remover-subscritor-{{ $subscritor->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn danger js-confirm" data-confirm-title="Remover subscritor" data-confirm-message="Remover {{ $subscritor->email }} da lista? Ação irreversível." data-confirm-text="Remover" data-confirm-form="remover-subscritor-{{ $subscritor->id }}" title="Remover"><i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4">Nenhum subscritor{{ $estado !== '' || $busca !== '' ? ' corresponde a estes filtros' : ' ainda — aparece através da modal em todo o site ou na página de Contactos' }}.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <nav class="dashboard-pagination" aria-label="Paginação">
            {{ $subscritores->onEachSide(1)->links() }}
        </nav>

        <p class="helper-text" style="margin-top:12px"><i class="fa fa-circle-info" aria-hidden="true"></i> A exportação (CSV) contém apenas <strong>confirmados</strong>. A lista alimenta o cartão «Receba as novidades» da página inicial quando a FASE N2 estiver pronta.</p>
    </div>
</div>

@endsection
