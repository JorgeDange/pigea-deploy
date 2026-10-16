@extends('layouts.dashboard')

@section('title', 'Gerir Eventos')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active">Eventos</li></ul>
@endsection

@section('content')
<div class="card">
  <div class="card-head">
    <h3 class="card-title">Eventos</h3>
    <div class="card-actions"><a href="{{ route('admin.conteudo.novo', ['tipo' => 'eventos']) }}" class="btn-primary btn-sm"><i class="fa fa-plus"></i> Novo evento</a></div>
  </div>
  <livewire:admin.content-table tipo="eventos" />
</div>
@endsection