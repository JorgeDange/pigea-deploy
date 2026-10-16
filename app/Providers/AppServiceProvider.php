<?php

namespace App\Providers;

use App\Models\Job;
use App\Models\News;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Os comandos artisan não têm de consultar a base de dados para
        // partilhar variáveis de vista (ex.: migrate antes das tabelas
        // existirem). Os testes, mesmo sendo CLI, precisam delas.
        if ($this->app->runningInConsole() && ! $this->app->runningUnitTests()) {
            return;
        }

        // O protótipo aponta todos os links de destaque (ticker, widgets e
        // blocos laterais) para a mesma página de artigo. Mantemos o mesmo
        // comportamento, mas com um slug real — a ligação item a item vem na
        // Fase 10.
        View::share('featuredNewsSlug', $this->featuredSlug(News::class));
        View::share('featuredProjectSlug', $this->featuredSlug(Project::class));
        View::share('featuredJobSlug', $this->featuredSlug(Job::class));

        // Itens dos widgets laterais (mesma lista em todas as páginas, como no
        // protótipo, onde o widget é idêntico em todos os HTML).
        View::share('widgetNews', $this->widgetNews());

        // Listas de cartões `list-post` (relacionados, destaques, categorias).
        // A home usa 11 itens distintos (skip/take por bloco), por isso o limite
        // tem de cobrir o maior bloco + os saltos que o precedem.
        View::share('listNews', $this->rows(News::class, 12));
        View::share('listProjects', $this->rows(Project::class, 12));
        View::share('listJobs', $this->rows(Job::class, 12));
    }

    /**
     * Lista partilhada pelos widgets `post-widget-item`.
     */
    private function widgetNews()
    {
        try {
            return News::published()->with(['category', 'author'])->orderBy('id')->take(8)->get();
        } catch (Throwable) {
            return collect();
        }
    }

    /**
     * Lista partilhada pelos cartões `list-post`.
     */
    private function rows(string $model, int $limit)
    {
        try {
            return $model::published()->with(['category', 'author'])->orderBy('id')->take($limit)->get();
        } catch (Throwable) {
            return collect();
        }
    }

    /**
     * Primeiro slug publicado de um módulo, com o slug de demonstração do
     * protótipo como recurso quando a tabela ainda não existe.
     */
    private function featuredSlug(string $model): string
    {
        try {
            return $model::published()->orderBy('id')->value('slug') ?? 'amostra';
        } catch (Throwable) {
            return 'amostra';
        }
    }
}
