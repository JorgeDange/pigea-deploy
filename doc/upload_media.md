# 📘 Documentação de Implementação — Upload de Imagens com Spatie Media Library no PIGEA

**Guia Técnico Completo para Configuração, Upload e Otimização de Imagens**

---

## 📋 Informações do Documento

| Campo | Valor |
| :--- | :--- |
| **Versão** | 1.0 |
| **Data** | 10 de Novembro de 2026 |
| **Ficheiro** | `DOCUMENTACAO_UPLOAD_IMAGENS_PIGEA.md` |
| **Projeto** | PIGEA — Portal de Notícias |
| **Stack** | Laravel 12 + Livewire 3 + Spatie Media Library + Intervention Image |

---

## 🎯 1. Objetivo

Esta documentação descreve a implementação completa do sistema de upload de imagens para as notícias do PIGEA, utilizando o pacote **Spatie Laravel Media Library** para garantir:

- **Upload simplificado** para supervisores e empresas
- **Conversão automática para WebP** (imagens mais leves)
- **Redimensionamento automático** (tamanhos consistentes)
- **Organização em coleções** (capa, contexto, documentos)
- **Armazenamento seguro** em disco dedicado

---

## 📦 2. Pré-requisitos

### 2.1 Requisitos do Servidor

| Requisito | Versão Mínima | Nota |
| :--- | :--- | :--- |
| PHP | 8.2+ | Necessário para Laravel 12 |
| Extensão GD | Habilitada | Para processamento de imagens  |
| Extensão Imagick | Opcional | Alternativa mais rápida ao GD  |
| Composer | 2.x | Gestão de dependências |

### 2.2 Verificar Extensões

```bash
php -m | grep -E "gd|imagick"
```

Se o GD não estiver habilitado, ativar no `php.ini`:
```ini
extension=gd
```

---

## 🚀 3. Instalação

### 3.1 Instalar Pacotes

```bash
composer require spatie/laravel-medialibrary
```

O pacote `intervention/image` é instalado automaticamente como dependência transitiva .

### 3.2 Publicar Migração

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan migrate
```

Isto cria a tabela `media` na base de dados .

### 3.3 Publicar Configuração (Opcional)

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-config"
```

Editar `config/media-library.php` conforme necessário .

---

## ⚙️ 4. Configuração

### 4.1 Disco Dedicado para Mídia

Em `config/filesystems.php`, adicionar o disco `media`:

```php
'disks' => [
    // ...
    'media' => [
        'driver' => 'local',
        'root' => public_path('media'),
        'url' => env('APP_URL').'/media',
        'visibility' => 'public',
    ],
],
```

### 4.2 Configurar o Media Library

Em `config/media-library.php`, ajustar:

```php
return [
    // Disco usado por padrão
    'disk_name' => 'media',

    // Tamanho máximo do ficheiro (2 MB)
    'max_file_size' => 1024 * 1024 * 2,

    // Driver de imagem (gd ou imagick)
    'image_driver' => env('IMAGE_DRIVER', 'gd'),

    // Processar conversões em fila (false para síncrono)
    'queue_conversions_by_default' => false,
];
```

**Nota:** `queue_conversions_by_default => false` faz as conversões correrem imediatamente, sem necessidade de worker de fila. Para produção com muitos uploads, considerar `true` e configurar queue worker .

### 4.3 Criar Link Simbólico

Se usar o disco `public` padrão do Laravel:

```bash
php artisan storage:link
```

Se usar o disco `media` (recomendado), o link não é necessário porque aponta diretamente para `public/media/`.

---

## 🏗️ 5. Configuração do Model `News`

### 5.1 Implementar Interface

No ficheiro `app/Models/News.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class News extends Model implements HasMedia
{
    use InteractsWithMedia;

    // ... resto do modelo
}
```

**Importante:** A interface `HasMedia` e o trait `InteractsWithMedia` são obrigatórios para associar ficheiros ao modelo .

### 5.2 Definir Coleções

No mesmo modelo, adicionar o método `registerMediaCollections()`:

```php
public function registerMediaCollections(): void
{
    // Imagem de capa (apenas uma)
    $this->addMediaCollection('capa')
        ->singleFile()
        ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

    // Imagens do contexto da notícia (múltiplas)
    $this->addMediaCollection('contexto')
        ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

    // Documentos anexados (PDFs)
    $this->addMediaCollection('documentos')
        ->acceptsMimeTypes(['application/pdf']);
}
```

**Coleções definidas:**
- `capa` — imagem principal (única) 
- `contexto` — imagens adicionais no corpo da notícia
- `documentos` — PDFs para download

### 5.3 Definir Conversões (Otimização)

