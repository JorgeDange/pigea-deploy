@extends('layouts.dashboard')

@section('title', 'Gerir Investigação')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active">Investigação</li></ul>
@endsection

@section('content')
<div class="card">
  <div class="card-head">
    <h3 class="card-title">Investigação</h3>
    <div class="card-actions"><a href="{{ route('admin.conteudo.novo', ['tipo' => 'investigacao']) }}" class="btn-primary btn-sm"><i class="fa fa-plus"></i> Novo estudo</a></div>
  </div>
  <livewire:admin.content-table tipo="investigacao" />
</div>
@endsection