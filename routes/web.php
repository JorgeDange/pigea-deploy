<?php

use App\Models\News;
use App\Http\Controllers\Admin\NewsEditorController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BannerFormController;
use App\Http\Controllers\Admin\ContentFormController;
use App\Http\Controllers\Admin\DocumentFormController;
use App\Http\Controllers\Admin\ProjectFormController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserFormController;
use App\Http\Controllers\CspReportController;
use App\Http\Controllers\Frontend\DirectoryController;
use App\Http\Controllers\Frontend\EventController;
use App\Http\Controllers\Frontend\FragmentController;
use App\Http\Controllers\Frontend\InterviewController;
use App\Http\Controllers\Frontend\LegislationController;
use App\Http\Controllers\Frontend\NewsController;
use App\Http\Controllers\Frontend\ProjectController;
use App\Http\Controllers\Frontend\ResearchController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\Frontend\SupplierController;
use App\Http\Controllers\Frontend\TechnologyController;
use App\Http\Controllers\Frontend\TrainingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas públicas (fase.md §4.7 — Fase 9)
|--------------------------------------------------------------------------
|
| /login, /register, /forgot-password e /reset-password são geridos pelo
| Laravel Fortify e não são redeclarados aqui.
|
*/

Route::view('/', 'home')->name('home');

// Notícias
Route::get('/noticias', [NewsController::class, 'index'])->name('news.index');
Route::get('/noticias/{slug}', [NewsController::class, 'show'])->name('news.show');

// Fragmentos AJAX dos separadores da home — os mesmos URLs `ajax/noticias-*.html`
// do protótipo (o `scripts.js` original continua a pedi-los por AJAX).
Route::get('/ajax/{arquivo}', [FragmentController::class, 'show'])
    ->where('arquivo', 'noticias-(mineracao|agua|ambiente|investigacao)\.html')
    ->name('fragments.show');

