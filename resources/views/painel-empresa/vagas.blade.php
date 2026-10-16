@extends('layouts.dashboard')

@section('title', 'Gerir vagas')

@section('sidebar')
<x-sidebar-company />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('home') }}">Início</a></li><li><a href="{{ route('company.dashboard') }}">Painel</a></li><li class="active">Vagas</li></ul>
@endsection

@section('content')

        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Vagas publicadas</h3>
            <div class="card-actions"><a href="{{ route('company.vaga-nova') }}" class="btn-primary btn-sm"><i class="fa fa-plus"></i> Publicar nova vaga</a></div>
          </div>
          <livewire:painel.vagas-empresa />
        </div>
@endsection
