@extends('layouts.dashboard')

@section('title', 'Vagas guardadas')

@section('sidebar')
<x-sidebar-user />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('home') }}">Início</a></li><li><a href="{{ route('user.dashboard') }}">Painel</a></li><li class="active">Vagas guardadas</li></ul>
@endsection

@section('content')

        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Vagas guardadas</h3>
            <div class="card-actions"><a href="{{ route('home') }}" class="btn-ghost btn-sm"><i class="fa fa-search"></i> Explorar vagas</a></div>
          </div>
          <div class="card-body">
            @php $PROVINCIAS = ['luanda' => 'Luanda', 'benguela' => 'Benguela', 'huila' => 'Huíla', 'malanje' => 'Malanje', 'cuando-cubango' => 'Cuando Cubango', 'zaire' => 'Zaire']; @endphp
            <div class="jobs-grid">
              @forelse ($guardadas as $guardada)
              @php
                $vaga = $guardada->job;
                $dias = (int) ($vaga?->published_at?->diffInDays(now()) ?? 0);
                $publicada = $dias < 7 ? 'há '.$dias.(($dias === 1 ? ' dia' : ' dias')) : ($dias < 14 ? 'há 1 semana' : ($dias < 21 ? 'há 2 semanas' : 'há '.$dias.' dias'));
              @endphp
              <div class="job-card">
                <h4><a href="{{ $vaga ? route('jobs.show', $vaga->slug) : '#' }}">{{ $vaga?->title ?? '—' }}</a></h4>
                <div class="job-meta">{{ $vaga?->author?->name ?? '—' }} · {{ $PROVINCIAS[$vaga?->province] ?? '—' }}</div>
                <div class="job-tags"><span>{{ $vaga?->category?->name ?? '—' }}</span><span>Publicada {{ $publicada }}</span></div>
                <form method="POST" action="{{ route('user.guardadas.remover', $vaga) }}">
                  @csrf
                  {{-- Sem classe `js-remove-item` (ver nota no dashboard). --}}
                  <button type="submit" class="btn-danger btn-sm"><i class="fa fa-trash"></i> Remover</button>
                </form>
              </div>
              @empty
              <div class="job-card"><h4>Nenhuma vaga guardada.</h4></div>
              @endforelse
            </div>
          </div>
        </div>
@endsection