Adicionar o método `registerMediaConversions()`:

```php
public function registerMediaConversions(?Media $media = null): void
{
    // Capa otimizada (WebP, 1200px)
    $this->addMediaConversion('capa-webp')
        ->width(1200)
        ->format('webp')
        ->quality(80)
        ->performOnCollections('capa')
        ->nonQueued();

    // Contexto otimizado (WebP, 800px)
    $this->addMediaConversion('contexto-webp')
        ->width(800)
        ->format('webp')
        ->quality(75)
        ->performOnCollections('contexto')
        ->nonQueued();

    // Miniatura para listagens (WebP, 400px)
    $this->addMediaConversion('thumb')
        ->width(400)
        ->format('webp')
        ->quality(70)
        ->performOnCollections('capa', 'contexto')
        ->nonQueued();
}
```

**Explicação:**
- `width()` — redimensiona mantendo proporção
- `format('webp')` — converte para WebP 
- `quality()` — qualidade da compressão (0-100)
- `nonQueued()` — executa imediatamente (sem fila) 

---

## 📤 6. Upload com Livewire

### 6.1 Criar Componente

```bash
php artisan make:livewire Admin/NewsForm
```

### 6.2 Implementação do Componente

Ficheiro `app/Livewire/Admin/NewsForm.php`:

```php
<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\News;

class NewsForm extends Component
{
    use WithFileUploads;

    public $news;
    public $title = '';
    public $excerpt = '';
    public $content = '';

    // Uploads
    public $capa;
    public $contexto = [];

    // Links alternativos
    public $capa_link = '';

    protected $rules = [
        'title' => 'required|string|max:255',
        'excerpt' => 'required|string|max:500',
        'content' => 'required|string',
        'capa' => 'nullable|image|max:2048',
        'contexto.*' => 'nullable|image|max:2048',
    ];

    public function mount(?News $news = null)
    {
        if ($news && $news->exists) {
            $this->news = $news;
            $this->title = $news->title;
            $this->excerpt = $news->excerpt;
            $this->content = $news->content;
        }
    }

    public function save()
    {
        $this->validate();

        $news = $this->news ?? new News();
        $news->title = $this->title;
        $news->excerpt = $this->excerpt;
        $news->content = $this->content;
        $news->author_id = auth()->id();
        $news->status = 'published';
        $news->published_at = now();
        $news->save();

        // Upload da capa
        if ($this->capa) {
            $news->addMedia($this->capa->getRealPath())
                ->usingFileName(uniqid() . '.webp')
                ->toMediaCollection('capa');
        }

        // Upload das imagens de contexto
        foreach ($this->contexto as $imagem) {
            $news->addMedia($imagem->getRealPath())
                ->usingFileName(uniqid() . '.webp')
                ->toMediaCollection('contexto');
        }

        session()->flash('success', 'Notícia guardada com sucesso!');

        return redirect()->route('admin.news.index');
    }

    public function render()
    {
        return view('livewire.admin.news-form');
    }
}
```

**Pontos-chave:**
- `WithFileUploads` — trait obrigatório do Livewire para uploads
- `$rules` — validação server-side (`image`, `max:2048` = 2MB)
- `addMedia()` — adiciona o ficheiro ao modelo 
- `toMediaCollection()` — finaliza e guarda na coleção 
- `usingFileName()` — define nome único para evitar conflitos

### 6.3 Vista Blade

Ficheiro `resources/views/livewire/admin/news-form.blade.php`:

```blade
<form wire:submit="save" enctype="multipart/form-data">
    {{-- Título --}}
    <div class="form-group">
        <label>Título</label>
        <input type="text" wire:model="title" class="form-control">
        @error('title') <span class="text-danger">{{ $message }}</span> @enderror
    </div>

    {{-- Excerto --}}
    <div class="form-group">
        <label>Excerto</label>
        <textarea wire:model="excerpt" class="form-control"></textarea>
        @error('excerpt') <span class="text-danger">{{ $message }}</span> @enderror
    </div>

    {{-- Conteúdo --}}
    <div class="form-group">
        <label>Conteúdo</label>
        <textarea wire:model="content" class="form-control" rows="10"></textarea>
        @error('content') <span class="text-danger">{{ $message }}</span> @enderror
    </div>

    {{-- Imagem de Capa --}}
    <div class="form-group">
        <label>Imagem de Capa</label>
        <input type="file" wire:model="capa" accept="image/*">
        @error('capa') <span class="text-danger">{{ $message }}</span> @enderror

        {{-- Preview --}}
        @if ($capa)
            <img src="{{ $capa->temporaryUrl() }}" class="img-thumbnail mt-2" style="max-width: 200px;">
        @endif
    </div>

    {{-- Imagens de Contexto --}}
    <div class="form-group">
        <label>Imagens do Contexto</label>
        <input type="file" wire:model="contexto" multiple accept="image/*">
        @error('contexto.*') <span class="text-danger">{{ $message }}</span> @enderror

        @if ($contexto)
            <div class="d-flex flex-wrap gap-2 mt-2">
                @foreach ($contexto as $img)
                    <img src="{{ $img->temporaryUrl() }}" class="img-thumbnail" style="max-width: 100px;">
                @endforeach
            </div>
        @endif
    </div>

    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
        <span wire:loading.remove>Guardar Notícia</span>
        <span wire:loading>A guardar...</span>
    </button>
</form>
```

