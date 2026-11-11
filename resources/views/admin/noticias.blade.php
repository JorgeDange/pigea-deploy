@extends('layouts.dashboard')

@section('title', 'Gerir Notícias')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active" aria-current="page">Notícias</li></ol>
@endsection

@section('content')
@if (session('estado'))<p class="status-badge active" role="status">{{ session('estado') }}</p>@endif
<div class="card">
  <div class="card-head">
    <h2 class="card-title">Notícias</h2>
    <div class="card-actions"><a href="{{ Route::has('admin.noticia-nova') ? route('admin.noticia-nova') : '#' }}" class="btn-primary btn-sm"><i class="fa fa-plus" aria-hidden="true"></i> Nova notícia</a></div>
  </div>
  <livewire:admin.content-table tipo="noticias" />
</div>
@endsection
