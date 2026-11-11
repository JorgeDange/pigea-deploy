@extends('layouts.dashboard')

@section('title', 'Gerir Empresas')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active" aria-current="page">Empresas</li></ol>
@endsection

@section('content')
<div class="card">
  <div class="card-head">
    <h3 class="card-title">Diretório de Empresas</h3>
  </div>
  {{-- Moderação (restrutura.md §5.3): as empresas entram pelo formulário
       público /registo-empresa com status pendente e são aprovadas/rejeitadas
       daqui — as aprovadas ficam visíveis no diretório público (Fase 5). --}}
  <livewire:admin.companies-table />
</div>
@endsection