---

## 🖼️ 7. Exibição das Imagens

### 7.1 Capa (Home e Listagens)

```blade
@if ($news->hasMedia('capa'))
    <img src="{{ $news->getFirstMediaUrl('capa', 'capa-webp') }}"
         alt="{{ $news->title }}"
         class="img-fluid"
         loading="lazy">
@elseif ($news->capa_link)
    <img src="{{ $news->capa_link }}"
         alt="{{ $news->title }}"
         class="img-fluid"
         loading="lazy">
@endif
```

### 7.2 Miniatura (Listagens)

```blade
@if ($news->hasMedia('capa'))
    <img src="{{ $news->getFirstMediaUrl('capa', 'thumb') }}"
         alt="{{ $news->title }}"
         loading="lazy">
@endif
```

### 7.3 Imagens de Contexto (Página da Notícia)

```blade
@foreach ($news->getMedia('contexto') as $imagem)
    <img src="{{ $imagem->getUrl('contexto-webp') }}"
         alt="Imagem de contexto"
         class="img-fluid my-3"
         loading="lazy">
@endforeach
```

---

## 🗑️ 8. Manutenção e Limpeza

### 8.1 Regenerar Conversões

Se alterar as definições de conversão (ex: mudar largura de 1200 para 1000), executar:

```bash
php artisan media-library:regenerate
```

Para um modelo específico:

```bash
php artisan media-library:regenerate "App\Models\News"
```

Para apenas conversões específicas:

```bash
php artisan media-library:regenerate --only=thumb --only=capa-webp
```



### 8.2 Limpeza de Imagens Órfãs

Criar comando Artisan:

```bash
php artisan make:command LimparImagensOrfas
```

Ficheiro `app/Console/Commands/LimparImagensOrfas.php`:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\News;

class LimparImagensOrfas extends Command
{
    protected $signature = 'pigea:limpar-imagens';
    protected $description = 'Remove imagens não usadas há mais de 90 dias';

    public function handle()
    {
        $limite = now()->subDays(90);

        News::where('status', 'draft')
            ->where('updated_at', '<', $limite)
            ->each(function ($news) {
                $news->clearMediaCollection('capa');
                $news->clearMediaCollection('contexto');
                $this->info("Imagens removidas: {$news->title}");
            });

        $this->info('Limpeza concluída.');
    }
}
```

Agendar em `routes/console.php` (Laravel 11+):

```php
Schedule::command('pigea:limpar-imagens')->weekly();
```

---

## ⚠️ 9. Resolução de Problemas Comuns

| Problema | Causa Provável | Solução |
| :--- | :--- | :--- |
| Conversão não funciona | Extensão GD/Imagick ausente | Verificar `php -m` e ativar no `php.ini` |
| Imagem não aparece | Disco mal configurado | Verificar `disk_name` em `config/media-library.php` |
| Erro 413 (Payload Too Large) | Limite do servidor | Ajustar `upload_max_filesize` e `post_max_size` no `php.ini` |
| Conversão lenta | Queue worker ausente | Usar `nonQueued()` ou configurar `QUEUE_CONNECTION` |
| URL da imagem errada | `APP_URL` incorrecto | Verificar `.env` |

---

## ✅ 10. Checklist de Implementação

- [ ] Spatie Media Library instalado
- [ ] Migração executada (tabela `media`)
- [ ] Disco `media` configurado em `filesystems.php`
- [ ] Configuração publicada e ajustada
- [ ] Model `News` implementa `HasMedia`
- [ ] Trait `InteractsWithMedia` adicionado
- [ ] Coleções definidas (`capa`, `contexto`, `documentos`)
- [ ] Conversões definidas (WebP, redimensionamento)
- [ ] Componente Livewire de upload criado
- [ ] Vista Blade do formulário criada
- [ ] Exibição no frontend implementada
- [ ] Comando de limpeza agendado
- [ ] Testado com imagens reais (JPG, PNG)
- [ ] Testado em mobile

