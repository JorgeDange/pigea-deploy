@extends('layouts.dashboard')

@section('title', 'Gerir Profissionais')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active" aria-current="page">Profissionais</li></ol>
@endsection

@section('content')
<div class="card">
  <div class="card-head">
    <h3 class="card-title">Diretório de Profissionais</h3>
  </div>
  {{-- Moderação (restrutura.md §5.4): submissão pública /registo-profissional
       com status pendente; aprovados ficam visíveis no diretório (Fase 5). --}}
  <livewire:admin.professionals-table />
</div>
@endsection
