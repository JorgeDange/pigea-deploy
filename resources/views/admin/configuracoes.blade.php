@extends('layouts.dashboard')

@section('title', 'Configurações')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active" aria-current="page">Configurações</li></ol>
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
                @error('portal')<p class="form-error" role="alert">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Descrição</label>
                <textarea class="dashboard-form-textarea" name="descricao" rows="3" placeholder="Descrição curta do portal…">{{ old('descricao', $definicoes['descricao'] ?? '') }}</textarea>
                @error('descricao')<p class="form-error" role="alert">{{ $message }}</p>@enderror
              </div>
                </div>
                <div class="dashboard-form-col">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Logótipo</label>
                <div class="file-upload" data-accept="image" data-max-bytes="2097152">
                    <img class="file-upload-preview" @if (!empty($definicoes['logo'])) src="{{ asset('storage/' . $definicoes['logo']) }}" alt="Logótipo atual" @else hidden alt="Logótipo" @endif>
                    <label class="file-upload-button" for="logo"><i class="fa fa-folder-open" aria-hidden="true"></i> Escolher imagem</label>
                    <div class="file-upload-meta">
                        <span class="file-upload-name">{{ !empty($definicoes['logo']) ? 'Logótipo atual: ' . basename($definicoes['logo']) : 'Nenhum ficheiro selecionado' }}</span>
                        <small class="help-text">PNG ou JPG, até 2MB. Rec.: 400x100px.</small>
                    </div>
                    <input type="file" id="logo" name="logo" accept="image/png,image/jpeg">
                    <button type="button" class="file-upload-remove">Remover</button>
                    <p class="file-upload-erro" role="alert"></p>
                </div>
                @error('logo')<p class="form-error" role="alert">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Favicon</label>
                <div class="file-upload" data-accept="image">
                    <img class="file-upload-preview" @if (!empty($definicoes['favicon'])) src="{{ asset('storage/' . $definicoes['favicon']) }}" alt="Favicon atual" @else hidden alt="Favicon" @endif>
                    <label class="file-upload-button" for="favicon"><i class="fa fa-folder-open" aria-hidden="true"></i> Escolher imagem</label>
                    <div class="file-upload-meta">
                        <span class="file-upload-name">{{ !empty($definicoes['favicon']) ? 'Favicon atual: ' . basename($definicoes['favicon']) : 'Nenhum ficheiro selecionado' }}</span>
                        <small class="help-text">PNG 32×32. Máx.: 256KB no servidor.</small>
                    </div>
                    <input type="file" id="favicon" name="favicon" accept="image/png">
                    <button type="button" class="file-upload-remove">Remover</button>
                    <p class="file-upload-erro" role="alert"></p>
                </div>
                @error('favicon')<p class="form-error" role="alert">{{ $message }}</p>@enderror
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
                @error('email')<p class="form-error" role="alert">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Telefone</label>
                <input class="dashboard-form-input" type="tel" name="telefone" placeholder="+244 000 000 000" value="{{ old('telefone', $definicoes['telefone'] ?? '') }}">
                @error('telefone')<p class="form-error" role="alert">{{ $message }}</p>@enderror
              </div>
                </div>
                <div class="dashboard-form-col">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Endereço</label>
                <input class="dashboard-form-input" type="text" name="endereco" placeholder="Rua, bairro, município, Luanda" value="{{ old('endereco', $definicoes['endereco'] ?? '') }}">
                @error('endereco')<p class="form-error" role="alert">{{ $message }}</p>@enderror
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
                <label class="dashboard-form-label" for="facebook">Facebook (link)</label>
                <input class="dashboard-form-input" type="text" id="facebook" name="facebook" placeholder="https://facebook.com/pigea.ao" value="{{ old('facebook', $definicoes['facebook'] ?? '') }}">
                @error('facebook')<p class="form-error" role="alert">{{ $message }}</p>@enderror
              </div>
              <div class="dashboard-form-group">
                <label class="dashboard-form-label" for="facebook_nome">Nome público da conta (Facebook)</label>
                <input class="dashboard-form-input" type="text" id="facebook_nome" name="facebook_nome" placeholder="pigea.ao" value="{{ old('facebook_nome', $definicoes['facebook_nome'] ?? '') }}">
                @error('facebook_nome')<p class="form-error" role="alert">{{ $message }}</p>@enderror
              </div>
              <div class="dashboard-form-group">
                <label class="dashboard-form-label" for="instagram">Instagram (link)</label>
                <input class="dashboard-form-input" type="text" id="instagram" name="instagram" placeholder="https://instagram.com/pigea.ao" value="{{ old('instagram', $definicoes['instagram'] ?? '') }}">
                @error('instagram')<p class="form-error" role="alert">{{ $message }}</p>@enderror
              </div>
              <div class="dashboard-form-group">
                <label class="dashboard-form-label" for="instagram_nome">Nome público da conta (Instagram)</label>
                <input class="dashboard-form-input" type="text" id="instagram_nome" name="instagram_nome" placeholder="pigea.ao" value="{{ old('instagram_nome', $definicoes['instagram_nome'] ?? '') }}">
                @error('instagram_nome')<p class="form-error" role="alert">{{ $message }}</p>@enderror
              </div>
                </div>
                <div class="dashboard-form-col">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label" for="linkedin">LinkedIn (link)</label>
                <input class="dashboard-form-input" type="text" id="linkedin" name="linkedin" placeholder="https://linkedin.com/company/pigea" value="{{ old('linkedin', $definicoes['linkedin'] ?? '') }}">
                @error('linkedin')<p class="form-error" role="alert">{{ $message }}</p>@enderror
              </div>
              <div class="dashboard-form-group">
                <label class="dashboard-form-label" for="linkedin_nome">Nome público da conta (LinkedIn)</label>
                <input class="dashboard-form-input" type="text" id="linkedin_nome" name="linkedin_nome" placeholder="pigea.ao" value="{{ old('linkedin_nome', $definicoes['linkedin_nome'] ?? '') }}">
                @error('linkedin_nome')<p class="form-error" role="alert">{{ $message }}</p>@enderror
              </div>
              <div class="dashboard-form-group">
                <label class="dashboard-form-label" for="youtube">YouTube (link)</label>
                <input class="dashboard-form-input" type="text" id="youtube" name="youtube" placeholder="https://youtube.com/@pigea" value="{{ old('youtube', $definicoes['youtube'] ?? '') }}">
                @error('youtube')<p class="form-error" role="alert">{{ $message }}</p>@enderror
              </div>
              <div class="dashboard-form-group">
                <label class="dashboard-form-label" for="youtube_nome">Nome público da conta (YouTube)</label>
                <input class="dashboard-form-input" type="text" id="youtube_nome" name="youtube_nome" placeholder="pigea.ao" value="{{ old('youtube_nome', $definicoes['youtube_nome'] ?? '') }}">
                @error('youtube_nome')<p class="form-error" role="alert">{{ $message }}</p>@enderror
              </div>
                </div>
              </div>
              <p class="helper-text">Os links alimentam o rodapé e a página de contactos; os «nomes públicos» aparecem na caixa «Siga-nos» do site (ex.: <strong>pigea.ao</strong>). Sem link não configurado, a rede não aparece no site.</p>
              <div class="dashboard-form-actions">
                <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Guardar</button>
                
              </div>
          </form>
          </div>
        </div>
        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Aparência nas pesquisas (Google)</h3>
            
          </div>
          <div class="card-body">
<form class="dashboard-form" action="{{ route('admin.configuracoes.seo') }}" method="post">
              @csrf
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Título que aparece nas pesquisas</label>
                <input class="dashboard-form-input" type="text" name="meta_title" placeholder="Título que aparece no Google (até 60 caracteres)" value="{{ old('meta_title', $definicoes['meta_title'] ?? '') }}">
                @error('meta_title')<p class="form-error" role="alert">{{ $message }}</p>@enderror
              </div>
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Descrição que aparece nas pesquisas</label>
                <textarea class="dashboard-form-textarea" name="meta_description" rows="2" placeholder="Descrição que aparece no Google (até 160 caracteres)">{{ old('meta_description', $definicoes['meta_description'] ?? '') }}</textarea>
                @error('meta_description')<p class="form-error" role="alert">{{ $message }}</p>@enderror
              </div>
              <div class="dashboard-form-actions">
                <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Guardar</button>
                
              </div>
          </form>
          </div>
        </div>
@endsection