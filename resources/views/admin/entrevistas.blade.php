@extends('layouts.dashboard')

@section('title', 'Gerir Entrevistas')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active">Entrevistas</li></ul>
@endsection

@section('content')
<div class="card">
  <div class="card-head">
    <h3 class="card-title">Entrevistas</h3>
    <div class="card-actions"><a href="{{ route('admin.conteudo.novo', ['tipo' => 'entrevistas']) }}" class="btn-primary btn-sm"><i class="fa fa-plus"></i> Nova entrevista</a></div>
  </div>
  <livewire:admin.content-table tipo="entrevistas" />
</div>
@endsection