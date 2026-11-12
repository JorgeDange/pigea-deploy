@extends('layouts.dashboard')

@section('title', 'Destaques — Publicidade')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active" aria-current="page">Destaques</li></ol>
@endsection

@section('content')
<div class="card">
  <div class="card-head">
    <h3 class="card-title">Destaques publicitários</h3>
    <a href="{{ route('admin.destaques.novo') }}" class="btn-primary"><i class="fa fa-plus"></i> Novo destaque</a>
  </div>
  <p class="helper-text">Rotativos, 4 segundos, no widget <strong>«Escolha da Equipa»</strong> (existe na home e na página de projeto). Só os <strong>ativos e dentro do período</strong> aparecem; sem nenhum, o widget segue a mostrar as notícias (sem rotação). O ideal é <strong>até 3 em simultâneo</strong> (use «Ordem»).</p>
  <div class="card-body">
  <div class="table-wrap">
    <table class="dashboard-table">
      <thead><tr><th scope="col">Título</th><th scope="col">Imagem</th><th scope="col">Escopo</th><th scope="col">Período</th><th scope="col">Ordem</th><th scope="col">Estado</th><th scope="col">Ações</th></tr></thead>
      <tbody>
        @forelse ($destaques as $destaque)
        <tr>
          <td>{{ $destaque->titulo }}</td>
          <td>
            @if ($destaque->imagem_url)
            <img src="{{ str_starts_with($destaque->imagem_url, 'http') ? $destaque->imagem_url : asset($destaque->imagem_url) }}" alt="{{ $destaque->titulo }}" style="max-width: 110px; max-height: 60px; object-fit: cover; border-radius: 3px;">
            @else —
            @endif
          </td>
          <td>{{ \App\Models\Destaque::ESCOPOS[$destaque->escopo] ?? $destaque->escopo }}</td>
          <td>{{ ($destaque->data_inicio?->format('d/m/Y') ?? '—').' — '.($destaque->data_fim?->format('d/m/Y') ?? '—') }}</td>
          <td>{{ $destaque->ordem }}</td>
          <td><span class="status-badge {{ $destaque->ativo ? 'active' : 'neutral' }}">{{ $destaque->ativo ? 'Ativo' : 'Inativo' }}</span></td>
          <td>
            <div class="row-actions">
              <form method="post" action="{{ route('admin.destaques.toggle', $destaque->id) }}" style="display:inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="icon-btn {{ $destaque->ativo ? 'danger' : '' }}" title="{{ $destaque->ativo ? 'Desativar' : 'Ativar' }}"><i class="fa {{ $destaque->ativo ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
              </form>
              <a class="icon-btn" href="{{ route('admin.destaques.editar', $destaque->id) }}" title="Editar"><i class="fa fa-pen"></i></a>
              <form method="post" action="{{ route('admin.destaques.destroy', $destaque->id) }}" id="destroy-destaque-{{ $destaque->id }}" style="display:inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="icon-btn danger js-confirm" data-confirm-title="Eliminar destaque" data-confirm-message="Eliminar o destaque «{{ $destaque->titulo }}»? Esta ação não pode ser desfeita." data-confirm-text="Eliminar" data-confirm-form="destroy-destaque-{{ $destaque->id }}" title="Eliminar"><i class="fa fa-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="7">Nenhum destaque criado. O destaque da home mostra as notícias enquanto não houver um ativo.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <nav class="dashboard-pagination" aria-label="Paginação">
    {{ $destaques->onEachSide(1)->links() }}
  </nav>
  </div>
</div>
@endsection
