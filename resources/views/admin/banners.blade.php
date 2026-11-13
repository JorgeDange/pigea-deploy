@extends('layouts.dashboard')

@section('title', 'Publicidade — Banners')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active" aria-current="page">Publicidade</li></ol>
@endsection

@section('content')
<div class="card">
  <div class="card-head">
    <h3 class="card-title">Banners</h3>
    <a href="{{ route('admin.banners.novo') }}" class="btn-primary"><i class="fa fa-plus"></i> Novo banner</a>
  </div>
  {{-- Publicidade (restrutura.md §5.6): imagem por upload ou link, posição
       topo/sidebar/rodapé e período de exibição. --}}
  <livewire:admin.banners-table />
</div>
@endsection