// Projetos
Route::get('/projetos', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projetos/{slug}', [ProjectController::class, 'show'])->name('projects.show');

// Diretórios de conteúdo
Route::get('/tecnologias', [TechnologyController::class, 'index'])->name('technologies.index');
Route::get('/fornecedores', [SupplierController::class, 'index'])->name('suppliers.index');
// Directório (restrutura.md §6 — ortografia do cliente). O URL antigo
// /diretorio (uma r) redireciona para não partir links já indexados.
Route::get('/directorio', [DirectoryController::class, 'index'])->name('directory.index');
Route::redirect('/diretorio', '/directorio', 301);

// Formação
Route::get('/formacao', [TrainingController::class, 'index'])->name('training.index');

// Recursos
Route::get('/legislacao', [LegislationController::class, 'index'])->name('legislation.index');
Route::get('/eventos', [EventController::class, 'index'])->name('events.index');
Route::get('/investigacao', [ResearchController::class, 'index'])->name('research.index');
Route::get('/entrevistas', [InterviewController::class, 'index'])->name('interviews.index');

// Pesquisa — todos os campos `se` do protótipo apontavam para `#`.
Route::get('/pesquisa', [SearchController::class, 'index'])->name('search.index');

// Institucional
Route::view('/sobre', 'about')->name('about');
Route::view('/contactos', 'contacts')->name('contacts');

// Registo público no Diretório (restrutura.md §4 — sem login; a submissão
// cria o registo com status `pendente` para aprovação no /admin). As vistas
// embutem os componentes Livewire RegistoEmpresa/RegistoProfissional.
// throttle:30,1 — anti-spam por IP (sem captcha; a moderação fica no /admin).
Route::view('/registo-empresa', 'registo-empresa')->middleware('throttle:30,1')->name('registo-empresa');
Route::view('/registo-profissional', 'registo-profissional')->middleware('throttle:30,1')->name('registo-profissional');

// Página 404 do protótipo — é também a vista usada pelo handler de exceções.
Route::view('/404', 'errors.404')->name('errors.404');

// Endpoint dos relatórios da CSP Report-Only (SecurityHeaders). CSRF desligado
// (o POST vem do navegador, sem token) e com throttle para não inundar o log.
Route::post('/csp-report', CspReportController::class)
    ->middleware('throttle:60,1')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
    ->name('csp.report');

/*
|--------------------------------------------------------------------------
| Painel admin (fase.md §4.7 — Fase 9 / plan.md §5)
|--------------------------------------------------------------------------
|
| Só com sessão iniciada e role `admin` (§3 — EnsureUserHasRole). As restantes
| páginas do protótipo `admin/*.html` entram aqui à medida que forem migradas.
|
| O prefixo vem de `ADMIN_PATH` (.env) para não expor o caminho óbvio
| "/admin" a varrimentos automatizados — ver `config/app.php`. Os NOMES das
| rotas ficam `admin.*` para que vistas e controladores não mudem.
|
| Obscurecimento, não barreira: o middleware `role:admin` continua a ser quem
| decide o acesso. O caminho antigo passa a devolver 404 (sem redirect, que
| denunciaria o novo caminho).
|
*/
// A equipa interna (restrutura.md: roles apenas admin + editor) entra no
// painel; o `can:` granular de cada rota decide o que cada role vê
// (editor: conteúdo + documentos/banners; moderação/utilizadores/configs
// continuam exclusivos do admin — Fase 7, ModeracaoDiretorioTest).
Route::middleware(['auth', 'role:admin,editor'])->prefix(config('app.admin_path'))->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::view('/noticias', 'admin.noticias')->middleware('can:news.manage')->name('noticias');
    Route::view('/eventos', 'admin.eventos')->middleware('can:event.manage')->name('eventos');
    Route::view('/investigacao', 'admin.investigacao')->middleware('can:research.manage')->name('investigacao');
    Route::view('/entrevistas', 'admin.entrevistas')->middleware('can:interview.manage')->name('entrevistas');
    Route::view('/legislacao', 'admin.legislacao')->middleware('can:legislation.manage')->name('legislacao');
    Route::view('/projetos', 'admin.projetos')->middleware('can:project.manage')->name('projetos');
    Route::view('/empresas', 'admin.empresas')->middleware('can:company.moderate')->name('empresas');
    Route::view('/utilizadores', 'admin.utilizadores')->middleware('can:user.manage')->name('utilizadores');
    // Modal "Adicionar utilizador": no protótipo o `<form>` não tinha `action`
    // (o botão era `type="button"` morto no rodapé do modal). Mesma permissão
    // da própria lista — quem pode ver, pode criar.
    Route::post('/utilizadores/novo', [UserFormController::class, 'store'])
        ->middleware('can:user.manage')
        ->name('utilizador.novo');

    /**
     * Editor de notícias (cms_pro.md §4.2 — "o ecrã mais importante").
     * Blade tradicional (sem Livewire) para evitar o bug de morph incremental
     * do Livewire destruir o DOM do TinyMCE.
     */
    Route::get('/noticias/nova', [NewsEditorController::class, 'create'])
        ->middleware('can:news.manage')
        ->name('noticia-nova');
    Route::post('/noticias/nova', [NewsEditorController::class, 'store'])
        ->middleware('can:news.manage')
        ->name('noticia-editor.store');
    Route::get('/noticias/{noticia}/editar', [NewsEditorController::class, 'edit'])
        ->middleware('can:news.manage')
        ->whereNumber('noticia')
        ->name('noticia-editar');
    Route::put('/noticias/{noticia}', [NewsEditorController::class, 'update'])
        ->middleware('can:news.manage')
        ->whereNumber('noticia')
        ->name('noticia-editor.update');
    Route::get('/projetos/novo', [ProjectFormController::class, 'create'])->middleware('can:project.manage')->name('projeto-novo');
    Route::post('/projetos/novo', [ProjectFormController::class, 'store'])->middleware('can:project.manage')->name('projeto-novo.store');

    // Configurações — os 5 cartões com `<form>` do protótipo; o cartão
    // "Idiomas" não tem form e continua visual (ver SettingsController).
    Route::get('/configuracoes', [SettingsController::class, 'index'])->middleware('can:settings.manage')->name('configuracoes');
    Route::post('/configuracoes/geral', [SettingsController::class, 'geral'])->middleware('can:settings.manage')->name('configuracoes.geral');
    Route::post('/configuracoes/contactos', [SettingsController::class, 'contactos'])->middleware('can:settings.manage')->name('configuracoes.contactos');
    Route::post('/configuracoes/redes', [SettingsController::class, 'redes'])->middleware('can:settings.manage')->name('configuracoes.redes');
    Route::post('/configuracoes/seo', [SettingsController::class, 'seo'])->middleware('can:settings.manage')->name('configuracoes.seo');
    Route::post('/configuracoes/email', [SettingsController::class, 'email'])->middleware('can:settings.manage')->name('configuracoes.email');

    /*
    |----------------------------------------------------------------------
    | Reestruturação (restrutura.md §5.3-5.6) — Diretório e Publicidade
    |----------------------------------------------------------------------
    |
    | Empresas: a página admin.empresas passou a moderar o novo modelo
    | `Company` (componente Livewire CompaniesTable) — submissão pública →
    | pendente → aprovar/rejeitar. Profissionais: mesmo mecanismo.
    | Documentos e Banners: CRUD completo (equipa interna apenas).
    |
    | ANTES das rotas genéricas `/{tipo}/novo` para ganhar o matching
    | (os whereIn do genérico não incluem estes tipos, mas a ordem explícita
    | evita depender do comportamento de constraint falhada).
    |
    */
    Route::view('/profissionais', 'admin.profissionais')->middleware('can:company.moderate')->name('profissionais');

    Route::view('/documentos', 'admin.documentos')->middleware('can:document.manage')->name('documentos');
    Route::get('/documentos/novo', [DocumentFormController::class, 'create'])->middleware('can:document.manage')->name('documentos.novo');
    Route::post('/documentos/novo', [DocumentFormController::class, 'store'])->middleware('can:document.manage')->name('documentos.novo.store');
    Route::get('/documentos/{id}/editar', [DocumentFormController::class, 'edit'])->middleware('can:document.manage')->whereNumber('id')->name('documentos.editar');
    Route::put('/documentos/{id}', [DocumentFormController::class, 'update'])->middleware('can:document.manage')->whereNumber('id')->name('documentos.atualizar');

    Route::view('/banners', 'admin.banners')->middleware('can:banner.manage')->name('banners');
    Route::get('/banners/novo', [BannerFormController::class, 'create'])->middleware('can:banner.manage')->name('banners.novo');
    Route::post('/banners/novo', [BannerFormController::class, 'store'])->middleware('can:banner.manage')->name('banners.novo.store');
    Route::get('/banners/{id}/editar', [BannerFormController::class, 'edit'])->middleware('can:banner.manage')->whereNumber('id')->name('banners.editar');
    Route::put('/banners/{id}', [BannerFormController::class, 'update'])->middleware('can:banner.manage')->whereNumber('id')->name('banners.atualizar');

    /*
    |----------------------------------------------------------------------
    | Formulários genéricos (ContentFormController)
    |----------------------------------------------------------------------
    |
    | Criação dos 4 tipos que no protótipo só tinham botão morto ("Novo
    | evento" era `type="submit"` sem `<form>` à volta) e edição de todos os
    | tipos da tabela — o botão caneta era renderizado sem `wire:click` nem
    | link, por isso não fazia nada.
    |
    | Estão NO FIM do grupo de propósito: as rotas explícitas `/noticias/nova`
    | e `/projetos/novo` continuam a ganhar o matching (e os `whereIn` nem
    | incluem esses tipos, porque a criação deles vive nos controladores
    | próprios).
    |
    | Não levam `can:` na rota: a permissão varia com o `$tipo` da URL, por
    | isso o `Gate::authorize` está dentro do controlador — mesma defesa em
    | profundidade que o `ContentTable` já usava no endpoint Livewire.
    |
    */
    Route::get('/{tipo}/novo', [ContentFormController::class, 'create'])
        ->whereIn('tipo', ContentFormController::tiposCriaveis())
        ->name('conteudo.novo');
    Route::post('/{tipo}/novo', [ContentFormController::class, 'store'])
        ->whereIn('tipo', ContentFormController::tiposCriaveis())
        ->name('conteudo.novo.store');
    Route::get('/{tipo}/{id}/editar', [ContentFormController::class, 'edit'])
        ->whereIn('tipo', ContentFormController::tiposEditaveis())
        ->whereNumber('id')
        ->name('conteudo.editar');
    Route::put('/{tipo}/{id}', [ContentFormController::class, 'update'])
        ->whereIn('tipo', ContentFormController::tiposEditaveis())
        ->whereNumber('id')
        ->name('conteudo.atualizar');
});
