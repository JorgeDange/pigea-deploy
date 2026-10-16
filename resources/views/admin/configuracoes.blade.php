@extends('layouts.dashboard')

@section('title', 'Configurações')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active">Configurações</li></ul>
@endsection

@section('content')

        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Geral</h3>
            
          </div>
          <div class="card-body">
<form class="dashboard-form" action="{{ route('admin.configuracoes.geral') }}" method="post" enctype="multipart/form-data">
              @csrf
              <div class="dashboard-form-row">
                <div class="dashboard-form-col">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Nome do portal</label>
                <input class="dashboard-form-input" type="text" name="portal" placeholder="PIGEA — Plataforma de Impulsionamento das Geociências em Angola" value="{{ old('portal', $definicoes['portal'] ?? '') }}" required>
                @error('portal')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Descrição</label>
                <textarea class="dashboard-form-textarea" name="descricao" rows="3" placeholder="Descrição curta do portal…">{{ old('descricao', $definicoes['descricao'] ?? '') }}</textarea>
                @error('descricao')<p class="form-error">{{ $message }}</p>@enderror
              </div>
                </div>
                <div class="dashboard-form-col">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Logótipo</label>
                <div class="upload-box">PNG ou JPG até 2MB<br><input type="file" name="logo"></div>
                @error('logo')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Favicon</label>
                <div class="upload-box">PNG 32×32<br><input type="file" name="favicon"></div>
                @error('favicon')<p class="form-error">{{ $message }}</p>@enderror
              </div>
                </div>
              </div>
              <div class="dashboard-form-actions">
                <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Guardar</button>
                
              </div>
          </form>
          </div>
        </div>
        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Contactos</h3>
            
          </div>
          <div class="card-body">
<form class="dashboard-form" action="{{ route('admin.configuracoes.contactos') }}" method="post">
              @csrf
              <div class="dashboard-form-row">
                <div class="dashboard-form-col">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Email</label>
                <input class="dashboard-form-input" type="email" name="email" placeholder="geral@pigea.ao" value="{{ old('email', $definicoes['email'] ?? '') }}" required>
                @error('email')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Telefone</label>
                <input class="dashboard-form-input" type="tel" name="telefone" placeholder="+244 000 000 000" value="{{ old('telefone', $definicoes['telefone'] ?? '') }}">
                @error('telefone')<p class="form-error">{{ $message }}</p>@enderror
              </div>
                </div>
                <div class="dashboard-form-col">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Endereço</label>
                <input class="dashboard-form-input" type="text" name="endereco" placeholder="Rua, bairro, município, Luanda" value="{{ old('endereco', $definicoes['endereco'] ?? '') }}">
                @error('endereco')<p class="form-error">{{ $message }}</p>@enderror
              </div>
                </div>
              </div>
              <div class="dashboard-form-actions">
                <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Guardar</button>
                
              </div>
          </form>
          </div>
        </div>
        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Redes sociais</h3>
            
          </div>
          <div class="card-body">
<form class="dashboard-form" action="{{ route('admin.configuracoes.redes') }}" method="post">
              @csrf
              <div class="dashboard-form-row">
                <div class="dashboard-form-col">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Facebook</label>
                <input class="dashboard-form-input" type="text" name="facebook" placeholder="https://facebook.com/…" value="{{ old('facebook', $definicoes['facebook'] ?? '') }}">
                @error('facebook')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Instagram</label>
                <input class="dashboard-form-input" type="text" name="instagram" placeholder="https://instagram.com/…" value="{{ old('instagram', $definicoes['instagram'] ?? '') }}">
                @error('instagram')<p class="form-error">{{ $message }}</p>@enderror
              </div>
                </div>
                <div class="dashboard-form-col">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">LinkedIn</label>
                <input class="dashboard-form-input" type="text" name="linkedin" placeholder="https://linkedin.com/…" value="{{ old('linkedin', $definicoes['linkedin'] ?? '') }}">
                @error('linkedin')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">YouTube</label>
                <input class="dashboard-form-input" type="text" name="youtube" placeholder="https://youtube.com/…" value="{{ old('youtube', $definicoes['youtube'] ?? '') }}">
                @error('youtube')<p class="form-error">{{ $message }}</p>@enderror
              </div>
                </div>
              </div>
              <div class="dashboard-form-actions">
                <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Guardar</button>
                
              </div>
          </form>
          </div>
        </div>
        <div class="card">
          <div class="card-head">
            <h3 class="card-title">SEO</h3>
            
          </div>
          <div class="card-body">
<form class="dashboard-form" action="{{ route('admin.configuracoes.seo') }}" method="post">
              @csrf
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Meta title padrão</label>
                <input class="dashboard-form-input" type="text" name="meta_title" placeholder="Título para os motores de busca (até 60 caracteres)" value="{{ old('meta_title', $definicoes['meta_title'] ?? '') }}">
                @error('meta_title')<p class="form-error">{{ $message }}</p>@enderror
              </div>
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Meta description padrão</label>
                <textarea class="dashboard-form-textarea" name="meta_description" rows="2" placeholder="Descrição para os motores de busca (até 160 caracteres)">{{ old('meta_description', $definicoes['meta_description'] ?? '') }}</textarea>
                @error('meta_description')<p class="form-error">{{ $message }}</p>@enderror
              </div>
              <div class="dashboard-form-actions">
                <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Guardar</button>
                
              </div>
          </form>
          </div>
        </div>
        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Idiomas</h3>
            
          </div>
          <div class="card-body">
            <ul class="list-check">
              <li><span>Português (PT) — idioma principal</span><label class="switch"><input type="checkbox" checked><span></span></label></li>
              <li><span>Inglês (EN) — tradução do portal</span><label class="switch"><input type="checkbox"><span></span></label></li>
            </ul>
          </div>
        </div>
        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Notificações por email</h3>
            
          </div>
          <div class="card-body">
<form class="dashboard-form" action="{{ route('admin.configuracoes.email') }}" method="post">
              @csrf
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Modelo de notificação</label>
                <select class="dashboard-form-select" name="template">
                <option value="" @selected(old('template', $definicoes['template'] ?? '') === '')>Selecionar modelo…</option>
                <option value="novo-registo" @selected(old('template', $definicoes['template'] ?? '') === 'novo-registo')>Novo registo de empresa</option>
                <option value="nova-candidatura" @selected(old('template', $definicoes['template'] ?? '') === 'nova-candidatura')>Nova candidatura a vaga</option>
                <option value="newsletter" @selected(old('template', $definicoes['template'] ?? '') === 'newsletter')>Newsletter semanal</option>
              </select>
                @error('template')<p class="form-error">{{ $message }}</p>@enderror
              </div>
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Texto do email</label>
                <textarea class="dashboard-form-textarea" name="texto_email" rows="4" placeholder="Corpo do email de notificação (suporta variáveis @{{nome}}, @{{data}})…">{{ old('texto_email', $definicoes['texto_email'] ?? '') }}</textarea>
                @error('texto_email')<p class="form-error">{{ $message }}</p>@enderror
              </div>
              <div class="dashboard-form-actions">
                <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Guardar</button>
                
              </div>
          </form>
          </div>
        </div>
@endsection
