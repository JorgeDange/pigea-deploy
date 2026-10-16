@extends('layouts.dashboard')

@section('title', 'Gerir Vagas')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active">Vagas</li></ul>
@endsection

@section('content')
<div class="card">
  <div class="card-head">
    <h3 class="card-title">Vagas</h3>
  </div>
  <livewire:admin.content-table tipo="vagas" />
</div>
@endsection