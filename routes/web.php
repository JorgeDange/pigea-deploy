<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ContentFormController;
use App\Http\Controllers\Admin\NewsFormController;
use App\Http\Controllers\Admin\ProjectFormController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserFormController;
use App\Http\Controllers\Auth\CompanyRegisterController;
use App\Http\Controllers\CspReportController;
use App\Http\Controllers\Painel\EmpresaController;
use App\Http\Controllers\Painel\UtilizadorController;
use App\Http\Controllers\Frontend\DirectoryController;
use App\Http\Controllers\Frontend\EventController;
use App\Http\Controllers\Frontend\FragmentController;
use App\Http\Controllers\Frontend\InterviewController;
use App\Http\Controllers\Frontend\JobController;
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
Route::get('/diretorio', [DirectoryController::class, 'index'])->name('directory.index');

// Emprego e formação
Route::get('/empregos', [JobController::class, 'index'])->name('jobs.index');
Route::get('/empregos/{slug}', [JobController::class, 'show'])->name('jobs.show');
// Formulário "Formulário de Candidatura" de `emprego-single.html` (público:
// visitante sem sessão candidata-se com nome+email; autenticado fica ligado
// à conta).
Route::post('/empregos/{slug}/candidatura', [JobController::class, 'candidatar'])->name('jobs.candidatura');
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

// Registo de empresa (o registo de utilizador normal é o do Fortify, em /register)
Route::view('/registo-empresa', 'auth.company-register')->name('company.register');
Route::post('/registo-empresa', [CompanyRegisterController::class, 'store'])->name('company.register.store');

// Página 404 do protótipo — é também a vista usada pelo handler de exceções.
Route::view('/404', 'errors.404')->name('errors.404');

// Endpoint dos relatórios da CSP Report-Only (SecurityHeaders). CSRF desligado
// (o POST vem do navegador, sem token) e com throttle para não inundar o log.
Route::post('/csp-report', CspReportController::class)
    ->middleware('throttle:60,1')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
    ->name('csp.report');

// Painéis público-privados (fase.md §4.7 — Fases 9–10). O `RoleBasedHomeResponse`
// já remete para /painel, /painel-empresa ou /admin consoante a role.
//
// Fase 13: além do `role:` do grupo, cada rota exige a permissão Spatie
// concreta (`can:`) — o papel abre a porta, a permissão autoriza a ação.
Route::middleware(['auth', 'role:user'])->prefix('painel')->name('user.')->group(function () {
    Route::get('/', [UtilizadorController::class, 'dashboard'])->name('dashboard');
    Route::get('/perfil', [UtilizadorController::class, 'perfil'])->middleware('can:profile.manage')->name('perfil');
    Route::post('/perfil', [UtilizadorController::class, 'guardarPerfil'])->middleware('can:profile.manage')->name('perfil.guardar');
    Route::get('/candidaturas', function () { return view('painel-utilizador.candidaturas'); })->middleware('can:job.apply')->name('candidaturas');
    Route::get('/guardadas', [UtilizadorController::class, 'guardadas'])->middleware('can:job.save')->name('guardadas');
    Route::post('/guardadas/remover/{job}', [UtilizadorController::class, 'removerGuardada'])->middleware('can:job.save')->name('guardadas.remover');
    Route::post('/guardar/{job}', [UtilizadorController::class, 'alternarGuardada'])->middleware('can:job.save')->name('guardar');
    Route::get('/notificacoes', [UtilizadorController::class, 'notificacoes'])->middleware('can:notification.manage')->name('notificacoes');
    Route::post('/notificacoes/ler-todas', [UtilizadorController::class, 'marcarLidas'])->middleware('can:notification.manage')->name('notificacoes.ler-todas');
    Route::get('/definicoes', [UtilizadorController::class, 'definicoes'])->middleware('can:profile.manage')->name('definicoes');
    Route::post('/definicoes/seguranca', [UtilizadorController::class, 'guardarSeguranca'])->middleware('can:profile.manage')->name('definicoes.seguranca');
    Route::post('/definicoes/privacidade', [UtilizadorController::class, 'guardarPrivacidade'])->middleware('can:profile.manage')->name('definicoes.privacidade');
});

