@extends('layouts.app')

@section('title', 'Subscrição — PIGEA')
@section('description', 'Não foi possível validar a ligação de subscrição.')

@section('content')
<div class="container" style="padding:80px 0; text-align:center; max-width:560px; margin:0 auto;">
    <i class="fas fa-circle-exclamation" style="font-size:48px; color:#C8102E; margin-bottom:18px;" aria-hidden="true"></i>
    <h1 style="font-family:'Playfair Display',serif; font-size:30px; color:#1A1A1A; margin:0 0 12px;">Ligação inválida</h1>
    <p style="font-size:15px; color:#555; line-height:1.6; margin:0 0 24px;">Esta ligação de subscrição não é válida ou já foi usada. Pode subscrever de novo em baixo — enviamos-lhe um email com uma nova ligação de confirmação.</p>
    <a href="{{ route('home') }}" class="color-bg" style="display:inline-block; color:#fff; padding:13px 30px; border-radius:4px; font-weight:600; text-decoration:none;">Voltar à página inicial</a>
</div>
@endsection
