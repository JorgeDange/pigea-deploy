@extends('layouts.dashboard')

@section('title', 'Equipa — Sobre Nós')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active" aria-current="page">Equipa</li></ol>
@endsection

@section('content')
@if (session('msg'))
<div class="card" style="border-color:#d8efd1;background:#f2faf0"><div class="card-body">{{ session('msg') }}</div></div>
@endif
<div class="card">
  <div class="card-head">
    <h3 class="card-title">Equipa PIGEA</h3>
    <a href="{{ route('admin.equipa.novo') }}" class="btn-primary"><i class="fa fa-plus"></i> Novo membro</a>
  </div>
  <div class="card-body">
    @php($membros = \App\Models\TeamMember::query()->orderBy('ordem')->orderBy('id')->get())
    <div class="table-wrap">
      <table class="dashboard-table">
        <thead><tr><th scope="col">#</th><th scope="col">Foto</th><th scope="col">Nome</th><th scope="col">Cargo</th><th scope="col">Ativo</th><th scope="col">Ações</th></tr></thead>
        <tbody>
          @forelse ($membros as $membro)
          <tr wire:key="membro-{{ $membro->id }}">
            <td>{{ $membro->ordem }}</td>
            <td>@if ($membro->foto_url)<img src="{{ $membro->foto_url }}" alt="" width="44" height="44" loading="lazy" style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid #C8102E">@else<span style="font-family:Georgia,serif;font-weight:700;color:#C8102E">{{ \App\Support\Institucional::iniciais($membro->nome) }}</span>@endif</td>
            <td>{{ $membro->nome }}</td>
            <td>{{ \App\Support\Institucional::cargoVisivel($membro) }}</td>
            <td><span class="status-badge {{ $membro->ativo ? 'active' : 'rejected' }}">{{ $membro->ativo ? 'Ativo' : 'Inativo' }}</span></td>
            <td>
              <div class="row-actions">
                <a class="icon-btn" href="{{ route('admin.equipa.editar', ['id' => $membro->id]) }}" title="Editar"><i class="fa fa-pen"></i></a>
                <form method="post" action="{{ route('admin.equipa.remover', ['id' => $membro->id]) }}" style="display:inline" onsubmit="return confirm('Remover {{ $membro->nome }} da equipa? Pode ser reposto a partir da lixeira.')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="icon-btn danger" title="Remover"><i class="fa fa-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr><td colspan="6">Ainda não há membros — crie o primeiro com «Novo membro».</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
