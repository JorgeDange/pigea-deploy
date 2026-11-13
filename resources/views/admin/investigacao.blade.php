@extends('layouts.dashboard')

@section('title', 'Gerir Investigação')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active" aria-current="page">Investigação</li></ol>
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
