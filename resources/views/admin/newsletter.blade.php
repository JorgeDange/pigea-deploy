@extends('layouts.dashboard')

@section('title', 'Newsletter')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active" aria-current="page">Newsletter</li></ol>
@endsection

@section('content')

{{-- ================= KPIs ================= --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-icon"><i class="fa fa-users"></i></div>
        <div class="stat-card-body">
            <div class="stat-card-number">{{ number_format($total, 0, ',', '.') }}</div>
            <div class="stat-card-label">Total</div>
        </div>
    </div>
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
        <div style="display:flex; gap:8px; align-items:center;">
            @if ($pendentes > 0)
                <form method="post" action="{{ route('admin.newsletter.confirmar-pendentes') }}" id="confirmar-pendentes-todos" style="display:inline">
                    @csrf
                    <button type="button" class="btn-secondary btn-sm js-confirm" data-confirm-form="confirmar-pendentes-todos" data-confirm-title="Confirmar todos" data-confirm-message="Confirmar {{ $pendentes }} subscritor(es) pendentes de uma vez?" data-confirm-text="Confirmar"><i class="fa fa-check-double"></i> Confirmar todos ({{ $pendentes }})</button>
                </form>
                <form method="post" action="{{ route('admin.newsletter.reenviar-pendentes') }}" id="reenviar-pendentes-todos" style="display:inline">
                    @csrf
                    <button type="button" class="btn-secondary btn-sm js-confirm" data-confirm-form="reenviar-pendentes-todos" data-confirm-title="Reenviar a todos" data-confirm-message="Reenviar email de confirmação para {{ $pendentes }} subscritor(es) pendentes?" data-confirm-text="Reenviar"><i class="fa fa-paper-plane"></i> Reenviar a todos ({{ $pendentes }})</button>
                </form>
            @endif
            <a href="{{ route('admin.newsletter.exportar', ['estado' => $estado !== '' ? $estado : 'confirmed']) }}" class="btn-secondary btn-sm"><i class="fa fa-file-csv"></i> Exportar {{ $estado !== '' ? $rotulos[$estado] : 'confirmados' }}</a>
        </div>
    </div>
    <div class="card-body">

        {{-- Flash de ações do painel --}}
        @if (session('newsletter_msg') && isset(session('newsletter_msg')['texto']))
            <p class="newsletter-msg newsletter-msg-{{ (session('newsletter_msg')['tipo'] ?? 'ok') === 'ok' ? 'ok' : 'erro' }}" role="status">{{ session('newsletter_msg')['texto'] }}</p>
        @endif

        {{-- Filtro + pesquisa --}}
        <form class="newsletter-toolbar" method="get" action="{{ route('admin.newsletter') }}">
            <input type="text" class="newsletter-input" name="q" value="{{ $busca }}" placeholder="Pesquisar email…" aria-label="Pesquisar subscritor">
            <select class="newsletter-estado" name="estado" aria-label="Filtrar por estado" onchange="this.form.submit()">
                <option value="" @selected($estado === '')>Todos ({{ $total }})</option>
                <option value="confirmed" @selected($estado === 'confirmed')>Confirmados ({{ $confirmados }})</option>
                <option value="pending" @selected($estado === 'pending')>Pendentes ({{ $pendentes }})</option>
                <option value="unsubscribed" @selected($estado === 'unsubscribed')>Cancelados ({{ $cancelados }})</option>
            </select>
            @if ($estado !== '' || $busca !== '')
                <a href="{{ route('admin.newsletter') }}" class="btn-secondary btn-sm">Limpar</a>
            @endif
        </form>

        <form class="newsletter-adicionar" method="post" action="{{ route('admin.newsletter.adicionar') }}">
            @csrf
            <strong class="newsletter-adicionar-rotulo">Adicionar manualmente:</strong>
            <input type="email" name="email" class="newsletter-input" placeholder="email@exemplo.com" value="{{ old('email') }}" required maxlength="190" aria-label="Email do subscritor">
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
                            <div style="display:flex; gap:6px; align-items:center;">
                                @if ($subscritor->status === 'pending')
                                    <form method="post" action="{{ route('admin.newsletter.confirmar', $subscritor->id) }}" id="confirmar-subscritor-{{ $subscritor->id }}" style="display:inline">
                                        @csrf
                                        <button type="button" class="icon-btn js-confirm" data-confirm-form="confirmar-subscritor-{{ $subscritor->id }}" data-confirm-title="Confirmar subscritor" data-confirm-message="Confirmar {{ $subscritor->email }} manualmente?" data-confirm-text="Confirmar" title="Confirmar"><i class="fa fa-check"></i></button>
                                    </form>
                                    <form method="post" action="{{ route('admin.newsletter.reenviar', $subscritor->id) }}" style="display:inline">
                                        @csrf
                                        <button type="submit" class="icon-btn" title="Reenviar confirmação"><i class="fa fa-paper-plane"></i></button>
                                    </form>
                                @endif
                                @if ($subscritor->status !== 'unsubscribed')
                                    <form method="post" action="{{ route('admin.newsletter.cancelar', $subscritor->id) }}" id="cancelar-subscritor-{{ $subscritor->id }}" style="display:inline">
                                        @csrf
                                        <button type="button" class="icon-btn danger js-confirm" data-confirm-form="cancelar-subscritor-{{ $subscritor->id }}" data-confirm-title="Cancelar subscrição" data-confirm-message="Cancelar a subscrição de {{ $subscritor->email }}?" data-confirm-text="Cancelar" title="Cancelar"><i class="fa fa-ban"></i></button>
                                    </form>
                                @endif
                                @if ($subscritor->status === 'unsubscribed')
                                    <form method="post" action="{{ route('admin.newsletter.reabilitar', $subscritor->id) }}" id="reabilitar-subscritor-{{ $subscritor->id }}" style="display:inline">
                                        @csrf
                                        <button type="button" class="icon-btn js-confirm" data-confirm-form="reabilitar-subscritor-{{ $subscritor->id }}" data-confirm-title="Reabilitar subscritor" data-confirm-message="Reabilitar {{ $subscritor->email }}? Vai ser reenviado o email de confirmação." data-confirm-text="Reabilitar" title="Reabilitar"><i class="fa fa-undo"></i></button>
                                    </form>
                                @endif
                                <form method="post" action="{{ route('admin.newsletter.remover', $subscritor->id) }}" style="display:inline" id="remover-subscritor-{{ $subscritor->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icon-btn danger js-confirm" data-confirm-title="Remover subscritor" data-confirm-message="Remover {{ $subscritor->email }} da lista? Ação irreversível." data-confirm-text="Remover" data-confirm-form="remover-subscritor-{{ $subscritor->id }}" title="Remover"><i class="fa fa-trash"></i></button>
                                </form>
                            </div>
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

        <p class="helper-text" style="margin-top:12px"><i class="fa fa-circle-info" aria-hidden="true"></i> A exportação (CSV UTF-8) respeita o filtro atual. Confirmados = quem abriu o email; Pendentes = aguardando confirmação; Cancelados = desistiram.</p>
    </div>
</div>

@endsection
