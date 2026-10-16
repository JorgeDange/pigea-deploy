@extends('layouts.dashboard')

@section('title', 'Candidaturas')

@section('sidebar')
<x-sidebar-user />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('home') }}">Início</a></li><li><a href="{{ route('user.dashboard') }}">Painel</a></li><li class="active">Candidaturas</li></ul>
@endsection

@section('content')

        <div class="card">
          <div class="card-head">
            <h3 class="card-title">As minhas candidaturas</h3>
            
          </div>
          <livewire:painel.minhas-candidaturas />
        </div>
@endsection
