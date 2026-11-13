@extends('layouts.app')

@section('title', 'Subscrição cancelada — PIGEA')
@section('description', 'A sua subscrição da newsletter do PIGEA foi cancelada.')

@section('content')
<div class="container" style="padding:80px 0; text-align:center; max-width:560px; margin:0 auto;">
    <i class="fas fa-envelope-open" style="font-size:48px; color:#9A9A9A; margin-bottom:18px;" aria-hidden="true"></i>
    <h1 style="font-family:'Playfair Display',serif; font-size:30px; color:#1A1A1A; margin:0 0 12px;">Subscrição cancelada</h1>
    <p style="font-size:15px; color:#555; line-height:1.6; margin:0 0 24px;">A sua subscrição da newsletter do PIGEA foi cancelada. Não receberá mais emails nossos.</p>
    <a href="{{ route('home') }}" class="color-bg" style="display:inline-block; color:#fff; padding:13px 30px; border-radius:4px; font-weight:600; text-decoration:none;">Voltar à página inicial</a>
</div>
@endsection
