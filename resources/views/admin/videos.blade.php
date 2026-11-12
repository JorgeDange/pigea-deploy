@extends('layouts.dashboard')

@section('title', 'Vídeos do site')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active" aria-current="page">Vídeos</li></ol>
@endsection

@section('content')
<div class="card">
  <div class="card-head">
    <h3 class="card-title">Vídeos do site</h3>
    <a href="{{ route('admin.videos.novo') }}" class="btn-primary"><i class="fa fa-plus"></i> Novo vídeo</a>
  </div>
  {{-- Alimenta as secções de vídeo da home (e das páginas de artigo no futuro).
       Cada entrada é um link (YouTube/Vimeo) — não se faz upload de ficheiros. --}}
  <livewire:admin.videos-table />
</div>
@endsection
