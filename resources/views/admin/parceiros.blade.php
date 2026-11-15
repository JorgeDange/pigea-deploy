@extends('layouts.dashboard')

@section('title', 'Parceiros — Sobre Nós')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active" aria-current="page">Parceiros</li></ol>
@endsection

@section('content')
@if (session('msg'))
<div class="card" style="border-color:#d8efd1;background:#f2faf0"><div class="card-body">{{ session('msg') }}</div></div>
@endif
<div class="card">
  <div class="card-head">
    <h3 class="card-title">Parceiros PIGEA</h3>
    <a href="{{ route('admin.parceiros.novo') }}" class="btn-primary"><i class="fa fa-plus"></i> Novo parceiro</a>
  </div>
  <div class="card-body">
    @php($parceiros = \App\Models\Partner::query()->orderBy('tipo')->orderBy('ordem')->orderBy('id')->get())
    <div class="table-wrap">
      <table class="dashboard-table">
        <thead><tr><th scope="col">#</th><th scope="col">Imagem</th><th scope="col">Nome</th><th scope="col">Tipo</th><th scope="col">Detalhes</th><th scope="col">Ativo</th><th scope="col">Ações</th></tr></thead>
        <tbody>
          @forelse ($parceiros as $parceiro)
          <tr wire:key="parceiro-{{ $parceiro->id }}">
            <td>{{ $parceiro->ordem }}</td>
            <td>@if ($parceiro->imagem_url)<img src="{{ $parceiro->imagem_url }}" alt="" width="52" height="36" loading="lazy" style="width:52px;height:36px;object-fit:contain;background:#F9F9F9;border:1px solid #ECECEC;border-radius:4px">@else<span style="font-family:Georgia,serif;font-weight:700;color:#C8102E">{{ \App\Support\Institucional::iniciais($parceiro->nome) }}</span>@endif</td>
            <td>{{ $parceiro->nome }}</td>
            <td><span class="status-badge {{ $parceiro->tipo === 'empresa' ? 'active' : 'pending' }}">{{ \App\Support\Institucional::tipoParceiro($parceiro->tipo) }}</span></td>
            <td>{{ \Illuminate\Support\Str::limit((string) $parceiro->detalhes, 60) ?: '—' }}</td>
            <td><span class="status-badge {{ $parceiro->ativo ? 'active' : 'rejected' }}">{{ $parceiro->ativo ? 'Ativo' : 'Inativo' }}</span></td>
            <td>
              <div class="row-actions">
                <a class="icon-btn" href="{{ route('admin.parceiros.editar', ['id' => $parceiro->id]) }}" title="Editar"><i class="fa fa-pen"></i></a>
                <form method="post" action="{{ route('admin.parceiros.remover', ['id' => $parceiro->id]) }}" style="display:inline" onsubmit="return confirm('Remover {{ $parceiro->nome }} da lista de parceiros?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="icon-btn danger" title="Remover"><i class="fa fa-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr><td colspan="7">Ainda não há parceiros — crie o primeiro com «Novo parceiro».</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
