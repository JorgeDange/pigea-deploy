@extends('layouts.dashboard')

@section('title', 'Gerir Legislação')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active" aria-current="page">Legislação</li></ol>
@endsection

@section('content')
<div class="card">
  <div class="card-head">
    <h3 class="card-title">Legislação</h3>
    <div class="card-actions"><a href="{{ route('admin.conteudo.novo', ['tipo' => 'legislacao']) }}" class="btn-primary btn-sm"><i class="fa fa-plus"></i> Nova legislação</a></div>
  </div>
  <livewire:admin.content-table tipo="legislacao" />
</div>
@endsection