Route::middleware(['auth', 'role:company'])->prefix('painel-empresa')->name('company.')->group(function () {
    Route::get('/', [EmpresaController::class, 'dashboard'])->name('dashboard');
    Route::get('/perfil', [EmpresaController::class, 'perfil'])->middleware('can:company.profile')->name('perfil');
    Route::post('/perfil', [EmpresaController::class, 'guardarPerfil'])->middleware('can:company.profile')->name('perfil.guardar');
    Route::get('/vagas', function () { return view('painel-empresa.vagas'); })->middleware('can:job.manage')->name('vagas');
    Route::get('/vagas/nova', [EmpresaController::class, 'vagaNova'])->middleware('can:job.manage')->name('vaga-nova');
    Route::post('/vagas/nova', [EmpresaController::class, 'guardarVaga'])->middleware('can:job.manage')->name('vaga-nova.guardar');
    Route::post('/vagas/eliminar/{job}', [EmpresaController::class, 'eliminarVaga'])->middleware('can:job.manage')->name('vagas.eliminar');
    Route::get('/candidaturas', function () { return view('painel-empresa.candidaturas'); })->middleware('can:application.receive')->name('candidaturas');
    Route::get('/oportunidades', [EmpresaController::class, 'oportunidades'])->middleware('can:opportunity.manage')->name('oportunidades');
    Route::post('/oportunidades/eliminar/{oportunidade}', [EmpresaController::class, 'eliminarOportunidade'])->middleware('can:opportunity.manage')->name('oportunidades.eliminar');
    Route::get('/estatisticas', [EmpresaController::class, 'estatisticas'])->name('estatisticas');
    Route::get('/definicoes', [EmpresaController::class, 'definicoes'])->middleware('can:company.profile')->name('definicoes');
    Route::post('/definicoes/seguranca', [EmpresaController::class, 'guardarSeguranca'])->middleware('can:company.profile')->name('definicoes.seguranca');
});

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
Route::middleware(['auth', 'role:admin'])->prefix(config('app.admin_path'))->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::view('/noticias', 'admin.noticias')->middleware('can:news.manage')->name('noticias');
    Route::view('/eventos', 'admin.eventos')->middleware('can:event.manage')->name('eventos');
    Route::view('/investigacao', 'admin.investigacao')->middleware('can:research.manage')->name('investigacao');
    Route::view('/entrevistas', 'admin.entrevistas')->middleware('can:interview.manage')->name('entrevistas');
    Route::view('/legislacao', 'admin.legislacao')->middleware('can:legislation.manage')->name('legislacao');
    Route::view('/projetos', 'admin.projetos')->middleware('can:project.manage')->name('projetos');
    Route::view('/empresas', 'admin.empresas')->middleware('can:company.moderate')->name('empresas');
    Route::view('/vagas', 'admin.vagas')->middleware('can:job.moderate')->name('vagas');
    Route::view('/utilizadores', 'admin.utilizadores')->middleware('can:user.manage')->name('utilizadores');
    // Modal "Adicionar utilizador": no protótipo o `<form>` não tinha `action`
    // (o botão era `type="button"` morto no rodapé do modal). Mesma permissão
    // da própria lista — quem pode ver, pode criar.
    Route::post('/utilizadores/novo', [UserFormController::class, 'store'])
        ->middleware('can:user.manage')
        ->name('utilizador.novo');

    // Páginas de criação (Fase 8): o `<form>` do protótipo não tinha `action`;
    // aqui validam e criam o registo (as duas únicas páginas de formulário do
    // painel admin — não existe página de edição no protótipo).
    Route::get('/noticias/nova', [NewsFormController::class, 'create'])->middleware('can:news.manage')->name('noticia-nova');
    Route::post('/noticias/nova', [NewsFormController::class, 'store'])->middleware('can:news.manage')->name('noticia-nova.store');
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
