@extends('layouts.dashboard')

@section('title', 'Documentos do Diretório')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active" aria-current="page">Documentos</li></ol>
@endsection

@section('content')
<div class="card">
  <div class="card-head">
    <h3 class="card-title">Artigos / Documentos</h3>
    <a href="{{ route('admin.documentos.novo') }}" class="btn-primary"><i class="fa fa-plus"></i> Novo documento</a>
  </div>
  {{-- Conteúdo publicado exclusivamente pela equipa (restrutura.md §5.5);
       aparece na aba "Artigos/Documentos" de /directorio (Fase 5). --}}
  <livewire:admin.documents-table />
</div>
@endsection
