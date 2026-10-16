@extends('layouts.dashboard')

@section('title', 'Candidaturas recebidas')

@section('sidebar')
<x-sidebar-company />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('home') }}">Início</a></li><li><a href="{{ route('company.dashboard') }}">Painel</a></li><li class="active">Candidaturas</li></ul>
@endsection

@section('content')

        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Candidaturas recebidas</h3>
            
          </div>
          <livewire:painel.candidaturas-recebidas />
        </div>
@endsection
