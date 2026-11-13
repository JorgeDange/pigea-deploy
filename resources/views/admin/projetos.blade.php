@extends('layouts.dashboard')

@section('title', 'Gerir Projetos')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active" aria-current="page">Projetos</li></ol>
@endsection

@section('content')
<div class="card">
  <div class="card-head">
    <h3 class="card-title">Projetos</h3>
    <div class="card-actions"><a href="{{ Route::has('admin.projeto-novo') ? route('admin.projeto-novo') : '#' }}" class="btn-primary btn-sm"><i class="fa fa-plus"></i> Novo projeto</a></div>
  </div>
  <livewire:admin.content-table tipo="projetos" />
</div>
@endsection
