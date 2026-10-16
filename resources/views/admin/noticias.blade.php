@extends('layouts.dashboard')

@section('title', 'Gerir Notícias')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active">Notícias</li></ul>
@endsection

@section('content')
<div class="card">
  <div class="card-head">
    <h3 class="card-title">Notícias</h3>
    <div class="card-actions"><a href="{{ Route::has('admin.noticia-nova') ? route('admin.noticia-nova') : '#' }}" class="btn-primary btn-sm"><i class="fa fa-plus"></i> Nova notícia</a></div>
  </div>
  <livewire:admin.content-table tipo="noticias" />
</div>
@endsection
