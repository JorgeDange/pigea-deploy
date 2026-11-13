@extends('layouts.app')

@section('title', 'Subscrição confirmada — PIGEA')
@section('description', 'A sua subscrição da newsletter do PIGEA foi confirmada.')

@section('content')
<div class="container" style="padding:80px 0; text-align:center; max-width:560px; margin:0 auto;">
    <i class="fas fa-envelope-open-check" style="font-size:48px; color:#C8102E; margin-bottom:18px;" aria-hidden="true"></i>
    <h1 style="font-family:'Playfair Display',serif; font-size:30px; color:#1A1A1A; margin:0 0 12px;">{{ $jaConfirmado ? 'Já estava confirmada' : 'Obrigado — está na lista!' }}</h1>
    <p style="font-size:15px; color:#555; line-height:1.6; margin:0 0 24px;">{{ $jaConfirmado ? 'A sua subscrição já estava confirmada. Continue a receber as novidades do PIGEA.' : 'Confirmaste a tua subscrição. Recebes as nossas novidades e atualizações importantes por email — sempre que houver.' }}</p>
    <a href="{{ route('home') }}" class="color-bg" style="display:inline-block; color:#fff; padding:13px 30px; border-radius:4px; font-weight:600; text-decoration:none;">Voltar à página inicial</a>
</div>
@endsection
